<?php
declare(strict_types=1);

namespace App\Listeners;

use Scheb\TwoFactorBundle\Security\TwoFactor\Event\TwoFactorAuthenticationEvent;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

#[AsEventListener(event: TwoFactorAuthenticationEvent::class, method: 'onTwoFactorLoginFailure')]
final readonly class TwoFactorLimiterListener
{
    public function __construct(
        #[Autowire(service: 'limiter.two_factor_login')]
      private RateLimiterFactory $twoFactorLimiter,
    ){}

    public function onTwoFactorLoginFailure(TwoFactorAuthenticationEvent $event): void{
        $user = $event->getToken()->getUser();
        $limiter = $this->twoFactorLimiter->create($user);

        if (!$limiter->consume(1)->isAccepted()) {
            throw new CustomUserMessageAuthenticationException('Too many login attempts. Try later.');
        }
    }

}
