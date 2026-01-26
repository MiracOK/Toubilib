<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Psr\Container\ContainerInterface;
use toubilib\gateway\api\middlewares\Cors;

return [

    Cors::class => fn() => new Cors(),

    // Client par défaut pour l'API Toubilib complète
    ClientInterface::class => function (ContainerInterface $c) {
        $settings = $c->get('settings');
        return new Client([
            'base_uri' => $settings['api.toubilib']['base_uri'],
            'timeout' => $settings['api.toubilib']['timeout'],
            'http_errors' => false,
        ]);
    },

    // Client spécifique pour le microservice Praticiens
    'client.praticiens' => function (ContainerInterface $c) {
        $settings = $c->get('settings');
        return new Client([
            'base_uri' => $settings['api.praticiens']['base_uri'],
            'timeout' => $settings['api.praticiens']['timeout'],
            'http_errors' => false,
        ]);
    },

    // Client spécifique pour le microservice RDV
    'client.rdv' => function (ContainerInterface $c) {
        $settings = $c->get('settings');
        return new Client([
            'base_uri' => $settings['api.rdv']['base_uri'],
            'timeout' => $settings['api.rdv']['timeout'],
            'http_errors' => false,
        ]);
    },

    'client.auth' => function (ContainerInterface $c) {
        $settings = $c->get('settings');
        return new Client([
            'base_uri' => $settings['api.auth']['base_uri'],
            'timeout' => $settings['api.auth']['timeout'],
            'http_errors' => false,
        ]);
    },
];
