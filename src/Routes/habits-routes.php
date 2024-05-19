<?php

use App\Models\DB;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app, $jwtMiddleware) {

  $app->get('/api/habits', function (Request $request, Response $response) {
    $date = $request->getQueryParams()['date'];

    $sql = "SELECT H.habitId,
                   H.habitName,
                   H.color,
                   H.icon,
                   H.frequencyId,
                   H.habitGoal,
                   H.habitGoalUnit,
                   HR.record
              FROM LIV.habits H
         LEFT JOIN LIV.habitsWeekDays HWD ON HWD.habitId = H.habitId AND HWD.weekdayId = DAYNAME(now())
         LEFT JOIN LIV.habitRecords HR ON HR.habitId = H.habitId AND HR.recordDate = :date
             WHERE H.enabled IS TRUE
               AND H.userId = 1";

    try {
        $db = new DB();
        $conn = $db->connect();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR); // Vinculación del parámetro
        $stmt->execute();
        $habits = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;

        $response->getBody()->write(json_encode($habits));
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

$app->get('/api/habit/{habitId}', function (Request $request, Response $response, array $args) {
  $habitId = $args['habitId'];
  $sql = "SELECT H.habitId,
                 H.habitName,
                 H.color,
                 H.icon,
                 H.frequencyId,
                 H.habitGoal,
                 H.habitGoalUnit
          FROM LIV.habits H
          WHERE H.habitId = :habitId";

  try {
      $db = new DB();
      $conn = $db->connect();
      $stmt = $conn->prepare($sql);
      $stmt->execute([':habitId' => $habitId]);
      $habit = $stmt->fetchAll(PDO::FETCH_OBJ);
      $db = null;

      if (!empty($habit)) {
          $habit = $habit[0];
      }

      $response->getBody()->write(json_encode($habit));
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



  $app->post('/api/habit', function (Request $request, Response $response, array $args) {
    $data = $request->getParsedBody();
    // cambiar cuando tenga el login
    $userId = 1; 
    $habitName = $data["habitName"];
    $color = $data["color"];
    $icon = $data["icon"];
    $frequencyId = $data["frequencyId"];
    $habitGoal = $data["habitGoal"];
    $habitGoalUnit = $data["habitGoalUnit"];

    try {
        $db = new DB();
        $conn = $db->connect();

        $conn->beginTransaction();

        $sql = "INSERT INTO LIV.habits (userId, habitName, color, icon, frequencyId, habitGoal, habitGoalUnit) 
                VALUES (:userId, :habitName, :color, :icon, :frequencyId, :habitGoal, :habitGoalUnit)";
 
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':userId', $userId);
        $stmt->bindParam(':habitName', $habitName);
        $stmt->bindParam(':color', $color);
        $stmt->bindParam(':icon', $icon);
        $stmt->bindParam(':frequencyId', $frequencyId);
        $stmt->bindParam(':habitGoal', $habitGoal);
        $stmt->bindParam(':habitGoalUnit', $habitGoalUnit);
        $stmt->execute();

        $habitId = $conn->lastInsertId();

        if($frequencyId == 'D'){
            $weekDays = $data["weekDays"];
            foreach($weekDays as $weekDay){
                if($weekDay['selected']){
                    $sql = "INSERT INTO LIV.habitsWeekDays (habitId, weekdayId) VALUES (:habitId, :weekdayId)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':habitId', $habitId);
                    $stmt->bindParam(':weekdayId', $weekDay['weekdayId']);
                    $stmt->execute();
                }
            }
        }

        $conn->commit();

        $db = null;
        $response->getBody()->write(json_encode(['success' => true]));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        $conn->rollBack();

        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
})->add($jwtMiddleware);;

  
  $app->get('/api/habits/frequencies', function (Request $request, Response $response) {
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

  $app->get('/api/habits/colors', function (Request $request, Response $response) {
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
  })->add($jwtMiddleware);;

  $app->get('/api/habits/week-days', function (Request $request, Response $response) {
    $sql = "SELECT weekdayId, 
                   weekdayName, 
                   true AS selected
              FROM LIV.weekDays";

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
  })->add($jwtMiddleware);

  // HABITS RECORDS
  $app->post('/api/habit/record', function (Request $request, Response $response, array $args) {
    $data = $request->getParsedBody();
    // cambiar cuando tenga el login
    $userId = 1; 
    $habitId = $data["habitId"];
    $recordDate = $data["recordDate"];
    $record = $data["record"];
    
    try {
        $db = new DB();
        $conn = $db->connect();
        $conn->beginTransaction();
        $sql = "INSERT INTO LIV.habitRecords (habitId, userId, recordDate, record)
                     VALUES (:habitId, :userId, :recordDate, :record) 
           ON DUPLICATE KEY UPDATE record = :record";
 
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':userId', $userId);
        $stmt->bindParam(':habitId', $habitId);
        $stmt->bindParam(':recordDate', $recordDate);
        $stmt->bindParam(':record', $record);
        $stmt->execute();
        

        $conn->commit();

        $db = null;
        $response->getBody()->write(json_encode(['success' => true]));
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
};