<?php

declare(strict_types=1);

namespace App\Enums;

// modify by claude
enum ProductOrigin: string
{
    case Local = 'local';
    case Imported = 'imported';
}
