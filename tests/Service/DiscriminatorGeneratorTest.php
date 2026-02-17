<?php

namespace App\Tests\Service;

use App\Repository\UserRepository;
use App\Service\DiscriminatorGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DiscriminatorGeneratorTest extends TestCase
{
    private UserRepository&MockObject $userRepo;

    protected function setUp(): void
    {
        $this->userRepo = $this->createMock(UserRepository::class);
    }

    public function testReturnsRandomNumber(): void
    {
        $this->userRepo
            ->method('findDiscriminatorsByUsername')
            ->willReturn([]);

        $generator = new DiscriminatorGenerator($this->userRepo);

        $discriminator = $generator->generateDiscriminator('test');

        $this->assertMatchesRegularExpression('/^\d{5}$/', $discriminator);
    }

    public function testWillRetry(): void
    {
        $uniqueDiscriminator = 12345;
        $foundDiscriminators = 99999;
        $sequence = [$foundDiscriminators, $uniqueDiscriminator];
        $this->userRepo
            ->method('findDiscriminatorsByUsername')
            ->willReturn([$foundDiscriminators]);
        $randomizer = function () use (&$sequence) {
            return array_shift($sequence);
        };

        $generator = new DiscriminatorGenerator($this->userRepo, $randomizer);

        $discriminator = $generator->generateDiscriminator('test');

        $this->assertEquals($uniqueDiscriminator, $discriminator);
    }

    public function testExceptionIfTooManyAttempts(): void
    {
        $this->userRepo
            ->method('findDiscriminatorsByUsername')
            ->willReturn([1]);
        $randomizer = fn (int $min, int $max) => 1;
        $generator = new DiscriminatorGenerator($this->userRepo, $randomizer);

        $this->expectException(\RuntimeException::class);

        $generator->generateDiscriminator('test');
    }
}
