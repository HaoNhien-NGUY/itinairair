<?php

namespace App\Tests\Enum;

enum TestUserType: string
{
    case USER = 'user';
    case ADMIN = 'admin';

    public function getEmail(): string
    {
        return match ($this) {
            self::USER => 'test@test.com',
            self::ADMIN => sprintf('%s@test.com', self::ADMIN->value),
        };
    }
}
