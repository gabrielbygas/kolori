<?php

declare(strict_types=1);

// modify by claude
// Réglages métier temporaires en attendant la vraie page config (point 7)
// qui les rendra éditables en base par l'admin (taux de change, mode de
// tarification détail/gros, TVA, branding, langue...).
return [
    /*
     * Taux de change CDF pour 1 USD, fixé manuellement (pas d'API externe en V1).
     */
    'exchange_rate' => (float) env('EXCHANGE_RATE', 2800),

    /*
     * Choix du prix détail/gros à la vente :
     * - "automatic" : le prix de gros s'applique dès que la quantité atteint
     *   le seuil de la variante, sans action du vendeur.
     * - "manual" : le vendeur choisit lui-même (une suggestion automatique
     *   reste proposée par défaut, mais reste modifiable).
     */
    'pricing_mode' => env('PRICING_MODE', 'automatic'),
];
