<?php

namespace App\Enums;

enum IrrigationSourceType: string
{
    case Canal = 'canal';
    case TubeWell = 'tube_well';
    case DugWell = 'dug_well';
    case BoreWell = 'bore_well';
    case RainFed = 'rain_fed';
    case River = 'river';
    case TankPond = 'tank_pond';

    public function label(): string
    {
        return match ($this) {
            self::Canal => 'Canal',
            self::TubeWell => 'Tube Well',
            self::DugWell => 'Dug Well',
            self::BoreWell => 'Bore Well',
            self::RainFed => 'Rain-fed',
            self::River => 'River / Stream',
            self::TankPond => 'Tank / Pond',
        };
    }
}
