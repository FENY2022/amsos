<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Priority Number</title>
    <style>
        
        .priority-number {
            font-size: 120px;
            font-weight: bold;
            letter-spacing: 20px;
            margin-bottom: 20px;
            text-shadow: 0 0 10px #ff0000, 0 0 20px #ff0000, 0 0 30px #ff0000, 0 0 40px #ff0000;
        }
        .refresh-button {
            font-size: 24px;
            padding: 10px 30px;
            color: #ff0000;
            background-color: #222;
            border: 2px solid #ff0000;
            border-radius: 5px;
            cursor: pointer;
        }
        .refresh-button:hover {
            background-color: #333;
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
        <?php echo $priorityNumber; ?>
    </div>
    <button class="refresh-button" onclick="window.location.reload();">Refresh</button>
</div>

</body>
</html>
