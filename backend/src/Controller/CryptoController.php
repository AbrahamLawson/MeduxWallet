<?php

namespace App\Controller;

use App\Service\CryptoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/crypto')]
class CryptoController extends AbstractController
{
    public function __construct(
        private CryptoService $cryptoService
    ) {
    }

    #[Route('/prices', name: 'api_crypto_prices', methods: ['GET'])]
    public function prices(Request $request): JsonResponse
    {
        $coinIds = $request->query->get('ids', '');
        $idsArray = $coinIds ? explode(',', $coinIds) : [];

        if (empty($idsArray)) {
            return $this->json(['error' => 'Aucun ID de coin fourni'], 400);
        }

        $prices = $this->cryptoService->getCryptoPrices($idsArray);

        return $this->json($prices);
    }

    #[Route('/search', name: 'api_crypto_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');

        if (empty($query)) {
            return $this->json(['error' => 'Query requis'], 400);
        }

        $results = $this->cryptoService->searchCoins($query);

        return $this->json($results);
    }

    #[Route('/top', name: 'api_crypto_top', methods: ['GET'])]
    public function top(Request $request): JsonResponse
    {
        $limit = (int) $request->query->get('limit', 20);

        $coins = $this->cryptoService->getTopCoins($limit);

        return $this->json($coins);
    }

    #[Route('/detailed', name: 'api_crypto_detailed', methods: ['GET'])]
    public function detailed(Request $request): JsonResponse
    {
        $coinIds = $request->query->get('ids', '');
        $idsArray = $coinIds ? explode(',', $coinIds) : [];

        if (empty($idsArray)) {
            return $this->json(['error' => 'Aucun ID de coin fourni'], 400);
        }

        $data = $this->cryptoService->getDetailedCoinData($idsArray);

        return $this->json($data);
    }

    #[Route('/info/{coinId}', name: 'api_crypto_info', methods: ['GET'])]
    public function info(string $coinId): JsonResponse
    {
        $data = $this->cryptoService->getCoinInfo($coinId);

        if (!$data) {
            return $this->json(['error' => 'Coin non trouvé'], 404);
        }

        return $this->json($data);
    }
}
