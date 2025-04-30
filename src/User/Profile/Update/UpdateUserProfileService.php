<?php
declare(strict_types=1);

namespace App\User\Profile\Update;

use App\Services\UserService;
use App\User\Support\UserRepository;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class UpdateUserProfileService
{

    public function __construct(
        private UserRepository $userRepository,
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

        $user = $this->userRepository->findOneById($userId);
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
}
