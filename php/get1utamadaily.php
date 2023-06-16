<?php
require_once "db_connect.php";

session_start();

if(isset($_POST['startDate'], $_POST['endDate'])){
    $startDate = filter_input(INPUT_POST, 'startDate', FILTER_SANITIZE_STRING);
    $endDate = filter_input(INPUT_POST, 'endDate', FILTER_SANITIZE_STRING);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);

    if ($select_stmt = $db->prepare("SELECT * FROM uniqlo_1u WHERE Date>=? AND Date<=? ORDER BY Date")) {
        $select_stmt->bind_param('ss', $startDate, $endDate);
        
        // Execute the prepared query.
        if (! $select_stmt->execute()) {
            echo json_encode(
                array(
                    "status" => "failed",
                    "message" => "Something went wrong"
                )); 
        }
        else{
            $result = $select_stmt->get_result();
            $message = array();
            $dateBar = array();
            $groundTotalCount = 0;
            $groundPassingCount = 0;
            $groundInCount = 0;
            $lvl1TotalCount = 0;
            $lvl1PassingCount = 0;
            $lvl1InCount = 0;
            
            while ($row = $result->fetch_assoc()) {
                if(!in_array(substr($row['Date'], 10, 3), $dateBar)){
                    $message[] = array( 
                        'Date' => substr($row['Date'], 10, 3).":00",
                        'TotalGroundCount' => 0,
                        'InStoreGroundCount' => 0,
                        'PassingGroundCount' => 0,
                        'TotalLvl1Count' => 0,
                        'InStoreLvl1Count' => 0,
                        'PassingLvl1Count' => 0
                    );

                    array_push($dateBar, substr($row['Date'], 10, 3));
                }

                $key = array_search(substr($row['Date'], 10, 3), $dateBar);

                if($row['Mode'] == 'Ground'){
                    if($row['Door'] == 'in'){
                        $groundInCount += (int)$row['Count'];
                        $groundTotalCount += (int)$row['Count'];
                        $message[$key]['TotalGroundCount'] += (int)$row['Count'];
                        $message[$key]['InStoreGroundCount'] += (int)$row['Count'];
                    }
                    else if($row['Door'] == 'passing by'){
                        $groundPassingCount += (int)$row['Count'];
                        //$groundTotalCount += (int)$row['Count'];
                        //$message[$key]['TotalGroundCount'] += (int)$row['Count'];
                        $message[$key]['PassingGroundCount'] += (int)$row['Count'];
                    }
                }
                else if($row['Mode'] == 'Level 1'){
                    if($row['Door'] == 'in'){
                        $lvl1InCount += (int)$row['Count'];
                        $lvl1TotalCount += (int)$row['Count'];
                        $message[$key]['TotalLvl1Count'] += (int)$row['Count'];
                        $message[$key]['InStoreLvl1Count'] += (int)$row['Count'];
                    }
                    else if($row['Door'] == 'passing by'){
                        $lvl1PassingCount += (int)$row['Count'];
                        //$lvl1TotalCount += (int)$row['Count'];
                        //$message[$key]['TotalLvl1Count'] += (int)$row['Count'];
                        $message[$key]['PassingLvl1Count'] += (int)$row['Count'];
                    }
                }
            }
            
            echo json_encode(
                array(
                    "status" => "success",
                    "message" => $message,
                    "groundTotalCount" => $groundTotalCount,
                    "groundInCount" => $groundInCount,
                    "groundPassingCount" => $groundPassingCount,
                    "lvl1TotalCount" => $lvl1TotalCount,
                    "lvl1InCount" => $lvl1InCount,
                    "lvl1PassingCount" => $lvl1PassingCount
                ));   
        }
    }
}
else{
    echo json_encode(
        array(
            "status" => "failed",
            "message" => "Missing Parameter"
        )
    ); 
}