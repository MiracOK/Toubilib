<?php

declare(strict_types=1);

namespace toubilib\gateway\api\action;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\ClientInterface;

class GenericGatewayAction extends AbstractGatewayAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        
        // Récupérer la méthode HTTP
        $method = $request->getMethod();
        
        // Récupérer le chemin complet de l'URI
        $path = $request->getUri()->getPath();
        
        // Récupérer les paramètres de query string
        $queryParams = $request->getQueryParams();
        
        // Préparer les options pour Guzzle
        $options = [
            'headers' => [
                'Origin' => 'http://gateway.toubilib'
            ]
        ];
        
        // Ajouter les query params si présents
        if (!empty($queryParams)) {
            $options['query'] = $queryParams;
        }
        
        // Ajouter le body pour POST, PUT, PATCH
        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $body = $request->getParsedBody();
            if (!empty($body)) {
                $options['json'] = $body;
            }
        }
        
        // Transférer le header Authorization si présent
        if ($request->hasHeader('Authorization')) {
            $options['headers']['Authorization'] = $request->getHeaderLine('Authorization');
        }
        
        try {
            // Envoyer la requête vers le service distant
            $apiResponse = $this->remote_service->request($method, $path, $options);
            
            // Récupérer le status code et le body
            $statusCode = $apiResponse->getStatusCode();
            $body = $apiResponse->getBody()->getContents();
            
            // Retourner la réponse au client
            $response->getBody()->write($body);
            
            return $response
                ->withStatus($statusCode)
                ->withHeader('Content-Type', 'application/json');
                
        } catch (ConnectException $e) {
            // Erreur de connexion au service
            $response->getBody()->write(json_encode([
                'type' => 'error',
                'error' => 503,
                'message' => 'Service non disponible'
            ]));
            return $response
                ->withStatus(503)
                ->withHeader('Content-Type', 'application/json');
                
        } catch (RequestException $e) {
            // Transférer la réponse d'erreur du service
            if ($e->hasResponse()) {
                $statusCode = $e->getResponse()->getStatusCode();
                $body = $e->getResponse()->getBody()->getContents();
            } else {
                $statusCode = 500;
                $body = json_encode([
                    'type' => 'error',
                    'error' => 500,
                    'message' => 'Erreur lors du traitement'
                ]);
            }
            
            $response->getBody()->write($body);
            return $response
                ->withStatus($statusCode)
                ->withHeader('Content-Type', 'application/json');
        }
    }
}
