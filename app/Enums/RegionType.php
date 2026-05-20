<?php

namespace App\Enums;

enum RegionType: string
{
    case Country = 'country';
    case State = 'state';
    case District = 'district';
    case Taluka = 'taluka';
    case Village = 'village';
}
