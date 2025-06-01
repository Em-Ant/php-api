<?php

declare(strict_types=1);

namespace Emanuele\PhpApi\Service;

use Emanuele\PhpApi\Repository\BeerRepositoryInterface;
use Psr\Log\LoggerInterface;

class BeerQueryService
{
    public function __construct(
        private BeerRepositoryInterface $beerRepository,
        private LoggerInterface $logger
    ) {}

    public function getRandomBeer(): array
    {
        $this->logger->debug('Getting random beer from repository');

        try {
            $beer = $this->beerRepository->getRandom();
            $this->logger->debug('Successfully retrieved random beer', ['beer_id' => $beer['id']]);
            return $beer;
        } catch (\RuntimeException $e) {
            $this->logger->error('Failed to get random beer', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getBeerById(string $id): array
    {
        $this->logger->debug('Getting random beer from repository');

        try {
            $beer = $this->beerRepository->getById($id);
            $this->logger->debug('Successfully retrieved beer by Id', ['beer_id' => $beer['id']]);
            return $beer;
        } catch (\RuntimeException $e) {
            $this->logger->error('Failed to get beer by Id', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
