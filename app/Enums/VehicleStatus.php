<?php

namespace App\Enums;

enum VehicleStatus: string
{
    case Active = 'active';
    case Testing = 'testing';
    case Retired = 'retired';
}
