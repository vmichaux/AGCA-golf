<?php
declare(strict_types=1);
namespace Agca\App;

use Agca\App\Repository\JournalRepository;
use Agca\App\Repository\SerieRepository;
use Agca\App\Repository\UtilisateurRepository;
use Agca\Domain\MotDePasse;

final class Auth
{
    public const MAX_TENTATIVES = 5;
    public const BLOCAGE_MINUTES = 15;
    private ?array $cache = null;

    public function __construct(private App $app) {}

    public function utilisateur(): ?array
    {
        $id = $this->app->session()->get('utilisateur_id');
        if ($id === null) { return null; }
        return $this->cache ??= $this->app->service(UtilisateurRepository::class)->parId((int) $id);
    }

    public function estConnecte(): bool { return $this->utilisateur() !== null; }
    public function estAdmin(): bool { return (bool) ($this->utilisateur()['est_admin'] ?? false); }

    /** @return array{ok:bool, erreur:?string} */
    public function connecter(string $identifiant, string $codeSerie, string $clair): array
    {
        $utilisateurs = $this->app->service(UtilisateurRepository::class);
        $journal = $this->app->service(JournalRepository::class);
        $serie = $this->app->service(SerieRepository::class)->parCode($codeSerie);
        $u = $utilisateurs->parIdentifiantEtSerie($identifiant, $serie === null ? null : (int) $serie['id']);
        $generique = 'Identifiant, série ou mot de passe incorrect.';
        if ($u === null) {
            $journal->ecrire(null, 'connexion_echec', null, null, ['identifiant' => $identifiant, 'serie' => $codeSerie]);
            return ['ok' => false, 'erreur' => $generique];
        }
        if ($u['bloque_jusqua'] !== null && strtotime($u['bloque_jusqua']) > time()) {
            return ['ok' => false, 'erreur' => 'Compte temporairement bloqué après plusieurs échecs. Réessayez dans ' . self::BLOCAGE_MINUTES . ' minutes.'];
        }
        if (!MotDePasse::verifier($clair, $u['hash_sha1'], $u['hash_bcrypt'])) {
            $tentatives = (int) $u['tentatives'] + 1;
            $blocage = $tentatives >= self::MAX_TENTATIVES ? date('Y-m-d H:i:s', time() + self::BLOCAGE_MINUTES * 60) : null;
            $utilisateurs->enregistrerEchec((int) $u['id'], $blocage);
            $journal->ecrire((int) $u['id'], 'connexion_echec', 'utilisateur', (int) $u['id'], ['tentatives' => $tentatives]);
            return ['ok' => false, 'erreur' => $blocage === null ? $generique : 'Compte temporairement bloqué après plusieurs échecs. Réessayez dans ' . self::BLOCAGE_MINUTES . ' minutes.'];
        }
        if (MotDePasse::doitMigrer($u['hash_sha1'], $u['hash_bcrypt'])) {
            $utilisateurs->definirBcrypt((int) $u['id'], MotDePasse::hacher($clair));
        }
        $utilisateurs->enregistrerSucces((int) $u['id']);
        $this->app->session()->regenerer();
        $this->app->session()->set('utilisateur_id', (int) $u['id']);
        $this->cache = null;
        $journal->ecrire((int) $u['id'], 'connexion', 'utilisateur', (int) $u['id']);
        return ['ok' => true, 'erreur' => null];
    }

    public function deconnecter(): void
    {
        $this->app->session()->detruire();
        $this->cache = null;
    }

    /** @return string|null message d'erreur, null si OK */
    public function changerMotDePasse(int $id, string $actuel, string $nouveau): ?string
    {
        $utilisateurs = $this->app->service(UtilisateurRepository::class);
        $u = $utilisateurs->parId($id);
        if ($u === null || !MotDePasse::verifier($actuel, $u['hash_sha1'], $u['hash_bcrypt'])) { return 'Mot de passe actuel incorrect.'; }
        if (strlen($nouveau) < 8) { return 'Le nouveau mot de passe doit faire au moins 8 caractères.'; }
        $utilisateurs->definirBcrypt($id, MotDePasse::hacher($nouveau));
        $this->app->service(JournalRepository::class)->ecrire($id, 'mot_de_passe_change', 'utilisateur', $id);
        $this->cache = null;
        return null;
    }
}
