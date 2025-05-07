<?php
declare(strict_types=1);

namespace App\User\UseCases\Login\TwoFactorLogin\Actions\GET;

use App\User\UseCases\Login\TwoFactorLogin\TwoFactorService;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Uid\Uuid;

final class TwoFactorGenerateQrAction extends AbstractController
{
    public function __construct(
        private readonly TwoFactorService      $twoFactorService,
        private readonly UriSigner             $uriSigner,
    ){
    }
    #[Route('/2fa/generate-qr', name:'generate_qr', methods: ['GET'])]
    public function generateQR(#[CurrentUser] UserInterface $user, Request $request): Response
    {
        $id = Uuid::fromString($user->getUserIdentifier());

        $isValid = $this->uriSigner->checkRequest($request);
        if (! $isValid) {
            return new Response(
                status: 403
            );
        }
        $builder = new Builder(
            data: $this->twoFactorService->getUserQrCode($id),
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
