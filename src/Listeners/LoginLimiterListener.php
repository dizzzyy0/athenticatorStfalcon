<?php
declare(strict_types=1);

namespace App\Listeners;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;


#[AsEventListener(event: LoginFailureEvent::class, method: 'onLoginFailure')]
final readonly class LoginLimiterListener
{
    public function __construct(
        #[Autowire(service: 'limiter.login')]
        private RateLimiterFactory $loginLimiter,
    ){}

    public function onLoginFailure(LoginFailureEvent $event): void{
        $request = $event->getRequest();
        $email = $request->getSession()->get('email');
        $limiter = $this->loginLimiter->create($email);

        if (!$limiter->consume(1)->isAccepted()) {
            throw new CustomUserMessageAuthenticationException('Too many login attempts. Try later.');
        }
    }
}
