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
    private function envoyer(array $remplacements = [], string $ip = '127.0.0.1'): array
    {
        return $this->app->service(Contact::class)->envoyer($remplacements + self::VALIDE, $this->app->session(), $ip);
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
        self::assertSame([], $mail['cc'], 'aucune copie au demandeur');
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

    /**
     * Passé le délai de session ET le délai IP (l'horloge de la session est avancée manuellement,
     * la ligne IP correspondante aussi : sans cela le plafond IP de 60 s bloquerait l'envoi).
     */
    public function testNouvelEnvoiApresLeDelai(): void
    {
        self::assertTrue($this->envoyer()['ok']);
        $this->app->session()->set('contact_dernier', time() - 120);
        $this->db->exec('UPDATE agca_contact_limite SET quand = ? WHERE ip = ?', [date('Y-m-d H:i:s', time() - 120), '127.0.0.1']);
        self::assertSame(['ok' => true, 'erreurs' => []], $this->envoyer(['objet' => 'Deuxième message']));
        self::assertSame('[AGCA contact] Deuxième message', $this->dernierMail()['sujet']);
        self::assertCount(2, $this->journalContact());
    }

    /** Contournement par jet du cookie de session : la même IP reste bloquée. */
    public function testMemeIpDeuxSessionsRefuse(): void
    {
        self::assertTrue($this->envoyer()['ok']);
        $_SESSION = []; // nouvelle session : le cookie a été jeté
        $r = $this->envoyer(['objet' => 'Deuxième message']);
        self::assertFalse($r['ok']);
        self::assertStringContainsString('Trop de messages', $r['erreurs'][0]);
        self::assertCount(1, $this->journalContact());
    }

    /** Une IP différente n'est pas concernée par la limitation de la première. */
    public function testIpDifferenteAccepte(): void
    {
        self::assertTrue($this->envoyer()['ok']);
        $_SESSION = [];
        $r = $this->envoyer(['objet' => 'Deuxième message'], '203.0.113.9');
        self::assertTrue($r['ok']);
        self::assertCount(2, $this->journalContact());
    }

    /** 5 envois espacés sur 24 h pour une même IP : le 6e est refusé même hors du délai de 60 s. */
    public function testPlafondIpSur24Heures(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->db->exec('INSERT INTO agca_contact_limite (ip, quand) VALUES (?, ?)', ['198.51.100.7', date('Y-m-d H:i:s', time() - 3600 * ($i + 2))]);
        }
        $r = $this->envoyer([], '198.51.100.7');
        self::assertFalse($r['ok']);
        self::assertStringContainsString('Trop de messages', $r['erreurs'][0]);
        self::assertSame([], $this->journalContact());
    }

    /** Plafond global de 40 messages sur 24 h, toutes IP confondues. */
    public function testPlafondGlobalSur24Heures(): void
    {
        for ($i = 0; $i < 40; $i++) {
            $this->db->exec('INSERT INTO agca_contact_limite (ip, quand) VALUES (?, ?)', ['192.0.2.' . $i, date('Y-m-d H:i:s', time() - 3600)]);
        }
        $r = $this->envoyer([], '203.0.113.55');
        self::assertFalse($r['ok']);
        self::assertStringContainsString('Trop de messages', $r['erreurs'][0]);
        self::assertSame([], $this->journalContact());
    }
}
