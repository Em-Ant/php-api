<?php

declare(strict_types=1);

namespace Emanuele\PhpApi\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Emanuele\PhpApi\Service\TokenValidator;
use Psr\Log\LoggerInterface;

class JwtAuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private TokenValidator $tokenValidator,
        private LoggerInterface $logger,
        private array $requiredRoles = []
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
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

            if (
                !empty($this->requiredRoles) &&
                !$this->tokenValidator->hasRequiredRoles(
                    $decodedToken,
                    $this->requiredRoles
                )
            ) {
                return $this->forbiddenResponse('Insufficient privileges');
            }

            $request = $request->withAttribute('token', $decodedToken);
        } catch (\Exception $e) {
            $this->logger->error('attempted access with invalid token');
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
