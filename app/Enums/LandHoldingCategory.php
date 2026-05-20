<?php

namespace App\Enums;

enum LandHoldingCategory: string
{
    case Marginal = 'marginal';
    case Small = 'small';
    case SemiMedium = 'semi_medium';
    case Medium = 'medium';
    case Large = 'large';

    public function label(): string
    {
        return match ($this) {
            self::Marginal => 'Marginal (<1 ha)',
            self::Small => 'Small (1–2 ha)',
            self::SemiMedium => 'Semi-medium (2–4 ha)',
            self::Medium => 'Medium (4–10 ha)',
            self::Large => 'Large (>10 ha)',
        };
    }
}
