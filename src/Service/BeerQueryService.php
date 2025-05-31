<?php

declare(strict_types=1);

namespace Emanuele\PhpApi\Service;

use Emanuele\PhpApi\Repository\BeerRepositoryInterface;
use Emanuele\PhpApi\Service\DTO\ResponseBeer;
use Psr\Log\LoggerInterface;

class BeerQueryService
{
    public function __construct(
        private BeerRepositoryInterface $beerRepository,
        private LoggerInterface $logger
    ) {}

    public function getRandomBeer(): ResponseBeer
    {
        $this->logger->debug('Getting random beer from repository');

        try {
            $beer = $this->beerRepository->getRandom();
            $this->logger->debug('Successfully retrieved beer', ['beer_id' => $beer['id']]);
            return $this->toResponseBeer($beer);
        } catch (\RuntimeException $e) {
            $this->logger->error('Failed to get random beer', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getBeerById(string $id): ResponseBeer
    {
        $this->logger->debug('Getting random beer from repository');

        try {
            $beer = $this->beerRepository->getById($id);
            $this->logger->debug('Successfully retrieved beer', ['beer_id' => $beer['id']]);
            return $this->toResponseBeer($beer);
        } catch (\RuntimeException $e) {
            $this->logger->error('Failed to get random beer', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function toResponseBeer(array $beerData): ResponseBeer
    {
        return new ResponseBeer(
            $beerData['id'],
            $beerData['name'],
            $beerData['descript'] === '' ? null : $beerData['descript'],
            $beerData['brewery'],
            $beerData['style_name'],
            $beerData['cat_name'],
            (float)$beerData['abv'],
            (int)$beerData['ibu'],
            (int)$beerData['srm'],
            (int)$beerData['upc']
        );
    }
}
