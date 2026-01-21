<?php

use Psr\Container\ContainerInterface;
use toubilib\core\application\ports\spi\repositoryInterfaces\ServicePatientInterface;
use toubilib\core\application\ports\spi\repositoryInterfaces\ServicePraticienInterface;
use toubilib\core\application\usecases\ServicePraticien;
use toubilib\infra\repositories\PDOPraticienRepository;
use toubilib\core\application\ports\spi\repositoryInterfaces\RdvRepositoryInterface;
use toubilib\infra\repositories\PDORdvRepository;
use toubilib\core\application\ports\spi\repositoryInterfaces\ServiceRendezVousInterface;
use toubilib\core\application\usecases\ServiceRendezVous;

// nouveaux imports pour patient
use toubilib\core\application\ports\spi\repositoryInterfaces\PatientRepositoryInterface;
use toubilib\infra\repositories\PDOPatientRepository;
use toubilib\core\application\usecases\ServicePatient;

use toubilib\core\application\ports\spi\repositoryInterfaces\AuthRepositoryInterface;
use toubilib\infra\repositories\PDOAuthRepository;
use toubilib\core\application\services\ToubilibAuthnService;
use toubilib\core\application\services\AuthzService;
use toubilib\infra\provider\JwtAuthProvider;
use toubilib\infra\jwt\JwtManager;
use toubilib\core\application\ports\api\service\ToubilibAuthnServiceInterface;
use toubilib\core\application\ports\api\service\AuthzServiceInterface;
use toubilib\core\application\ports\api\provider\AuthProviderInterface;
use toubilib\core\application\ports\api\jwt\JwtManagerInterface;

return [
   
        // service rendez-vous
    ServiceRendezVousInterface::class => function (ContainerInterface $c) {
        return new ServiceRendezVous($c->get(RdvRepositoryInterface::class));
    },


    'toubirdv.pdo' => function (ContainerInterface $c) {
        $config = parse_ini_file($c->get('toubirdv.db.config'));
        $dsn = "{$config['driver']}:host={$config['host']};dbname={$config['database']}";
        $user = $config['username'];
        $password = $config['password'];
        return new \PDO($dsn, $user, $password, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
    },

    'toubiprat.pdo' => function (ContainerInterface $c) {
        $config = parse_ini_file($c->get('toubiprat.db.config'));
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
    
    RdvRepositoryInterface::class => fn(ContainerInterface $c) => new PDORdvRepository(
        $c->get('toubirdv.pdo'),  
        $c->get('toubiprat.pdo'),  
        $c->get('toubipat.pdo')    
    ),


];