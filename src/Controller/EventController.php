<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
}
