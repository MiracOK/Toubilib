<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use toubilib\gateway\api\action\ValidateTokenAction;
use toubilib\gateway\api\action\GetAllPraticiensAction;
use toubilib\gateway\api\action\GetPraticienByIdAction;
use toubilib\gateway\api\action\GetCreneauxPraticienAction;
use toubilib\gateway\api\action\GetRdvByIdAction;
use toubilib\gateway\api\action\CreateRdvGatewayAction;
use toubilib\gateway\api\action\UpdateRdvStatusGatewayAction;
use toubilib\gateway\api\action\DeleteRdvGatewayAction;
use toubilib\gateway\api\action\RefreshGatewayAction;
use toubilib\gateway\api\action\SigninGatewayAction;
use toubilib\gateway\api\action\SignupGatewayAction;

return [
    // // Auth Actions
    SignupGatewayAction::class => fn(ContainerInterface $c) => new SignupGatewayAction($c->get('client.auth')),
    SigninGatewayAction::class => fn(ContainerInterface $c) => new SigninGatewayAction($c->get('client.auth')),
    RefreshGatewayAction::class => fn(ContainerInterface $c) => new RefreshGatewayAction($c->get('client.auth')),


    ValidateTokenAction::class => fn(ContainerInterface $c) => new ValidateTokenAction($c->get('client.auth')),
    // Praticien Actions
    GetAllPraticiensAction::class => fn(ContainerInterface $c) => new GetAllPraticiensAction($c),
    GetPraticienByIdAction::class => fn(ContainerInterface $c) => new GetPraticienByIdAction($c),
    GetCreneauxPraticienAction::class => fn(ContainerInterface $c) => new GetCreneauxPraticienAction($c),

    // RDV Actions
    GetRdvByIdAction::class => fn(ContainerInterface $c) => new GetRdvByIdAction($c->get('client.rdv')),
    CreateRdvGatewayAction::class => fn(ContainerInterface $c) => new CreateRdvGatewayAction($c->get('client.rdv')),
    UpdateRdvStatusGatewayAction::class => fn(ContainerInterface $c) => new UpdateRdvStatusGatewayAction($c->get('client.rdv')),
    DeleteRdvGatewayAction::class => fn(ContainerInterface $c) => new DeleteRdvGatewayAction($c->get('client.rdv')),
];
