<?php

namespace App\Enums;

enum VehiclePartStatus: string
{
    case Required = 'required';
    case InTransit = 'in-transit';
    case Delivered = 'delivered';
    case Fitted = 'fitted';
}
