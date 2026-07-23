<?php

declare(strict_types=1);

namespace App\Enums;

// modify by claude
enum PricingTier: string
{
    case Retail = 'retail';
    case Wholesale = 'wholesale';
}
