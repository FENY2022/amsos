<?php

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Include database connection
    require_once 'connect.php'; // Replace with your actual connection file
 

  

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['approve'])) {
    $srfId = intval($_GET['approve']); // Sanitize input
    $trackid = intval($_GET['approve']);
    $name = $_SESSION['Full_NameSRF']; // Sanitize input
    $equipment_id = intval($_GET['equipment_id']);


    $NID =  $_GET['level'] + 1 ;
             $stationID = $_SESSION['StationidSRF'];
  
             // Prepare the query
             $sql = "SELECT * FROM srfsigner WHERE stationid = ? AND level = ?";
             // Prepare the statement
             $stmt = $conn->prepare($sql);
             // Bind the parameter
             $stmt->bind_param("si", $stationID, $NID);
             // Execute the query
             $stmt->execute();
             // Get the result
             $result = $stmt->get_result();
             // Fetch the results
             $results = $result->fetch_all(MYSQLI_ASSOC);
             // Display the filtered results
             if ($results) {
                foreach ($results as $row) {
                    $tracking = htmlspecialchars($row['personelid']);  // Store in session without echo

                  
                }
            } elseif (empty($results)) {  // Check if $results is empty
                $tracking = $_SESSION['idSRF'];
                $NID = 101 ;
            } else {
      
            }

             // Close the statement and connection
             $stmt->close();
             
             
                // $email = "mlchua@denr.gov.ph";
                // // $email = "venzonanthonie@gmail.com";

                // $subject = "ICTAMSOS - " . date("Y-m-d H:i:s");
                // $message_2 = "Please Check ICTAMSOS Login to your account";
                // $yourname = "ICTAMSOS-ALERT";
             
                //              $url = 'https://o-ldpms.denr.gov.ph/sendemail/send.php?send=1&email=' . urlencode($email) . '&Subject=' . urlencode($subject) . '&message=' . urlencode($message_2) . '&yourname=' . urlencode($yourname);
                
                // // Initialize cURL
                // $ch = curl_init($url);
                // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                
                // // Execute the request
                // $response = curl_exec($ch);
                
                // // Check for errors
                // if (curl_errno($ch)) {
                //     echo 'cURL error: ' . curl_error($ch);
                // } else {
                //     echo 'Response from server: ' . $response;
                // }
                
                // // Close cURL session
                // curl_close($ch);
                
   



if ($NID == 2) {
    $status = "Forwarded to ICT/RICTU";
    $details = "Approved By: " .  $_SESSION['Full_NameSRF'] . "";


} else {

$status = "Received by Chief RICTU";
$details = "Received By: " .  $_SESSION['Full_NameSRF'] . "";
}


    $date = date("Y-m-d");
    $time = date("h:i:s A");

        $equipment_id = intval($_GET['equipment_id']);

        $office = $_SESSION['OfficeSRF'];
        $stmth = $conn->prepare("INSERT INTO srfhistory (trackid, name, details, date, time, status, equipment_id, office) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmth->bind_param("isssssis", $srfId, $name, $details, $date, $time, $status, $equipment_id, $office);
        $stmth->execute();
        $stmth->close();

            $div_section_unit = $_SESSION['StationSRF'];
            $station = $_SESSION['StationSRF'];
            $requestType = $_GET['requestType'];
            $description = $_GET['description'];
            $username =  $_GET['name'];
            $user =  $_SESSION['idSRF'];
            
            $stmthi = $conn->prepare("INSERT INTO srfaction (trackid, name, div_section_unit, requestType, description, station, date, time, status, user) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmthi->bind_param("isssssssss", $srfId, $username, $div_section_unit, $requestType, $description, $station, $date, $time, $status, $user);
            $stmthi->execute();
            $stmthi->close();
            
            

    


 
    $stmt = $conn->prepare("UPDATE srf SET tracking = ?, status = ?, level = ? WHERE id = ?");
    $stmt->bind_param("isii", $tracking, $status, $NID, $srfId,  );

    if ($stmt->execute()) {


        $stmt = $conn->prepare("UPDATE srf SET step_counter = step_counter + 1 WHERE id = ?");
        $stmt->bind_param("i", $srfId); 

        if ($stmt->execute()) {

        } else {

        }
        
        $stmt->close();

        echo '<script>alert("Record Successfully Approved."); window.location.href = "mainmenu.php?dir=requestlist";</script>';
        exit();
    } else {
        // Failure: redirect to the request list page with error status
        echo '<script>alert("Error Approving Record."); window.location.href = "mainmenu.php?dir=requestlist";</script>';
        exit();
    }

    // Close the statement
    $stmt->close();


}

// Close the database connection
$conn->close();

?>
