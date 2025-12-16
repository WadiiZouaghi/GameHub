<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/events')]
class EventController extends AbstractController
{
    #[Route('', name: 'event_index')]
    public function index(EventRepository $repo): Response
    {
        $events = $repo->findAll();
        return $this->render('event/index.html.twig', ['events' => $events]);
    }

    #[Route('/new', name: 'event_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'Event created successfully!');

            return $this->redirectToRoute('event_index');
        }

        return $this->render('event/new.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'event_show')]
    public function show(int $id, EventRepository $repo): Response
    {
        $event = $repo->find($id);

        if (!$event) {
            throw $this->createNotFoundException();
        }

        return $this->render('event/show.html.twig', ['event' => $event]);
    }


    // ---------------------------------------------------------
    // JOIN EVENT
    // ---------------------------------------------------------
    #[Route('/{id}/join', name: 'event_join', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function join(
        Event $event,
        EntityManagerInterface $em,
        Security $security
    ): Response {
        $user = $security->getUser();

        // Event ended
        if ($event->getDate() < new \DateTime()) {
            $this->addFlash('error', 'You cannot join an event that has already ended.');
            return $this->redirectToRoute('event_show', ['id' => $event->getId()]);
        }

        // Event full
        if (count($event->getAttendees()) >= $event->getMaxPlayers()) {
            $this->addFlash('error', 'This event is already full.');
            return $this->redirectToRoute('event_show', ['id' => $event->getId()]);
        }

        // Already joined
        if ($event->getAttendees()->contains($user)) {
            $this->addFlash('info', 'You are already attending this event.');
            return $this->redirectToRoute('event_show', ['id' => $event->getId()]);
        }

        // Add attendee
        $event->addAttendee($user);
        $em->flush();

        $this->addFlash('success', 'You have joined the event!');
        return $this->redirectToRoute('event_show', ['id' => $event->getId()]);
    }


    // ---------------------------------------------------------
    // LEAVE EVENT
    // ---------------------------------------------------------
    #[Route('/{id}/leave', name: 'event_leave', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function leave(
        Event $event,
        EntityManagerInterface $em,
        Security $security
    ): Response {
        $user = $security->getUser();

        // Not attending
        if (!$event->getAttendees()->contains($user)) {
            $this->addFlash('info', 'You are not attending this event.');
            return $this->redirectToRoute('event_show', ['id' => $event->getId()]);
        }

        // Remove attendee
        $event->removeAttendee($user);
        $em->flush();

        $this->addFlash('success', 'You have left the event.');
        return $this->redirectToRoute('event_show', ['id' => $event->getId()]);
    }
}
