<?php
declare(strict_types=1);

namespace App\User\Email\Validator;

use App\User\Support\UserRepository;
use InvalidArgumentException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Uid\Uuid;
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

        if(!$constraint instanceof UniqueEmail){
            throw new UnexpectedTypeException($constraint, self::class);
        }

        if($value === null){
            return;
        }

        if(!is_string($value) ){
            throw new InvalidArgumentException('Value must be a string');
        }

        $currentUserToken = $this->tokenStorage->getToken();

        if ($currentUserToken instanceof TokenInterface) {
            $currentUser = $this->userRepository->findOneById(Uuid::fromString($currentUserToken->getUserIdentifier()));

            if ($currentUser->getEmail() === $value) {
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
