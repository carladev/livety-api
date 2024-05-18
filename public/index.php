<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('content-type: application/json; charset=utf-8');

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Selective\BasePath\BasePathMiddleware;
use Slim\Factory\AppFactory;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;


require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
$secretKey = $_ENV['JWT_SECRET'];

require_once __DIR__ . '/../src/config.php';

$app = AppFactory::create();
$app->addBodyParsingMiddleware();

$app->addRoutingMiddleware();
$app->add(new BasePathMiddleware($app));
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$jwtMiddleware = function (Request $request, $handler) use ($secretKey) {
  $authHeader = $request->getHeader('Authorization');
  if ($authHeader) {
      $arr = explode(' ', $authHeader[0]);
      if (count($arr) == 2 && $arr[0] == 'Bearer') {
          $jwt = $arr[1];
          try {
              $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
              $request = $request->withAttribute('jwt', $decoded);
              return $handler->handle($request);
          } catch (Exception $e) {
              return (new Slim\Psr7\Response())->withStatus(401);
          }
      }
  }

  return (new Slim\Psr7\Response())->withStatus(401);
};


// Login
(require __DIR__ . '/../src/Routes/login-routes.php')($app);

// Habits
(require __DIR__ . '/../src/Routes/habits-routes.php')($app, $jwtMiddleware);

$app->run();