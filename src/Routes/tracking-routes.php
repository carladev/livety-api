<?php
use App\Models\DB;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app, $jwtMiddleware) {

    $app->get('/api/weekly-tracking/{weekNumber}', function (Request $request, Response $response, $args) {
        $weekNumber = $args['weekNumber'];
        $userId = $request->getAttribute('userId');

        $sql = "WITH HabitWeekDays AS (
                  SELECT
                      H.habitId,
                      H.habitName,
                      H.color,
                      H.icon,
                      H.habitGoal,
                      WD.weekdayId,
                      WD.weekdayName
                 FROM LIV.habits H
           CROSS JOIN LIV.weekDays WD
                WHERE H.userId = :userId)
              SELECT
                  HWD.habitId,
                  HWD.habitName,
                  HWD.color,
                  HWD.icon,
                  HWD.habitGoal,
                  HWD.weekdayId,
                  HWD.weekdayName,
                  ROUND(COALESCE((HR.record * 100)/HWD.habitGoal, 0), 0) AS progress,
                  HR.recordDate
             FROM HabitWeekDays HWD
        LEFT JOIN LIV.habitRecords HR ON HWD.habitId = HR.habitId
              AND WEEKDAY(HR.recordDate) = HWD.weekdayId
              AND WEEK(HR.recordDate) = :weekNumber
         ORDER BY HWD.habitId, HWD.weekdayId, progress DESC";

        try {
            $db = new DB();
            $conn = $db->connect();
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':weekNumber', $weekNumber, PDO::PARAM_INT);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;

            $weekdaysHabits = [];
            foreach ($results as $row) {
                if (!isset($weekdaysHabits[$row->weekdayId])) {
                    $weekdaysHabits[$row->weekdayId] = [
                        'weekdayId' => $row->weekdayId,
                        'weekdayName' => $row->weekdayName,
                        'habits' => []
                    ];
                }
                $weekdaysHabits[$row->weekdayId]['habits'][] = [
                    'habitId' => $row->habitId,
                    'habitName' => $row->habitName,
                    'color' => $row->color,
                    'icon' => $row->icon,
                    'habitGoal' => $row->habitGoal,
                    'progress' => $row->progress,
                    'recordDate' => $row->recordDate
                ];
            }

            $response->getBody()->write(json_encode(array_values($weekdaysHabits)));
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
