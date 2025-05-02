<?php
declare(strict_types=1);

namespace App\User\UseCases\Login\TwoFactorLogin;

use App\User\Support\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;

readonly class TwoFactorService
{
    public function __construct(
        private  UserRepository $userRepository,
        private  UserPasswordHasherInterface         $passwordHasher,
        private  EntityManagerInterface              $entityManager,
        private  TotpAuthenticatorInterface $totpAuthenticator,
    ){}

    public function enableTwoFactorAuthentication(Uuid $userId, string $password): bool
    {
        $user = $this->userRepository->findOneById($userId);
        $isValidPassword = $this->passwordHasher->isPasswordValid($user, $password);
        if (! $isValidPassword) {
            return false;
        }

//        $user->setSecretKey($this->encryptionService->encryptSecret($this->totpAuthenticator->generateSecret()));
        $user->setSecretKey($this->totpAuthenticator->generateSecret());
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return true;
    }

    public function disableTwoFactorAuthentication(Uuid $userId, string $password): bool
    {
        $user = $this->userRepository->findOneById($userId);
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
        $user = $this->userRepository->findOneById($userId);
        return $this->totpAuthenticator->getQRContent($user);
    }
}
