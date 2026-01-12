<?php

declare(strict_types=1);

namespace toubilib\gateway\api\action;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;

class GetAllPraticiensAction extends AbstractGatewayAction
{
    public function __invoke(ServerRequestInterface $request,ResponseInterface $response,array $args): ResponseInterface {
        

            $apiResponse = $this->remote_service->request('GET', '/praticiens');
            
            $statusCode = $apiResponse->getStatusCode();
            $body = $apiResponse->getBody()->getContents();
            
            $response->getBody()->write($body);
            
            return $response
                ->withStatus($statusCode)
                ->withHeader('Content-Type', 'application/json');
                
    }
}
