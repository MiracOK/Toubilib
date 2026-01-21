<?php

declare(strict_types=1);

namespace toubilib\gateway\api\action;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;

final class DeleteRdvGatewayAction
{
    private ClientInterface $rdvClient;

    public function __construct(ClientInterface $rdvClient)
    {
        $this->rdvClient = $rdvClient;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $rdvId = (string)($args['id'] ?? '');
        
        if ($rdvId === '') {
            $response->getBody()->write(json_encode([
                'error' => 'invalid_id',
                'message' => 'Identifiant du RDV requis'
            ], JSON_UNESCAPED_UNICODE));
            return $response
                ->withStatus(400)
                ->withHeader('Content-Type', 'application/json');
        }

        try {
            $options = [
                'headers' => []
            ];

            // Transférer le header Authorization si présent
            if ($request->hasHeader('Authorization')) {
                $options['headers']['Authorization'] = $request->getHeaderLine('Authorization');
            }

            // Appel au microservice
            $apiResponse = $this->rdvClient->request('DELETE', "/rdvs/{$rdvId}", $options);
            
            $statusCode = $apiResponse->getStatusCode();
            $bodyContent = $apiResponse->getBody()->getContents();
            
            $response->getBody()->write($bodyContent);
            return $response
                ->withStatus($statusCode)
                ->withHeader('Content-Type', 'application/json');
                
        } catch (ConnectException $e) {
            $response->getBody()->write(json_encode([
                'error' => 'service_unavailable',
                'message' => 'Service RDV non disponible'
            ], JSON_UNESCAPED_UNICODE));
            return $response
                ->withStatus(503)
                ->withHeader('Content-Type', 'application/json');
                
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $statusCode = $e->getResponse()->getStatusCode();
                $bodyContent = $e->getResponse()->getBody()->getContents();
            } else {
                $statusCode = 500;
                $bodyContent = json_encode([
                    'error' => 'internal_error',
                    'message' => 'Erreur lors du traitement'
                ], JSON_UNESCAPED_UNICODE);
            }
            
            $response->getBody()->write($bodyContent);
            return $response
                ->withStatus($statusCode)
                ->withHeader('Content-Type', 'application/json');
        }
    }
}
