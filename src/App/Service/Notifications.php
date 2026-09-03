<?php
declare(strict_types=1);
namespace Agca\App\Service;

use Agca\App\App;
use Agca\Domain\Controles;

final class Notifications
{
    public function __construct(private App $app) {}

    public function feuilleEnregistree(array $r, bool $correction, ?array $auteur): void
    {
        $sujet = sprintf('[AGCA %s] Feuille %s : %s – %s (%s - %s)', $r['serie_code'], $correction ? 'corrigée' : 'enregistrée', $r['recevant_nom'], $r['invite_nom'], $r['total_pour'], $r['total_contre']);
        $texte = $this->entete($r)
            . "Résultat des parties : {$r['total_pour']} - {$r['total_contre']}\n"
            . 'Points de rencontre : ' . fmt_pts($r['pts_rencontre_pour']) . ' - ' . fmt_pts($r['pts_rencontre_contre'])
            . ((float) $r['bonus_invite'] > 0 ? ' (+' . fmt_pts($r['bonus_invite']) . ' bonus pour l\'équipe invitée)' : '') . "\n"
            . $this->alertes($r)
            . ($correction ? "Cette feuille a été corrigée par l'administrateur.\n" : "Saisie par le capitaine recevant" . ($auteur ? ' (' . $auteur['identifiant'] . ')' : '') . ".\n")
            . $this->pied($r);
        $this->envoyerAuxDeux($r['invite_email'], $sujet, $texte, []);
    }

    public function report(array $r, string $ancienneDate, ?array $auteur): void
    {
        $sujet = sprintf('[AGCA %s] Report : %s – %s au %s', $r['serie_code'], $r['recevant_nom'], $r['invite_nom'], fmt_date($r['date_reelle']));
        $texte = $this->entete($r) . 'La rencontre initialement prévue le ' . fmt_date($ancienneDate) . ' est reportée au ' . fmt_date($r['date_reelle']) . ".\n"
            . 'Modification saisie par ' . ($auteur['identifiant'] ?? 'l\'administrateur') . ".\n" . $this->pied($r);
        $this->envoyerAuxDeux($r['invite_email'], $sujet, $texte, []);
    }

    public function forfait(array $r, bool $annulation, ?array $auteur): void
    {
        $forfaitaire = (int) $r['forfaitaire_id'] === (int) $r['recevant_id'] ? $r['recevant_nom'] : $r['invite_nom'];
        $sujet = sprintf('[AGCA %s] %s : %s – %s', $r['serie_code'], $annulation ? 'Forfait annulé' : 'Forfait', $r['recevant_nom'], $r['invite_nom']);
        $texte = $this->entete($r) . ($annulation
            ? "Le forfait précédemment déclaré est annulé ; la rencontre est de nouveau à jouer.\n"
            : "Forfait de l'équipe $forfaitaire, déclaré par l'administrateur. Score 15-0, 0 point au forfaitaire, 3 points au bénéficiaire" . ((float) $r['bonus_invite'] > 0 ? ' +1 bonus' : '') . ".\n")
            . $this->pied($r);
        $this->envoyerAuxDeux($r['recevant_email'], $sujet, $texte, [$r['invite_email']]);
    }

    public function relance(array $r): bool
    {
        $sujet = sprintf('[AGCA %s] Feuille de match à saisir : %s – %s', $r['serie_code'], $r['recevant_nom'], $r['invite_nom']);
        $texte = $this->entete($r) . 'La feuille de cette rencontre jouée le ' . fmt_date($r['date_reelle']) . " n'est pas encore saisie.\n"
            . "Merci de la saisir dans votre espace capitaine. Si la rencontre a été reportée, indiquez la nouvelle date convenue avec votre adversaire.\n"
            . 'Saisie : ' . $this->app->config('app.base_url') . '/rencontre/' . $r['id'] . "/saisie\n";
        return $this->envoyerAuxDeux($r['recevant_email'], $sujet, $texte, []);
    }

    private function entete(array $r): string
    {
        return "AGCA — {$r['division_libelle']} — " . ucfirst($r['journee_phase']) . " journée {$r['journee_numero']}\n"
            . "{$r['recevant_nom']} reçoit {$r['invite_nom']}\n\n";
    }

    private function alertes(array $r): string
    {
        $codes = $r['alertes'] ? json_decode((string) $r['alertes'], true) : [];
        if (!$codes) { return ''; }
        return "Points à vérifier : " . implode(' ; ', array_map(fn($c) => Controles::LIBELLES[$c] ?? $c, $codes)) . "\n";
    }

    private function pied(array $r): string
    {
        return "\nVoir la feuille : " . $this->app->config('app.base_url') . '/rencontre/' . $r['id'] . "\n\nMessage automatique, merci de ne pas répondre.\n";
    }

    /** Destinataire principal (ou l'admin si vide) + copie admin. */
    private function envoyerAuxDeux(?string $a, string $sujet, string $texte, array $cc): bool
    {
        $admin = (string) $this->app->config('mail.admin', '');
        $cc[] = $admin;
        $a = trim((string) $a);
        if ($a === '') { $a = $admin; }
        return $this->app->service(Mailer::class)->envoyer($a, $sujet, $texte, $cc);
    }
}
