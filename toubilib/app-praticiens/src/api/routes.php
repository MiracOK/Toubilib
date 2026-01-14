<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use toubilib\api\actions\ListerPraticienAction;
use toubilib\api\actions\ListerCreneauDejaPraticien;
use toubilib\api\actions\PraticienDetailAction;


use toubilib\core\application\middlewares\AuthnMiddleware;
use toubilib\core\application\middlewares\AuthzMiddleware;

return function (App $app): App {

   
    $app->get('/praticiens', ListerPraticienAction::class);
    $app->get('/praticiens/{id}', PraticienDetailAction::class);
    


    // Opération 7: agenda praticien (authentification requise)
    $app->get('/praticiens/{praticienId}/creneaux', ListerCreneauDejaPraticien::class)
        ->setName('agenda')
        ->add(AuthzMiddleware::class)
        ->add(AuthnMiddleware::class);

   
    // Preflight CORS
    $app->options('/{routes:.+}', function (
        ServerRequestInterface $request,
        ResponseInterface $response
        ): ResponseInterface {
        return $response;
    });

    return $app;
};
