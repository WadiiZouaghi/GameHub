<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class NewsApiService
{
    private HttpClientInterface $httpClient;
    private string $apiKey;
    private string $apiUrl = 'https://newsapi.org/v2';

    public function __construct(string $newsapiKey)
    {
        $this->httpClient = HttpClient::create();
        $this->apiKey = $newsapiKey;
    }

    public function getTopHeadlines(array $options = []): array
    {
        $params = [
            'apiKey' => $this->apiKey,
            'sortBy' => 'publishedAt',
            'language' => $options['language'] ?? 'en',
            'pageSize' => $options['pageSize'] ?? 1000,
            'q' => '(PlayStation OR Xbox OR Nintendo OR Steam OR PC gaming OR console OR "video game" OR esports OR Twitch) AND (game OR gaming OR esports)',
        ];

        if (isset($options['page'])) {
            $params['page'] = $options['page'];
        }

        if (isset($options['searchQuery'])) {
            $params['q'] = $options['searchQuery'];
        }

        try {
            $response = $this->httpClient->request('GET', $this->apiUrl . '/everything', [
                'query' => $params,
            ]);

            $data = $response->toArray();

            if ($response->getStatusCode() !== 200) {
                \error_log('NewsAPI Error (Status ' . $response->getStatusCode() . '): ' . json_encode($data));
                return ['articles' => [], 'totalResults' => 0, 'error' => $data['message'] ?? 'Unknown error'];
            }

            if (!isset($data['articles'])) {
                \error_log('NewsAPI Response invalid: ' . json_encode($data));
                return ['articles' => [], 'totalResults' => 0, 'error' => 'Invalid response from API'];
            }

            return [
                'articles' => $this->formatArticles($data['articles'], true),
                'totalResults' => $data['totalResults'] ?? 0,
            ];
        } catch (\Exception $e) {
            \error_log('NewsAPI Exception: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            return ['articles' => [], 'totalResults' => 0, 'error' => $e->getMessage()];
        }
    }

    public function searchNews(string $query, array $options = []): array
    {
        $params = [
            'apiKey' => $this->apiKey,
            'q' => $query,
            'sortBy' => 'publishedAt',
            'language' => $options['language'] ?? 'en',
            'pageSize' => $options['pageSize'] ?? 100,
        ];

        if (isset($options['page'])) {
            $params['page'] = $options['page'];
        }

        try {
            $response = $this->httpClient->request('GET', $this->apiUrl . '/everything', [
                'query' => $params,
            ]);

            $data = $response->toArray();

            if ($response->getStatusCode() !== 200) {
                \error_log('NewsAPI Search Error: ' . json_encode($data));
                return ['articles' => [], 'totalResults' => 0, 'error' => $data['message'] ?? 'Unknown error'];
            }

            if (!isset($data['articles'])) {
                \error_log('NewsAPI Search Response invalid: ' . json_encode($data));
                return ['articles' => [], 'totalResults' => 0, 'error' => 'Invalid response from API'];
            }

            return [
                'articles' => $this->formatArticles($data['articles'], false),
                'totalResults' => $data['totalResults'] ?? 0,
            ];
        } catch (\Exception $e) {
            \error_log('NewsAPI Search Exception: ' . $e->getMessage());
            return ['articles' => [], 'totalResults' => 0, 'error' => $e->getMessage()];
        }
    }

    private function formatArticles(array $articles, bool $requireImage = true): array
    {
        return array_filter(array_map(function ($article) use ($requireImage) {
            if ($requireImage && empty($article['urlToImage'])) {
                return null;
            }

            return [
                'id' => md5($article['url'] ?? ''),
                'title' => $article['title'] ?? 'No Title',
                'content' => $article['description'] ?? $article['content'] ?? '',
                'image' => $article['urlToImage'],
                'category' => $article['source']['name'] ?? 'Gaming News',
                'author' => $article['author'] ?? 'NewsAPI',
                'publishDate' => new \DateTimeImmutable($article['publishedAt'] ?? 'now'),
                'url' => $article['url'] ?? '',
                'viewCount' => 0,
            ];
        }, $articles));
    }
}
