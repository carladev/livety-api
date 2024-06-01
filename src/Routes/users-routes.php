<?php

use App\Models\DB;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app, $jwtMiddleware) {
  $app->get('/api/user', function (Request $request, Response $response, array $args) {
  $userId = $request->getAttribute('userId');
    $sql = "SELECT * FROM LIV.users
            WHERE userId = :userId";
  
    try {
        $db = new DB();
        $conn = $db->connect();
        $stmt = $conn->prepare($sql);
        $stmt->execute([':userId' => $userId]);
        $user = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
  
        if (!empty($user)) {
            $user = $user[0];
        }
  
        $response->getBody()->write(json_encode($user));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        $error = array(
            "message" => $e->getMessage()
        );
  
        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
  })->add($jwtMiddleware);

  $secretKey = $_ENV['JWT_SECRET'];

  $app->post('/api/update-user/{userId}', function (Request $request, Response $response, array $args) use ($secretKey) {
    $userId = $args['userId'];
    $data = $request->getParsedBody();

    if (!isset($data['userName']) || !isset($data['email']) || !isset($data['password'])) {
        $response->getBody()->write(json_encode(['error' => 'Missing required fields']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $userName = $data['userName'];
    $email = $data['email'];
    $password = $data['password'];
    $photo = $data['photo'];

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        $db = new DB();
        $conn = $db->connect();
        $stmt = $conn->prepare('UPDATE LIV.users 
                                SET userName = :userName,
                                    email = :email,
                                    password = IF(:password IS NULL, password, :password),
                                    photo = IF(:photo IS NULL, photo, :photo),
                                WHERE userId = :userId');
        $stmt->bindParam(':userName', $userName);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindValue(':photo', $photo); 
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();

   
        $response->getBody()->write(json_encode(['message' => 'User updated successfully']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
})->add($jwtMiddleware);

};
