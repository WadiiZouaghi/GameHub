<?php

namespace App\Controller;

use App\Service\NewsApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/news')]
class NewsController extends AbstractController
{
    #[Route('', name: 'news_index')]
    public function index(NewsApiService $newsApi, Request $request): Response
    {
        $search = $request->query->get('search');
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 12;

        $result = $search 
            ? $newsApi->searchNews($search, ['page' => $page, 'pageSize' => 50])
            : $newsApi->getTopHeadlines(['page' => $page, 'pageSize' => 50]);

        $allNews = $result['articles'] ?? [];
        $news = array_slice($allNews, 0, $limit);

        $categories = array_unique(array_column($news, 'category'));
        sort($categories);

        if ($error = $result['error'] ?? null) {
            error_log('News Index Error: ' . $error);
        }

        return $this->render('news/index.html.twig', [
            'news' => $news,
            'categories' => $categories,
            'selected_category' => null,
            'search_query' => $search,
            'next_page' => count($allNews) > $limit ? $page + 1 : null,
            'previous_page' => $page > 1 ? $page - 1 : null,
            'current_page' => $page,
            'api_error' => $error,
        ]);
    }

    #[Route('/{id}', name: 'news_show')]
    public function show(NewsApiService $newsApi, string $id): Response
    {
        $articles = $newsApi->getTopHeadlines(['pageSize' => 100])['articles'];

        $news = current(array_filter($articles, fn($a) => $a['id'] === $id)) ?: throw $this->createNotFoundException('News article not found');

        $relatedNews = array_slice(
            array_filter($articles, fn($a) => $a['category'] === $news['category'] && $a['id'] !== $id),
            0,
            3
        );

        return $this->render('news/show.html.twig', [
            'news' => $news,
            'relatedNews' => $relatedNews,
        ]);
    }
}
