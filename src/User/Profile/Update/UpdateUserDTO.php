<?php

declare(strict_types=1);

namespace App\User\Profile\Update;
use App\User\Email\Validator\UniqueEmail;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateUserDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        #[UniqueEmail]
        public string $email,
        #[Assert\NotBlank(allowNull: true)]
        public ?string $password,
        #[Assert\NotBlank(allowNull: true)]
        public ?string $picturePath,
    ) {
    }
}
