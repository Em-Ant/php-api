<?php

declare(strict_types=1);

namespace Emanuele\PhpApi\Controller;

use Emanuele\PhpApi\Service\BeerQueryService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Beer API",
    description: "A simple beer API with JWT authentication"
)]
#[OA\Server(url: "/api/v1")]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]
class BeerController
{
    public function __construct(
        private BeerQueryService $beerQueryService,
        private LoggerInterface $logger
    ) {}

    #[OA\Get(
        path: "/beers/random",
        summary: "Get a random beer",
        security: [["bearerAuth" => []]],
        tags: ["Beer"]
    )]
    #[OA\Response(
        response: 200,
        description: "Random beer data",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "id", type: "integer"),
                new OA\Property(property: "brand", type: "string"),
                new OA\Property(property: "name", type: "string"),
                new OA\Property(property: "description", type: "string"),
                new OA\Property(property: "style", type: "string"),
                new OA\Property(property: "category", type: "string"),
                new OA\Property(property: "abv", type: "float"),
                new OA\Property(property: "ibu", type: "integer"),
                new OA\Property(property: "srm", type: "integer"),
                new OA\Property(property: "upc", type: "integer")
            ]
        )
    )]
    #[OA\Response(response: 401, description: "Unauthorized")]
    #[OA\Response(response: 403, description: "Forbidden - insufficient roles")]
    #[OA\Response(response: 500, description: "Internal server error")]
    public function getRandomBeer(Request $request, Response $response): Response
    {
        try {
            $this->logger->info('Fetching random beer');

            $beer = $this->beerQueryService->getRandomBeer();

            $this->logger->info('Successfully serving random beer', [
                'beer_id' => $beer['id'] ?? null
            ]);

            $response->getBody()->write(json_encode(
                ResponseBeer::fromBeerData($beer),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $this->logger->error('Error serving random beer', ['error' => $e->getMessage()]);

            $error = ['error' => 'Internal server error'];
            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }

    #[OA\Get(
        path: "/beers/{id}",
        summary: "Get beer by ID",
        security: [["bearerAuth" => []]],
        tags: ["Beer"]
    )]
    #[OA\Parameter(
        name: "id",
        description: "Beer ID",
        in: "path",
        required: true,
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Response(
        response: 200,
        description: "Beer by id",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "id", type: "integer"),
                new OA\Property(property: "brand", type: "string"),
                new OA\Property(property: "name", type: "string"),
                new OA\Property(property: "description", type: "string"),
                new OA\Property(property: "style", type: "string"),
                new OA\Property(property: "category", type: "string"),
                new OA\Property(property: "abv", type: "float"),
                new OA\Property(property: "ibu", type: "integer"),
                new OA\Property(property: "srm", type: "integer"),
                new OA\Property(property: "upc", type: "integer")
            ]
        )
    )]
    #[OA\Response(response: 401, description: "Unauthorized")]
    #[OA\Response(response: 403, description: "Forbidden - insufficient roles")]
    #[OA\Response(response: 500, description: "Internal server error")]
    public function getBeerById(Request $request, Response $response): Response
    {
        try {
            $id = $request->getAttribute('id');


            $this->logger->info('Fetching random beer');

            $beer = $this->beerQueryService->getBeerById($id);

            $this->logger->info('Successfully serving beer by id', [
                'beer_id' => $beer['id'] ?? null
            ]);

            $response->getBody()->write(
                json_encode(
                    ResponseBeer::fromBeerData($beer),
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                )
            );
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $this->logger->error('Error serving beer by id', ['error' => $e->getMessage()]);

            $error = ['error' => 'Internal server error'];
            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}
