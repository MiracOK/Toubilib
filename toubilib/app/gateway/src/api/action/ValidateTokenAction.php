<?php
declare(strict_types=1);

namespace toubilib\gateway\api\action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

class ValidateTokenAction
{
    private ClientInterface $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        try {
            $body = $request->getBody()->getContents();
            $request->getBody()->rewind();

            $headers = ['Content-Type' => 'application/json'];
            $auth = $request->getHeaderLine('Authorization');
            if ($auth !== '') {
                $headers['Authorization'] = $auth;
            }
            // forward internal secret header if the gateway uses one
            $internal = $request->getHeaderLine('X-Internal-Secret');
            if ($internal !== '') {
                $headers['X-Internal-Secret'] = $internal;
            }

            $guzzleResponse = $this->client->request(
                'POST',
                '/tokens/validate',
                [
                    'body' => $body,
                    'headers' => $headers,
                ]
            );

            $response->getBody()->write($guzzleResponse->getBody()->getContents());
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus($guzzleResponse->getStatusCode());
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $statusCode = $e->getResponse()->getStatusCode();
                $body = $e->getResponse()->getBody()->getContents();
                $response->getBody()->write($body);
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus($statusCode);
            }

            $response->getBody()->write(json_encode([
                'error' => 'Internal server error',
                'details' => $e->getMessage()
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Internal server error',
                'details' => $e->getMessage()
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}