<?php

namespace App\Enums;

enum UserStatus: string
{
    case PENDING   = 'pending';
    case ACTIVE    = 'active';
    case SUSPENDED = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::PENDING   => 'Menunggu Persetujuan',
            self::ACTIVE    => 'Aktif',
            self::SUSPENDED => 'Dinonaktifkan',
        };
    }

    public function isAccessAllowed(): bool
    {
        return $this === self::ACTIVE;
    }
}
