<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Principal = 'principal';
    case Engineer = 'engineer';
    case Driver = 'driver';
    case Staff = 'staff';
}
