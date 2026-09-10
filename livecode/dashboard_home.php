<?php
// Start the session to access the logged-in user's variables
session_start();

// Redirect to login if the user is not authenticated
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.top.location.href = 'login.php';</script>";
    exit;
}

// Retrieve the Account ID and Name from the session
$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];

// 1. Include the database connection file
// Make sure this correctly points to your db.php which initializes $conn
require_once 'db.php'; 

// 2. Initialize counters to display on the dashboard
$my_documents_count = 0;
$pending_action_count = 0;
$completed_documents_count = 0;

// 3. Fetch data filtered EXCLUSIVELY by the logged-in user's Account ID ($user_id)

// Query 1: My Documents - Count documents initiated by THIS specific user account
$sql_my_docs = "SELECT COUNT(doc_id) AS total FROM documents WHERE initiator_id = ?";
if ($stmt_my_docs = $conn->prepare($sql_my_docs)) {
    $stmt_my_docs->bind_param("i", $user_id); // Bind the user's account ID
    $stmt_my_docs->execute();
    $result_my_docs = $stmt_my_docs->get_result();
    if ($row = $result_my_docs->fetch_assoc()) {
        $my_documents_count = $row['total'];
    }
    $stmt_my_docs->close();
}

// Query 2: Pending My Action - Count documents currently waiting on THIS specific user account
$sql_pending = "SELECT COUNT(doc_id) AS total FROM documents WHERE current_owner_id = ? AND status IN ('Review', 'Signing')";
if ($stmt_pending = $conn->prepare($sql_pending)) {
    $stmt_pending->bind_param("i", $user_id); // Bind the user's account ID
    $stmt_pending->execute();
    $result_pending = $stmt_pending->get_result();
    if ($row = $result_pending->fetch_assoc()) {
        $pending_action_count = $row['total'];
    }
    $stmt_pending->close();
}

// Query 3: Completed Documents - Count completed documents initiated by THIS specific user account
$sql_completed = "SELECT COUNT(doc_id) AS total FROM documents WHERE initiator_id = ? AND status = 'Completed'";
if ($stmt_completed = $conn->prepare($sql_completed)) {
    $stmt_completed->bind_param("i", $user_id); // Bind the user's account ID
    $stmt_completed->execute();
    $result_completed = $stmt_completed->get_result();
    if ($row = $result_completed->fetch_assoc()) {
        $completed_documents_count = $row['total'];
    }
    $stmt_completed->close();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Home</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
        body { 
            font-family: 'Inter', sans-serif; 
            padding: 2.5rem; 
            background-color: #f3f4f6; 
        }
    </style>
</head>
<body>
    
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Welcome back, <?php echo htmlspecialchars($full_name); ?>!</h1>
        <p class="text-gray-600 mt-2">Here's an overview of your personal document queue.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-blue-500 hover:shadow-xl transition-shadow duration-300">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">My Documents</h3>
            <p class="text-4xl font-bold text-blue-600"><?php echo htmlspecialchars($my_documents_count); ?></p>
            <p class="text-sm text-gray-500 mt-1">Total documents you've initiated.</p>
            <a href="my_submitted_documents.php" class="inline-block mt-4 text-sm font-medium text-blue-600 hover:text-blue-800">View All &rarr;</a>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-yellow-500 hover:shadow-xl transition-shadow duration-300">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Pending My Action</h3>
            <p class="text-4xl font-bold text-yellow-600"><?php echo htmlspecialchars($pending_action_count); ?></p>
            <p class="text-sm text-gray-500 mt-1">Documents waiting for your review or signature.</p>
            <a href="my_queue.php" class="inline-block mt-4 text-sm font-medium text-yellow-600 hover:text-yellow-800">View Queue &rarr;</a>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-green-500 hover:shadow-xl transition-shadow duration-300">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Completed Documents</h3>
            <p class="text-4xl font-bold text-green-600"><?php echo htmlspecialchars($completed_documents_count); ?></p>
            <p class="text-sm text-gray-500 mt-1">Documents successfully processed.</p>
            <a href="completed_docs.php" class="inline-block mt-4 text-sm font-medium text-green-600 hover:text-green-800">View Completed &rarr;</a>
        </div>
        
    </div>
</body>
</html>