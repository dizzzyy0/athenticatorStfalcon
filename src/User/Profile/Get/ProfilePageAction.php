<?php
declare(strict_types=1);
namespace App\User\Profile\Get;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ProfilePageAction extends AbstractController
{
    public function __construct(
        private readonly UriSigner $uriSigner,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    #[Route(path: '/profile', name: 'profile')]
    public function profile(): Response
    {
        $id = $this->tokenStorage->getToken()?->getUserIdentifier();
        $qrCodeUrl = $this->urlGenerator->generate(
            'generate_qr',
            [
                'id' => $id,
            ],
            referenceType: UrlGeneratorInterface::ABSOLUTE_URL
        );

        $signedUrl = $this->uriSigner->sign($qrCodeUrl);
        return $this->render(
            'profile.html.twig',
            [
                'url' => $signedUrl,
            ]
        );
    }
}
