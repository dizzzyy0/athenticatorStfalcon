<?php
declare(strict_types=1);

namespace App\Services;

use App\DTO\RegisterDTO;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class RegisterUserService
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
    ) {
    }

    public function register(RegisterDTO $registerDTO): void
    {
        $constraintValidator = $this->validator->validate($registerDTO);
        if(count($constraintValidator) > 0){
            throw new ValidationFailedException($registerDTO,$constraintValidator);
        }

        $user = new User();
        $user->setEmail($registerDTO->email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $registerDTO->password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
