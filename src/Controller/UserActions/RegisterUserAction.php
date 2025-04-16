<?php
declare(strict_types=1);

namespace App\Controller\UserActions;

use App\DTO\RegisterDTO;
use App\Services\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RegisterUserAction extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
    ){}
    #[Route(path: '/auth/register', name: 'register')]
    public function register(): Response{
        return $this->render('auth/register.html.twig');
    }

    #[Route('/auth/register/process', name: 'registerProcess', methods: ['POST'])]
    public function registerProcess(Request $request, HttpClientInterface $httpClient): Response
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');
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

        } catch (ClientExceptionInterface $e) {
            $error = json_decode($e->getResponse()->getContent(false), true);
            $message = $error['message'] ?? 'Error during registration';
            $this->addFlash('error', $message);
            return $this->redirectToRoute('register');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Undefined error');
            return $this->redirectToRoute('register');
        }
    }
}
