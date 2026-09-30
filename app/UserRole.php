<?php

namespace App;

enum UserRole: string
{
    case Admin = 'admin';
    case Mechanic = 'mekanik';
    case Customer = 'pelanggan';
}
