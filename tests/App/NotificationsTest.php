<?php
declare(strict_types=1);
namespace Agca\Tests\App;

use Agca\App\App;
use Agca\App\Repository\JournalRepository;
use Agca\App\Service\Mailer;
use Agca\App\Service\Notifications;

final class NotificationsTest extends DbTestCase
{
    private function rencontre(): array
    {
        return ['id' => 42, 'division_libelle' => 'DIV2/POULE B', 'serie_code' => 'M', 'journee_phase' => 'aller', 'journee_numero' => 1,
            'date_calendrier' => '2026-09-26', 'date_reelle' => '2026-09-26', 'reportee' => 0, 'statut' => 'enregistree',
            'recevant_nom' => 'SALON', 'recevant_email' => 'salon@test', 'invite_nom' => 'FREGATE', 'invite_email' => 'fregate@test',
            'total_pour' => 18, 'total_contre' => 12, 'pts_rencontre_pour' => '3.0', 'pts_rencontre_contre' => '1.0', 'bonus_invite' => '0.0',
            'alertes' => json_encode(['FEUILLE_INCOMPLETE']), 'forfaitaire_id' => null, 'recevant_id' => 1, 'invite_id' => 2];
    }

    public function testModeSimuleJournalise(): void
    {
        $app = new App($this->config());
        $app->service(Notifications::class)->feuilleEnregistree($this->rencontre(), false, ['id' => 7, 'identifiant' => 'SALON']);
        $j = $app->service(JournalRepository::class)->recents(5);
        self::assertSame('mail_simule', $j[0]['action']);
        $d = json_decode($j[0]['detail'], true);
        self::assertSame('fregate@test', $d['a']);
        self::assertSame(['admin@test'], $d['cc']);
        self::assertStringContainsString('SALON', $d['sujet']);
        self::assertStringContainsString('18 - 12', $d['texte']);
        self::assertStringContainsString('http://test/rencontre/42', $d['texte']);
        self::assertStringContainsString('Feuille incomplète', $d['texte']);
    }

    public function testForfaitPreviensLesDeuxCapitaines(): void
    {
        $app = new App($this->config());
        $app->service(Notifications::class)->forfait(array_merge($this->rencontre(), ['forfaitaire_id' => 2]), false, null);
        $d = $app->service(Mailer::class)->dernierEnvoi();
        self::assertSame(['salon@test', ['fregate@test', 'admin@test']], [$d['a'], $d['cc']]);
        self::assertStringContainsString('FREGATE', $d['texte']);
    }

    public function testRelanceEtReport(): void
    {
        $app = new App($this->config());
        $n = $app->service(Notifications::class);
        self::assertTrue($n->relance($this->rencontre()));
        self::assertSame('salon@test', $app->service(Mailer::class)->dernierEnvoi()['a']);
        $n->report(array_merge($this->rencontre(), ['date_reelle' => '2026-10-03', 'reportee' => 1]), '2026-09-26', null);
        self::assertStringContainsString('sam. 3 oct. 2026', $app->service(Mailer::class)->dernierEnvoi()['texte']);
    }

    public function testDestinataireVideNEnvoiePasMaisJournalise(): void
    {
        $app = new App($this->config());
        self::assertTrue($app->service(Notifications::class)->relance(array_merge($this->rencontre(), ['recevant_email' => null])));
        self::assertSame('admin@test', $app->service(Mailer::class)->dernierEnvoi()['a']);
    }
}
