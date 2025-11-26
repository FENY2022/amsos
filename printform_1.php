
<?php


            require_once 'connect.php' ;


           $id  = $_GET['id'];

            $sql = "SELECT * FROM srf WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Output data of each row
                while($row = $result->fetch_assoc()) {
                    $srfId = $row['id']; // Assuming 'id' is used here; adjust if needed
                    $ticketNumber = $row['ticketNumber'];
                    $date = $row['date'];
                    $name = $row['name'];
               
                    $idname = $row['idname'];
                    $divSecUnit = $row['divSecUnit'];
                    $office = $row['office'];
                    $position = $row['position'];
                    $contactNumber = $row['contactNumber'];
                    $email = $row['email'];
                    $requestType = $row['requestType'];
                    $otherSpecify = $row['otherSpecify'];
                    $description = $row['description'];
                    $tracking = $row['tracking'];
                    $status = $row['status'];
                    $level = $row['level'];
                    $remarks = $row['remarks'];
                    $station = $row['station'];
    

                }

            }










        
        
            // Fetch the latest feedback
// Fetch the latest feedback
$query = "SELECT feedback, date_rated FROM srffeedback ORDER BY id DESC LIMIT 1";
$result_1 = $conn->query($query);

// Initialize feedback variable
$selectedFeedback = '';

if ($result_1 && $result_1->num_rows > 0) {
    $row = $result_1->fetch_assoc();
    $selectedFeedback = $row['feedback'];
    $date_rated = $row['date_rated'];
    $formattedDate = (new DateTime($date_rated))->format('F j, Y : g:i a');

   
}



$level = 1 ;

$query = "SELECT office, station, position, name, level, signature FROM srfsigner WHERE office = ? AND station = ? AND level = ? ORDER BY id DESC LIMIT 1";
$stmt = $conn->prepare($query);

if ($stmt) {

    $stmt->bind_param("ssi", $office, $station, $level); // Assuming $office and $station are strings

    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
         $selectedOffice  = $row['office'];
         $selectedStation = $row['station'];
         $selectedName = $row['name'];
         $selectedPosition = $row['position'];
         $signature1 = $row['signature'];
    }

    $stmt->close(); 
}


$level = 2 ;

$query = "SELECT office, station, position, name, level, signature FROM srfsigner WHERE office = ? AND station = ? AND level = ? ORDER BY id DESC LIMIT 1";
$stmt = $conn->prepare($query);

if ($stmt) {

    $stmt->bind_param("ssi", $office, $station, $level); // Assuming $office and $station are strings

    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
         $selectedOffice2  = $row['office'];
         $selectedStation2 = $row['station'];
         $selectedName2 = $row['name'];
         $selectedPosition2 = $row['position'];
         $signature2 = $row['signature'];
     
    }

    $stmt->close(); 
}



$user_id = $idname;

// Define your SQL query with a WHERE clause to filter by id
$query = "SELECT * FROM useremployee WHERE id = $user_id";

// Execute the query
$result_45 = $conn->query($query);

if ($result_45) {
    // Check if a row was found
    if ($result_45->num_rows > 0) {
        // Fetch the data for the user
        $row = $result_45->fetch_assoc();
        $id = $row["id"];
         $Full_Name = $row["Full_Name"];
         $Signature_dir = $row["Signature_dir"];
        $Employment_Status = $row['Employment_Status'];
        // Process the data as needed
    } else {
        echo "No user found with ID: $user_id.";
    }
} else {
    echo "Query failed: " . $conn->error;
}

// Close the database connection

            
  
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Request Form (SRF)</title>
    <style>

        .container {
            max-width: 1000px;
            margin: 0 auto;
            border: 2px solid #000;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 2px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        input[type="text"], input[type="date"], input[type="email"], input[type="tel"] {
            width: 100%;
            padding: 2px;
            margin-top: 2px;
            margin-bottom: 2px;
        }
        .section-title {
            font-weight: bold;
            margin-top: 2px;
        }
        .signature-box {
            margin-top: 5px;
            padding: 5px;
            border: 2px solid #000;
        }

        
    </style>
</head>
<body>

<div class="container">

<table>
        <tr>
            <td rowspan="2" style="width: 20%;">
                <img src="icon/denr.jpg" alt="Logo" style="width: 30%;">
            </td>
            <td colspan="2" class="header">
                PLANNING AND MANAGEMENT DIVISION<br>
                Regional Information and Communication Technology Unit (RICTU)
            </td>
        </tr>
        <tr>
            <td colspan="2" class="header">
                SERVICE REQUEST FORM (SRF)
            </td>
        </tr>
        <tr>
            <td colspan="2" class="right-column small-text">Document ID #</td>
            <td class="small-text"><strong>R13-PMD.FO.01</strong></td>
        </tr>
        <tr>
            <td colspan="2" class="right-column small-text">Revision No.</td>
            <td class="small-text"><strong>2</strong></td>
        </tr>
        <tr>
            <td colspan="2" class="right-column small-text">Effectivity</td>
            <td class="small-text">8/25/2022</td>
        </tr>
    </table>

    <h2 class="section-title">Service Request Form (SRF)</h2>
    <p><b>Reminder:</b> Please complete this form and submit it at the RICT Unit Service Area located on the 4th floor of the LPDD/TS Building, Planning and Management Division, or email a scanned copy to [email address]. Once processed, a Technical Support Representative will contact you to schedule service.</p>

    <table>
        <tr>
            <th colspan="2">Requester's Information</th>
        </tr>
        <tr>
            <td>
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo $name; ?>" style="font-weight: bold;" required>
                </td>
            <td>
                <label for="position">Position:</label>
                <input type="text" id="position" name="position" value="<?php echo $position ;?>" required>
            </td>
        </tr>
        <tr>
            <td>
                <label for="division">Division:</label>
                <input type="text" id="division" name="division" value="<?php echo $divSecUnit ;?>" required>
            </td>
            <td>
                <label for="section">Section:</label>
                <input type="text" id="section" name="section" value="<?php echo $divSecUnit ;?>"  required>
            </td>
        </tr>
        <tr>
            <td>
                <label for="phone">Phone:</label>
                <input type="tel" id="phone" name="phone" value="<?php echo $contactNumber ;?>"  required>
            </td>
            <td>
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email"  value="<?php echo $email ;?>" required>
            </td>
        </tr>
    </table>

    <h5 class="section-title">Request Information</h5>

    <label for="ticketNo">Ticket No:</label>
    <input type="text" id="ticketNo" name="ticketNo" value="<?php echo $ticketNumber ;?>" ><br>
    <label for="date">Date:</label>

    <?php
            // Assuming $date is a valid date string in the desired format (e.g., "2024-01-29")
            $formattedDate = date("F j, Y", strtotime($date));

            echo "<input type='text' id='date' name='date' value='$formattedDate'>";
        ?>

    <h5 class="section-title">Type of Request</h5>


    <?php 

// Check for Technical Assistance
if ($requestType == 'Technical Assistance') {
    echo '<input type="checkbox" id="technical" name="requestType" value="Technical Assistance" checked>';
} else {
    echo '<input type="checkbox" id="technical" name="requestType" value="Technical Assistance">';
}
?>
<label for="technical">Technical Assistance</label>

<?php 
// Check for Asset/Borrow
if ($requestType == 'Asset/Borrow') {
    echo '<input type="checkbox" id="asset" name="requestType" value="Asset/Borrow" checked>';
} else {
    echo '<input type="checkbox" id="asset" name="requestType" value="Asset/Borrow">';
}
?>
<label for="asset">Asset/Borrow</label>

<?php 
// Check for Email
if ($requestType == 'Email') {
    echo '<input type="checkbox" id="emailReq" name="requestType" value="Email" checked>';
} else {
    echo '<input type="checkbox" id="emailReq" name="requestType" value="Email">';
}
?>
<label for="emailReq">Email</label>

<?php 
// Check for In House Software
if ($requestType == 'In House Software') {
    echo '<input type="checkbox" id="inHouseSoftware" name="requestType" value="In House Software" checked>';
} else {
    echo '<input type="checkbox" id="inHouseSoftware" name="requestType" value="In House Software">';
}
?>
<label for="inHouseSoftware">In House Software</label>

<?php 
// Check for Otos Web+
if ($requestType == 'Otos Web+') {
    echo '<input type="checkbox" id="otosWeb" name="requestType" value="Otos Web+" checked>';
} else {
    echo '<input type="checkbox" id="otosWeb" name="requestType" value="Otos Web+">';
}
?>
<label for="otosWeb">Otos Web+</label>

<?php 
// Check for E-Dats
if ($requestType == 'E-Dats') {
    echo '<input type="checkbox" id="eDats" name="requestType" value="E-Dats" checked>';
} else {
    echo '<input type="checkbox" id="eDats" name="requestType" value="E-Dats">';
}
?>

<br>
<label for="eDats">E-Dats</label>


<label for="other">Others (specify)</label><br>
<input type="text" id="other" name="other" required value="<?php echo $otherSpecify ; ?>">


    <label for="description">Description of Request:</label><br>
    <textarea id="description" name="description" rows="4" style="width:100%;" ><?php echo $description ;?></textarea>

    <h5 class="section-title">Authorization</h5>
    <p>All requests for service must be approved by the appropriate manager/supervisor (at least division chief, OIC, immediate supervisor, or next in rank staff) of the requester. By signing below, the manager/supervisor certifies that the service is required.</p>
    <label for="managerName">Full Name (Manager/Supervisor):</label>
    <input type="text" id="managerName" name="managerName" value="<?php echo $selectedName; ?>" style="font-weight: bold;" >
    <label for="managerPosition">Position/Title:</label>
    <input type="text" id="managerPosition" name="managerPosition" value="<?php echo $selectedPosition; ?>" required ">

<!--     
    <div class="signature-box">
    <img src="https://denrcaraga-infosys.online/forms/dashboard/<?php echo $_SESSION['Profile_LinkSRF']; ?>" alt="Account Picture" class="account-img">
        <p>Signature: ___________________________</p>
        <label for="managerDate">Date:</label>
        <input type="date" id="managerDate" name="managerDate">
    </div> -->
    <div class="signature-box" 
     style="position: relative; 
            padding: 20px; 
            background-color: transparent; 
            background-image: url('<?php echo $signature1; ?>'); 
            background-size: 30%; 
            background-repeat: no-repeat; 
            background-position: left; 
            border: 1px solid #000;"> <!-- Adjust color as needed -->
    <p style="margin-top: 50px;">Signature: ___________________________</p>
    <label for="managerDate" style="display: block; margin-top: 0.5em;">Date: <b><?php echo $formattedDate; ?></b></label>
    <!-- <input type="date" id="managerDate" name="managerDate" style="margin-top: 0.5em;"> -->
</div>






    <h5 class="section-title">Infrastructure Service Authorization</h5>
    <p>All requests for service must be coordinated with and signed by the Chief of RICTU or his/her authorized representative.</p>
    <label for="chiefName">Full Name (Chief of RICTU):</label>
    <input type="text" id="chiefName" name="chiefName"  value="<?php echo $selectedName2; ?>" readonly style="font-weight: bold;><br>
    <label for="chiefPosition">Title/Position:</label>
    <input type="text" id="chiefPosition" name="chiefPosition" value="<?php echo $selectedPosition2; ?>" readonly>

    <div class="signature-box" 
     style="position: relative; 
            padding: 20px; 
            background-color: transparent; 
            background-image: url('<?php echo $signature2 ; ?>'); 
            background-size: 30%; 
            background-repeat: no-repeat; 
            background-position: left; 
            border: 1px solid #000;"> <!-- Adjust color as needed -->
    <p style="margin-top: 20px;">Signature: ___________________________</p>
    <label for="managerDate" style="display: block; margin-top: 0.5em;">Date: <b><?php echo $formattedDate; ?></b></label>
    <!-- <input type="date" id="managerDate" name="managerDate" style="margin-top: 0.5em;"> -->
</div>
    <h5 class="section-title">For RICTU Staff Only</h5>



    <?php
            $trackid = $_GET['id'];

            // SQL query to fetch data based on trackid
            $query = "SELECT * FROM srf_actiontaken WHERE trackid = ?";
            $stmt = $conn->prepare($query);

            if ($stmt === false) {
                die('Prepare failed: ' . htmlspecialchars($conn->error));
            }

            $stmt->bind_param("i", $trackid); // Bind trackid as integer
            $stmt->execute();

            // Get the result
            $result = $stmt->get_result();

            if ($result === false) {
                die('Execute failed: ' . htmlspecialchars($stmt->error));
            }

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>" . htmlspecialchars($row['date']) . "</td>
                            <td>" . htmlspecialchars($row['time']) . "</td>
                            <td>" . htmlspecialchars($row['remarks']) . "</td>
                            <td>" . htmlspecialchars($row['name']) . "</td>
                        </tr>";
                }
                echo "</table>";
            } else {
                echo "No records found for Track ID: " . htmlspecialchars($trackid);
            }

            // Close the statement
            $stmt->close();
?>


<h5 class="section-title">Feedback Rating</h5>
<input type="radio" id="excellent" name="rating" value="Excellent" <?php echo ($selectedFeedback == 'Excellent') ? 'checked' : ''; ?>>
<label for="excellent">Excellent</label>

<input type="radio" id="verySatisfactory" name="rating" value="Very Satisfactory" <?php echo ($selectedFeedback == 'Very Satisfactory') ? 'checked' : ''; ?>>
<label for="verySatisfactory">Very Satisfactory</label>

<input type="radio" id="satisfactory" name="rating" value="Satisfactory" <?php echo ($selectedFeedback == 'Satisfactory') ? 'checked' : ''; ?>>
<label for="satisfactory">Satisfactory</label>

<input type="radio" id="belowSatisfactory" name="rating" value="Below Satisfactory" <?php echo ($selectedFeedback == 'Below Satisfactory') ? 'checked' : ''; ?>>
<label for="belowSatisfactory">Below Satisfactory</label>

<input type="radio" id="poor" name="rating" value="Poor" <?php echo ($selectedFeedback == 'Poor') ? 'checked' : ''; ?>>
<label for="poor">Poor</label>


<?php

?>


<div style="position: relative; width: 400px; height: 200px; margin-top: 10px;">
    <!-- Background Image -->
    <p><em>Acknowledged by:</em></p>
    <div style="position: absolute; 
            top: -20px; 
            left: -90px; /* Adjust the amount to move the background more to the left */
            width: 100%; 
            height: 100%; 
            background-image: url('https://denrcaraga-infosys.online/forms/setEmployee/<?php echo $Signature_dir; ?>'); 
            background-size: cover; 
            background-position: left top; 
            z-index: -1;">
</div>

    <br> <br> <br> 
    <!-- Text Content -->
    <label for="acknowledgedBy">Signature over printed name:</label><br>
    <label for="acknowledgedDate"><b>Date/Time:</b> <?php echo $formattedDate; ?></label><br>
  
    
</div>

<label for="acknowledgedBy"><strong><em>NIMD</em></strong><em> Request Form 22 March 2021</em></label><br>

<!-- Download as PDF Button -->
<button id="download-pdf">Download as PDF</button>

<!-- jsPDF Script -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
    document.getElementById('download-pdf').addEventListener('click', function () {
        const { jsPDF } = window.jspdf;

        // Reference to the form container
        var form = document.querySelector('.container');

        // Use html2canvas to capture the form as an image
        html2canvas(form).then(function (canvas) {
            var imgData = canvas.toDataURL('image/png'); // Convert canvas to image

            // Initialize jsPDF
            var pdf = new jsPDF('p', 'mm', 'Legal'); // 'p' for portrait, 'Legal' size

            var pageWidth = pdf.internal.pageSize.getWidth(); // Get the PDF page width
            var imgWidth = 160; // Desired image width in mm
            var imgHeight = (canvas.height * imgWidth) / canvas.width; // Maintain aspect ratio

            // Calculate the x-coordinate to center the image
            var xOffset = (pageWidth - imgWidth) / 2;

            // Define the top margin in mm
            var topMargin = 5;

            // Add image to PDF at the calculated x and y position (topMargin for y)
            pdf.addImage(imgData, 'PNG', xOffset, topMargin, imgWidth, imgHeight);

            // Save the PDF
            pdf.save('service-request-form.pdf');
        });
    });
</script>




</body>
</html>
