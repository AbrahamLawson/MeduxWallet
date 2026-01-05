<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class CryptoService
{
    private const COINGECKO_API = 'https://api.coingecko.com/api/v3';

    public function __construct(
        private HttpClientInterface $httpClient
    ) {
    }

    public function getCryptoPrices(array $coinIds): array
    {
        if (empty($coinIds)) {
            return [];
        }

        $ids = implode(',', $coinIds);

        try {
            $response = $this->httpClient->request('GET', self::COINGECKO_API . '/simple/price', [
                'query' => [
                    'ids' => $ids,
                    'vs_currencies' => 'usd,eur',
                    'include_24hr_change' => 'true',
                    'include_market_cap' => 'true',
                    'include_24hr_vol' => 'true',
                    'include_last_updated_at' => 'true'
                ]
            ]);

            return $response->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getDetailedCoinData(array $coinIds): array
    {
        if (empty($coinIds)) {
            return [];
        }

        $ids = implode(',', $coinIds);

        try {
            $response = $this->httpClient->request('GET', self::COINGECKO_API . '/coins/markets', [
                'query' => [
                    'vs_currency' => 'usd',
                    'ids' => $ids,
                    'order' => 'market_cap_desc',
                    'sparkline' => 'true',
                    'price_change_percentage' => '1h,24h,7d'
                ]
            ]);

            return $response->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getCoinInfo(string $coinId): ?array
    {
        try {
            $response = $this->httpClient->request('GET', self::COINGECKO_API . "/coins/{$coinId}", [
                'query' => [
                    'localization' => 'false',
                    'tickers' => 'false',
                    'community_data' => 'true',
                    'developer_data' => 'false'
                ]
            ]);

            return $response->toArray();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function searchCoins(string $query): array
    {
        if (empty($query)) {
            return [];
        }

        try {
            $response = $this->httpClient->request('GET', self::COINGECKO_API . '/search', [
                'query' => ['query' => $query]
            ]);

            $data = $response->toArray();
            
            // Limiter aux 10 premiers résultats
            return array_slice($data['coins'] ?? [], 0, 10);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getTopCoins(int $limit = 20): array
    {
        try {
            $response = $this->httpClient->request('GET', self::COINGECKO_API . '/coins/markets', [
                'query' => [
                    'vs_currency' => 'usd',
                    'order' => 'market_cap_desc',
                    'per_page' => $limit,
                    'page' => 1,
                    'sparkline' => 'false'
                ]
            ]);

            return $response->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }
}
