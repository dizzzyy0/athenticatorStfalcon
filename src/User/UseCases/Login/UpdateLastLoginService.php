<?php
declare(strict_types=1);

namespace App\User\UseCases\Login;

use App\User\Support\UserRepository;
use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

readonly class UpdateLastLoginService
{
    public function __construct(
        private UserRepository         $userRepository,
        private EntityManagerInterface $entityManager,
    ){}

    public function updateUserLogin(Uuid $userId): void {
        $user = $this->userRepository->findOneById($userId);
        $user->setLastLogin(CarbonImmutable::now()->toDateTimeImmutable());
        $this->entityManager->flush();
    }
}
