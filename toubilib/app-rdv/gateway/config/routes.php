<?php
declare(strict_types=1);

use Slim\App;
use toubilib\gateway\api\action\GetAllPraticiensAction;
use toubilib\gateway\api\action\GetPraticienByIdAction;
use toubilib\gateway\api\middlewares\Cors;

return function (App $app) {
    
    // Appliquer le middleware CORS globalement
    $app->add(Cors::class);
    
    // ==================== Exercice 1 & 2: Praticiens ====================
    $app->get('/praticiens', GetAllPraticiensAction::class)->setName('praticiens.list');
    $app->get('/praticiens/{id}', GetPraticienByIdAction::class)->setName('praticiens.detail');
};
