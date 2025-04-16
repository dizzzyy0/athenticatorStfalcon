<?php

declare(strict_types=1);

namespace App\Controller\WebActions;

use App\Services\UserService;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\Font\OpenSans;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\UriSigner;

class TwoFactorAuthenticationAction extends AbstractController
{
    public function __construct(
      private readonly UserService $userService,
      private readonly UriSigner $uriSigner
    ){
    }

    #[Route('/2fa', name: 'tow-factor-login')]
    public function twoFactorAuthentication(): Response
    {
        return $this->render('towFactorAuth.html.twig');
    }

    #[Route('/2fa/enable', name: 'tow-factor-auth-enable', methods: ['POST'])]
    public function twoFactorAuthenticationEnable(Request $request, HttpClientInterface $httpClient): Response
    {
        /** @var string $id */
        $id = $request->request->get('id');
        /** @var string $password */
        $password = $request->request->get('password');

        $uuid = Uuid::fromString($id);
        $success = $this->userService->enableTwoFactorAuthentication($uuid, $password);
        if ($success === true) {
            $this->addFlash('success', 'Tow factor authentication enable.');
        } else {
            $this->addFlash('danger', 'Incorrect password. Try again.');
        }

        return $this->redirectToRoute('main');
    }

    #[Route('/2fa/disable', name: 'tow-factor-auth-disable', methods: ['POST'])]
    public function twoFactorAuthenticationDisable(Request $request, HttpClientInterface $httpClient): Response
    {
        /** @var string $id */
        $id = $request->request->get('id');
        /** @var string $password */
        $password = $request->request->get('password');

        $uuid = Uuid::fromString($id);
        $success = $this->userService->disableTwoFactorAuthentication($uuid, $password);
        if ($success === true) {
            $this->addFlash('success', 'Tow factor authentication disable.');
        } else {
            $this->addFlash('danger', 'Incorrect password. Try again.');
        }
        return $this->render('main.html.twig');
    }

    #[Route('/2fa/create-qr/{id}', name:'create-qr', methods: ['GET'])]
    public function createQR(Uuid $id): Response
    {
        $builder = new Builder(
            writer: new PngWriter(),
            writerOptions: [],
            validateResult: false,
            data: $this->userService->getUserQrCodeData($id),
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            labelText: 'QR code for authentication',
            labelFont: new OpenSans(15),
            labelAlignment: LabelAlignment::Center,
            logoResizeToWidth: 50,
            logoPunchoutBackground: true
        );
        $qrCode = $builder->build();
        return new Response(
            $this->uriSigner->sign($qrCode->getDataUri(), new \DateInterval('P1Y')),
            Response::HTTP_OK,
            [
                'content-type' => 'image/png',
                'Content-Disposition' => 'inline; filename="qrCode.png"',
            ],
        );
    }
}
