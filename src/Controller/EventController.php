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
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', ['event' => $event]);
    }

    #[Route('/{id}/join', name: 'event_join', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function join(Event $event, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if ($event->getDate() < new \DateTime()) {
            $this->addFlash('error', 'You cannot join an event that has already ended.');
        } elseif (count($event->getAttendees()) >= $event->getMaxPlayers()) {
            $this->addFlash('error', 'This event is already full.');
        } elseif ($event->getAttendees()->contains($user)) {
            $this->addFlash('info', 'You are already attending this event.');
        } else {
            $event->addAttendee($user);
            $em->flush();
            $this->addFlash('success', 'You have joined the event!');
        }

        return $this->redirectToRoute('event_show', ['id' => $event->getId()]);
    }

    #[Route('/{id}/leave', name: 'event_leave', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function leave(Event $event, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$event->getAttendees()->contains($user)) {
            $this->addFlash('info', 'You are not attending this event.');
        } else {
            $event->removeAttendee($user);
            $em->flush();
            $this->addFlash('success', 'You have left the event.');
        }

        return $this->redirectToRoute('event_show', ['id' => $event->getId()]);
    }
}
