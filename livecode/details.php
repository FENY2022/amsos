<?php
// 1. Include your database connection script
require_once 'connect.php';

// 2. Check if an ID parameter exists in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "No equipment ID provided.";
    exit;
}

// 3. Retrieve the 'id' from the URL and prepare the query
$id = intval($_GET['id']);
$query = "SELECT
            employeeName,
            equipmentType,
            yearAcquired,
            brand,
            amount,
            propertyNumber,
            id
          FROM inv_inventory
          WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

// 4. Check if a record was found
if ($result->num_rows === 0) {
    echo "No record found for the provided ID.";
    exit;
}

// 5. Fetch the record
$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Equipment Details</title>
    <link rel="shortcut icon" type="image/x-icon" href="icon/amsos.ico">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .details-container {
            max-width: 600px;
            margin: 0 auto;
        }
        .details-container h2 {
            text-align: center;
        }
        .detail-row {
            margin: 5px 0;
        }
        .detail-label {
            font-weight: bold;
        }
        .btn-back {
            display: inline-block;
            margin-top: 20px;
            padding: 8px 15px;
            background-color: #6c757d;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn-back:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>

<div class="details-container">
    <h2>Equipment Details</h2>
    <hr>

    <div class="detail-row">
        <span class="detail-label">Employee Name:</span>
        <span><?php echo htmlspecialchars($row['employeeName']); ?></span>
    </div>

    <div class="detail-row">
        <span class="detail-label">Equipment Type:</span>
        <span><?php echo htmlspecialchars($row['equipmentType']); ?></span>
    </div>

    <div class="detail-row">
        <span class="detail-label">Year Acquired:</span>
        <span><?php echo htmlspecialchars($row['yearAcquired']); ?></span>
    </div>

    <div class="detail-row">
        <span class="detail-label">Brand:</span>
        <span><?php echo htmlspecialchars($row['brand']); ?></span>
    </div>

    <div class="detail-row">
        <span class="detail-label">Amount:</span>
        <span><?php echo htmlspecialchars($row['amount']); ?></span>
    </div>

    <div class="detail-row">
        <span class="detail-label">Property Number:</span>
        <span><?php echo htmlspecialchars($row['propertyNumber']); ?></span>
    </div>

    <div class="detail-row">
        <span class="detail-label">ID:</span>
        <span><?php echo htmlspecialchars($row['id']); ?></span>
    </div>

    <!-- Example "Back" link or button -->
    <!-- <a href="javascript:history.back()" class="btn-back">Go Back</a> -->

    <div class="dropdown">
	<button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
		Action
	</button>
	<ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
		<li><a class="dropdown-item bg-warning text-white" href="#" data-bs-toggle="modal" data-bs-target="#updateborrower<?php echo $fetch['borrower_id']?>">Edit</a></li>
		<li><a class="dropdown-item bg-danger text-white" href="#" data-bs-toggle="modal" data-bs-target="#deleteborrower<?php echo $fetch['borrower_id']?>">Login</a></li>
	</ul>
</div>

</div>

</body>
</html>
