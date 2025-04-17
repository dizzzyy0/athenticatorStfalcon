<?php

declare(strict_types=1);

namespace App\Controller\UserActions;

use App\DTO\RegisterDTO;
use App\Services\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RegisterUserAction extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
    ) {
    }

    #[Route(path: '/auth/register', name: 'register', methods: ['GET'])]
    public function register(): Response
    {
        return $this->render('auth/register.html.twig');
    }

    #[Route('/auth/register/submit', name: 'register-submit', methods: ['POST'])]
    public function registerSubmit(Request $request, HttpClientInterface $httpClient): Response
    {
        /** @var string $email */
        $email = $request->request->get('email');
        /** @var string $password */
        $password = $request->request->get('password');
        /** @var string $passwordConfirm */
        $passwordConfirm = $request->request->get('passwordConfirm');

        if ($password !== $passwordConfirm) {
            $this->addFlash('error', 'Passwords do not match.');
            return $this->redirectToRoute('register');
        }
        $registerDTO = new RegisterDTO($email, $password);
        try {
            $this->userService->register($registerDTO);
            $this->addFlash('success', 'Register is successfully. Login to your account.');

            return $this->redirectToRoute('login');

        } catch (ValidationFailedException $e) {
            $message = $e->getViolations()[0]->getMessage();
            $this->addFlash('danger', $message);
            return $this->redirectToRoute('register');
        }
    }
}
