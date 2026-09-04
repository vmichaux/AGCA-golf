<?php
declare(strict_types=1);
namespace Agca\App\Service;

use Agca\App\App;
use Agca\App\Repository\ContactLimiteRepository;
use Agca\App\Repository\JournalRepository;
use Agca\App\Session;

/**
 * Formulaire de contact : validation, anti-robot, limitation par session et par IP,
 * envoi à l'administrateur seul (aucune copie au demandeur). Le message complet
 * n'est jamais journalisé.
 */
final class Contact
{
    /** Délai minimal entre deux messages d'une même session, en secondes. */
    public const DELAI = 60;
    /** Délai minimal entre deux messages d'une même IP, en secondes. */
    public const DELAI_IP = 60;
    /** Nombre maximal de messages pour une même IP sur 24 h. */
    public const PLAFOND_IP_24H = 5;
    /** Nombre maximal de messages, toutes IP confondues, sur 24 h. */
    public const PLAFOND_GLOBAL_24H = 40;
    /** Purge des lignes de `agca_contact_limite` plus anciennes que ce délai, en heures. */
    private const PURGE_HEURES = 48;
    private const LONGUEUR_MESSAGE = 10;

    public function __construct(private App $app) {}

    /**
     * @param array<string, mixed> $post champs bruts du formulaire (`nom`, `email`, `objet`, `message`, piège `site_web`)
     * @return array{ok: bool, erreurs: list<string>}
     */
    public function envoyer(array $post, Session $session, string $ip): array
    {
        // Champ piège : rempli, c'est un robot. Succès apparent, aucun envoi.
        if (trim((string) ($post['site_web'] ?? '')) !== '') { return ['ok' => true, 'erreurs' => []]; }

        $nom = trim((string) ($post['nom'] ?? ''));
        $email = trim((string) ($post['email'] ?? ''));
        $objet = trim((string) ($post['objet'] ?? ''));
        $message = trim((string) ($post['message'] ?? ''));

        $erreurs = [];
        if ($nom === '') { $erreurs[] = 'Merci d\'indiquer votre nom.'; }
        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) { $erreurs[] = 'Merci d\'indiquer une adresse e-mail valide.'; }
        if ($objet === '') { $erreurs[] = 'Merci d\'indiquer un objet.'; }
        if (mb_strlen($message) < self::LONGUEUR_MESSAGE) { $erreurs[] = 'Merci d\'écrire un message d\'au moins ' . self::LONGUEUR_MESSAGE . ' caractères.'; }
        if ($erreurs !== []) { return ['ok' => false, 'erreurs' => $erreurs]; }

        $dernier = (int) $session->get('contact_dernier', 0);
        if ($dernier > 0 && time() - $dernier < self::DELAI) {
            return ['ok' => false, 'erreurs' => ['Un message vient d\'être envoyé : merci d\'attendre une minute avant le suivant.']];
        }

        // Limitation par IP : contourne le contournement par jet du cookie de session.
        $limite = $this->app->service(ContactLimiteRepository::class);
        $maintenant = new \DateTimeImmutable();
        $depuisIp = $maintenant->modify('-' . self::DELAI_IP . ' seconds')->format('Y-m-d H:i:s');
        $depuis24h = $maintenant->modify('-24 hours')->format('Y-m-d H:i:s');
        if ($limite->compterDepuis($ip, $depuisIp) >= 1
            || $limite->compterDepuis($ip, $depuis24h) >= self::PLAFOND_IP_24H
            || $limite->compterGlobalDepuis($depuis24h) >= self::PLAFOND_GLOBAL_24H
        ) {
            return ['ok' => false, 'erreurs' => ['Trop de messages envoyés, réessayez plus tard.']];
        }

        $texte = "Message envoyé depuis le formulaire de contact du site AGCA.\n\n"
            . "Nom : $nom\nE-mail : $email\nObjet : $objet\n\n$message\n";
        $this->app->service(Mailer::class)->envoyer((string) $this->app->config('mail.admin'), "[AGCA contact] $objet", $texte);

        $session->set('contact_dernier', time());
        $limite->enregistrer($ip);
        $limite->purger($maintenant->modify('-' . self::PURGE_HEURES . ' hours')->format('Y-m-d H:i:s'));
        // Journal volontairement sans le message : seulement qui a écrit et à quel sujet.
        $this->app->service(JournalRepository::class)->ecrire(null, 'contact_envoye', null, null, ['nom' => $nom, 'objet' => $objet]);

        return ['ok' => true, 'erreurs' => []];
    }
}
