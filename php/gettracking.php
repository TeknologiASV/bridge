<?php
require_once "db_connect.php";

session_start();

if(isset($_POST['startDate'], $_POST['endDate'], $_POST['harvesterId'])){
    $startDate = filter_input(INPUT_POST, 'startDate', FILTER_SANITIZE_STRING);
    $endDate = filter_input(INPUT_POST, 'endDate', FILTER_SANITIZE_STRING);
    $harvesterId = filter_input(INPUT_POST, 'harvesterId', FILTER_SANITIZE_STRING);

    if ($select_stmt = $db->prepare("SELECT * FROM felda_table1 WHERE Rec_time>=? AND Rec_time<=? AND ID=? ORDER BY Rec_time")) {
        $select_stmt->bind_param('sss', $startDate, $endDate, $harvesterId);
        
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
            $current = "";
            $distance = 0;
            
            while ($row = $result->fetch_assoc()) {
                if(!in_array(substr($row['Rec_time'], 0, 10), $dateBar)){
                    $message[] = array( 
                        'Date' => substr($row['Rec_time'], 0, 10),
                        'Path' => array()
                    );

                    array_push($dateBar, substr($row['Rec_time'], 0, 10));
                    $current = "";
                }

                $key = array_search(substr($row['Rec_time'], 0, 10), $dateBar);

                if(count($message[$key]['Path']) <= 0){
                    if(substr($row['Rec_time'], 11, 2) >= "06"){
                        array_push($message[$key]['Path'], array( 
                            'Date' => substr($row['Rec_time'], 11, 8),
                            'Location' => $row['Node_name'],
                            'Distance' => $row['R_m']
                        ));
    
                        $current = $row['Node_name'];
                        $distance = (int)$row['R_m'];
                    }
                }
                else{
                    if($row['Node_name'] != $current){
                        if(substr($row['Rec_time'], 11, 2) >= "06"){
                            if($distance >= (int)$row['R_m']){
                                array_push($message[$key]['Path'], array( 
                                    'Date' => substr($row['Rec_time'], 11, 8),
                                    'Location' => $row['Node_name'],
                                    'Distance' => $row['R_m']
                                ));

                                $current = $row['Node_name'];
                                $distance = (int)$row['R_m'];
                            }
                        }
                    }
                    else{
                        if($distance >= (int)$row['R_m']){
                            $distance = (int)$row['R_m'];
                        }
                    }
                }
            }
            
            echo json_encode(
                array(
                    "status" => "success",
                    "message" => $message
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