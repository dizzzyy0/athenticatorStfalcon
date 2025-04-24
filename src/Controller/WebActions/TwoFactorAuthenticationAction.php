<?php

declare(strict_types=1);

namespace App\Controller\WebActions;

use App\Services\UserService;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Uid\Uuid;

class TwoFactorAuthenticationAction extends AbstractController
{


    public function __construct(
      private readonly UserService $userService,
      private readonly TokenStorageInterface $tokenStorage,
      private readonly UriSigner $uriSigner,
    ){
    }

    #[Route('/2fa', name: 'two-factor-login', methods: ['GET'])]
    public function twoFactorAuthentication(): Response
    {
        return $this->render('security/twoFactorAuth.html.twig');
    }

    #[Route('enable', name: 'two-factor-auth-enable', methods: ['POST'])]
    public function twoFactorAuthenticationEnable(Request $request): Response
    {
        if ($this->tokenStorage->getToken() instanceof TokenInterface) {
            $uuidId = Uuid::fromString($this->tokenStorage->getToken()->getUserIdentifier());
        } else {
            return new Response(status: 401);
        }
        /** @var string $password */
        $password = $request->request->get('password');

        $success = $this->userService->enableTwoFactorAuthentication($uuidId, $password);
        if ($success) {
            $this->addFlash('success', 'two factor authentication enable.');
        } else {
            $this->addFlash('danger', 'Incorrect password. Try again.');
        }

        return $this->redirectToRoute('main');
    }

    #[Route('disable', name: 'two-factor-auth-disable', methods: ['POST'])]
    public function twoFactorAuthenticationDisable(Request $request): Response
    {
        if ($this->tokenStorage->getToken() instanceof TokenInterface) {
            $uuidId = Uuid::fromString($this->tokenStorage->getToken()->getUserIdentifier());
        } else {
            return new Response(status: 401);
        }

        /** @var string $password */
        $password = $request->request->get('password');

        $success = $this->userService->disableTwoFactorAuthentication($uuidId, $password);
        if ($success) {
            $this->addFlash('success', 'two factor authentication disable.');
        } else {
            $this->addFlash('danger', 'Incorrect password. Try again.');
        }

        return $this->render('main.html.twig');
    }

    #[Route('/2fa/create-qr/{id}', name:'create_qr', methods: ['GET'])]
    public function createQR(Uuid $id, Request $request): Response
    {
        $isValid = $this->uriSigner->checkRequest($request);
        if (! $isValid) {
            return new Response(
                status: 403
            );
        }
        $builder = new Builder(
            data: $this->userService->getUserQrCode($id),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            logoResizeToWidth: 50,
            logoPunchoutBackground: true
        );
        $qrCode = $builder->build();
        return new Response(
            $qrCode->getString(),
            Response::HTTP_OK,
            [
                'content-type' => 'image/png',
                'Content-Disposition' => 'inline; filename="qrCode.png"',
            ],
        );
    }
}
