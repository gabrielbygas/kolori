<?php

declare(strict_types=1);

namespace App\Enums;

// modify by claude
enum PaymentCurrency: string
{
    case Usd = 'usd';
    case Cdf = 'cdf';
}
