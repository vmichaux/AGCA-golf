<?php
declare(strict_types=1);
namespace Agca\App\Service;

use Agca\App\App;
use Agca\App\Repository\DocumentRepository;
use Agca\App\Repository\JournalRepository;

final class Documents
{
    public const TAILLE_MAX = 10 * 1024 * 1024;
    public const TYPES = [
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'xls' => ['application/vnd.ms-excel'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'jpg' => ['image/jpeg'], 'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
    ];
    public const CATEGORIES = ['statuts', 'ag', 'voyage', 'palmares', 'autre'];

    public function __construct(private App $app) {}

    public function dossier(): string
    {
        return rtrim((string) $this->app->config('app.documents_dir', $this->app->racine() . '/public/documents'), '/');
    }

    public function urlPublique(array $document): string { return '/documents/' . $document['fichier']; }

    /** @return array{id:?int, erreur:?string} */
    public function televerser(?array $fichier, string $titre, string $categorie, ?int $utilisateurId): array
    {
        $titre = trim($titre);
        if ($titre === '') { return $this->echec('Le titre est obligatoire.'); }
        if (!in_array($categorie, self::CATEGORIES, true)) { return $this->echec('Catégorie inconnue.'); }
        if ($fichier === null || ($fichier['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) { return $this->echec('Aucun fichier reçu.'); }
        if ((int) $fichier['error'] !== UPLOAD_ERR_OK) { return $this->echec('Échec du téléversement (code ' . (int) $fichier['error'] . ').'); }
        if ((int) $fichier['size'] > self::TAILLE_MAX) { return $this->echec('Fichier trop volumineux (10 Mo maximum).'); }
        $ext = strtolower(pathinfo((string) $fichier['name'], PATHINFO_EXTENSION));
        if (!isset(self::TYPES[$ext])) { return $this->echec('Type de fichier non autorisé (' . implode(', ', array_keys(self::TYPES)) . ').'); }
        $mime = (string) (new \finfo(FILEINFO_MIME_TYPE))->file((string) $fichier['tmp_name']);
        if (!in_array($mime, self::TYPES[$ext], true) && !($ext === 'doc' && $mime === 'application/CDFV2') && !(in_array($ext, ['docx', 'xlsx'], true) && $mime === 'application/zip')) {
            return $this->echec("Le contenu du fichier ($mime) ne correspond pas à son extension .$ext.");
        }
        $slug = trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', pathinfo((string) $fichier['name'], PATHINFO_FILENAME)) ?: 'document')), '-') ?: 'document';
        $nom = date('Y-m-d') . '-' . substr($slug, 0, 60) . '-' . substr(bin2hex(random_bytes(4)), 0, 6) . '.' . $ext;
        $dossier = $this->dossier();
        if (!is_dir($dossier) && !mkdir($dossier, 0755, true)) { return $this->echec('Dossier des documents inaccessible.'); }
        if (!$this->deplacer((string) $fichier['tmp_name'], "$dossier/$nom")) { return $this->echec('Impossible d\'enregistrer le fichier.'); }
        $id = $this->app->service(DocumentRepository::class)->creer(['titre' => $titre, 'fichier' => $nom, 'type_mime' => $mime === 'application/zip' || $mime === 'application/CDFV2' ? self::TYPES[$ext][0] : $mime,
            'taille' => (int) $fichier['size'], 'categorie' => $categorie, 'televerse_par' => $utilisateurId]);
        $this->app->service(JournalRepository::class)->ecrire($utilisateurId, 'document_televerse', 'document', $id, ['fichier' => $nom]);
        return ['id' => $id, 'erreur' => null];
    }

    public function supprimer(int $id, ?int $utilisateurId = null): ?string
    {
        $repo = $this->app->service(DocumentRepository::class);
        $d = $repo->parId($id);
        if ($d === null) { return 'Document introuvable.'; }
        if ($repo->estUtilise($id)) { return 'Ce document est utilisé par une page ou un palmarès : retirez-le d\'abord.'; }
        $chemin = $this->dossier() . '/' . $d['fichier'];
        if (is_file($chemin)) { unlink($chemin); }
        $repo->supprimer($id);
        $this->app->service(JournalRepository::class)->ecrire($utilisateurId, 'document_supprime', 'document', $id, ['fichier' => $d['fichier']]);
        return null;
    }

    /** move_uploaded_file en HTTP ; rename en CLI (tests). */
    protected function deplacer(string $source, string $cible): bool
    {
        return PHP_SAPI === 'cli' && !is_uploaded_file($source) ? rename($source, $cible) : move_uploaded_file($source, $cible);
    }

    private function echec(string $m): array { return ['id' => null, 'erreur' => $m]; }
}
