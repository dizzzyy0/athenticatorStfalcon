<?php

declare(strict_types=1);

namespace App\User\UseCases\Register;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Contracts\Translation\TranslatorInterface;


class RegisterUserAction extends AbstractController
{
    public function __construct(
        private readonly RegisterUserService $registerUserService,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route(path: '/register', name: 'register', methods: ['GET'])]
    public function register(): Response
    {
        return $this->render('auth/register.html.twig');
    }

    #[Route('/register', name: 'register_submit', methods: ['POST'])]
    public function registerSubmit(Request $request): Response
    {
        /** @var string $email */
        $email = $request->request->get('email');
        /** @var string $password */
        $password = $request->request->get('password');
        /** @var string $passwordConfirm */
        $passwordConfirm = $request->request->get('passwordConfirm');

        $registerDTO = new RegisterDTO($email, $password, $passwordConfirm);
        try {
            $this->registerUserService->register($registerDTO);
            $this->addFlash('success', $this->translator->trans('register.success'));
        } catch (ValidationFailedException $e) {
            $errors = $e->getViolations();
            return $this->render('auth/register.html.twig', [
                'errors' => $errors,
            ]);
        }

        return $this->redirectToRoute('login');
    }
}
