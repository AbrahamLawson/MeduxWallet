<?php

namespace App\Controller;

use App\Service\BlockchainService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/blockchain')]
class BlockchainController extends AbstractController
{
    public function __construct(
        private BlockchainService $blockchainService
    ) {
    }

    #[Route('/blocks', name: 'api_blockchain_blocks', methods: ['GET'])]
    public function getBlocks(Request $request): JsonResponse
    {
        $limit = (int) $request->query->get('limit', 10);
        $blocks = $this->blockchainService->getLatestBlocks($limit);

        return $this->json($blocks);
    }

    #[Route('/block/{hash}', name: 'api_blockchain_block', methods: ['GET'])]
    public function getBlock(string $hash): JsonResponse
    {
        $block = $this->blockchainService->getBlock($hash);

        if (!$block) {
            return $this->json(['error' => 'Block non trouvé'], 404);
        }

        return $this->json($block);
    }

    #[Route('/transactions/unconfirmed', name: 'api_blockchain_mempool', methods: ['GET'])]
    public function getUnconfirmedTransactions(): JsonResponse
    {
        $transactions = $this->blockchainService->getUnconfirmedTransactions();

        return $this->json($transactions);
    }

    #[Route('/stats', name: 'api_blockchain_stats', methods: ['GET'])]
    public function getStats(): JsonResponse
    {
        $stats = $this->blockchainService->getStats();

        return $this->json($stats);
    }

    #[Route('/latest', name: 'api_blockchain_latest', methods: ['GET'])]
    public function getLatestBlock(): JsonResponse
    {
        $block = $this->blockchainService->getLatestBlock();

        if (!$block) {
            return $this->json(['error' => 'Impossible de récupérer le dernier bloc'], 500);
        }

        return $this->json($block);
    }

    #[Route('/address/{address}', name: 'api_blockchain_address', methods: ['GET'])]
    public function getAddress(string $address): JsonResponse
    {
        $data = $this->blockchainService->getAddress($address);

        if (!$data) {
            return $this->json(['error' => 'Adresse non trouvée'], 404);
        }

        return $this->json($data);
    }

    #[Route('/tx/{hash}', name: 'api_blockchain_transaction', methods: ['GET'])]
    public function getTransaction(string $hash): JsonResponse
    {
        $transaction = $this->blockchainService->searchTransaction($hash);

        if (!$transaction) {
            return $this->json(['error' => 'Transaction non trouvée'], 404);
        }

        return $this->json($transaction);
    }
}
