<?php

declare(strict_types=1);

namespace App\DTO;
use Symfony\Component\Validator\Constraints as Assert;
class UpdateUserDTO
{
    public function __construct(
        #[Assert\NotBlank]
        public string $email,
        public ?string $password,
    ) {
    }
}
