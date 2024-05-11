<?php

use App\Models\DB;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
  $app->get('/habits', function (Request $request, Response $response) {
    $sql = "SELECT * FROM LIV.habits";

    try {
      $db = new DB();
      $conn = $db->connect();
      $stmt = $conn->query($sql);
      $users = $stmt->fetchAll(PDO::FETCH_OBJ);
      $db = null;

      $response->getBody()->write(json_encode($users));
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
  });

  $app->post('/habit', function (Request $request, Response $response, array $args) {
    $data = $request->getParsedBody();
    // cambiar cuando tenga el login
    $userId = 1; 
    $habitName = $data["habitName"];
    $color = $data["color"];
    $icon = $data["icon"];
    $frequencyId = $data["frequencyId"];
    $habitGoal = $data["habitGoal"];
    $habitGoalUnit = $data["habitGoalUnit"];
    $enabled = $data["enabled"];

    $sql = "INSERT INTO LIV.habits (userId, habitName, color, icon, frequencyId, habitGoal, habitGoalUnit, enabled) 
    VALUES (:userId, :habitName, :color, :icon, :frequencyId, :habitGoal, :habitGoalUnit, :enabled)";

if($frequencyId == 'D'){
  // for para insertar habitos por dias de la semana 
}
    try {
      $db = new DB();
      $conn = $db->connect();

      $stmt = $conn->prepare($sql);
      $stmt->bindParam(':userId', $userId);
      $stmt->bindParam(':habitName', $habitName);
      $stmt->bindParam(':title', $title);
      $stmt->bindParam(':color', $color);
      $stmt->bindParam(':icon', $icon);
      $stmt->bindParam(':frequencyId', $frequencyId);
      $stmt->bindParam(':habitGoal', $habitGoal);
      $stmt->bindParam(':habitGoalUnit', $habitGoalUnit);
      $stmt->bindParam(':enabled', $enabled);
      $result = $stmt->execute();

      $db = null;
      $response->getBody()->write(json_encode($result));
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
  });

  $app->get('/habits/frequencies', function (Request $request, Response $response) {
    $sql = "SELECT * FROM LIV.frequencies";

    try {
      $db = new DB();
      $conn = $db->connect();
      $stmt = $conn->query($sql);
      $users = $stmt->fetchAll(PDO::FETCH_OBJ);
      $db = null;

      $response->getBody()->write(json_encode($users));
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
  });

  $app->get('/habits/colors', function (Request $request, Response $response) {
    $sql = "SELECT * FROM LIV.defaultColors";

    try {
      $db = new DB();
      $conn = $db->connect();
      $stmt = $conn->query($sql);
      $users = $stmt->fetchAll(PDO::FETCH_OBJ);
      $db = null;

      $response->getBody()->write(json_encode($users));
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
  });

  $app->get('/habits/week-days', function (Request $request, Response $response) {
    $sql = "SELECT * FROM LIV.weekDays";

    try {
      $db = new DB();
      $conn = $db->connect();
      $stmt = $conn->query($sql);
      $users = $stmt->fetchAll(PDO::FETCH_OBJ);
      $db = null;

      $response->getBody()->write(json_encode($users));
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
  });
};
