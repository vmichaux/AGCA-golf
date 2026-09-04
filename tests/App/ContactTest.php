<?php
declare(strict_types=1);
namespace Agca\Tests\App;

use Agca\App\App;
use Agca\App\Repository\JournalRepository;
use Agca\App\Service\Contact;
use Agca\App\Service\Mailer;

final class ContactTest extends DbTestCase
{
    /** Champs d'un envoi valide ; les tests remplacent le champ qu'ils veulent éprouver. */
    private const VALIDE = [
        'nom' => 'Jean Dupont',
        'email' => 'jean.dupont@example.org',
        'objet' => 'Engager une équipe',
        'message' => 'Notre club souhaite engager une équipe la saison prochaine.',
        'site_web' => '',
    ];

    private App $app;

    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
        $this->app = new App($this->config());
    }

    /** @param array<string, string> $remplacements */
    private function envoyer(array $remplacements = []): array
    {
        return $this->app->service(Contact::class)->envoyer($remplacements + self::VALIDE, $this->app->session());
    }

    private function dernierMail(): ?array
    {
        return $this->app->service(Mailer::class)->dernierEnvoi();
    }

    /** @return list<array<string, mixed>> lignes `contact_envoye` du journal */
    private function journalContact(): array
    {
        $lignes = array_filter($this->app->service(JournalRepository::class)->recents(), fn(array $l) => $l['action'] === 'contact_envoye');
        return array_values($lignes);
    }

    public function testEnvoiValide(): void
    {
        self::assertSame(['ok' => true, 'erreurs' => []], $this->envoyer());

        $mail = $this->dernierMail();
        self::assertNotNull($mail);
        self::assertSame('admin@test', $mail['a']);
        self::assertSame(['jean.dupont@example.org'], $mail['cc'], 'le demandeur reçoit une copie');
        self::assertSame('[AGCA contact] Engager une équipe', $mail['sujet']);
        self::assertStringContainsString('Jean Dupont', $mail['texte']);
        self::assertStringContainsString('Notre club souhaite engager une équipe', $mail['texte']);
    }

    public function testEnvoiJournaliseSansLeMessage(): void
    {
        $this->envoyer();
        $lignes = $this->journalContact();
        self::assertCount(1, $lignes);
        $detail = json_decode((string) $lignes[0]['detail'], true);
        self::assertSame(['nom' => 'Jean Dupont', 'objet' => 'Engager une équipe'], $detail);
    }

    public function testEmailInvalide(): void
    {
        $r = $this->envoyer(['email' => 'jean.dupont(at)example.org']);
        self::assertFalse($r['ok']);
        self::assertNotSame([], $r['erreurs']);
        self::assertNull($this->dernierMail());
        self::assertSame([], $this->journalContact());
    }

    public function testChampsObligatoires(): void
    {
        $r = $this->envoyer(['nom' => '  ', 'objet' => '']);
        self::assertFalse($r['ok']);
        self::assertCount(2, $r['erreurs']);
        self::assertNull($this->dernierMail());
    }

    public function testMessageTropCourt(): void
    {
        $r = $this->envoyer(['message' => 'Bonjour.']);
        self::assertFalse($r['ok']);
        self::assertNotSame([], $r['erreurs']);
        self::assertNull($this->dernierMail());
    }

    /** Robot : le champ piège est rempli — succès apparent, aucun envoi, aucun journal. */
    public function testPiegeAntiRobot(): void
    {
        self::assertSame(['ok' => true, 'erreurs' => []], $this->envoyer(['site_web' => 'http://exemple.test']));
        self::assertNull($this->dernierMail());
        self::assertSame([], $this->journalContact());
    }

    public function testUneSoumissionParMinute(): void
    {
        self::assertTrue($this->envoyer()['ok']);
        $r = $this->envoyer(['objet' => 'Deuxième message']);
        self::assertFalse($r['ok']);
        self::assertCount(1, $r['erreurs']);
        self::assertStringContainsString('minute', $r['erreurs'][0]);
        self::assertCount(1, $this->journalContact());
        self::assertSame('[AGCA contact] Engager une équipe', $this->dernierMail()['sujet'], 'le deuxième message n\'est pas parti');
    }

    /** Passé le délai, un nouvel envoi est accepté. */
    public function testNouvelEnvoiApresLeDelai(): void
    {
        self::assertTrue($this->envoyer()['ok']);
        $this->app->session()->set('contact_dernier', time() - 120);
        self::assertSame(['ok' => true, 'erreurs' => []], $this->envoyer(['objet' => 'Deuxième message']));
        self::assertSame('[AGCA contact] Deuxième message', $this->dernierMail()['sujet']);
        self::assertCount(2, $this->journalContact());
    }
}
