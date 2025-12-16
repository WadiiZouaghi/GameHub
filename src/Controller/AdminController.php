<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\User;
use App\Entity\Event;
use App\Entity\Review;
use App\Form\GameType;
use App\Form\UserAdminType;
use App\Form\EventType;
use App\Repository\GameRepository;
use App\Repository\UserRepository;
use App\Repository\EventRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin')]
class AdminController extends AbstractController
{
    private function denyAccessUnlessAdmin(): void
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
    }

    #[Route('', name: 'admin_index')]
    public function index(): Response
    {
        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/dashboard', name: 'admin_dashboard')]
    public function dashboard(
        GameRepository $gameRepository,
        UserRepository $userRepository,
        EventRepository $eventRepository
    ): Response {
        $this->denyAccessUnlessAdmin();

        return $this->render('admin/dashboard.html.twig', [
            'stats' => [
                'totalUsers' => $userRepository->count([]),
                'totalGames' => $gameRepository->count([]),
                'totalEvents' => $eventRepository->count([]),
            ],
        ]);
    }

    #[Route('/games', name: 'admin_games')]
    public function games(GameRepository $gameRepository): Response
    {
        $this->denyAccessUnlessAdmin();

        return $this->render('admin/games.html.twig', [
            'games' => $gameRepository->findAll(),
        ]);
    }

    #[Route('/games/new', name: 'admin_games_new')]
    public function newGame(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        $this->denyAccessUnlessAdmin();

        $game = new Game();
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleSystemRequirements($form, $game);
            $this->handleFiles($form, $game, $slugger);

            $em->persist($game);
            $em->flush();

            $this->addFlash('success', 'Game created successfully!');
            return $this->redirectToRoute('admin_games');
        }

        return $this->render('admin/game_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/games/{id}/edit', name: 'admin_games_edit')]
    public function editGame(
        Request $request,
        Game $game,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        $this->denyAccessUnlessAdmin();

        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleSystemRequirements($form, $game);
            $this->handleFiles($form, $game, $slugger);

            $em->flush();

            $this->addFlash('success', 'Game updated successfully!');
            return $this->redirectToRoute('admin_games');
        }

        return $this->render('admin/game_edit.html.twig', [
            'game' => $game,
            'form' => $form->createView(),
        ]);
    }

    private function handleSystemRequirements($form, Game $game): void
    {
        $min = array_filter([
            'os' => $form->get('minOs')->getData(),
            'processor' => $form->get('minProcessor')->getData(),
            'memory' => $form->get('minMemory')->getData(),
            'graphics' => $form->get('minGraphics')->getData(),
            'storage' => $form->get('minStorage')->getData(),
        ]);

        if ($min) {
            $game->setMinSystemRequirements($min);
        }

        $rec = array_filter([
            'os' => $form->get('recOs')->getData(),
            'processor' => $form->get('recProcessor')->getData(),
            'memory' => $form->get('recMemory')->getData(),
            'graphics' => $form->get('recGraphics')->getData(),
            'storage' => $form->get('recStorage')->getData(),
        ]);

        if ($rec) {
            $game->setRecommendedSystemRequirements($rec);
        }
    }

    private function handleFiles($form, Game $game, SluggerInterface $slugger): void
    {
        $baseDir = $this->getParameter('kernel.project_dir') . '/public/uploads';

        $cover = $form->get('coverImage')->getData();
        if ($cover) {
            $name = $slugger->slug(pathinfo($cover->getClientOriginalName(), PATHINFO_FILENAME));
            $filename = $name . '-' . uniqid() . '.' . $cover->guessExtension();

            $cover->move($baseDir . '/covers', $filename);
            $game->setCoverImage('uploads/covers/' . $filename);
        }

        $galleryFiles = $form->get('gallery')->getData();
        if ($galleryFiles) {
            $paths = [];
            foreach ($galleryFiles as $file) {
                $name = $slugger->slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
                $filename = $name . '-' . uniqid() . '.' . $file->guessExtension();

                $file->move($baseDir . '/gallery', $filename);
                $paths[] = 'uploads/gallery/' . $filename;
            }
            if ($paths) {
                $game->setGallery($paths);
            }
        }
    }

    #[Route('/games/{id}/delete', name: 'admin_games_delete', methods: ['POST'])]
    public function deleteGame(
        Game $game,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessAdmin();

        $em->remove($game);
        $em->flush();

        $this->addFlash('success', 'Game deleted successfully!');
        return $this->redirectToRoute('admin_games');
    }

    #[Route('/users', name: 'admin_users')]
    public function users(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessAdmin();

        return $this->render('admin/users.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/users/{id}/edit', name: 'admin_users_edit')]
    public function editUser(
        User $user,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessAdmin();

        $form = $this->createForm(UserAdminType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'User updated successfully!');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/user_edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/users/{id}/delete', name: 'admin_users_delete', methods: ['POST'])]
    public function deleteUser(
        User $user,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessAdmin();

        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'User deleted successfully!');
        return $this->redirectToRoute('admin_users');
    }

    #[Route('/events', name: 'admin_events')]
    public function events(EventRepository $eventRepository): Response
    {
        $this->denyAccessUnlessAdmin();

        return $this->render('admin/events.html.twig', [
            'events' => $eventRepository->findAll(),
        ]);
    }

    #[Route('/events/new', name: 'admin_events_new')]
    public function newEvent(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessAdmin();

        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($event);
            $em->flush();

            $this->addFlash('success', 'Event created successfully!');
            return $this->redirectToRoute('admin_events');
        }

        return $this->render('admin/event_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/events/{id}/edit', name: 'admin_events_edit')]
    public function editEvent(
        Event $event,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessAdmin();

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Event updated successfully!');
            return $this->redirectToRoute('admin_events');
        }

        return $this->render('admin/event_edit.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/events/{id}/delete', name: 'admin_events_delete', methods: ['POST'])]
    public function deleteEvent(
        Event $event,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessAdmin();

        $em->remove($event);
        $em->flush();

        $this->addFlash('success', 'Event deleted successfully!');
        return $this->redirectToRoute('admin_events');
    }

    #[Route('/reviews', name: 'admin_reviews')]
    public function reviews(ReviewRepository $reviewRepository): Response
    {
        $this->denyAccessUnlessAdmin();

        return $this->render('admin/reviews.html.twig', [
            'reviews' => $reviewRepository->findAll(),
        ]);
    }

    #[Route('/reviews/{id}/delete', name: 'admin_reviews_delete', methods: ['POST'])]
    public function deleteReview(
        Review $review,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessAdmin();

        $em->remove($review);
        $em->flush();

        $this->addFlash('success', 'Review deleted successfully!');
        return $this->redirectToRoute('admin_reviews');
    }
}
