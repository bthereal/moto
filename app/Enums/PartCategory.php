<?php

namespace App\Enums;

enum PartCategory: string
{
    case Aerodynamics = 'aerodynamics';
    case Powertrain = 'powertrain';
    case Brakes = 'brakes';
    case Suspension = 'suspension';
    case Electronics = 'electronics';
    case Chassis = 'chassis';
    case Cooling = 'cooling';
    case Transmission = 'transmission';
}
