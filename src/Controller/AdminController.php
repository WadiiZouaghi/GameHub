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
use App\Repository\PurchaseRepository;
use App\Repository\NewsRepository;
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
        GameRepository $gameRepo,
        UserRepository $userRepo,
        EventRepository $eventRepo,
        ReviewRepository $reviewRepo,
        PurchaseRepository $purchaseRepo
    ): Response {
        $this->denyAccessUnlessAdmin();

        $startDate = new \DateTime('-30 days');

        return $this->render('admin/dashboard.html.twig', [
            'stats' => [
                'totalUsers' => $userRepo->count([]),
                'totalGames' => $gameRepo->count([]),
                'totalEvents' => $eventRepo->count([]),
                'totalReviews' => $reviewRepo->count([]),
                'totalPurchases' => $purchaseRepo->count([]),
            ],
            'recentUsers' => $userRepo->findBy([], ['createdAt' => 'DESC'], 5),
            'userTrendData' => $userRepo->getNewUsersTrendData($startDate),
            'gameTrendData' => $gameRepo->getNewGamesTrendData($startDate),
        ]);
    }

    #[Route('/games', name: 'admin_games')]
    public function games(GameRepository $gameRepo): Response
    {
        $this->denyAccessUnlessAdmin();
        return $this->render('admin/games.html.twig', ['games' => $gameRepo->findAll()]);
    }

    #[Route('/games/new', name: 'admin_games_new')]
    public function newGame(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
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

        return $this->render('admin/game_new.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/games/{id}/edit', name: 'admin_games_edit')]
    public function editGame(Request $request, Game $game, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessAdmin();

        $form = $this->createForm(GameType::class, $game);
        
        $this->populateSystemRequirements($form, $game->getMinSystemRequirements(), 'min');
        $this->populateSystemRequirements($form, $game->getRecommendedSystemRequirements(), 'rec');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleSystemRequirements($form, $game);
            $this->handleFiles($form, $game, $slugger);
            $em->flush();

            $this->addFlash('success', 'Game updated successfully!');
            return $this->redirectToRoute('admin_games');
        }

        return $this->render('admin/game_edit.html.twig', ['game' => $game, 'form' => $form->createView()]);
    }

    private function populateSystemRequirements($form, ?array $reqs, string $prefix): void
    {
        if (!$reqs) return;

        foreach (['Os', 'Processor', 'Memory', 'Graphics', 'Storage'] as $field) {
            $form->get($prefix . $field)->setData($reqs[strtolower($field)] ?? null);
        }
    }

    private function handleSystemRequirements($form, Game $game): void
    {
        $fields = ['os', 'processor', 'memory', 'graphics', 'storage'];

        $min = array_filter(array_combine($fields, array_map(fn($f) => $form->get('min' . ucfirst($f))->getData(), $fields)));
        $game->setMinSystemRequirements($min ?: null);

        $rec = array_filter(array_combine($fields, array_map(fn($f) => $form->get('rec' . ucfirst($f))->getData(), $fields)));
        $game->setRecommendedSystemRequirements($rec ?: null);
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
    public function deleteGame(Game $game, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessAdmin();
        $em->remove($game);
        $em->flush();
        $this->addFlash('success', 'Game deleted successfully!');
        return $this->redirectToRoute('admin_games');
    }

    #[Route('/users', name: 'admin_users')]
    public function users(UserRepository $userRepo): Response
    {
        $this->denyAccessUnlessAdmin();
        return $this->render('admin/users.html.twig', ['users' => $userRepo->findAll()]);
    }

    #[Route('/users/{id}/edit', name: 'admin_users_edit')]
    public function editUser(User $user, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessAdmin();

        $form = $this->createForm(UserAdminType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'User updated successfully!');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/user_edit.html.twig', ['user' => $user, 'form' => $form->createView()]);
    }

    #[Route('/users/{id}/delete', name: 'admin_users_delete', methods: ['POST'])]
    public function deleteUser(User $user, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessAdmin();

        if ($count = $user->getPurchases()->count()) {
            $this->addFlash('error', "Cannot delete user with existing purchases. User has $count purchase(s) in the system.");
            return $this->redirectToRoute('admin_users');
        }

        $em->remove($user);
        $em->flush();
        $this->addFlash('success', 'User deleted successfully!');
        return $this->redirectToRoute('admin_users');
    }

    #[Route('/events', name: 'admin_events')]
    public function events(EventRepository $eventRepo): Response
    {
        $this->denyAccessUnlessAdmin();
        return $this->render('admin/events.html.twig', ['events' => $eventRepo->findAll()]);
    }

    #[Route('/events/new', name: 'admin_events_new')]
    public function newEvent(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessAdmin();

        $form = $this->createForm(EventType::class, new Event());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($form->getData());
            $em->flush();
            $this->addFlash('success', 'Event created successfully!');
            return $this->redirectToRoute('admin_events');
        }

        return $this->render('admin/event_new.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/events/{id}/edit', name: 'admin_events_edit')]
    public function editEvent(Event $event, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessAdmin();

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Event updated successfully!');
            return $this->redirectToRoute('admin_events');
        }

        return $this->render('admin/event_edit.html.twig', ['event' => $event, 'form' => $form->createView()]);
    }

    #[Route('/events/{id}/delete', name: 'admin_events_delete', methods: ['POST'])]
    public function deleteEvent(Event $event, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessAdmin();
        $em->remove($event);
        $em->flush();
        $this->addFlash('success', 'Event deleted successfully!');
        return $this->redirectToRoute('admin_events');
    }

    #[Route('/reviews', name: 'admin_reviews')]
    public function reviews(ReviewRepository $reviewRepo): Response
    {
        $this->denyAccessUnlessAdmin();
        return $this->render('admin/reviews.html.twig', ['reviews' => $reviewRepo->findAll()]);
    }

    #[Route('/reviews/{id}/delete', name: 'admin_reviews_delete', methods: ['POST'])]
    public function deleteReview(Review $review, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessAdmin();
        $em->remove($review);
        $em->flush();
        $this->addFlash('success', 'Review deleted successfully!');
        return $this->redirectToRoute('admin_reviews');
    }
}
