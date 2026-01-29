<?php
declare(strict_types=1);

namespace toubilib\gateway\api\middlewares;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Slim\Psr7\Response;

final class AuthnMiddleware implements MiddlewareInterface
{
    private ClientInterface $authClient;

    public function __construct(ClientInterface $authClient)
    {
        $this->authClient = $authClient;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authHeader = $request->getHeaderLine('Authorization');
        if ($authHeader === '' || !str_starts_with($authHeader, 'Bearer ')) {
            $res = new Response();
            $res->getBody()->write(json_encode(['error' => 'Access token required']));
            return $res->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        try {
            $guzzleResponse = $this->authClient->request(
                'POST',
                '/tokens/validate',
                [
                    'headers' => [
                        'Authorization' => $authHeader,
                        'Content-Type' => 'application/json',
                    ],
                    'body' => '{}',
                    'http_errors' => false,
                ]
            );

            $status = $guzzleResponse->getStatusCode();
            $body = (string)$guzzleResponse->getBody();

            if ($status === 200) {
                $payload = json_decode($body, true);
                $profile = $payload['profile'] ?? null;
                if ($profile !== null) {
                    $request = $request->withAttribute('authenticated_user', $profile);
                    $request = $request->withHeader('X-Authenticated-User', json_encode($profile));
                }
                return $handler->handle($request);
            }

            $res = new Response();
            $res->getBody()->write($body === '' ? json_encode(['error' => 'Unauthorized']) : $body);
            return $res->withStatus(401)->withHeader('Content-Type', 'application/json');

        } catch (RequestException $e) {
            $res = new Response();
            $res->getBody()->write(json_encode(['error' => 'Auth service unreachable', 'details' => $e->getMessage()]));
            return $res->withStatus(502)->withHeader('Content-Type', 'application/json');
        } catch (\Throwable $e) {
            $res = new Response();
            $res->getBody()->write(json_encode(['error' => 'Internal error', 'details' => $e->getMessage()]));
            return $res->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
}