<?php

declare(strict_types=1);

use DI\Container;
use Slim\Factory\AppFactory;
use Emanuele\PhpApi\Config\Bootstrap;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..', 'cfg.env');
$dotenv->load();

$container = new Container();

// Bootstrap application
$bootstrap = new Bootstrap($container);
$bootstrap->configureRepositories();
$bootstrap->configureServices();

AppFactory::setContainer($container);
$app = AppFactory::create();

if ($_ENV['APP_ENV'] === 'development') {
  $bootstrap->configureStaticServer(
    $app,
    "/docs",
    __DIR__ . "/../docs"
  );
}

$bootstrap->configureMiddleware($app);
$bootstrap->configureHealthCheck($app);
$bootstrap->configureRoutes($app);


$app->run();
