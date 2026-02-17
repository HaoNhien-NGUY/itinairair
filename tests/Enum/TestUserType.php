<?php

namespace App\Tests\Enum;

enum TestUserType: string
{
    case USER = 'user';

    case USER_2 = 'user2';

    case ADMIN = 'admin';

    public function getEmail(): string
    {
        return match ($this) {
            self::USER => 'test@test.com',
            self::USER_2 => sprintf('%s@test.com', self::USER->value),
            self::ADMIN => sprintf('%s@test.com', self::ADMIN->value),
        };
    }
}
