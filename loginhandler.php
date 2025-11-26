<?php
session_start();
require 'connect.php';
require 'connect_otos.php';


error_reporting(E_ALL);
ini_set('display_errors', 1);

// Security headers
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; object-src 'none'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; frame-ancestors 'none';");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
header("Referrer-Policy: no-referrer");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in both fields.']);
        exit;
    } else {
        // Create a prepared statement
        if ($stmt = $conn_otos->prepare('SELECT id, Full_Name, Office, Station, Profile_Link, User_Role, username, password FROM useremployee WHERE username = ?')) {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            // Verify user and password
            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION["loggedin"] = $user['username'];
                $_SESSION['idSRF'] = $user['id'];
                $_SESSION['usernameSRF'] = $user['username'];
                $_SESSION['Full_NameSRF'] = $user['Full_Name'];
                $_SESSION['OfficeSRF'] = $user['Office'];
                $_SESSION['StationSRF'] = $user['Station'];
                $_SESSION['Profile_LinkSRF'] = $user['Profile_Link'];
                $_SESSION['User_RoleSRF'] = $user['User_Role'];
                $Station = $user['Station'];

                $Endsrd = "";
                $sql = "SELECT * FROM signatory_setup WHERE Station = ? and date_endservice = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $Station, $Endsrd);
                $stmt->execute();
                $result = $stmt->get_result();
                $results = $result->fetch_all(MYSQLI_ASSOC);
                if ($results) {
                    foreach ($results as $row) {
                        $_SESSION['StationidSRF'] = htmlspecialchars($row['id']);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'No records found for the station.']);
                    exit;
                }
                $stmt->close();

                echo json_encode(['success' => true, 'message' => 'Login successful. Redirecting...']);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
                exit;
            }

            // Close the statement
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Database query error.']);
            exit;
        }
    }
}else{

    echo '<script>window.location.href = "login.php";</script>';
    exit();

}
?>