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
    public function index(NewsApiService $newsApiService, Request $request): Response
    {
        $search = $request->query->get('search');
        $page = (int) $request->query->get('page', 1);

        if ($search) {
            $result = $newsApiService->searchNews($search, [
                'page' => $page,
                'pageSize' => 50,
            ]);
        } else {
            $result = $newsApiService->getTopHeadlines([
                'page' => $page,
                'pageSize' => 50,
            ]);
        }

        $allNews = $result['articles'] ?? [];
        $totalResults = $result['totalResults'] ?? 0;
        $limit = 12;
        
        $news = array_slice($allNews, 0, $limit);

        $categories = [];
        foreach ($news as $article) {
            $categories[$article['category']] = $article['category'];
        }
        sort($categories);

        $hasMoreNews = count($allNews) > $limit;
        $nextPage = $hasMoreNews ? $page + 1 : null;
        $previousPage = $page > 1 ? $page - 1 : null;

        $error = $result['error'] ?? null;
        if ($error) {
            \error_log('News Index Error: ' . $error);
        }

        return $this->render('news/index.html.twig', [
            'news' => $news,
            'categories' => $categories,
            'selected_category' => null,
            'search_query' => $search,
            'next_page' => $nextPage,
            'previous_page' => $previousPage,
            'current_page' => $page,
            'api_error' => $error,
        ]);
    }

    #[Route('/{id}', name: 'news_show')]
    public function show(NewsApiService $newsApiService, string $id): Response
    {
        $result = $newsApiService->getTopHeadlines([
            'pageSize' => 100,
        ]);

        $news = null;
        $relatedNews = [];

        foreach ($result['articles'] as $article) {
            if ($article['id'] === $id) {
                $news = $article;
                break;
            }
        }

        if (!$news) {
            throw $this->createNotFoundException('News article not found');
        }

        $category = $news['category'];
        foreach ($result['articles'] as $article) {
            if ($article['category'] === $category && $article['id'] !== $id && count($relatedNews) < 3) {
                $relatedNews[] = $article;
            }
        }

        return $this->render('news/show.html.twig', [
            'news' => $news,
            'relatedNews' => $relatedNews,
        ]);
    }
}
