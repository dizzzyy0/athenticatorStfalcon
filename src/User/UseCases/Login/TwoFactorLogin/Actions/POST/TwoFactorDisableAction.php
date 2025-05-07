<?php
declare(strict_types=1);

namespace App\User\UseCases\Login\TwoFactorLogin\Actions\POST;

use App\User\UseCases\Login\TwoFactorLogin\TwoFactorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

final class TwoFactorDisableAction extends AbstractController
{
    public function __construct(
        private readonly TwoFactorService      $twoFactorService,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly TranslatorInterface $translator,
    ){
    }
    #[Route('/2fa/disable', name: 'disable', methods: ['POST'])]
    public function twoFactorAuthenticationDisable(Request $request): Response
    {
        if ($this->tokenStorage->getToken() instanceof TokenInterface) {
            $uuidId = Uuid::fromString($this->tokenStorage->getToken()->getUserIdentifier());
        } else {
            return new Response(status: 401);
        }

        /** @var string $password */
        $password = $request->request->get('password');

        $success = $this->twoFactorService->disableTwoFactorAuthentication($uuidId, $password);
        if ($success) {
            $this->addFlash('success', $this->translator->trans('two_factor.success_disable'));
        } else {
            $this->addFlash('danger', $this->translator->trans('two_factor.danger'));
        }

        return $this->render('profile.html.twig');
    }

}
