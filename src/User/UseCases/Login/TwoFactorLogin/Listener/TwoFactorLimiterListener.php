<?php
declare(strict_types=1);

namespace App\User\UseCases\Login\TwoFactorLogin\Listener;

use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;


#[AsEventListener(event: RequestEvent::class, method: 'onTwoFactorLoginFailure')]
final readonly class TwoFactorLimiterListener
{
    public function __construct(
        #[Autowire(service: 'limiter.two_factor_login')]
      private RateLimiterFactory $twoFactorLimiter,
    ){}

    public function onTwoFactorLoginFailure(RequestEvent $event): void{
        $request = $event->getRequest();
        if ($request->getPathInfo() !== '/2fa_check' || ! $request->isMethod('POST')) {
            return;
        }

        $ip = $request->getClientIp();
        if ($ip === null) {
            throw new RuntimeException('IP address is not available.');
        }

        $limiter = $this->twoFactorLimiter->create($ip);

        if (!$limiter->consume()->isAccepted()) {
            throw new CustomUserMessageAuthenticationException('Too many login attempts. Try later.');
        }
    }
}
