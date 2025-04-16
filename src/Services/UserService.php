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

readonly class  UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager,
        private TotpAuthenticatorInterface $totpAuthenticator,
    ){}

    public function register(RegisterDTO $registerDTO):User
    {
        $existingUser = $this->userRepository->findOneBy(['email' => $registerDTO->email]);
        if($existingUser){
            throw new \Exception(sprintf('User with email %s already exists', $registerDTO->email));
        }

        $user = new User();
        $user->setEmail($registerDTO->email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $registerDTO->password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function getAllUsers(): array
    {
        return $this->userRepository->findAll();
    }

    public function getById(Uuid $userId): User{
        $user = $this->userRepository->find($userId);
        if(!$user){
            throw new \Exception(sprintf('User with id %s does not exists', $userId));
        }
        return $user;
    }

    public function createSecretForUser(Uuid $userId): User{
        $user = $this->getById($userId);
        $user->setSecretKey($this->totpAuthenticator->generateSecret());
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $user;
    }

    public function getSecretForUser(Uuid $userId): string
    {
        $user = $this->getById($userId);
        return $user->getSecretKey();
    }

    public function verifySecretForUser(Uuid $userId, string $secretKey): bool{
        $user = $this->getById($userId);
        return $this->totpAuthenticator->checkCode($user, $secretKey);
    }
}
