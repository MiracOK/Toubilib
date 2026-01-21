<?php
declare(strict_types=1);

use Slim\App;
use toubilib\gateway\api\action\GetAllPraticiensAction;
use toubilib\gateway\api\action\GetPraticienByIdAction;
use toubilib\gateway\api\action\GetCreneauxPraticienAction;
use toubilib\gateway\api\action\GenericRdvGatewayAction;
use toubilib\gateway\api\actions\SignupGatewayAction;
use toubilib\gateway\api\actions\SigninGatewayAction;
use toubilib\gateway\api\actions\RefreshGatewayAction;
use toubilib\gateway\api\middlewares\Cors;

return function (App $app) {
    
    // Appliquer le middleware CORS globalement
    $app->add(Cors::class);
    
    // ==================== Exercice 1 (TD 2.2): Authentication ====================
    $app->post('/auth/signup', SignupGatewayAction::class)->setName('auth.signup');
    $app->post('/auth/signin', SigninGatewayAction::class)->setName('auth.signin');
    $app->post('/auth/refresh', RefreshGatewayAction::class)->setName('auth.refresh');
    
    // ==================== Exercice 1 & 2 & 3: Praticiens (microservice) ====================
    $app->get('/praticiens', GetAllPraticiensAction::class)->setName('praticiens.list');
    $app->get('/praticiens/{id}', GetPraticienByIdAction::class)->setName('praticiens.detail');
    $app->get('/praticiens/{praticienId}/creneaux', GetCreneauxPraticienAction::class)->setName('praticiens.creneaux');
    
    // ==================== Exercice 4: RDV (microservice) ====================
    $app->get('/rdvs/{id}', GenericRdvGatewayAction::class)->setName('rdvs.detail');
    $app->post('/rdvs', GenericRdvGatewayAction::class)->setName('rdvs.create');
    $app->patch('/rdvs/{id}', GenericRdvGatewayAction::class)->setName('rdvs.update');
    $app->delete('/rdvs/{id}', GenericRdvGatewayAction::class)->setName('rdvs.delete');
};
