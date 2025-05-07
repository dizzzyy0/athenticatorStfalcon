<?php

declare(strict_types=1);

namespace App\User\UseCases\Login\TwoFactorLogin\Actions\GET;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TwoFactorAuthenticationAction extends AbstractController
{
    #[Route('/2fa', name: 'two_factor_login', methods: ['GET'])]
    public function twoFactorAuthentication(): Response
    {
        return $this->render('security/twoFactorAuth.html.twig');
    }
}
