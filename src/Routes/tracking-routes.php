<?php

use App\Models\DB;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app, $jwtMiddleware) {

  $app->get('/api/weekly-tracking', function (Request $request, Response $response) {
    $weekNumber = $request->getQueryParams()['weekNumber'];
    $userId = $request->getAttribute('userId');

    $sql = "SELECT H.habitId,
                   H.habitName,
                   H.icon,
                   H.color,
                   WEEK(HR.recordDate) AS weekNumber,
                   SUM(HR.record) AS weeklyRecord,
                   H.habitGoal,
                   (SUM(HR.record) / H.habitGoal)*100 AS achievementPercentage,
                   H.habitGoalUnit
              FROM LIV.habitRecords HR
        INNER JOIN LIV.habits H ON H.habitId = HR.habitId
             WHERE H.userId = :userId
               AND WEEK(HR.recordDate) = :weekNumber
         GROUP BY H.habitId,
                  H.habitName,
                  weekNumber";

    try {
        $db = new DB();
        $conn = $db->connect();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':weekNumber', $weekNumber, PDO::PARAM_STR);
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


};