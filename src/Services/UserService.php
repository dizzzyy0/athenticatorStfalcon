<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\RegisterDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;

readonly class UserService
{

    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager,
        private TotpAuthenticatorInterface $totpAuthenticator,
    ) {
    }

    public function register(RegisterDTO $registerDTO): User
    {
        $existingUser = $this->userRepository->findOneBy([
            'email' => $registerDTO->email,
        ]);
        if ($existingUser) {
            throw new \Exception(sprintf('User with email %s already exists', $registerDTO->email));
        }

        $user = new User();
        $user->setEmail($registerDTO->email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $registerDTO->password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function getById(Uuid $userId): User
    {
        $user = $this->userRepository->find($userId);
        if (! $user) {
            throw new \Exception(sprintf('User with id %s does not exists', $userId));
        }
        return $user;
    }

    public function enableTwoFactorAuthentication(Uuid $userId, string $password): bool
    {
        $user = $this->getById($userId);
        $isValidPassword = $this->passwordHasher->isPasswordValid($user, $password);
        if (! $isValidPassword) {
            return false;
        }

        $user->setSecretKey($this->totpAuthenticator->generateSecret());
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return true;
    }

    public function disableTwoFactorAuthentication(Uuid $userId, string $password): bool
    {
        $user = $this->getById($userId);
        $isValidPassword = $this->passwordHasher->isPasswordValid($user, $password);
        if (! $isValidPassword){
            return false;
        }

        $user->setSecretKey(null);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return true;
    }

    public function getUserQrCodeData(Uuid $uuid): string
    {
        $user = $this->getById($uuid);
        return $this->totpAuthenticator->getQRContent($user);
    }
}
