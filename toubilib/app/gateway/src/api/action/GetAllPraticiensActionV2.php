<?php

declare(strict_types=1);

namespace toubilib\gateway\api\action;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\ClientInterface;
use Psr\Container\ContainerInterface;

class GetAllPraticiensActionV2
{
    private ClientInterface $praticienClient;
    
    public function __construct(ContainerInterface $container)
    {
        $this->praticienClient = $container->get('client.praticiens');
    }
    
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        
        // Récupérer les query params
        $queryParams = $request->getQueryParams();
        
        // Préparer les options pour Guzzle
        $options = [
            'headers' => [
                'Origin' => 'http://gateway.toubilib'
            ]
        ];
        
        if (!empty($queryParams)) {
            $options['query'] = $queryParams;
        }
        
        try {
            // Appeler le microservice Praticiens
            $apiResponse = $this->praticienClient->request('GET', '/praticiens', $options);
            
            // Récupérer la réponse
            $statusCode = $apiResponse->getStatusCode();
            $body = $apiResponse->getBody()->getContents();
            
            // Retourner la réponse au client
            $response->getBody()->write($body);
            
            return $response
                ->withStatus($statusCode)
                ->withHeader('Content-Type', 'application/json');
                
        } catch (ConnectException $e) {
            $response->getBody()->write(json_encode([
                'type' => 'error',
                'error' => 503,
                'message' => 'Service Praticiens non disponible'
            ]));
            return $response
                ->withStatus(503)
                ->withHeader('Content-Type', 'application/json');
                
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $statusCode = $e->getResponse()->getStatusCode();
                $body = $e->getResponse()->getBody()->getContents();
            } else {
                $statusCode = 500;
                $body = json_encode([
                    'type' => 'error',
                    'error' => 500,
                    'message' => 'Erreur lors de la récupération des praticiens'
                ]);
            }
            
            $response->getBody()->write($body);
            return $response
                ->withStatus($statusCode)
                ->withHeader('Content-Type', 'application/json');
        }
    }
}
