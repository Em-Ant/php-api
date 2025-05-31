<?php

declare(strict_types=1);

namespace Emanuele\PhpApi\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Emanuele\PhpApi\Service\TokenValidator;

class JwtAuthMiddleware implements MiddlewareInterface
{
    private const SKIP_ROUTES = ['/health', '/docs'];

    public function __construct(private TokenValidator $tokenValidator) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();

        // Skip authentication for certain routes
        foreach (self::SKIP_ROUTES as $skipRoute) {
            if (str_starts_with($path, $skipRoute)) {
                return $handler->handle($request);
            }
        }

        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader)) {
            return $this->unauthorizedResponse('Authorization header not found');
        }

        if (!str_starts_with($authHeader, 'Bearer ')) {
            return $this->unauthorizedResponse('Invalid authorization header format');
        }

        $token = substr($authHeader, 7);



        try {
            $decodedToken = $this->tokenValidator->validateToken($token);

            // Check for admin role for beer endpoints
            if (str_contains($path, '/beer')) {
                if (!$this->tokenValidator->hasRequiredRoles($decodedToken, ['admin'])) {
                    return $this->forbiddenResponse('Insufficient privileges');
                }
            }

            // Add token to request attributes
            $request = $request->withAttribute('token', $decodedToken);
        } catch (\Exception $e) {
            return $this->unauthorizedResponse('Invalid token');
        }

        return $handler->handle($request);
    }

    private function unauthorizedResponse(string $message): ResponseInterface
    {
        $response = new \Slim\Psr7\Response();
        $error = json_encode(['error' => 'Unauthorized', 'message' => $message]);
        $response->getBody()->write($error);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }

    private function forbiddenResponse(string $message): ResponseInterface
    {
        $response = new \Slim\Psr7\Response();
        $error = json_encode(['error' => 'Forbidden', 'message' => $message]);
        $response->getBody()->write($error);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(403);
    }
}
