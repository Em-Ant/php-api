<?php

declare(strict_types=1);

namespace Emanuele\PhpApi\Service;

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
    ) {}

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
                throw new \RuntimeException('Unable to get key set');
            }
            return JWT::decode($token, JWK::parseKeySet($keySet));
        } catch (JWTExceptionWithPayloadInterface | \UnexpectedValueException $e) {
            throw new \UnexpectedValueException('Invalid token', 401, $e);
        }
    }

    private function publicKeyDecode(string $token): stdClass
    {
        try {
            return JWT::decode($token, new Key($this->publicKey, $this->algorithm));
        } catch (JWTExceptionWithPayloadInterface $e) {
            throw new \UnexpectedValueException('Invalid token', 401);
        }
    }

    public function validateToken(string $token): array
    {
        try {
            if (isset($this->publicKey)) {
                $decoded = $this->publicKeyDecode($token);
            } elseif (isset($this->jwksUri)) {
                $decoded = $this->jwksDecode($token);
            } else {
                throw new \RuntimeException('No validation method configured');
            }

            return json_decode(json_encode($decoded), true);
        } catch (\Exception $e) {
            throw new \UnexpectedValueException('Token validation failed: ' . $e->getMessage(), 401, $e);
        }
    }

    public function hasRequiredRoles(array $token, array $requiredRoles): bool
    {
        if (!isset($token['realm_access']['roles'])) {
            return false;
        }

        $userRoles = $token['realm_access']['roles'];

        foreach ($userRoles as $userRole) {
            if (in_array($userRole, $requiredRoles)) {
                return true;
            }
        }

        return false;
    }
}
