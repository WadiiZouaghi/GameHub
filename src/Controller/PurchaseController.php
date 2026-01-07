<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Purchase;
use App\Repository\GameRepository;
use App\Repository\PurchaseRepository;
use App\Service\SquareService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PurchaseController extends AbstractController
{
    #[Route('/purchase/process-payment', name: 'process_payment', methods: ['POST'])]
    public function processPayment(
        Request $request,
        GameRepository $gameRepo,
        EntityManagerInterface $em,
        PurchaseRepository $purchaseRepo,
        SquareService $square
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $data = json_decode($request->getContent(), true);
        if (!($data['sourceId'] ?? null) || !($data['gameId'] ?? null) || !($data['amount'] ?? null)) {
            return $this->json(['success' => false, 'message' => 'Invalid payment data'], 400);
        }

        $game = $gameRepo->find($data['gameId']) ?: throw $this->createNotFoundException('Game not found');

        if ($purchaseRepo->findOneBy(['user' => $this->getUser(), 'game' => $game])) {
            return $this->json(['success' => false, 'message' => 'You already own this game'], 400);
        }

        try {
            $square->createPayment($data['sourceId'], $data['amount'], $game, $this->getUser());

            $purchase = (new Purchase())->setUser($this->getUser())->setGame($game)->setStatus('completed');
            $em->persist($purchase);
            $em->flush();

            return $this->json(['success' => true, 'message' => 'Payment successful', 'redirectUrl' => $this->generateUrl('user_library')]);
        } catch (\Exception $e) {
            error_log('Square Payment Error: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Payment processing failed: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/purchase/{id}', name: 'game_purchase')]
    public function purchase(Game $game, EntityManagerInterface $em, PurchaseRepository $purchaseRepo, ParameterBagInterface $params): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($purchaseRepo->findOneBy(['user' => $this->getUser(), 'game' => $game])) {
            $this->addFlash('warning', 'You already own this game!');
            return $this->redirectToRoute('game_show', ['id' => $game->getId()]);
        }

        if ($game->getPrice() == 0) {
            $purchase = (new Purchase())->setUser($this->getUser())->setGame($game)->setStatus('completed');
            $em->persist($purchase);
            $em->flush();

            $this->addFlash('success', 'Game added to your library!');
            return $this->redirectToRoute('user_library');
        }

        return $this->render('purchase/checkout.html.twig', [
            'game' => $game,
            'square_application_id' => $params->get('square_application_id'),
            'square_location_id' => $params->get('square_location_id'),
        ]);
    }

    #[Route('/library', name: 'user_library')]
    public function library(PurchaseRepository $purchaseRepo, \App\Repository\WishlistRepository $wishlistRepo): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('user/library.html.twig', [
            'purchases' => $purchaseRepo->findByUser($this->getUser()),
            'wishlistItems' => $wishlistRepo->findByUser($this->getUser()),
        ]);
    }

    #[Route('/library/remove/{id}', name: 'library_remove', methods: ['POST'])]
    public function removeFromLibrary(Request $request, Purchase $purchase, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        if (!$this->isCsrfTokenValid('library_remove', $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid security token.');
            return $this->redirectToRoute('user_library');
        }
        
        if ($purchase->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot remove this purchase.');
        }

        $em->remove($purchase);
        $em->flush();

        $this->addFlash('success', 'Game removed from your library.');
        return $this->redirectToRoute('user_library');
    }
}
