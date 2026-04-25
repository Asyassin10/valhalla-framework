<?php

declare(strict_types=1);

use Valhalla\Framework\Core\Application;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$app = new Application(dirname(__DIR__));
$app->loadRoutes(dirname(__DIR__) . '/routes/api.php');
$app->handle()->send();
