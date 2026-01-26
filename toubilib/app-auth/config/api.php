<?php

use Psr\Container\ContainerInterface;
use toubilib\api\actions\ListerPraticienAction;
use toubilib\core\application\ports\spi\repositoryInterfaces\ServicePraticienInterface;
use toubilib\api\actions\ListerCreneauDejaPraticien;
use toubilib\core\application\ports\spi\repositoryInterfaces\ServiceRendezVousInterface;
use toubilib\api\actions\PraticienDetailAction;
use toubilib\api\actions\ValidateTokenAction;
use toubilib\core\application\ports\api\provider\AuthProviderInterface;

return [
    // application
    ListerPraticienAction::class => function (ContainerInterface $c) {
        return new ListerPraticienAction($c->get(ServicePraticienInterface::class));
    },
    ListerCreneauDejaPraticien::class => function (ContainerInterface $c) {
        return new ListerCreneauDejaPraticien($c->get(ServiceRendezVousInterface::class));
    },


    PraticienDetailAction::class => function (ContainerInterface $c) {
        return new PraticienDetailAction($c->get(ServicePraticienInterface::class));
    },

    ValidateTokenAction::class => function (ContainerInterface $c) {
        return new ValidateTokenAction($c->get(AuthProviderInterface::class));
    },
];
