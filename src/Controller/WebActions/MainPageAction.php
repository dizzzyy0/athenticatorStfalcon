<?php
declare(strict_types=1);

namespace App\Controller\WebActions;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainPageAction extends AbstractController
{
    #[Route(path: '/main', name: 'main')]
    public function main(): Response
    {
        return $this->render('main.html.twig');
    }
}
