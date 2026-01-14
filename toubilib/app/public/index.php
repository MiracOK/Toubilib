<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$app = (require_once __DIR__ . '/../gateway/config/bootstrap.php')();

(require_once __DIR__ . '/../gateway/config/routes.php')($app);

$app->run();
