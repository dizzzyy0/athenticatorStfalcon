<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Entity\User;
use App\Services\UpdateUserProfileService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

#[AsEventListener(event: LoginSuccessEvent::class, method: 'onLoginSuccessEvent')]
readonly class SuccessLoginListener
{
    public function __construct(
        private UpdateUserProfileService $userProfileService
    ) {
    }

    public function onLoginSuccessEvent(LoginSuccessEvent $loginSuccessEvent): void
    {
        $user = $loginSuccessEvent->getUser();

        if (! $user instanceof User) {
            return;
        }

        $this->userProfileService->udateLastLogin($user->getId());
    }

}
