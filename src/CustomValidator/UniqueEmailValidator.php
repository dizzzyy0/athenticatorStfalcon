<?php
declare(strict_types=1);

namespace App\CustomValidator;

use App\Repository\UserRepository;
use InvalidArgumentException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueEmailValidator extends ConstraintValidator
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly TokenStorageInterface $tokenStorage,
    ){
    }
    public function validate(mixed $value, Constraint $constraint): void{

        if(!$constraint instanceof UniqueEmailConstraint){
            throw new UnexpectedTypeException($constraint, self::class);
        }

        if($value === null){
            return;
        }

        if(!is_string($value) ){
            throw new InvalidArgumentException('Value must be a string');
        }

        $userId = $this->tokenStorage->getToken()?->getUserIdentifier();
        if($userId !== null){
            $user = $this->userRepository->find($userId);
            if($user !== null && $user->getEmail() === $value){
                return;
            }
        }

        $user = $this->userRepository->findOneBy(
            ['email' => $value]
        );
        if($user !== null){
            $this->context->buildViolation('This email already exist')->addViolation();
        }

    }
}
