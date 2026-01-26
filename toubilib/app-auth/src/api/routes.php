<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use toubilib\api\actions\SignupAction;
use toubilib\api\actions\SigninAction;
use toubilib\api\actions\RefreshTokenAction;
use toubilib\api\actions\ValidateTokenAction;


return function (App $app): App {

    $app->post('/auth/signin', SigninAction::class)->setName('auth.signin');
    $app->post('/auth/signup', SignupAction::class)->setName('auth.signup');
    $app->post('/auth/refresh', RefreshTokenAction::class)->setName('auth.refresh');

    $app->post('/tokens/validate', ValidateTokenAction::class)->setName('tokens.validate');



    // Preflight CORS
    $app->options('/{routes:.+}', function (
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        return $response;
    });

    return $app;
};
