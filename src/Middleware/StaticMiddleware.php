<?php

declare(strict_types=1);

namespace Emanuele\PhpApi\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\StreamFactoryInterface;

class StaticMiddleware implements MiddlewareInterface
{
  private $basePath;
  private $directory;
  private $streamFactory;

  private const MIME_TYPES = [
    'txt' => 'text/plain',
    'html' => 'text/html',
    'css' => 'text/css',
    'js' => 'application/javascript',
    'json' => 'application/json',
    'png' => 'image/png',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif' => 'image/gif',
    'ico' => 'image/x-icon',
  ];
  private const INDEX_FILE = 'index.html';

  public function __construct(
    string $basePath,
    string $directory,
    StreamFactoryInterface $streamFactory
  ) {
    $this->basePath = rtrim($basePath, '/');
    $this->directory = rtrim($directory, DIRECTORY_SEPARATOR);
    $this->streamFactory = $streamFactory;
  }

  public function process(Request $request, RequestHandler $handler): Response
  {
    $uri = $request->getUri()->getPath();


    if (strpos($uri, $this->basePath) === 0) {
      $relativePath = substr($uri, strlen($this->basePath));
      $relativePath = ltrim($relativePath, '/');

      // Handle root path by serving index.html
      if ($relativePath === '') {
        $relativePath = self::INDEX_FILE;
      }

      $filePath = $this->directory . DIRECTORY_SEPARATOR . $relativePath;
      $filePath = realpath($filePath);

      if ($filePath && is_file($filePath) && strpos($filePath, realpath($this->directory)) === 0) {
        $response = new \Slim\Psr7\Response();

        // Set MIME type
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeType = self::MIME_TYPES[$extension] ?? 'application/octet-stream';
        $response = $response->withHeader('Content-Type', $mimeType);

        // Add caching headers
        $response = $response->withHeader('Cache-Control', 'public, max-age=3600');
        $etag = md5_file($filePath);
        $response = $response->withHeader('ETag', $etag);

        if (
          $request->hasHeader('If-None-Match') &&
          $request->getHeaderLine('If-None-Match') === $etag
        ) {
          return $response->withStatus(304);
        }

        return $response->withBody($this->streamFactory->createStreamFromFile($filePath));
      }
    }

    return $handler->handle($request);
  }
}
