<?php

use Psr\Container\ContainerInterface;
use toubilib\core\application\ports\spi\repositoryInterfaces\ServicePraticienInterface;
use toubilib\core\application\usecases\ServicePraticien;
use toubilib\infra\repositories\PDOPraticienRepository;
use toubilib\infra\jwt\JwtManager;
use toubilib\core\application\ports\api\jwt\JwtManagerInterface;
use toubilib\core\application\ports\spi\repositoryInterfaces\ServiceRendezVousInterface;
use toubilib\core\application\usecases\ServiceRendezVous;
use toubilib\core\application\ports\spi\repositoryInterfaces\RdvRepositoryInterface;
use toubilib\infra\repositories\PDORdvRepository;

return [
    // service
    ServicePraticienInterface::class => function (ContainerInterface $c) {
        return new ServicePraticien($c->get(PDOPraticienRepository::class));
    },

    ServiceRendezVousInterface::class => function (ContainerInterface $c) {
        return new ServiceRendezVous($c->get(RdvRepositoryInterface::class));
    },

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

    RdvRepositoryInterface::class => fn(ContainerInterface $c) => new PDORdvRepository(
        $c->get('toubirdv.pdo'),
        $c->get('toubiprat.pdo'),
        $c->get('toubipat.pdo')
    ),
];
