<?php

namespace App\Controller;

use App\Repository\EventRepository;
use App\Repository\GameRepository;
use App\Form\RegistrationFormType;
use App\Form\UserProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class UserController extends AbstractController
{
    #[Route('/dashboard', name: 'user_dashboard')]
    public function dashboard(
        EventRepository $eventRepository,
        GameRepository $gameRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        // Get upcoming events (next 5)
        $upcomingEvents = $eventRepository->findBy(
            [],
            ['date' => 'ASC'],
            5
        );

        $recommendedGames = $gameRepository->findBy(
            [],
            ['id' => 'DESC'],
            6
        );

        $recentActivity = [];

        foreach ($user->getReviews() as $review) {
            $recentActivity[] = [
                'type' => 'review',
                'item' => $review,
                'date' => $review->getCreatedAt(),
                'action' => 'Reviewed',
            ];
        }

        usort($recentActivity, function($a, $b) {
            return $b['date'] <=> $a['date'];
        });
        $recentActivity = array_slice($recentActivity, 0, 10);

        return $this->render('user/dashboard.html.twig', [
            'user' => $user,
            'upcomingEvents' => $upcomingEvents,
            'recommendedGames' => $recommendedGames,
            'recentActivity' => $recentActivity,
        ]);
    }

    #[Route('/profile', name: 'user_profile')]
    public function profile(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        $form = $this->createForm(UserProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle avatar upload
            $avatarFile = $form->get('avatar')->getData();

            if ($avatarFile) {
                $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $avatarFile->guessExtension();

                try {
                    $avatarFile->move(
                        $this->getParameter('avatars_directory'),
                        $newFilename
                    );

                    // Remove old avatar if exists
                    if ($user->getAvatar()) {
                        $oldAvatarPath = $this->getParameter('avatars_directory') . '/' . $user->getAvatar();
                        if (file_exists($oldAvatarPath)) {
                            unlink($oldAvatarPath);
                        }
                    }

                    $user->setAvatar('uploads/avatars/'.$newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload avatar. Please try again.');
                    return $this->redirectToRoute('user_profile');
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Profile updated successfully!');

            return $this->redirectToRoute('user_profile');
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'profileForm' => $form->createView(),
        ]);
    }
}
