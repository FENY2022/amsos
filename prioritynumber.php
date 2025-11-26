<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Priority Number</title>
    <style>
            .priority-number {
            font-size: 60px;
            color: #333;
            margin-bottom: 20px;
        }
        .refresh-button {
            font-size: 18px;
            padding: 10px 20px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .refresh-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<?php
function generatePriorityNumber() {
    // Generate a random priority number, for example between 1 and 100
    return rand(1, 100);
}

// Get the priority number
$priorityNumber = generatePriorityNumber();
?>

<div class="container">
    <div class="priority-number">
        Priority Number: <?php echo $priorityNumber; ?>
    </div>
    <button class="refresh-button" onclick="window.location.reload();">Refresh</button>
</div>

</body>
</html>
