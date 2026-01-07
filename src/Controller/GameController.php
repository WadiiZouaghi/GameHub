<?php

namespace App\Controller;

use App\Entity\Game;
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
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 12;

        $qb = $repo->createQueryBuilder('g');

        if ($category) {
            $qb->andWhere('g.category = :category')->setParameter('category', $category);
        }
        if ($search) {
            $qb->andWhere('LOWER(g.title) LIKE :search')->setParameter('search', '%' . strtolower($search) . '%');
        }

        $orderMap = [
            'title_desc' => ['g.title', 'DESC'],
            'newest' => ['g.id', 'DESC'],
            'oldest' => ['g.id', 'ASC'],
            'category' => ['g.category', 'ASC'],
            'title_asc' => ['g.title', 'ASC'],
        ];
        [$field, $direction] = $orderMap[$sort] ?? $orderMap['title_asc'];
        $qb->orderBy($field, $direction);

        $total = (int) (clone $qb)->select('COUNT(g.id)')->getQuery()->getSingleScalarResult();
        $games = $qb->setFirstResult(($page - 1) * $limit)->setMaxResults($limit)->getQuery()->getResult();

        $categories = array_unique(array_map(fn($g) => $g->getCategory(), $repo->findAll()));
        sort($categories);

        return $this->render('game/index.html.twig', [
            'games' => $games,
            'categories' => $categories,
            'selected_category' => $category,
            'search_query' => $search,
            'selected_sort' => $sort,
            'next_page' => ($page * $limit < $total) ? $page + 1 : null,
            'previous_page' => $page > 1 ? $page - 1 : null,
        ]);
    }

    #[Route('/new', name: 'game_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $game = new Game();
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleFileUploads($form, $game, $slugger);

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

    #[Route('/{id}', name: 'game_show')]
    public function show(Game $game, Request $request, EntityManagerInterface $em, PurchaseRepository $purchaseRepo, \App\Repository\WishlistRepository $wishlistRepo): Response
    {
        $user = $this->getUser();
        $hasPurchased = $user && $purchaseRepo->findOneBy(['user' => $user, 'game' => $game]);
        $userReview = $user ? $em->getRepository(Review::class)->findOneBy(['user' => $user, 'game' => $game]) : null;
        $isInWishlist = $user && $wishlistRepo->isInWishlist($user, $game);

        $form = $this->createForm(ReviewType::class, new Review());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $hasPurchased) {
            if ($userReview) {
                $this->addFlash('warning', 'You have already reviewed this game.');
            } else {
                $review = $form->getData();
                $review->setUser($user)->setGame($game);
                $em->persist($review);
                $em->flush();
                $this->addFlash('success', 'Review submitted successfully!');
            }
            return $this->redirectToRoute('game_show', ['id' => $game->getId()]);
        }

        $reviews = $game->getReviews();
        $averageRating = $reviews->count() > 0 
            ? array_sum(array_map(fn($r) => $r->getRating(), $reviews->toArray())) / $reviews->count() 
            : 0;

        $relatedGames = array_filter(
            $em->getRepository(Game::class)->findBy(['category' => $game->getCategory()], ['id' => 'DESC'], 4),
            fn($g) => $g->getId() !== $game->getId()
        );

        return $this->render('game/show.html.twig', [
            'game' => $game,
            'hasPurchased' => $hasPurchased,
            'userReview' => $userReview,
            'reviewForm' => $form->createView(),
            'averageRating' => $averageRating,
            'relatedGames' => array_slice($relatedGames, 0, 3),
            'isInWishlist' => $isInWishlist,
        ]);
    }
    
    #[Route('/{id}/edit', name: 'game_edit')]
    public function edit(Game $game, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleFileUploads($form, $game, $slugger);

            $entityManager->flush();
            $this->addFlash('success', 'Game updated successfully!');
            return $this->redirectToRoute('game_show', ['id' => $game->getId()]);
        }

        return $this->render('game/edit.html.twig', [
            'game' => $game,
            'form' => $form->createView(),
        ]);
    }

    private function handleFileUploads($form, Game $game, SluggerInterface $slugger): void
    {
        $baseDir = $this->getParameter('kernel.project_dir') . '/public/uploads';

        $coverFile = $form->get('coverImage')->getData();
        if ($coverFile) {
            $name = $slugger->slug(pathinfo($coverFile->getClientOriginalName(), PATHINFO_FILENAME));
            $filename = $name . '-' . uniqid() . '.' . $coverFile->guessExtension();

            try {
                $coverFile->move($baseDir . '/covers', $filename);
                $game->setCoverImage('uploads/covers/' . $filename);
            } catch (FileException $e) {
                $this->addFlash('error', 'Failed to upload cover image.');
            }
        }

        $galleryFiles = $form->get('gallery')->getData();
        if ($galleryFiles) {
            $paths = [];
            foreach ($galleryFiles as $file) {
                $name = $slugger->slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
                $filename = $name . '-' . uniqid() . '.' . $file->guessExtension();

                try {
                    $file->move($baseDir . '/gallery', $filename);
                    $paths[] = 'uploads/gallery/' . $filename;
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload gallery image.');
                }
            }
            if ($paths) {
                $game->setGallery($paths);
            }
        }
    }
}
