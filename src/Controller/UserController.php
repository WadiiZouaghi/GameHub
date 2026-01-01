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
    public function dashboard(EventRepository $eventRepo, GameRepository $gameRepo): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        $recentActivity = array_map(fn($r) => [
            'type' => 'review',
            'item' => $r,
            'date' => $r->getCreatedAt(),
            'action' => 'Reviewed',
        ], $user->getReviews()->toArray());

        usort($recentActivity, fn($a, $b) => $b['date'] <=> $a['date']);

        return $this->render('user/dashboard.html.twig', [
            'user' => $user,
            'upcomingEvents' => $eventRepo->findBy([], ['date' => 'ASC'], 5),
            'recommendedGames' => $gameRepo->findBy([], ['id' => 'DESC'], 6),
            'recentActivity' => array_slice($recentActivity, 0, 10),
        ]);
    }

    #[Route('/profile', name: 'user_profile')]
    public function profile(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        $form = $this->createForm(UserProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $avatarFile = $form->get('avatar')->getData();

            if ($avatarFile) {
                $name = $slugger->slug(pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME));
                $filename = $name . '-' . uniqid() . '.' . $avatarFile->guessExtension();

                try {
                    $avatarFile->move($this->getParameter('avatars_directory'), $filename);

                    if ($user->getAvatar()) {
                        $old = $this->getParameter('avatars_directory') . '/' . $user->getAvatar();
                        if (file_exists($old)) unlink($old);
                    }

                    $user->setAvatar('uploads/avatars/' . $filename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload avatar.');
                    return $this->redirectToRoute('user_profile');
                }
            }

            $em->flush();
            $this->addFlash('success', 'Profile updated successfully!');
            return $this->redirectToRoute('user_profile');
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'profileForm' => $form->createView(),
        ]);
    }
}
