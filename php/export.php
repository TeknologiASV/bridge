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

$query = $db->query("SELECT * FROM vehicle_count WHERE ".$searchQuery);

if($query->num_rows > 0){ 
    $prevTime = null; // Initialize previous time
    $interval = 15;   // Time interval in minutes
    $dataArray = [];  // Array to store data
    $class1Total = 0;
    $class2Total = 0;
    $class3Total = 0;
    $class4Total = 0;
    $class5Total = 0;
    $bigTotal = 0;

    while ($row = $query->fetch_assoc()) {
        $dateTime = new DateTime($row['Date']); // Assuming 'Date' is the column name
        $currentTime = $dateTime->format('H:i'); // Extract HH:mm format
    
        // Calculate the interval start time
        $intervalStart = clone $dateTime;
        $intervalStart->setTime(
            $dateTime->format('H'),
            floor($dateTime->format('i') / $interval) * $interval
        );
    
        $intervalKey = $intervalStart->format('Hi') . ' - ';
        $intervalStart->add(new DateInterval("PT{$interval}M"));
        $intervalKey .= $intervalStart->format('Hi');
    
        if (!isset($dataArray[$intervalKey])) {
            $dataArray[$intervalKey] = [
                'Time' => $intervalKey,
                'Kelas 1' => 0,
                'Kelas 2' => 0,
                'Kelas 3' => 0,
                'Kelas 4' => 0,
                'Kelas 5' => 0,
                'Total' => 0
            ];
        }
    
        // Process the current row data here
        $vehicleType = $row['Vehicle_Type']; // Assuming 'Vehicle_Type' is the column name
        $dataArray[$intervalKey]['Kelas ' . $vehicleType] += (int)$row['Count'];
        $dataArray[$intervalKey]['Total'] += (int)$row['Count'];
        $bigTotal += (int)$row['Count']; 

        if ($vehicleType == 1) {
            $class1Total += (int)$row['Count'];
        } 
        elseif ($vehicleType == 2) {
            $class2Total += (int)$row['Count'];
        } 
        elseif ($vehicleType == 3) {
            $class3Total += (int)$row['Count'];
        } 
        elseif ($vehicleType == 4) {
            $class4Total += (int)$row['Count'];
        }
        elseif ($vehicleType == 5) {
            $class5Total += (int)$row['Count'];
        }
    }

    //$output = json_encode($dataArray);

    $output .= '<table class="table">
        <tbody>
            <tr>
                <td colspan="6" rowspan="7"><img src="https://www.llm.gov.my/assets/img/logo.png" width="20%" height="auto"></td>
                <td rowspan="80"></td>
                <td colspan="7" rowspan="5"></td>
            </tr>
            <tr>
                    <td colspan="6"></td>
                </tr>
                <tr>
                    <td colspan="6"></td>
                </tr>
                <tr>
                    <td colspan="6"></td>
                </tr>
                <tr>
                    <td colspan="6"></td>
                </tr>
                <tr>
                    <td colspan="3">BILANGAN LORONG</td>
                    <td colspan="4">: 4</td>
                </tr>
                <tr>
                    <td colspan="3">LEBAR LORONG</td>
                    <td colspan="4">: 3.5 M</td>
                </tr>
                <tr>
                    <td>LEBUHRAYA</td>
                    <td colspan="2">: LEBUHRAYA BARU PANTAI</td>
                    <td>DIRECTION</td>
                    <td colspan="2">: BANGSAR BOUND</td>
                    <td colspan="3">SHOULDER CLEARANCE</td>
                    <td colspan="4">: 1.0 M</td>
                </tr>
                <tr>
                    <td>STATION</td>
                    <td colspan="2">: KM1.6</td>
                    <td>FROM</td>
                    <td colspan="2">: KEWAJIPAN</td>
                    <td colspan="3">MEDIAN CLEARANCE</td>
                    <td colspan="4">: 1.0 M</td>
                </tr>
                <tr>
                    <td>DATE</td>
                    <td colspan="2">: 8 SEP 2020</td>
                    <td>TO</td>
                    <td colspan="2">: SUNWAY</td>
                    <td colspan="3">KEADAAN MUKA BUMI(TERRAIN)</td>
                    <td colspan="4">: FLAT</td>
                </tr>
                <tr>
                    <td colspan="6"></td>
                    <td colspan="3">REKABENTUK HALAJU (DESIGN SPEED)</td>
                    <td colspan="4">: 60 KM/J</td>
                </tr>
                <tr>
                    <td colspan="13"></td>
                </tr>
            <tr>
                <td style="border: 1px solid #000000;">Time</td>
                <td style="border: 1px solid #000000;">Kelas 1 <br> KERETA, VAN</td>
                <td style="border: 1px solid #000000;">Kelas 2 <br> LORI KECIL (2 gandar & 6 roda)</td>
                <td style="border: 1px solid #000000;">Kelas 3 <br> LORI BESAR (3 gandar atau lebih)</td>
                <td style="border: 1px solid #000000;">Kelas 4 <br> TEKSI, LIMOSIN</td>
                <td style="border: 1px solid #000000;">Kelas 5 <br> BAS</td>
                <td style="border: 1px solid #000000;">Time</td>
                <td style="border: 1px solid #000000;">Kelas 1 <br> KERETA, VAN</td>
                <td style="border: 1px solid #000000;">Kelas 2 <br> LORI KECIL (2 gandar & 6 roda)</td>
                <td style="border: 1px solid #000000;">Kelas 3 <br> LORI BESAR (3 gandar atau lebih)</td>
                <td style="border: 1px solid #000000;">Kelas 4 <br> TEKSI, LIMOSIN</td>
                <td style="border: 1px solid #000000;">Kelas 5 <br> BAS</td>
                <td style="border: 1px solid #000000;">Total Vehicle</td>
                <td style="border: 1px solid #000000;">LOS</td>
            </tr>';

foreach ($dataArray as $intervalData) {
    $output .=  '<tr>';
    $output .=  "<td style='border: 1px solid #000000;'>{$intervalData['Time']}</td>";
    $output .=  "<td style='border: 1px solid #000000;'>{$intervalData['Kelas 1']}</td>";
    $output .=  "<td style='border: 1px solid #000000;'>{$intervalData['Kelas 2']}</td>";
    $output .=  "<td style='border: 1px solid #000000;'>{$intervalData['Kelas 3']}</td>";
    $output .=  "<td style='border: 1px solid #000000;'>{$intervalData['Kelas 4']}</td>";
    $output .=  "<td style='border: 1px solid #000000;'>{$intervalData['Kelas 5']}</td>";
    $output .=  "<td style='border: 1px solid #000000;'>{$intervalData['Time']}</td>";
    $output .=  "<td style='border: 1px solid #000000;'>{$intervalData['Kelas 1']}</td>";
    $output .=  "<td style='border: 1px solid #000000;'>{$intervalData['Kelas 2']}</td>";
    $output .=  "<td style='border: 1px solid #000000;'>{$intervalData['Kelas 3']}</td>";
    $output .=  "<td style='border: 1px solid #000000;'>{$intervalData['Kelas 4']}</td>";
    $output .=  "<td style='border: 1px solid #000000;'>{$intervalData['Kelas 5']}</td>";
    $output .=  "<td style='border: 1px solid #000000;'>{$intervalData['Total']}</td>";
    $output .=  '<td style="border: 1px solid #000000;"></td>';
    $output .=  '</tr>';
}

$output .=  '</tbody><tfoot>';
$output .=  '<tr>';
$output .=  "<td colspan='6'></td>";
$output .=  "<td style='border: 1px solid #000000;'>Total</td>";
$output .=  "<td style='border: 1px solid #000000;'>{$class1Total}</td>";
$output .=  "<td style='border: 1px solid #000000;'>{$class2Total}</td>";
$output .=  "<td style='border: 1px solid #000000;'>{$class3Total}</td>";
$output .=  "<td style='border: 1px solid #000000;'>{$class4Total}</td>";
$output .=  "<td style='border: 1px solid #000000;'>{$class5Total}</td>";
$output .=  "<td style='border: 1px solid #000000;'>{$bigTotal}</td>";
$output .=  '<td style="border: 1px solid #000000;"></td>';
$output .=  '</tr>';
$output .=  '<tr>';
$output .=  "<td colspan='6'></td>";
$output .=  "<td style='border: 1px solid #000000;'>Composition (%)</td>";
$output .=  "<td style='border: 1px solid #000000;'>".round($class1Total/$bigTotal * 100, 2)." %</td>";
$output .=  "<td style='border: 1px solid #000000;'>".round($class2Total/$bigTotal * 100, 2)." %</td>";
$output .=  "<td style='border: 1px solid #000000;'>".round($class3Total/$bigTotal * 100, 2)." %</td>";
$output .=  "<td style='border: 1px solid #000000;'>".round($class4Total/$bigTotal * 100, 2)." %</td>";
$output .=  "<td style='border: 1px solid #000000;'>".round($class5Total/$bigTotal * 100, 2)." %</td>";
$output .=  "<td style='border: 1px solid #000000;'>100 %</td>";
$output .=  '<td style="border: 1px solid #000000;"></td>';
$output .=  '</tr>';
$output .=  '</tfoot></table>';
}
else{ 
    $output .= 'No records found...'. "\n"; 
}


// Headers for download 
header("Content-Type: application/vnd.ms-excel; charset=utf-8"); 
header("Content-Disposition: attachment; filename=\"$fileName\"");

// Render excel data 
// $str = utf8_decode($excelData);
echo $output; 
exit;
?>
