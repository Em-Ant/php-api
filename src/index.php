<?php

namespace Emanuele\PhpApi;

use Emant\BrowniePhp\Router;
use Emant\BrowniePhp\Utils;
use Emanuele\PhpApi\TokenValidator;

$env = Utils::read_env('../.env');

$validator = TokenValidator::createJwksValidator($env['JWKS_URI']);

$app = new Router();

$app->get('/beers', $validator->allowRealmRoles(['admin']), function () {
  $beer = json_decode(
    file_get_contents('https://random-data-api.com/api/v2/beers'),
    JSON_UNESCAPED_UNICODE
  );
  Utils::send_json($beer);
});

$app->get('/', [Utils::class, 'not_found']);
$app->get('/{param}', [Utils::class, 'not_found']);

$app->run();
