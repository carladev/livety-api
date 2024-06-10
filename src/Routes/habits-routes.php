<?php

use App\Models\DB;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app, $jwtMiddleware) {

  $app->get('/api/habits', function (Request $request, Response $response) {
    $date = $request->getQueryParams()['date'];
    $userId = $request->getAttribute('userId');

    $sql = "SELECT H.habitId,
                   H.habitName,
                   H.color,
                   H.icon,
                   H.frequencyId,
                   F.frequencyName,
                   H.habitGoal,
                   H.habitGoalUnit,
                   HR.record
              FROM LIV.habits H
        INNER JOIN LIV.frequencies F ON F.frequencyId = H.frequencyId
         LEFT JOIN LIV.habitsWeekDays HWD ON HWD.habitId = H.habitId AND HWD.weekdayId = WEEKDAY(now())
         LEFT JOIN LIV.habitRecords HR ON HR.habitId = H.habitId AND HR.recordDate = :date
             WHERE H.enabled IS TRUE
               AND H.userId = :userId";

    try {
        $db = new DB();
        $conn = $db->connect();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
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
    $userId = $request->getAttribute('userId');
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
                   weekdayAlias,
                   weekdayName, 
                   false AS selected
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
    $userId = $request->getAttribute('userId'); 
    $habitId = $data["habitId"];
    $recordDate = $data["recordDate"];
    $record = $data["record"];

    try {
        $db = new DB();
        $conn = $db->connect();
        $conn->beginTransaction();

        // Obtener el frequencyId del hábito
        $sql = "SELECT frequencyId FROM LIV.habits WHERE habitId = :habitId";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':habitId', $habitId);
        $stmt->execute();
        $habit = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$habit) {
            throw new Exception('Habit not found');
        }

        $frequencyId = $habit['frequencyId'];

        if ($frequencyId == 'W') {
            // Calcula la fecha de inicio de la semana (lunes)
            $startDate = new DateTime($recordDate);
            $dayOfWeek = $startDate->format('N'); // 1 (lunes) a 7 (domingo)
            $startDate->modify('-' . ($dayOfWeek - 1) . ' days');

            // Crea un registro para cada día de la semana (lunes a domingo)
            for ($i = 0; $i < 7; $i++) {
                $currentDate = clone $startDate;
                $currentDate->modify("+$i days");
                $formattedDate = $currentDate->format('Y-m-d');
                $sql = "INSERT INTO LIV.habitRecords (habitId, userId, recordDate, record)
                         VALUES (:habitId, :userId, :recordDate, :record) 
                   ON DUPLICATE KEY UPDATE record = :record";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':userId', $userId);
                $stmt->bindParam(':habitId', $habitId);
                $stmt->bindParam(':recordDate', $formattedDate);
                $stmt->bindParam(':record', $record);
                $stmt->execute();
            }
        } elseif ($frequencyId == 'D') {
            $sql = "INSERT INTO LIV.habitRecords (habitId, userId, recordDate, record)
                     VALUES (:habitId, :userId, :recordDate, :record) 
               ON DUPLICATE KEY UPDATE record = :record";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':userId', $userId);
            $stmt->bindParam(':habitId', $habitId);
            $stmt->bindParam(':recordDate', $recordDate);
            $stmt->bindParam(':record', $record);
            $stmt->execute();
        } else {
            throw new Exception('Invalid frequencyId');
        }

        $conn->commit();

        $db = null;
        $response->getBody()->write(json_encode(['success' => true]));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        if ($conn) {
            $conn->rollBack();
        }

        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    } catch (Exception $e) {
        if ($conn) {
            $conn->rollBack();
        }

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