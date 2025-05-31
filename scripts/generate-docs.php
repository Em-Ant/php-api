<?php
require_once __DIR__ . '/../vendor/autoload.php';

$openapi = \OpenApi\Generator::scan([__DIR__ . '/../src']);
file_put_contents(__DIR__ . '/../docs/openapi.json', $openapi->toJson());
echo "OpenAPI spec generated successfully!\n";
