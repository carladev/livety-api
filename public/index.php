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
                error_log('UserID: ' . $decoded->userId);
                $request = $request->withAttribute('userId', $decoded->userId);
                return $handler->handle($request);
            } catch (Exception $e) {
                $response = new Response();
                $response->getBody()->write(json_encode(['message' => 'Unauthorized']));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }
        }
    }

    $response = new Response();
    $response->getBody()->write(json_encode(['message' => 'Unauthorized']));
    return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
};


// Auth
(require __DIR__ . '/../src/Routes/auth-routes.php')($app);

// Users
(require __DIR__ . '/../src/Routes/users-routes.php')($app, $jwtMiddleware);

// Habits
(require __DIR__ . '/../src/Routes/habits-routes.php')($app, $jwtMiddleware);

// Tracking
(require __DIR__ . '/../src/Routes/tracking-routes.php')($app, $jwtMiddleware);
$app->run();