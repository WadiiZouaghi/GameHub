<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Purchase;
use App\Repository\GameRepository;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PurchaseController extends AbstractController
{
    #[Route('/purchase/{id}', name: 'game_purchase')]
    public function purchase(
        Game $game,
        EntityManagerInterface $entityManager,
        PurchaseRepository $purchaseRepository
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        // Check if user already owns the game
        $existingPurchase = $purchaseRepository->findOneBy([
            'user' => $user,
            'game' => $game
        ]);

        if ($existingPurchase) {
            $this->addFlash('warning', 'You already own this game!');
            return $this->redirectToRoute('game_show', ['id' => $game->getId()]);
        }

        // Create purchase (without payment for now)
        $purchase = new Purchase();
        $purchase->setUser($user);
        $purchase->setGame($game);
        $purchase->setStatus('completed'); // Since no payment yet

        $entityManager->persist($purchase);
        $entityManager->flush();

        $this->addFlash('success', 'Game purchased successfully!');

        return $this->redirectToRoute('user_library');
    }

    #[Route('/library', name: 'user_library')]
    public function library(PurchaseRepository $purchaseRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        $purchases = $purchaseRepository->findByUser($user);

        return $this->render('user/library.html.twig', [
            'purchases' => $purchases,
        ]);
    }
}
