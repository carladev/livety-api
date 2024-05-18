<?php

use App\Models\DB;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

return function (App $app) {
    $secretKey = $_ENV['JWT_SECRET'];

    $app->post('/api/login', function (Request $request, Response $response) use ($secretKey) {
        $data = $request->getParsedBody();
        $userName = $data['userName'];
        $password = $data['password'];

        
        $db = new DB();
        $conn = $db->connect();
    
        $stmt = $conn->prepare('SELECT * FROM users WHERE userName = :userName');
        $stmt->execute(['userName' => $userName]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $issuedAt = time();
            $expirationTime = $issuedAt + 3600; 
            $payload = [
                'iat' => $issuedAt,
                'exp' => $expirationTime,
                'userName' => $userName,
            ];

            $jwt = JWT::encode($payload, $secretKey, 'HS256');
            $response->getBody()->write(json_encode(['token' => $jwt]));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    });
};
