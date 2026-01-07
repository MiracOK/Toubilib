<?php
declare(strict_types=1);

use Slim\App;
use toubilib\gateway\api\action\GetAllPraticiensAction;

return function (App $app) {
    
    $app->get('/praticiens', GetAllPraticiensAction::class);
};
