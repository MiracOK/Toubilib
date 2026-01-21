<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;

return function () {
    $settings = require __DIR__ . '/settings.php';

    $containerBuilder = new ContainerBuilder();
    
    $containerBuilder->addDefinitions([
        'settings' => $settings
    ]);
    
    $containerBuilder->addDefinitions(__DIR__ . '/services.php');
    $containerBuilder->addDefinitions(__DIR__ . '/api.php');
    
    $container = $containerBuilder->build();

    AppFactory::setContainer($container);
    $app = AppFactory::create();

    $app->addBodyParsingMiddleware();

    $app->addRoutingMiddleware();

    $errorMiddleware = $app->addErrorMiddleware(
        $settings['displayErrorDetails'],
        $settings['logErrors'],
        $settings['logErrorDetails']
    );

    return $app;
};
