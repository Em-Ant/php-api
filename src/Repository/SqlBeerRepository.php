<?php

declare(strict_types=1);

namespace Emanuele\PhpApi\Repository;

use PDO;
use Psr\Log\LoggerInterface;
use RuntimeException;

class SqlBeerRepository implements BeerRepositoryInterface
{
  private PDO $pdo;
  private LoggerInterface $logger;

  public function __construct(PDO $pdo, LoggerInterface $logger)
  {
    $this->pdo = $pdo;
    $this->logger = $logger;
  }

  public function getRandom(): array
  {
    $this->logger->debug('Fetching random beer from database');

    try {
      $stmt = $this->pdo->query('
        SELECT 
            b.*, 
            c.cat_name, 
            s.style_name,
            br.name AS brewery,
            br.city,
            br.state,
            br.country
        FROM beers b
        LEFT JOIN categories c ON b.cat_id = c.id
        LEFT JOIN styles s ON b.style_id = s.id
        LEFT JOIN breweries br ON b.brewery_id = br.id
        ORDER BY RANDOM()
        LIMIT 1
      ');
      $beer = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($beer === false) {
        $this->logger->error('No beers found in database');
        throw new RuntimeException('No beers available');
      }

      $this->logger->debug('Successfully fetched beer data', ['beer_name' => $beer['name']]);

      return $beer;
    } catch (\PDOException $e) {
      $this->logger->error('Database error while fetching beer', ['error' => $e->getMessage()]);
      throw new RuntimeException('Failed to fetch beer data from database');
    }
  }
  public function getById(string $id): array
  {
    $this->logger->debug('Fetching be beer with id ' . $id . ' from database');

    try {
      $stmt = $this->pdo->prepare(
        '
        SELECT 
            b.*, 
            c.cat_name, 
            s.style_name,
            br.name AS brewery,
            br.city,
            br.state,
            br.country
        FROM beers b
        LEFT JOIN categories c ON b.cat_id = c.id
        LEFT JOIN styles s ON b.style_id = s.id
        LEFT JOIN breweries br ON b.brewery_id = br.id
        WHERE b.id = ?
        LIMIT 1'
      );
      $stmt->execute([$id]);
      $beer = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($beer === false) {
        $this->logger->error('No beers found in database');
        throw new RuntimeException('No beers available');
      }

      $this->logger->debug('Successfully fetched beer data', ['beer_name' => $beer['name']]);

      return $beer;
    } catch (\PDOException $e) {
      $this->logger->error('Database error while fetching beer', ['error' => $e->getMessage()]);
      throw new RuntimeException('Failed to fetch beer data from database');
    }
  }
}
