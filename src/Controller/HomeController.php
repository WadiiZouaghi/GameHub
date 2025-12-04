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
        // Get featured games (limit to 4)
        $featuredGames = $gameRepo->findBy([], ['id' => 'DESC'], 4);

        // Get featured events (limit to 3)
        $featuredEvents = $eventRepo->findBy([], ['date' => 'ASC'], 3);

        // Get some stats
        $totalGames = $gameRepo->count([]);
        $totalEvents = $eventRepo->count([]);

        return $this->render('home/index.html.twig', [
            'featuredGames' => $featuredGames,
            'featuredEvents' => $featuredEvents,
            'totalGames' => $totalGames,
            'totalEvents' => $totalEvents,
        ]);
    }
}
