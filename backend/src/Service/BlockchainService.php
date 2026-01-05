<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class BlockchainService
{
    private const BLOCKCHAIN_API = 'https://blockchain.info';

    public function __construct(
        private HttpClientInterface $httpClient
    ) {
    }

    public function getLatestBlocks(int $limit = 10): array
    {
        try {
            $response = $this->httpClient->request('GET', self::BLOCKCHAIN_API . '/blocks?format=json');
            $blocks = $response->toArray();
            
            return array_slice($blocks, 0, $limit);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getBlock(string $blockHash): ?array
    {
        try {
            $response = $this->httpClient->request('GET', self::BLOCKCHAIN_API . "/rawblock/{$blockHash}");
            return $response->toArray();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getUnconfirmedTransactions(): array
    {
        try {
            $response = $this->httpClient->request('GET', self::BLOCKCHAIN_API . '/unconfirmed-transactions?format=json');
            $data = $response->toArray();
            
            // Limiter aux 20 premières transactions
            if (isset($data['txs'])) {
                return array_slice($data['txs'], 0, 20);
            }
            
            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getStats(): array
    {
        try {
            $response = $this->httpClient->request('GET', self::BLOCKCHAIN_API . '/stats?format=json');
            return $response->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getLatestBlock(): ?array
    {
        try {
            $response = $this->httpClient->request('GET', self::BLOCKCHAIN_API . '/latestblock');
            return $response->toArray();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getAddress(string $address): ?array
    {
        try {
            $response = $this->httpClient->request('GET', self::BLOCKCHAIN_API . "/rawaddr/{$address}?limit=10");
            return $response->toArray();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function searchTransaction(string $txHash): ?array
    {
        try {
            $response = $this->httpClient->request('GET', self::BLOCKCHAIN_API . "/rawtx/{$txHash}");
            return $response->toArray();
        } catch (\Exception $e) {
            return null;
        }
    }
}
