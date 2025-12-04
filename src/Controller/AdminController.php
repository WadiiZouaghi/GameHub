<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\User;
use App\Entity\Reservation;
use App\Entity\Event;
use App\Entity\Review;
use App\Form\GameType;
use App\Form\UserAdminType;
use App\Form\EventType;
use App\Form\ReservationType;
use App\Repository\GameRepository;
use App\Repository\UserRepository;
use App\Repository\ReservationRepository;
use App\Repository\EventRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

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
        ReservationRepository $reservationRepository,
        EventRepository $eventRepository
    ): Response
    {
        $this->denyAccessUnlessAdmin();

        $qb = $gameRepository->createQueryBuilder('g')
            ->select('g, COUNT(r.id) as reservationCount')
            ->leftJoin('g.reservations', 'r', 'WITH', 'r.status = :confirmed')
            ->setParameter('confirmed', 'confirmed')
            ->groupBy('g.id')
            ->orderBy('reservationCount', 'DESC')
            ->setMaxResults(5);

        $topGamesResult = $qb->getQuery()->getResult();

        $topGames = [];
        foreach ($topGamesResult as $row) {
            $topGames[] = [
                'game' => $row[0],
                'reservations' => $row['reservationCount']
            ];
        }

        $stats = [
            'totalUsers' => $userRepository->count([]),
            'totalGames' => $gameRepository->count([]),
            'totalReservations' => $reservationRepository->count([]),
            'totalEvents' => $eventRepository->count([]),
            'pendingReservations' => $reservationRepository->count(['status' => 'pending']),
            'confirmedReservations' => $reservationRepository->count(['status' => 'confirmed']),
        ];

        $recentReservations = $reservationRepository->findBy([], ['reservationDate' => 'DESC'], 5);
        $recentUsers = $userRepository->findBy([], ['createdAt' => 'DESC'], 5);

        return $this->render('admin/dashboard.html.twig', [
            'topGames' => $topGames,
            'stats' => $stats,
            'recentReservations' => $recentReservations,
            'recentUsers' => $recentUsers,
        ]);
    }

    #[Route('/games', name: 'admin_games')]
    public function games(GameRepository $gameRepository): Response
    {
        $this->denyAccessUnlessAdmin();

        $games = $gameRepository->findAll();

        return $this->render('admin/games.html.twig', [
            'games' => $games,
        ]);
    }

    #[Route('/games/new', name: 'admin_games_new')]
    public function newGame(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $this->denyAccessUnlessAdmin();

        $game = new Game();
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($game);
            $entityManager->flush();
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
        EntityManagerInterface $entityManager
    ): Response
    {
        $this->denyAccessUnlessAdmin();

        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Game updated successfully!');

            return $this->redirectToRoute('admin_games');
        }

        return $this->render('admin/game_edit.html.twig', [
            'game' => $game,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/games/{id}/delete', name: 'admin_games_delete', methods: ['POST'])]
    public function deleteGame(
        Request $request,
        Game $game,
        EntityManagerInterface $entityManager
    ): Response
    {
        $this->denyAccessUnlessAdmin();

        if ($this->isCsrfTokenValid('delete'.$game->getId(), $request->request->get('_token'))) {
            // Remove related entities first to avoid foreign key constraint violations
            foreach ($game->getPurchases() as $purchase) {
                $entityManager->remove($purchase);
            }
            foreach ($game->getReservations() as $reservation) {
                $entityManager->remove($reservation);
            }
            foreach ($game->getReviews() as $review) {
                $entityManager->remove($review);
            }
            foreach ($game->getEvents() as $event) {
                $entityManager->remove($event);
            }

            $entityManager->remove($game);
            $entityManager->flush();
            $this->addFlash('success', 'Game deleted successfully!');
        }

        return $this->redirectToRoute('admin_games');
    }

    #[Route('/users', name: 'admin_users')]
    public function users(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessAdmin();

        $users = $userRepository->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/users/{id}/edit', name: 'admin_users_edit')]
    public function editUser(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager
    ): Response
    {
        $this->denyAccessUnlessAdmin();

        $form = $this->createForm(UserAdminType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
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
        Request $request,
        User $user,
        EntityManagerInterface $entityManager
    ): Response
    {
        $this->denyAccessUnlessAdmin();

        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', 'User deleted successfully!');
        }

        return $this->redirectToRoute('admin_users');
    }

    #[Route('/reservations', name: 'admin_reservations')]
    public function reservations(ReservationRepository $reservationRepository): Response
    {
        $this->denyAccessUnlessAdmin();

        $reservations = $reservationRepository->findBy([], ['reservationDate' => 'DESC']);

        return $this->render('admin/reservations.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/reservations/new', name: 'admin_reservations_new')]
    public function newReservation(
        Request $request,
        EntityManagerInterface $entityManager,
        GameRepository $gameRepository,
        UserRepository $userRepository
    ): Response
    {
        $this->denyAccessUnlessAdmin();

        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reservation);
            $entityManager->flush();
            $this->addFlash('success', 'Reservation created successfully!');

            return $this->redirectToRoute('admin_reservations');
        }

        return $this->render('admin/reservation_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/reservations/{id}/confirm', name: 'admin_reservations_confirm', methods: ['POST'])]
    public function confirmReservation(
        Request $request,
        Reservation $reservation,
        EntityManagerInterface $entityManager
    ): Response
    {
        $this->denyAccessUnlessAdmin();

        if ($this->isCsrfTokenValid('confirm'.$reservation->getId(), $request->request->get('_token'))) {
            $reservation->setStatus('confirmed');
            $entityManager->flush();
            $this->addFlash('success', 'Reservation confirmed successfully!');
        }

        return $this->redirectToRoute('admin_reservations');
    }

    #[Route('/reservations/{id}/reject', name: 'admin_reservations_reject', methods: ['POST'])]
    public function rejectReservation(
        Request $request,
        Reservation $reservation,
        EntityManagerInterface $entityManager
    ): Response
    {
        $this->denyAccessUnlessAdmin();

        if ($this->isCsrfTokenValid('reject'.$reservation->getId(), $request->request->get('_token'))) {
            $reservation->setStatus('cancelled');
            $entityManager->flush();
            $this->addFlash('success', 'Reservation rejected successfully!');
        }

        return $this->redirectToRoute('admin_reservations');
    }

    #[Route('/events', name: 'admin_events')]
    public function events(EventRepository $eventRepository): Response
    {
        $this->denyAccessUnlessAdmin();

        $events = $eventRepository->findAll();

        return $this->render('admin/events.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/events/new', name: 'admin_events_new')]
    public function newEvent(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $this->denyAccessUnlessAdmin();

        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($event);
            $entityManager->flush();
            $this->addFlash('success', 'Event created successfully!');

            return $this->redirectToRoute('admin_events');
        }

        return $this->render('admin/event_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/events/{id}/edit', name: 'admin_events_edit')]
    public function editEvent(
        Request $request,
        Event $event,
        EntityManagerInterface $entityManager
    ): Response
    {
        $this->denyAccessUnlessAdmin();

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
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
        Request $request,
        Event $event,
        EntityManagerInterface $entityManager
    ): Response
    {
        $this->denyAccessUnlessAdmin();

        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
            $this->addFlash('success', 'Event deleted successfully!');
        }

        return $this->redirectToRoute('admin_events');
    }

    #[Route('/reviews', name: 'admin_reviews')]
    public function reviews(ReviewRepository $reviewRepository): Response
    {
        $this->denyAccessUnlessAdmin();

        $reviews = $reviewRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/reviews.html.twig', [
            'reviews' => $reviews,
        ]);
    }

    #[Route('/reviews/{id}/delete', name: 'admin_reviews_delete', methods: ['POST'])]
    public function deleteReview(
        Request $request,
        Review $review,
        EntityManagerInterface $entityManager
    ): Response
    {
        $this->denyAccessUnlessAdmin();

        if ($this->isCsrfTokenValid('delete'.$review->getId(), $request->request->get('_token'))) {
            $entityManager->remove($review);
            $entityManager->flush();
            $this->addFlash('success', 'Review deleted successfully!');
        }

        return $this->redirectToRoute('admin_reviews');
    }

    #[Route('/dashboard/chart-data', name: 'admin_chart_data')]
    public function getChartData(
        ReservationRepository $reservationRepository,
        UserRepository $userRepository,
        Request $request
    ): JsonResponse
    {
        $this->denyAccessUnlessAdmin();

        $period = $request->query->getInt('period', 30);
        $startDate = new \DateTime("-$period days");

        $reservationData = $reservationRepository->getReservationsTrendData($startDate);
        $usersData = $userRepository->getNewUsersTrendData($startDate);

        return new JsonResponse([
            'reservations' => $reservationData,
            'users' => $usersData,
        ]);
    }
}
