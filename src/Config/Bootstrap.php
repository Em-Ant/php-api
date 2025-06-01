<?php

declare(strict_types=1);

namespace Emanuele\PhpApi\Config;

use DI\Container;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Level;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Middleware\ErrorMiddleware;
use Emanuele\PhpApi\Middleware\CorsMiddleware;
use Emanuele\PhpApi\Middleware\JwtAuthMiddleware;
use Emanuele\PhpApi\Service\TokenValidator;
use Emanuele\PhpApi\Service\BeerQueryService;
use Emanuele\PhpApi\Controller\BeerController;
use Emanuele\PhpApi\Repository\BeerRepositoryInterface;
use Emanuele\PhpApi\Repository\SqlBeerRepository;
use PDO;

class Bootstrap
{
    public function __construct(private Container $container) {}

    public function configureRepositories(): void
    {
        $this->container->set(PDO::class, function () {
            $dbPath = $_ENV['SQLITE_DB_PATH'] ?? __DIR__ . '/../../data/beers.db';
            $pdo = new PDO("sqlite:$dbPath");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        });

        // Beer Repository
        $this->container->set(BeerRepositoryInterface::class, function (Container $c) {
            return new SqlBeerRepository(
                $c->get(PDO::class),
                $c->get(LoggerInterface::class)
            );
        });
    }
    public function configureServices(): void
    {
        // Logger
        $this->container->set(LoggerInterface::class, function () {
            $logger = new Logger($_ENV['APP_NAME'] ?? 'app');
            $handler = new StreamHandler(
                $_ENV['LOG_PATH'] ?? './logs/app.log',
                $_ENV['LOG_LEVEL'] ?? Level::Debug
            );
            $logger->pushHandler($handler);
            return $logger;
        });

        // Token Validator
        $this->container->set(TokenValidator::class, function () {
            return TokenValidator::createJwksValidator($_ENV['JWKS_URI']);
        });

        // Services
        $this->container->set(BeerQueryService::class, function (Container $c) {
            return new BeerQueryService(
                $c->get(BeerRepositoryInterface::class),
                $c->get(LoggerInterface::class)
            );
        });

        // Controllers
        $this->container->set(BeerController::class, function (Container $c) {
            return new BeerController(
                $c->get(BeerQueryService::class),
                $c->get(LoggerInterface::class)
            );
        });

        $this->container->set(
            \Psr\Http\Message\StreamFactoryInterface::class,
            new \Slim\Psr7\Factory\StreamFactory()
        );
    }

    public function configureMiddleware(App $app): void
    {
        // Error middleware
        $errorMiddleware = new ErrorMiddleware(
            $app->getCallableResolver(),
            $app->getResponseFactory(),
            $_ENV['APP_DEBUG'] === 'true',
            true,
            true
        );
        $app->add($errorMiddleware);


        // CORS middleware

        $corsOrigins = $_ENV['CORS_ALLOWED_ORIGINS'] ?? '';
        $corsOrigins = $corsOrigins != "" ? explode(',', $corsOrigins) : [];
        $app->add(new CorsMiddleware($corsOrigins));
    }

    public function configureRoutes(App $app): void
    {
        $app->group('/api/v1/beers', function ($group) {
            $group->get('/random', [BeerController::class, 'getRandomBeer']);
            $group->get('/{id}', [BeerController::class, 'getBeerById']);
        })->add(new JwtAuthMiddleware(
            $this->container->get(TokenValidator::class),
            $this->container->get(LoggerInterface::class)
        ));

        $app->get('/api/v1/health', function ($request, $response) {
            $data = [
                'status' => 'ok',
                'timestamp' => date('Y-m-d H:i:s'),
                'version' => '1.0.0'
            ];
            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json');
        });
    }
}
