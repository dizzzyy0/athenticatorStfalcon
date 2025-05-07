<?php

declare(strict_types=1);

namespace App\User\Profile\Update;

use App\User\Support\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class UpdateUserProfileAction extends AbstractController
{
    public function __construct(
        private readonly UpdateUserProfileService $updateUserProfileService,
        private readonly UserRepository $userRepository,
        private readonly FileService $fileService,
        private readonly TranslatorInterface $translator
    ){}

    #[Route('/update-profile', name: 'update_profile', methods: ['GET'])]
    public function getUpdateUserProfile(#[CurrentUser] UserInterface $currentUser): Response
    {
        $user = $this->userRepository->findOneById(Uuid::fromString($currentUser->getUserIdentifier()));

        return $this->render(
            'update/updateProfile.html.twig',
            [
                'user' => $user
            ]
        );
    }

    #[Route('/update-profile', name: 'update_profile_submit', methods: ['POST'])]
    public function updateUserProfile(#[CurrentUser] UserInterface $user, Request $request): Response{

        $userId = Uuid::fromString($user->getUserIdentifier());

        $email = $request->request->get('email');
        $password = $request->request->get('password');

        /** @var ?UploadedFile $profilePictureFile */
        $profilePictureFile = $request->files->get('profile_picture');
        $picturePath = null;

        if ($profilePictureFile !== null) {
            $picturePath = $this->fileService->saveFile($profilePictureFile);
        }
        $updateUserDTO = new UpdateUserDTO (
            $email,
            $password,
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

        $this->addFlash('success', $this->translator->trans('update_profile.success'));

        return $this->redirectToRoute(
            'update_profile',
            [
                'userId' => $userId,
            ]
        );
    }
}
