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
use Symfony\Contracts\Translation\TranslatorInterface;

class UniqueEmailValidator extends ConstraintValidator
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly TranslatorInterface $translator,
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
            throw new InvalidArgumentException($this->translator->trans('errors.must_be_string'));
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
            $this->context->buildViolation($this->translator->trans('errors.existing_email'))->addViolation();
        }

    }
}
