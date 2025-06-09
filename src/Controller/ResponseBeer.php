<?php

namespace Emanuele\PhpApi\Controller;

use JsonSerializable;

class ResponseBeer implements JsonSerializable
{
  private function __construct(
    private int $id,
    private string $name,
    private ?string $description,
    private ?string $brand,
    private ?string $style,
    private ?string $category,
    private ?string $city,
    private ?string $state,
    private ?string $country,
    private float $abv,
    private int $ibu,
    private int $srm,
    private int $upc
  ) {}

  public function getId()
  {
    return $this->id;
  }

  public function jsonSerialize(): mixed
  {
    return [
      'id' => $this->id,
      'name' => $this->name,
      'description' => $this->description,
      'brand' => $this->brand,
      'style' => $this->style,
      'category' => $this->category,
      'city' => $this->city,
      'state' => $this->state,
      'country' => $this->country,
      'abv' => $this->abv,
      'ibu' => $this->ibu,
      'srm' => $this->srm,
      'upc' => $this->upc
    ];
  }

  public static function fromBeerData(array $beerData): ResponseBeer
  {
    return new ResponseBeer(
      $beerData['id'],
      $beerData['name'],
      $beerData['descript'] === '' ? null : $beerData['descript'],
      $beerData['brewery'],
      $beerData['style_name'],
      $beerData['cat_name'],
      $beerData['city'] === '' ? null : $beerData['city'],
      $beerData['state'] === '' ? null : $beerData['state'],
      $beerData['country'] === '' ? null : $beerData['country'],
      (float)$beerData['abv'],
      (int)$beerData['ibu'],
      (int)$beerData['srm'],
      (int)$beerData['upc']
    );
  }
}
