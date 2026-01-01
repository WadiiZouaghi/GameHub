<?php

namespace App\Controller;

use App\Repository\EventRepository;
use App\Repository\GameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(GameRepository $gameRepo, EventRepository $eventRepo): Response
    {
        return $this->render('home/index.html.twig', [
            'featuredGames' => $gameRepo->findBy([], ['id' => 'DESC'], 4),
            'featuredEvents' => $eventRepo->findBy([], ['date' => 'ASC'], 3),
            'totalGames' => $gameRepo->count([]),
            'totalEvents' => $eventRepo->count([]),
        ]);
    }
}
