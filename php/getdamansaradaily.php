<?php
require_once "db_connect.php";

session_start();

if(isset($_POST['startDate'], $_POST['endDate'])){
    $startDate = filter_input(INPUT_POST, 'startDate', FILTER_SANITIZE_STRING);
    $endDate = filter_input(INPUT_POST, 'endDate', FILTER_SANITIZE_STRING);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);

    if ($select_stmt = $db->prepare("SELECT * FROM uniqlo_DA WHERE Date>=? AND Date<=? ORDER BY Date")) {
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
            $totalL1 = 0;
            $totalL2 = 0;
            $totalL3 = 0;
            $totalL4 = 0;
            $totalR1 = 0;
            $totalR2 = 0;
            $totalR3 = 0;
            $totalR4 = 0;
            $totalC = 0;
            
            while ($row = $result->fetch_assoc()) {
                if(!in_array(substr($row['Date'], 10, 3), $dateBar)){
                    $message[] = array( 
                        'Date' => substr($row['Date'], 10, 3).":00",
                        'TotalGroundCount' => 0,
                        'InStoreGroundCount' => 0,
                        'LeftDoorCount' => 0,
                        'RightDoorCount' => 0,
                        'PassingGroundCount' => 0,
                        'TotalL1' => 0,
                        'TotalL2' => 0,
                        'TotalL3' => 0,
                        'TotalL4' => 0,
                        'TotalR1' => 0,
                        'TotalR2' => 0,
                        'TotalR3' => 0,
                        'TotalR4' => 0,
                        'TotalC' => 0
                    );

                    array_push($dateBar, substr($row['Date'], 10, 3));
                }

                $key = array_search(substr($row['Date'], 10, 3), $dateBar);

                if($row['Mode'] == 'Ground'){
                    if($row['Door'] == 'in'){
                        $groundInCount += (int)$row['Count'];
                        //$groundTotalCount += (int)$row['Count'];
                        $message[$key]['TotalGroundCount'] += (int)$row['Count'];
                        $message[$key]['InStoreGroundCount'] += (int)$row['Count'];

                        if($row['Device'] == 'L1' || $row['Device'] == 'l1'){
                            //$totalL1 += (int)$row['Count'];
                            $message[$key]['LeftDoorCount'] += (int)$row['Count'];
                        }
                        if($row['Device'] == 'R1' || $row['Device'] == 'r1'){
                            //$totalL1 += (int)$row['Count'];
                            $message[$key]['RightDoorCount'] += (int)$row['Count'];
                        }
                    }
                    else if($row['Door'] == 'passing by'){
                        $groundPassingCount += (int)$row['Count'];
                        $groundTotalCount += (int)$row['Count'];
                        $message[$key]['TotalGroundCount'] += (int)$row['Count'];
                        $message[$key]['PassingGroundCount'] += (int)$row['Count'];

                        if($row['Device'] == 'L1' || $row['Device'] == 'l1'){
                            $totalL1 += (int)$row['Count'];
                            $message[$key]['TotalL1'] += (int)$row['Count'];
                        }
                        else if($row['Device'] == 'L2' || $row['Device'] == 'l2'){
                            $totalL2 += (int)$row['Count'];
                            $message[$key]['TotalL2'] += (int)$row['Count'];
                        }
                        else if($row['Device'] == 'L3' || $row['Device'] == 'l3'){
                            $totalL3 += (int)$row['Count'];
                            $message[$key]['TotalL3'] += (int)$row['Count'];
                        }
                        else if($row['Device'] == 'L4' || $row['Device'] == 'l4'){
                            $totalL4 += (int)$row['Count'];
                            $message[$key]['TotalL4'] += (int)$row['Count'];
                        }
                        else if($row['Device'] == 'R1' || $row['Device'] == 'r1'){
                            $totalR1 += (int)$row['Count'];
                            $message[$key]['TotalR1'] += (int)$row['Count'];
                        }
                        else if($row['Device'] == 'R2' || $row['Device'] == 'r2'){
                            $totalR2 += (int)$row['Count'];
                            $message[$key]['TotalR2'] += (int)$row['Count'];
                        }
                        else if($row['Device'] == 'R3' || $row['Device'] == 'r3'){
                            $totalR3 += (int)$row['Count'];
                            $message[$key]['TotalR3'] += (int)$row['Count'];
                        }
                        else if($row['Device'] == 'R4' || $row['Device'] == 'r4'){
                            $totalR4 += (int)$row['Count'];
                            $message[$key]['TotalR4'] += (int)$row['Count'];
                        }
                        else if($row['Device'] == 'C' || $row['Device'] == 'c'){
                            $totalC += (int)$row['Count'];
                            $message[$key]['TotalC'] += (int)$row['Count'];
                        }
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
                    "totalL1" => $totalL1,
                    "totalL2" => $totalL2,
                    "totalL3" => $totalL3,
                    "totalL4" => $totalL4,
                    "totalR1" => $totalR1,
                    "totalR2" => $totalR2,
                    "totalR3" => $totalR3,
                    "totalR4" => $totalR4,
                    "totalC" => $totalC
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