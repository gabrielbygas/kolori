<?php

declare(strict_types=1);

namespace App\Enums;

// modify by claude
enum StockMovementType: string
{
    case In = 'in';
    case Out = 'out';
}
