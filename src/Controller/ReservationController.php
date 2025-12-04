<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/reservations')]
class ReservationController extends AbstractController
{
    #[Route('', name: 'reservation_index')]
    public function index(ReservationRepository $repo): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        // If admin, show all reservations, otherwise show user's reservations
        if ($this->isGranted('ROLE_ADMIN')) {
            $reservations = $repo->findAll();
        } else {
            $reservations = $repo->findBy(['user' => $user]);
        }

        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/new', name: 'reservation_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $reservation = new Reservation();
        $reservation->setUser($this->getUser());
        $reservation->setStatus('pending');

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reservation);
            $entityManager->flush();

            $this->addFlash('success', 'Reservation created successfully!');

            return $this->redirectToRoute('reservation_index');
        }

        return $this->render('reservation/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'reservation_edit')]
    public function edit(
        Request $request,
        Reservation $reservation,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Check if user owns this reservation or is admin
        if ($reservation->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You cannot edit this reservation.');
        }

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Reservation updated successfully!');

            return $this->redirectToRoute('reservation_index');
        }

        return $this->render('reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/cancel', name: 'reservation_cancel', methods: ['POST'])]
    public function cancel(
        Request $request,
        Reservation $reservation,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Check if user owns this reservation or is admin
        if ($reservation->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You cannot cancel this reservation.');
        }

        if ($this->isCsrfTokenValid('cancel'.$reservation->getId(), $request->request->get('_token'))) {
            $reservation->setStatus('cancelled');
            $entityManager->flush();

            $this->addFlash('success', 'Reservation cancelled successfully.');
        }

        return $this->redirectToRoute('reservation_index');
    }

    #[Route('/{id}/confirm', name: 'reservation_confirm', methods: ['POST'])]
    public function confirm(
        Request $request,
        Reservation $reservation,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('confirm'.$reservation->getId(), $request->request->get('_token'))) {
            $reservation->setStatus('confirmed');
            $entityManager->flush();

            $this->addFlash('success', 'Reservation confirmed successfully.');
        }

        return $this->redirectToRoute('reservation_index');
    }
}
