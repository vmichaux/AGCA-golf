<?php
declare(strict_types=1);
// Composition 2026-27 telle qu'affichée sur le site actuel (M_suivi_page.php, H1_suivi_page.php) le 3 septembre 2026.
// Positions 1..n = ordre des équipes dans la grille (J1 aller : 1-2, 3-4).
return [
    'saison' => ['libelle' => '2026-27', 'date_debut' => '2026-09-01', 'date_fin' => '2027-06-30'],
    'dates' => [
        'M' => ['aller' => ['2026-09-26', '2026-11-14', '2026-11-28', '2026-12-05', '2026-12-19'], 'retour' => ['2027-01-16', '2027-01-30', '2027-02-13', '2027-02-27', '2027-03-13']],
        'H1' => ['aller' => ['2026-10-24', '2026-11-14', '2026-12-12'], 'retour' => ['2027-01-23', '2027-02-13', '2027-03-06']],
    ],
    'golfs' => [
        'M' => [
            'ROQUEBRUNE' => 'ROQUEBRUNE', 'Gde-BASTIDE-1' => 'GRANDE BASTIDE', 'Gde-BASTIDE-2' => 'GRANDE BASTIDE', 'VALGARDE-1' => 'VALGARDE',
            'VALCROS' => 'VALCROS', 'SAINTE-MAXIME' => 'SAINTE-MAXIME', 'FREGATE' => 'FREGATE', 'ESTEREL' => 'ESTEREL', "CHATEAU-L'ARC" => "CHATEAU-L'ARC",
            'LUBERON-1' => 'LUBERON', 'LUBERON-2' => 'LUBERON', 'GAP' => 'GAP', 'SAINT-MARTIN-1' => 'SAINT-MARTIN', 'SAINT-MARTIN-2' => 'SAINT-MARTIN',
            'AIX-EN-PROVENCE' => 'AIX-EN-PROVENCE', 'CHATEAUBLANC' => 'CHATEAUBLANC', 'DIGNE' => 'DIGNE', 'VICTORIA' => 'VICTORIA',
            'BARBAROUX' => 'BARBAROUX', 'SALON' => 'SALON', 'ORANGE' => 'ORANGE',
        ],
        'H1' => ['VALGARDE-1' => 'VALGARDE', 'SALON' => 'SALON', 'ORANGE' => 'ORANGE', 'VICTORIA' => 'VICTORIA'],
    ],
    'divisions' => [
        'M' => [
            ['DIV2/POULE B', [1 => 'SAINT-MARTIN-2', 2 => 'LUBERON-1', 3 => 'SALON', 4 => 'FREGATE']],
            ['DIV3/POULE C', [1 => 'ORANGE', 2 => 'DIGNE', 3 => 'GAP', 4 => 'CHATEAUBLANC']],
            ['DIV4/POULE D', [1 => 'VICTORIA', 2 => 'AIX-EN-PROVENCE', 3 => 'BARBAROUX', 4 => 'VALCROS']],
            ['DIV5/POULE E', [1 => 'Gde-BASTIDE-2', 2 => 'ROQUEBRUNE', 3 => "CHATEAU-L'ARC", 4 => 'LUBERON-2']],
            ['DIV à 5 n°1', [1 => 'SAINT-MARTIN-1', 2 => 'VALGARDE-1', 3 => 'SAINTE-MAXIME', 4 => 'ESTEREL', 5 => 'Gde-BASTIDE-1']],
        ],
        'H1' => [
            ['DIVISION 1', [1 => 'VALGARDE-1', 2 => 'SALON', 3 => 'ORANGE', 4 => 'VICTORIA']],
        ],
    ],
    'exclues' => ['M' => ['VALGARDE-2'], 'H1' => ['AIX-EN-PROVENCE', 'LUBERON', "CABRE-D'OR", 'VALGARDE-2']],
    // Reports déjà connus : [série, recevant, invité, phase, journée, date réelle]. À confirmer avec l'admin (voir docs/bascule.md).
    'reports' => [],
];
