<?php
declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Psr\Container\ContainerInterface;

return [
    ClientInterface::class => function (ContainerInterface $c) {
        $settings = $c->get('settings');
        return new Client([
            'base_uri' => $settings['api.toubilib']['base_uri'],
            'timeout' => $settings['api.toubilib']['timeout'],
            'http_errors' => false,
        ]);
    },
];
