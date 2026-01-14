<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use toubilib\api\actions\AnnulerRdvAction;
use toubilib\api\actions\CreateRdvAction;
use toubilib\api\actions\ListerRDVbyId;
use toubilib\api\actions\UpdateRdvStatusAction;

use toubilib\api\middlewares\ValidateInputRdv;

use toubilib\core\application\middlewares\AuthnMiddleware;
use toubilib\core\application\middlewares\AuthzMiddleware;

return function (App $app): App {

    
    
    // Opération 4,6: consulter un RDV (authentification requise)
    $app->get('/rdvs/{id}', ListerRDVbyId::class)
        ->setName('rdv.get')
        ->add(AuthzMiddleware::class)
        ->add(AuthnMiddleware::class);
    
    // Opération 6: créer un RDV (authentification requise - patient seulement)
    $app->post('/rdvs', CreateRdvAction::class)
        ->setName('rdv.create')
        ->add(ValidateInputRdv::class)
        ->add(AuthzMiddleware::class)
        ->add(AuthnMiddleware::class);
    
    // Opération 5: annuler un RDV (authentification requise - patient du RDV seulement)
    $app->delete('/rdvs/{id}', AnnulerRdvAction::class)
        ->setName('rdv.delete')
        ->add(AuthzMiddleware::class)
        ->add(AuthnMiddleware::class);

    // Opération 10: modifier statut RDV (authentification requise - praticien du RDV seulement)
    $app->patch('/rdvs/{id}', UpdateRdvStatusAction::class)
        ->setName('rdv.update')
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
