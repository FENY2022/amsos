<?php


    define('USER', 'root');
    define('PASSWORD', '');
    define('HOST', 'localhost');
    define('DATABASE', 'ict_inventory');

    // Create a new database connection
    $conn = mysqli_connect(HOST, USER, PASSWORD, DATABASE);
    
    // Check the database connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
    // Uncomment the following line if you want to see a success message
    // echo "Connected successfully.";
    
    // Close the database connection when you're done with it
    // mysqli_close($conn);
?>
