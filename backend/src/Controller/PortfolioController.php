<?php

namespace App\Controller;

use App\Entity\Asset;
use App\Entity\User;
use App\Repository\AssetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/portfolio')]
class PortfolioController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AssetRepository $assetRepository
    ) {
    }

    #[Route('', name: 'api_portfolio_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        $assets = $this->assetRepository->findByUser($user->getId());

        $data = array_map(function (Asset $asset) {
            return [
                'id' => $asset->getId(),
                'symbol' => $asset->getSymbol(),
                'name' => $asset->getName(),
                'quantity' => $asset->getQuantity(),
                'purchasePrice' => $asset->getPurchasePrice(),
                'coinId' => $asset->getCoinId(),
                'createdAt' => $asset->getCreatedAt()?->format('Y-m-d H:i:s'),
                'updatedAt' => $asset->getUpdatedAt()?->format('Y-m-d H:i:s')
            ];
        }, $assets);

        return $this->json($data);
    }

    #[Route('', name: 'api_portfolio_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['symbol']) || !isset($data['quantity'])) {
            return $this->json(['error' => 'Symbol et quantity requis'], Response::HTTP_BAD_REQUEST);
        }

        $asset = new Asset();
        $asset->setUser($user);
        $asset->setSymbol(strtoupper($data['symbol']));
        $asset->setName($data['name'] ?? strtoupper($data['symbol']));
        $asset->setQuantity($data['quantity']);
        $asset->setPurchasePrice($data['purchasePrice'] ?? null);
        $asset->setCoinId($data['coinId'] ?? null);

        $this->entityManager->persist($asset);
        $this->entityManager->flush();

        return $this->json([
            'id' => $asset->getId(),
            'symbol' => $asset->getSymbol(),
            'name' => $asset->getName(),
            'quantity' => $asset->getQuantity(),
            'purchasePrice' => $asset->getPurchasePrice(),
            'coinId' => $asset->getCoinId(),
            'createdAt' => $asset->getCreatedAt()?->format('Y-m-d H:i:s')
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_portfolio_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        $asset = $this->assetRepository->find($id);

        if (!$asset || $asset->getUser() !== $user) {
            return $this->json(['error' => 'Asset non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['symbol'])) {
            $asset->setSymbol(strtoupper($data['symbol']));
        }
        if (isset($data['name'])) {
            $asset->setName($data['name']);
        }
        if (isset($data['quantity'])) {
            $asset->setQuantity($data['quantity']);
        }
        if (isset($data['purchasePrice'])) {
            $asset->setPurchasePrice($data['purchasePrice']);
        }
        if (isset($data['coinId'])) {
            $asset->setCoinId($data['coinId']);
        }

        $asset->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        return $this->json([
            'id' => $asset->getId(),
            'symbol' => $asset->getSymbol(),
            'name' => $asset->getName(),
            'quantity' => $asset->getQuantity(),
            'purchasePrice' => $asset->getPurchasePrice(),
            'coinId' => $asset->getCoinId(),
            'updatedAt' => $asset->getUpdatedAt()?->format('Y-m-d H:i:s')
        ]);
    }

    #[Route('/{id}', name: 'api_portfolio_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        $asset = $this->assetRepository->find($id);

        if (!$asset || $asset->getUser() !== $user) {
            return $this->json(['error' => 'Asset non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($asset);
        $this->entityManager->flush();

        return $this->json(['message' => 'Asset supprimé'], Response::HTTP_OK);
    }
}
