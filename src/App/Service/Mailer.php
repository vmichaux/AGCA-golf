<?php
declare(strict_types=1);
namespace Agca\App\Service;

use Agca\App\App;
use Agca\App\Repository\JournalRepository;
use PHPMailer\PHPMailer\PHPMailer;

final class Mailer
{
    private ?array $dernier = null;

    public function __construct(private App $app) {}

    /** @param list<string> $cc */
    public function envoyer(string $a, string $sujet, string $texte, array $cc = []): bool
    {
        $cc = array_values(array_unique(array_filter($cc, fn($x) => is_string($x) && trim($x) !== '' && $x !== $a)));
        $this->dernier = ['a' => $a, 'cc' => $cc, 'sujet' => $sujet, 'texte' => $texte];
        $journal = $this->app->service(JournalRepository::class);
        if (!$this->app->config('mail.enabled', false)) {
            $journal->ecrire(null, 'mail_simule', null, null, $this->dernier);
            return true;
        }
        try {
            $m = new PHPMailer(true);
            $m->CharSet = 'UTF-8';
            $m->isSMTP();
            $m->Host = (string) $this->app->config('mail.host');
            $m->Port = (int) $this->app->config('mail.port', 587);
            $m->SMTPAuth = true;
            $m->SMTPSecure = $m->Port === 465 ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $m->Username = (string) $this->app->config('mail.user');
            $m->Password = (string) $this->app->config('mail.pass');
            $m->setFrom((string) $this->app->config('mail.from'), (string) $this->app->config('mail.from_nom', 'AGCA'));
            $m->addAddress($a);
            foreach ($cc as $c) { $m->addCC($c); }
            $m->Subject = $sujet;
            $m->Body = $texte;
            $m->isHTML(false);
            $m->send();
            $journal->ecrire(null, 'mail_envoye', null, null, ['a' => $a, 'cc' => $cc, 'sujet' => $sujet]);
            return true;
        } catch (\Throwable $e) {
            error_log('Mail échec : ' . $e->getMessage());
            $journal->ecrire(null, 'mail_echec', null, null, ['a' => $a, 'sujet' => $sujet, 'erreur' => $e->getMessage()]);
            return false;
        }
    }

    public function dernierEnvoi(): ?array { return $this->dernier; }
}
