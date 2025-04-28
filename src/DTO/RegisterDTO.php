<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use App\CustomValidator as CustomValidator;
class RegisterDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[CustomValidator\UniqueEmailConstraint]
        public string $email,
        #[Assert\NotBlank]
        public string $password,
    ) {
    }
}
