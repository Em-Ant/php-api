<?php

declare(strict_types=1);

namespace Emanuele\PhpApi\Repository;

interface BeerRepositoryInterface
{
  /**
   * @throws \RuntimeException
   */
  public function getRandom(): array;
  /**
   * @throws \RuntimeException
   */
  public function getById(string $id): array;
}
