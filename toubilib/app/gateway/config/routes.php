<?php
declare(strict_types=1);

use Slim\App;
use toubilib\gateway\api\action\GetAllPraticiensAction;
use toubilib\gateway\api\action\GetPraticienByIdAction;
use toubilib\gateway\api\action\GetCreneauxPraticienAction;
use toubilib\gateway\api\action\GetRdvByIdAction;
use toubilib\gateway\api\action\CreateRdvGatewayAction;
use toubilib\gateway\api\action\UpdateRdvStatusGatewayAction;
use toubilib\gateway\api\action\DeleteRdvGatewayAction;
use toubilib\gateway\api\action\SignupGatewayAction;
use toubilib\gateway\api\action\SigninGatewayAction;
use toubilib\gateway\api\action\RefreshGatewayAction;
use toubilib\gateway\api\action\ValidateTokenAction;
use toubilib\gateway\api\middlewares\Cors;

return function (App $app) {
    
    // Appliquer le middleware CORS globalement
    $app->add(Cors::class);
    
    // ==================== Exercice 1 (TD 2.2): Authentication ====================
    $app->post('/auth/signup', SignupGatewayAction::class)->setName('auth.signup');
    $app->post('/auth/signin', SigninGatewayAction::class)->setName('auth.signin');
    $app->post('/auth/refresh', RefreshGatewayAction::class)->setName('auth.refresh');

    $app->post('/tokens/validate', ValidateTokenAction::class)->setName('tokens.validate');
    
    // ==================== Exercice 1 & 2 & 3: Praticiens (microservice) ====================
    $app->get('/praticiens', GetAllPraticiensAction::class)->setName('praticiens.list');
    $app->get('/praticiens/{id}', GetPraticienByIdAction::class)->setName('praticiens.detail');
    $app->get('/praticiens/{praticienId}/creneaux', GetCreneauxPraticienAction::class)->setName('praticiens.creneaux');
    
    // ==================== Exercice 4: RDV (microservice) ====================
    $app->get('/rdvs/{id}', GetRdvByIdAction::class)->setName('rdvs.detail');
    $app->post('/rdvs', CreateRdvGatewayAction::class)->setName('rdvs.create');
    $app->patch('/rdvs/{id}', UpdateRdvStatusGatewayAction::class)->setName('rdvs.update');
    $app->delete('/rdvs/{id}', DeleteRdvGatewayAction::class)->setName('rdvs.delete');
};
