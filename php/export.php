<?php

require_once 'db_connect.php';
// // Load the database configuration file 
 
// Filter the excel data 
function filterData(&$str){ 
    $str = preg_replace("/\t/", "\\t", $str); 
    $str = preg_replace("/\r?\n/", "\\n", $str); 
    if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"'; 
} 
 
// Excel file name for download 
$fileName = "Summary_report" . date('Y-m-d') . ".xls";
$output = '';
//$itemType = $_GET['itemType'];
## Search 
$searchQuery = "";
$type = "passedinlvl1daily";

if($_GET['fromDate'] != null && $_GET['fromDate'] != ''){
    $start = (string)$_GET['fromDate'];
    $searchQuery .= "Date >= '".$start."'";
}

if($_GET['toDate'] != null && $_GET['toDate'] != ''){
    $end = (string)$_GET['toDate'];
    
    if($_GET['fromDate'] != null && $_GET['fromDate'] != ''){
        $searchQuery .= " AND Date <= '".$end."'";
    }
    else{
        $searchQuery .= "Date <= '".$end."'";
    }
}

if($_GET['type'] != null && $_GET['type'] != '' && $_GET['type'] != '-'){
    $type = $_GET['type'];

    if($type == 'passedingroundmonthly' || $type == 'passedinlvl1monthly' || $type == 'passedingrounddaily' || $type =='passedinlvl1daily'){
        $fields = array('TIMESTAMP', 'PASSING BY', 'IN');
        $excelData = implode("\t", array_values($fields)) . "\n";
    }
    else if($type == 'groundlvl1monthly' || $type == 'groundlvl1daily'){
        $fields = array('TIMESTAMP', 'GROUND', 'LVL 1');
        $excelData = implode("\t", array_values($fields)) . "\n";
    }
    else if($type == 'dastotalvisitorsmonthly' || $type == 'dastotalvisitorsdaily'){
        $fields = array('TIMESTAMP', 'LEFT DOOR', 'RIGHT DOOR');
        $excelData = implode("\t", array_values($fields)) . "\n";
    }
    else if($type == 'dastotalzonevisitorsmonthly' || $type == 'dastotalzonevisitorsdaily'){
        $fields = array('TIMESTAMP', 'M1', 'M2', 'M3', 'M4', 'W1', 'W2', 'W3', 'W4', 'MVP + WVP');
        $excelData = implode("\t", array_values($fields)) . "\n";
    }
}
 
// Fetch records from database
if($type == 'dastotalvisitorsmonthly' || $type == 'dastotalvisitorsdaily' || $type == 'dastotalzonevisitorsmonthly' || $type == 'dastotalzonevisitorsdaily'){
    $query = $db->query("SELECT * FROM uniqlo_DA WHERE ".$searchQuery);
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

    if($query->num_rows > 0){ 
        while($row = $query->fetch_assoc()){
            $dateTime = "";

            if($type == 'dastotalvisitorsmonthly' || $type == 'dastotalzonevisitorsmonthly'){
                $dateTime = substr($row['Date'], 0, 10);
            }
            else if($type == 'dastotalvisitorsdaily' || $type =='dastotalzonevisitorsdaily'){
                $dateTime = substr($row['Date'], 10, 3).":00";
            }

            if(!in_array($dateTime, $dateBar)){
                $message[] = array( 
                    'Date' => $dateTime,
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

                array_push($dateBar, $dateTime);
            }

            $key = array_search($dateTime, $dateBar);

            if($row['Mode'] == 'Ground'){
                if($row['Door'] == 'in'){
                    $groundInCount += (int)$row['Count'];
                    $message[$key]['TotalGroundCount'] += (int)$row['Count'];
                    $message[$key]['InStoreGroundCount'] += (int)$row['Count'];

                    if($row['Device'] == 'L1' || $row['Device'] == 'l1'){
                        $message[$key]['LeftDoorCount'] += (int)$row['Count'];
                    }
                    if($row['Device'] == 'R1' || $row['Device'] == 'r1'){
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


        for($i=0; $i<count($message); $i++){
            if($type == 'dastotalvisitorsmonthly' || $type == 'dastotalvisitorsdaily'){
                $lineData = array($message[$i]['Date'], $message[$i]['LeftDoorCount'], $message[$i]['RightDoorCount']);
                array_walk($lineData, 'filterData'); 
                $excelData .= implode("\t", array_values($lineData)) . "\n"; 
            }
            else if($type == 'dastotalzonevisitorsmonthly' || $type == 'dastotalzonevisitorsdaily'){
                $lineData = array($message[$i]['Date'], $message[$i]['TotalL1'], $message[$i]['TotalL2'], $message[$i]['TotalL3'], $message[$i]['TotalL4'], $message[$i]['TotalR1'], $message[$i]['TotalR2'], $message[$i]['TotalR3'], $message[$i]['TotalR4'], $message[$i]['TotalC']);
                array_walk($lineData, 'filterData'); 
                $excelData .= implode("\t", array_values($lineData)) . "\n";
            }
        }
    }
    else{ 
        $excelData .= 'No records found...'. "\n"; 
    }
}
else{
    $query = $db->query("SELECT * FROM uniqlo_1u WHERE ".$searchQuery);
    $message = array();
    $dateBar = array();
    $groundTotalCount = 0;
    $groundPassingCount = 0;
    $groundInCount = 0;
    $lvl1TotalCount = 0;
    $lvl1PassingCount = 0;
    $lvl1InCount = 0;

    if($query->num_rows > 0){ 
        while($row = $query->fetch_assoc()){
            $dateTime = "";

            if($type == 'passedingroundmonthly' || $type == 'passedinlvl1monthly' || $type == 'groundlvl1monthly'){
                $dateTime = substr($row['Date'], 0, 10);
            }
            else if($type == 'passedingrounddaily' || $type =='passedinlvl1daily' || $type == 'groundlvl1daily'){
                $dateTime = substr($row['Date'], 10, 3).":00";
            }

            if(!in_array($dateTime, $dateBar)){
                $message[] = array( 
                    'Date' => $dateTime,
                    'TotalGroundCount' => 0,
                    'InStoreGroundCount' => 0,
                    'PassingGroundCount' => 0,
                    'TotalLvl1Count' => 0,
                    'InStoreLvl1Count' => 0,
                    'PassingLvl1Count' => 0
                );

                array_push($dateBar, $dateTime);
            }

            $key = array_search($dateTime, $dateBar);

            if($row['Mode'] == 'Ground'){
                if($row['Door'] == 'in'){
                    $groundInCount += (int)$row['Count'];
                    $groundTotalCount += (int)$row['Count'];
                    $message[$key]['TotalGroundCount'] += (int)$row['Count'];
                    $message[$key]['InStoreGroundCount'] += (int)$row['Count'];
                }
                else if($row['Door'] == 'passing by'){
                    $groundPassingCount += (int)$row['Count'];
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
                    $message[$key]['PassingLvl1Count'] += (int)$row['Count'];
                }
            }
        }


        for($i=0; $i<count($message); $i++){
            if($type == 'passedingroundmonthly' || $type == 'passedingrounddaily'){
                $lineData = array($message[$i]['Date'], $message[$i]['PassingGroundCount'], $message[$i]['InStoreGroundCount']);
                array_walk($lineData, 'filterData'); 
                $excelData .= implode("\t", array_values($lineData)) . "\n"; 
            }
            else if($type == 'passedinlvl1monthly' || $type == 'passedinlvl1daily'){
                $lineData = array($message[$i]['Date'], $message[$i]['PassingLvl1Count'], $message[$i]['InStoreLvl1Count']);
                array_walk($lineData, 'filterData'); 
                $excelData .= implode("\t", array_values($lineData)) . "\n";
            }
            else if($type == 'groundlvl1monthly' || $type == 'groundlvl1daily'){
                $lineData = array($message[$i]['Date'], $message[$i]['TotalGroundCount'], $message[$i]['TotalLvl1Count']);
                array_walk($lineData, 'filterData'); 
                $excelData .= implode("\t", array_values($lineData)) . "\n"; 
            }
        }
    }
    else{ 
        $excelData .= 'No records found...'. "\n"; 
    }
}


// Headers for download 
header("Content-Type: application/vnd.ms-excel"); 
header("Content-Disposition: attachment; filename=\"$fileName\""); 
 
// Render excel data 
echo $excelData;
 
exit;
?>
