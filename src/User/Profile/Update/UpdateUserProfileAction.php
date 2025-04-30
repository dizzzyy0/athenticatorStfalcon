<?php

declare(strict_types=1);

namespace App\User\Profile\Update;

use App\Services\UserService;
use App\User\Support\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class UpdateUserProfileAction extends AbstractController
{
    public function __construct(
        private readonly UpdateUserProfileService $updateUserProfileService,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly UserRepository $userRepository,
        private readonly FileService $fileService,
    ){}

    #[Route('/update-profile/{userId}', name: 'update_profile', methods: ['GET'])]
    public function getUpdateUserProfile(Uuid $userId, Request $request): Response
    {
        $user = $this->userRepository->findOneById($userId);
        $request->query->get('errorMessage');
        $request->query->get('invalidValue');

        return $this->render(
            'update/updateProfile.html.twig',
            [
                'user' => $user
            ]
        );
    }

    #[Route('/update-profile/{userId}', name: 'update_profile_submit', methods: ['POST'])]
    public function updateUserProfile(Uuid $userId, Request $request): Response{
        $currentUserId = $this->tokenStorage->getToken()?->getUserIdentifier();

        if($currentUserId === null){
            return new Response(status: 401);
        }

        if(! Uuid::fromString($currentUserId)->equals($userId)){
            return new Response(status: 403);
        }

        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $passwordConfirm = empty($password) ? null : $password;
        /** @var ?UploadedFile $profilePictureFile */
        $profilePictureFile = $request->files->get('profile_picture');
        $picturePath = null;

        if ($profilePictureFile !== null) {
            $picturePath = $this->fileService->saveFile($profilePictureFile);
        }
        $updateUserDTO = new UpdateUserDTO (
            $email,
            $passwordConfirm,
            $picturePath
        );

        try {
            $this->updateUserProfileService->updateUserProfile($userId, $updateUserDTO);
        } catch (ValidationFailedException $e) {
            $user = $this->userRepository->findOneById($userId);
            return $this->render('update/updateProfile.html.twig', [
                'user' => $user,
                'errors' => $e->getViolations(),
            ]);
        }

        $this->addFlash('success', 'Profile updated successfully.');

        return $this->redirectToRoute(
            'update_profile',
            [
                'userId' => $userId,
            ]
        );
    }
}
