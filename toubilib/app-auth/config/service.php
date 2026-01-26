<?php

use Psr\Container\ContainerInterface;
use toubilib\infra\jwt\JwtManager;
use toubilib\core\application\ports\api\jwt\JwtManagerInterface;
use toubilib\core\application\ports\spi\repositoryInterfaces\AuthRepositoryInterface;
use toubilib\infra\repositories\PDOAuthRepository;
use toubilib\core\application\services\ToubilibAuthnService;
use toubilib\infra\provider\JwtAuthProvider;
use toubilib\core\application\ports\api\service\ToubilibAuthnServiceInterface;
use toubilib\core\application\ports\api\provider\AuthProviderInterface;

return [
   
    'toubiprat.pdo' => function (ContainerInterface $c) {
        $config = parse_ini_file($c->get('toubiprat.db.config'));
        $dsn = "{$config['driver']}:host={$config['host']};dbname={$config['database']}";
        $user = $config['username'];
        $password = $config['password'];
        return new \PDO($dsn, $user, $password, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
    },

    'toubirdv.pdo' => function (ContainerInterface $c) {
        $config = parse_ini_file($c->get('toubirdv.db.config'));
        $dsn = "{$config['driver']}:host={$config['host']};dbname={$config['database']}";
        $user = $config['username'];
        $password = $config['password'];
        return new \PDO($dsn, $user, $password, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
    },

    'toubipat.pdo' => function (ContainerInterface $c) {
        $config = parse_ini_file($c->get('toubipat.db.config'));
        $dsn = "{$config['driver']}:host={$config['host']};dbname={$config['database']}";
        $user = $config['username'];
        $password = $config['password'];
        return new \PDO($dsn, $user, $password, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
    },

    'toubiauth.pdo' => function (ContainerInterface $c) {
        $config = parse_ini_file($c->get('toubiauth.db.config'));
        $dsn = "{$config['driver']}:host={$config['host']};dbname={$config['database']}";
        $user = $config['username'];
        $password = $config['password'];
        return new \PDO($dsn, $user, $password, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
    },

    AuthRepositoryInterface::class => fn(ContainerInterface $c) => new PDOAuthRepository($c->get('toubiauth.pdo')),

    ToubilibAuthnServiceInterface::class => function (ContainerInterface $c) {
        return new ToubilibAuthnService($c->get(AuthRepositoryInterface::class));
    },

    AuthProviderInterface::class => function (ContainerInterface $c) {
        return new JwtAuthProvider(
            $c->get(ToubilibAuthnServiceInterface::class),
            $c->get(JwtManagerInterface::class)
        );
    },

    JwtManagerInterface::class => function (ContainerInterface $c) {
        return new JwtManager(
            $_ENV['JWT_SECRET'],
            'toubilib.api',
            'HS512',
            900,
            2592000
        );
    },

];
