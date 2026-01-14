<?php

use Psr\Container\ContainerInterface;
use toubilib\core\application\ports\spi\repositoryInterfaces\ServicePraticienInterface;
use toubilib\core\application\usecases\ServicePraticien;
use toubilib\infra\repositories\PDOPraticienRepository;
use toubilib\infra\jwt\JwtManager;
use toubilib\core\application\ports\api\jwt\JwtManagerInterface;

return [
    // service
    ServicePraticienInterface::class => function (ContainerInterface $c) {
        return new ServicePraticien($c->get(PDOPraticienRepository::class));
    },
    'toubiprat.pdo' => function (ContainerInterface $c) {
        $config = parse_ini_file($c->get('toubiprat.db.config'));
        $dsn = "{$config['driver']}:host={$config['host']};dbname={$config['database']}";
        $user = $config['username'];
        $password = $config['password'];
        return new \PDO($dsn, $user, $password, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
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

    PDOPraticienRepository::class => fn(ContainerInterface $c) => new PDOPraticienRepository($c->get('toubiprat.pdo')),



];
