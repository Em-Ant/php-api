<?php

declare(strict_types=1);

namespace Emanuele\PhpApi\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CorsMiddleware implements MiddlewareInterface
{
    public function __construct(private array $allowedOrigins = []) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $origin = $request->getHeaderLine('Origin');

        if ($request->getMethod() === 'OPTIONS') {
            $response = new \Slim\Psr7\Response();
        } else {
            $response = $handler->handle($request);
        }

        if ($this->isOriginAllowed($origin)) {
            $response = $response
                ->withHeader('Access-Control-Allow-Origin', $origin)
                ->withHeader('Access-Control-Allow-Credentials', 'true');
        }

        return $response
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
            ->withHeader('Access-Control-Max-Age', '86400');
    }

    private function isOriginAllowed(string $origin): bool
    {
        if (
            empty($this->allowedOrigins) ||
            (count($this->allowedOrigins)  == 1 && in_array("*", $this->allowedOrigins))
        ) {
            return true;
        }

        return in_array($origin, $this->allowedOrigins) || in_array('*', $this->allowedOrigins);
    }
}
