<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use toubilib\api\actions\ListerPraticienAction;
use toubilib\api\actions\ListerCreneauDejaPraticien;
use toubilib\api\actions\PraticienDetailAction;

return function (App $app): App {

   
    $app->get('/praticiens', ListerPraticienAction::class);
    $app->get('/praticiens/{id}', PraticienDetailAction::class);
    
    // Opération 7: agenda praticien (authentification gérée par le gateway)
    $app->get('/praticiens/{praticienId}/creneaux', ListerCreneauDejaPraticien::class)
        ->setName('agenda');

   
    // Preflight CORS
    $app->options('/{routes:.+}', function (
        ServerRequestInterface $request,
        ResponseInterface $response
        ): ResponseInterface {
        return $response;
    });

    return $app;
};
