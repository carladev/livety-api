<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Selective\BasePath\BasePathMiddleware;
use Slim\Factory\AppFactory;


require_once __DIR__ . '/../vendor/autoload.php';
//$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
//$dotenv->load();

require_once __DIR__ . '/../src/config.php';

$app = AppFactory::create();
$app->addBodyParsingMiddleware();

$app->addRoutingMiddleware();
$app->add(new BasePathMiddleware($app));
$app->addErrorMiddleware(true, true, true);

// Users
(require __DIR__ . '/../src/Routes/users-routes.php')($app);

$app->run();
