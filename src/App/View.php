<?php
declare(strict_types=1);
namespace Agca\App;

final class View
{
    /** @var array<string, mixed> variables disponibles dans toutes les vues (utilisateur, flashs, csrf…) */
    private array $globales = [];

    public function __construct(private string $dossier) {}

    public function partager(string $cle, mixed $valeur): void { $this->globales[$cle] = $valeur; }

    /** @param array<string, mixed> $vars */
    public function rendre(string $template, array $vars = [], ?string $layout = 'layout'): string
    {
        $contenu = $this->inclure($template, $vars);
        if ($layout === null) { return $contenu; }
        return $this->inclure($layout, $vars + ['contenu' => $contenu]);
    }

    /** @param array<string, mixed> $vars */
    public function inclure(string $template, array $vars = []): string
    {
        $fichier = $this->dossier . '/' . $template . '.php';
        if (!is_file($fichier)) { throw new \RuntimeException("Template introuvable : $template"); }
        extract($this->globales + $vars, EXTR_SKIP);
        $vue = $this;
        ob_start();
        try { require $fichier; } finally { $sortie = ob_get_clean(); }
        return (string) $sortie;
    }
}
