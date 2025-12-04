<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Reservation;
use App\Entity\Review;
use App\Form\GameType;
use App\Form\ReviewType;
use App\Repository\GameRepository;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/games')]
class GameController extends AbstractController
{
    #[Route('', name: 'game_index')]
    public function index(GameRepository $repo, Request $request): Response
    {
        $category = $request->query->get('category');
        $search = $request->query->get('search');
        $sort = $request->query->get('sort', 'title_asc');
        $page = (int) $request->query->get('page', 1);
        $limit = 12;
        $offset = ($page - 1) * $limit;

        $qb = $repo->createQueryBuilder('g');

        if ($category) {
            $qb->andWhere('g.category = :category')
                ->setParameter('category', $category);
        }
        if ($search) {
            $qb->andWhere('LOWER(g.title) LIKE :search')
                ->setParameter('search', '%' . strtolower($search) . '%');
        }

        switch ($sort) {
            case 'title_desc':
                $qb->orderBy('g.title', 'DESC');
                break;
            case 'newest':
                $qb->orderBy('g.id', 'DESC');
                break;
            case 'oldest':
                $qb->orderBy('g.id', 'ASC');
                break;
            case 'category':
                $qb->orderBy('g.category', 'ASC');
                break;
            case 'title_asc':
            default:
                $qb->orderBy('g.title', 'ASC');
                break;
        }

        $qbForCount = clone $qb;
        $qbForCount->select('COUNT(g.id)');
        $totalGames = (int) $qbForCount->getQuery()->getSingleScalarResult();

        $games = $qb->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        // Group categories for filter dropdown
        $allGames = $repo->findAll();
        $categories = [];
        foreach ($allGames as $game) {
            $categories[$game->getCategory()] = $game->getCategory();
        }
        sort($categories);

        $nextPage = ($offset + $limit) < $totalGames ? $page + 1 : null;
        $previousPage = $page > 1 ? $page - 1 : null;

        return $this->render('game/index.html.twig', [
            'games' => $games,
            'categories' => $categories,
            'selected_category' => $category,
            'search_query' => $search,
            'selected_sort' => $sort,
            'next_page' => $nextPage,
            'previous_page' => $previousPage,
        ]);
    }

    #[Route('/new', name: 'game_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $game = new Game();
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coverImageFile = $form->get('coverImage')->getData();

            if ($coverImageFile) {
                $originalFilename = pathinfo($coverImageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$coverImageFile->guessExtension();

                try {
                    $coverImageFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/covers',
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload cover image.');
                    return $this->redirectToRoute('game_new');
                }

                $game->setCoverImage('uploads/covers/'.$newFilename);
            }

            $entityManager->persist($game);
            $entityManager->flush();

            $this->addFlash('success', 'Game created successfully!');

            return $this->redirectToRoute('game_index');
        }

        return $this->render('game/new.html.twig', [
            'game' => $game,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/reserve', name: 'game_reserve', methods: ['POST'])]
    public function reserve(
        Request $request,
        Game $game,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        // Check if user already has a reservation for this game
        $existingReservation = $entityManager->getRepository(Reservation::class)->findOneBy([
            'user' => $user,
            'game' => $game,
            'status' => ['pending', 'confirmed']
        ]);

        if ($existingReservation) {
            $this->addFlash('error', 'You already have a reservation for this game.');
            return $this->redirectToRoute('game_show', ['id' => $game->getId()]);
        }

        // Check capacity if set
        if ($game->getCapacity() !== null) {
            $confirmedReservations = $entityManager->getRepository(Reservation::class)->count([
                'game' => $game,
                'status' => 'confirmed'
            ]);
            if ($confirmedReservations >= $game->getCapacity()) {
                $this->addFlash('error', 'This game is fully booked.');
                return $this->redirectToRoute('game_show', ['id' => $game->getId()]);
            }
        }

        $reservation = new Reservation();
        $reservation->setUser($user);
        $reservation->setGame($game);
        $reservation->setReservationDate(new \DateTime());
        $reservation->setStatus('pending');

        $entityManager->persist($reservation);
        $entityManager->flush();

        $this->addFlash('success', 'Reservation created successfully!');

        return $this->redirectToRoute('game_show', ['id' => $game->getId()]);
    }



    #[Route('/{id}', name: 'game_show')]
    public function show(
        Game $game,
        Request $request,
        EntityManagerInterface $entityManager,
        PurchaseRepository $purchaseRepository
    ): Response {
        $user = $this->getUser();
        $hasPurchased = false;
        $userReview = null;

        if ($user) {
            // Check if user has purchased this game
            $purchase = $purchaseRepository->findOneBy([
                'user' => $user,
                'game' => $game
            ]);
            $hasPurchased = $purchase !== null;

            // Get user's review if exists
            $userReview = $entityManager->getRepository(Review::class)->findOneBy([
                'user' => $user,
                'game' => $game
            ]);
        }

        // Handle review submission
        $review = new Review();
        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $hasPurchased) {
            if (!$userReview) {
                $review->setUser($user);
                $review->setGame($game);
                $entityManager->persist($review);
                $this->addFlash('success', 'Review submitted successfully!');
            } else {
                $this->addFlash('warning', 'You have already reviewed this game.');
                return $this->redirectToRoute('game_show', ['id' => $game->getId()]);
            }

            $entityManager->flush();
            return $this->redirectToRoute('game_show', ['id' => $game->getId()]);
        }

        // Calculate average rating
        $reviews = $game->getReviews();
        $averageRating = 0;
        if ($reviews->count() > 0) {
            $totalRating = 0;
            foreach ($reviews as $review) {
                $totalRating += $review->getRating();
            }
            $averageRating = $totalRating / $reviews->count();
        }

        // Get related games (same category, excluding current)
        $relatedGames = $entityManager->getRepository(Game::class)->findBy(
            ['category' => $game->getCategory()],
            ['id' => 'DESC'],
            4
        );

        $relatedGames = array_filter($relatedGames, fn($g) => $g->getId() !== $game->getId());

        return $this->render('game/show.html.twig', [
            'game' => $game,
            'hasPurchased' => $hasPurchased,
            'userReview' => $userReview,
            'reviewForm' => $form->createView(),
            'averageRating' => $averageRating,
            'relatedGames' => array_slice($relatedGames, 0, 3),
        ]);
    }
}
