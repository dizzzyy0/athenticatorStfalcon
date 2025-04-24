<?php

namespace App\DataFixtures;

use App\DTO\RegisterDTO;
use App\Services\RegisterUserService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixture extends Fixture
{
    public function __construct(
        private readonly RegisterUserService $registerService,
    ) {

    }

    public function load(ObjectManager $manager): void
    {
        $registerDto = new RegisterDto(
            'example@gmail.com',
            'example',
        );
        $this->registerService->register($registerDto);
    }
}
