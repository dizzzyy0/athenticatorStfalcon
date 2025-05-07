<?php
declare(strict_types=1);

namespace App\User\UseCases\Login\Listeners;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Contracts\Translation\TranslatorInterface;


#[AsEventListener(event: LoginFailureEvent::class, method: 'onLoginFailure')]
final readonly class LoginLimiterListener
{

    public function __construct(
        #[Autowire( '@limiter.login')]
        private RateLimiterFactory $loginLimiter,
        private TranslatorInterface $translator,
    ){}

    public function onLoginFailure(LoginFailureEvent $event): void{

        $request = $event->getRequest();
        $ip = $request->getClientIp();
        $limiter = $this->loginLimiter->create($ip);

        if (!$limiter->consume()->isAccepted()) {
            throw new TooManyRequestsHttpException($this->translator->trans('login.login_failure'));
        }
    }
}
