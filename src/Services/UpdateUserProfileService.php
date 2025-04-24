<?php
declare(strict_types=1);

namespace App\Services;

use App\DTO\UpdateUserDTO;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class UpdateUserProfileService
{

    public function __construct(
      private UserService             $userService,
      private EntityManagerInterface  $entityManager,
      private UserPasswordHasherInterface $passwordHasher,
      private ValidatorInterface      $validator,
    ){
    }

    public function updateUserProfile(Uuid $userId, UpdateUserDTO $updateUserProfileDTO): void {
        $constraintValidator = $this->validator->validate($updateUserProfileDTO);
        if(count($constraintValidator) > 0){
            throw new ValidationFailedException($updateUserProfileDTO,$constraintValidator);
        }

        $user = $this->userService->getById($userId);
        $user->setEmail($updateUserProfileDTO->email);

        if($updateUserProfileDTO->password !== null) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $updateUserProfileDTO->password);
            $user->setPassword($hashedPassword);
        }

        if($updateUserProfileDTO->picturePath !== null) {
            $user->setPicturePath($updateUserProfileDTO->picturePath);
        }

        $this->entityManager->flush();
    }

    public function udateLastLogin(Uuid $userId): void {
        $user = $this->userService->getById($userId);
        $user->setLastLogin(Carbon::now());
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
