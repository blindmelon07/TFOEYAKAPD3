<?php

namespace App\Enums;

enum UserRole: string
{
    case DistrictAdmin = 'district_admin';
    case Member = 'member';

    /**
     * Get the human-readable name of the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::DistrictAdmin => 'District Admin',
            self::Member => 'Member',
        };
    }
}
