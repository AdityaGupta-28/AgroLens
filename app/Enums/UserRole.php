<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case GovernmentOfficer = 'government_officer';
    case PublicViewer = 'public_viewer';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::GovernmentOfficer => 'Government Officer',
            self::PublicViewer => 'Public Viewer',
        };
    }
}
