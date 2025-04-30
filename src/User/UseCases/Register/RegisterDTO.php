<?php

declare(strict_types=1);

namespace App\User\UseCases\Register;

use App\User\Email\Validator\UniqueEmail;
use Symfony\Component\Validator\Constraints as Assert;

class RegisterDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[UniqueEmail]
        public string $email,
        #[Assert\NotBlank]
        public string $password,
        #[Assert\NotBlank]
        #[Assert\EqualTo(propertyPath: 'password')]
        public string $confirmPassword,

    ) {
    }
}
