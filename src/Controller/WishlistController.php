<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Wishlist;
use App\Repository\WishlistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/wishlist')]
class WishlistController extends AbstractController
{
    #[Route('/toggle/{id}', name: 'wishlist_toggle', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function toggle(Request $request, Game $game, WishlistRepository $wishlistRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isCsrfTokenValid('wishlist_toggle', $request->request->get('_token'))) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid CSRF token'], 403);
        }

        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $wishlistItem = $wishlistRepository->findWishlistItem($user, $game);

        if ($wishlistItem) {
            $entityManager->remove($wishlistItem);
            $entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'action' => 'removed',
                'message' => 'Removed from wishlist'
            ]);
        } else {
            $wishlistItem = new Wishlist();
            $wishlistItem->setUser($user);
            $wishlistItem->setGame($game);

            $entityManager->persist($wishlistItem);
            $entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'action' => 'added',
                'message' => 'Added to wishlist'
            ]);
        }
    }

    #[Route('', name: 'wishlist_index')]
    #[IsGranted('ROLE_USER')]
    public function index(WishlistRepository $wishlistRepository): Response
    {
        $user = $this->getUser();
        $wishlistItems = $wishlistRepository->findByUser($user);

        return $this->render('wishlist/index.html.twig', [
            'wishlistItems' => $wishlistItems,
        ]);
    }
}
