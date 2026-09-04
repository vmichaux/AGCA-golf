<?php
declare(strict_types=1);
namespace Agca\Tests\App;

use Agca\App\App;
use Agca\App\Repository\DocumentRepository;
use Agca\App\Repository\JournalRepository;
use Agca\App\Repository\PageRepository;
use Agca\App\Service\Documents;

final class DocumentsTest extends DbTestCase
{
    private App $app; private string $dossier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dossier = sys_get_temp_dir() . '/agca-docs-' . uniqid();
        mkdir($this->dossier);
        $cfg = $this->config(); $cfg['app']['documents_dir'] = $this->dossier;
        $this->app = new App($cfg);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dossier . '/*') ?: [] as $f) { unlink($f); }
        @rmdir($this->dossier);
    }

    private function faux(string $contenu, string $nom, int $erreur = UPLOAD_ERR_OK): array
    {
        $tmp = tempnam(sys_get_temp_dir(), 'up'); file_put_contents($tmp, $contenu);
        return ['name' => $nom, 'type' => 'application/octet-stream', 'tmp_name' => $tmp, 'error' => $erreur, 'size' => strlen($contenu)];
    }

    public function testPdfAccepteEtRenomme(): void
    {
        $svc = $this->app->service(Documents::class);
        $r = $svc->televerser($this->faux("%PDF-1.4\n%fake\n", 'Statuts AGCA (2026).pdf'), 'Statuts', 'statuts', null);
        self::assertNull($r['erreur']);
        $d = $this->app->service(DocumentRepository::class)->parId($r['id']);
        self::assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}-statuts-agca-2026-[a-z0-9]{6}\.pdf$/', $d['fichier']);
        self::assertSame(['application/pdf', 'statuts', 'Statuts'], [$d['type_mime'], $d['categorie'], $d['titre']]);
        self::assertFileExists($this->dossier . '/' . $d['fichier']);
        self::assertSame('/documents/' . $d['fichier'], $svc->urlPublique($d));
    }

    public function testTypeRefuse(): void
    {
        $r = $this->app->service(Documents::class)->televerser($this->faux("<?php echo 1;", 'x.php'), 'X', 'autre', null);
        self::assertNull($r['id']);
        self::assertStringContainsString('Type de fichier', $r['erreur']);
        self::assertSame([], glob($this->dossier . '/*'));
    }

    public function testExtensionTrompeuseRefusee(): void
    {
        $r = $this->app->service(Documents::class)->televerser($this->faux("<?php echo 1;", 'x.pdf'), 'X', 'autre', null);
        self::assertNotNull($r['erreur']);
    }

    public function testTailleEtErreursPhp(): void
    {
        $svc = $this->app->service(Documents::class);
        $gros = $this->faux('%PDF-1.4', 'g.pdf'); $gros['size'] = Documents::TAILLE_MAX + 1;
        self::assertStringContainsString('10 Mo', $svc->televerser($gros, 'G', 'autre', null)['erreur']);
        self::assertNotNull($svc->televerser($this->faux('', 'v.pdf', UPLOAD_ERR_NO_FILE), 'V', 'autre', null)['erreur']);
        self::assertNotNull($svc->televerser(null, 'V', 'autre', null)['erreur']);
        self::assertNotNull($svc->televerser($this->faux('%PDF-1.4', 'v.pdf'), '', 'autre', null)['erreur']);
        self::assertNotNull($svc->televerser($this->faux('%PDF-1.4', 'v.pdf'), 'V', 'inconnue', null)['erreur']);
    }

    public function testSuppressionRefuseeSiUtilise(): void
    {
        $svc = $this->app->service(Documents::class);
        $id = $svc->televerser($this->faux('%PDF-1.4', 'a.pdf'), 'A', 'ag', null)['id'];
        $page = $this->app->service(PageRepository::class)->creer(['slug' => 'ag', 'titre' => 'AG', 'corps_md' => '', 'corps_html' => '', 'systeme' => 1, 'dans_menu' => 0, 'ordre' => 0, 'modifie_par' => null]);
        $this->app->service(PageRepository::class)->definirDocuments($page, [$id]);
        self::assertNotNull($svc->supprimer($id));
        $this->app->service(PageRepository::class)->definirDocuments($page, []);
        $fichier = $this->app->service(DocumentRepository::class)->parId($id)['fichier'];
        self::assertNull($svc->supprimer($id, 42));
        self::assertFileDoesNotExist($this->dossier . '/' . $fichier);
        self::assertNull($this->app->service(DocumentRepository::class)->parId($id));
        $ligne = $this->app->service(JournalRepository::class)->parCible('document', $id)[0];
        self::assertSame(42, (int) $ligne['utilisateur_id'], 'l\'auteur de la suppression est journalisé');
    }
}
