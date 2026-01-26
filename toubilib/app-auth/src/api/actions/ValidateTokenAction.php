<?php
declare(strict_types=1);

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use toubilib\core\application\ports\api\provider\AuthProviderInterface;
use toubilib\core\application\ports\api\provider\AuthProviderExpiredAccessTokenException;
use toubilib\core\application\ports\api\provider\AuthProviderInvalidAccessTokenException;

final class ValidateTokenAction
{
    private AuthProviderInterface $authProvider;

    public function __construct(AuthProviderInterface $authProvider)
    {
        $this->authProvider = $authProvider;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $authHeader = $request->getHeaderLine('Authorization');
        $token = '';

        if ($authHeader !== '' && str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7);
        } else {
            $data = (array)$request->getParsedBody();
            $token = trim((string)($data['token'] ?? $data['access_token'] ?? ''));
        }

        if ($token === '') {
            $response->getBody()->write(json_encode(['error' => 'Access token is required']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        try {
            $profile = $this->authProvider->getSignedInUser($token);

            $payload = [
                'valid' => true,
                'profile' => [
                    'id' => $profile->ID,
                    'email' => $profile->email ?? null,
                    'role' => $profile->role ?? null,
                ],
            ];

            $response->getBody()->write(json_encode($payload));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (AuthProviderExpiredAccessTokenException $e) {
            $response->getBody()->write(json_encode(['error' => 'Access token expired']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        } catch (AuthProviderInvalidAccessTokenException $e) {
            $response->getBody()->write(json_encode(['error' => 'Invalid access token']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        } catch (\Throwable $e) {
            $response->getBody()->write(json_encode(['error' => 'Token validation failed']));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
}