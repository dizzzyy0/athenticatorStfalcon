<?php
declare(strict_types=1);

namespace App\CustomValidator;

use Attribute;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class UniqueEmailConstraint extends Constraint
{
    #[HasNamedArguments]
    public function __construct(
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct([], $groups, $payload);
    }

    public function validatedBy(): string
    {
        return static::class.'Validator';
    }

}
