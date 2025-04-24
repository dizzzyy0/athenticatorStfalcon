<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use App\DTO\RegisterDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class UserService
{

    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager,
        private TotpAuthenticatorInterface $totpAuthenticator,
        private EncryptionService $encryptionService,
    ) {
    }

    public function getById(Uuid $userId): User
    {
        $user = $this->userRepository->find($userId);
        if ($user === null) {
            throw new Exception(sprintf('User with id %s does not exists', $userId));
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

        $user->setSecretKey($this->encryptionService->encryptSecret($this->totpAuthenticator->generateSecret()));
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

    /**
     * @throws Exception
     */
    public function getUserQrCode(Uuid $userId): string
    {
        $user = $this->getById($userId);
        return $this->totpAuthenticator->getQRContent($user);
    }
}
