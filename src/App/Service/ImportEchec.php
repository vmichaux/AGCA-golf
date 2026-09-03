<?php
declare(strict_types=1);
namespace Agca\App\Service;

/** Échec métier intentionnel pendant l'import initial (déclenche le rollback de la transaction). */
final class ImportEchec extends \RuntimeException {}
