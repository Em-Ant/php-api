<?php

namespace Emanuele\PhpApi;

use Emant\BrowniePhp\Utils;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\JWTExceptionWithPayloadInterface;

use stdClass;

class TokenValidator
{
  private function __construct(
    private ?string $publicKey = null,
    private ?string $algorithm = null,
    private ?string $jwksUri = null
  ) {
  }

  public static function createJwksValidator(string $jwksUri): TokenValidator
  {
    return new self(null, null, $jwksUri);
  }

  public static function createPublicKeyValidator(
    string $publicKey,
    string $algorithm = 'RS256'
  ): TokenValidator {
    return new self($publicKey, $algorithm, null);
  }

  private function jwksDecode(string $token): stdClass
  {
    try {
      $keySet = json_decode(file_get_contents($this->jwksUri), true);

      if (!isset($keySet)) {
        return Utils::server_error('internal server error', 'unable to get key set', 500);
      }

      return JWT::decode($token, JWK::parseKeySet($keySet));
    } catch (JWTExceptionWithPayloadInterface | \UnexpectedValueException) {
      return Utils::server_error('unauthorized', 'invalid token', 401);
    }
  }

  private function publicKeyDecode(string $token): stdClass
  {
    try {
      return JWT::decode($token, new Key($this->publicKey, $this->algorithm));
    } catch (JWTExceptionWithPayloadInterface) {
      return Utils::server_error('unauthorized', 'invalid token', 401);
    }
  }

  private function decode(array $ctx): array
  {
    $headers = $ctx['headers'];
    if (!isset($headers['authorization'])) {
      return Utils::server_error('unauthorized', 'authorization header not found', 401);
    }

    $token = str_replace('Bearer ', '', $headers['authorization']);

    if (isset($this->publicKey)) {
      return json_decode(json_encode($this->publicKeyDecode($token)), true);
    } else if (isset($this->jwksUri)) {
      return json_decode(json_encode($this->jwksDecode($token)), true);
    }
  }

  public function validate(array &$ctx)
  {
    $ctx['token'] = $this->decode($ctx);
  }

  public function allowRealmRoles(array $requiredRoles)
  {
    return function (array &$ctx) use ($requiredRoles) {
      $token = $this->decode($ctx);
      $userRoles = $token['realm_access']['roles'];

      $isAuthorized = false;
      foreach ($userRoles as $userRole) {
        if (in_array($userRole, $requiredRoles)) {
          $isAuthorized = true;
          break;
        }
      }

      if (!$isAuthorized) {
        return Utils::server_error('forbidden', 'invalid user roles', 403);
      }

      $ctx['token'] = $token;
    };
  }
}
