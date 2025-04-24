<?php
declare(strict_types=1);

namespace App\Listeners;

use Psr\Log\LoggerInterface;
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
        private LoggerInterface $logger,
    ){}

    public function onLoginFailure(LoginFailureEvent $event): void{

        $request = $event->getRequest();
        $ip = $request->getClientIp();
        $limiter = $this->loginLimiter->create($ip);

        $this->logger->info('LoginEventListener triggered');
        if (!$limiter->consume()->isAccepted()) {
            throw new CustomUserMessageAuthenticationException('Too many login attempts. Try later.');
        }
    }
}
