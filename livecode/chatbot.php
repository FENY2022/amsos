<?php
// --- SESSION MANAGEMENT & CONVERSATION RESET ---
// Start a session to store the conversation history server-side.
session_start();

// Add a simple way to reset the conversation by visiting your_page.php?reset=true
if (isset($_GET['reset']) && $_GET['reset'] === 'true') {
    unset($_SESSION['gemini_conversation_history']);
    // Redirect to the same page without the query parameter to avoid accidental resets on refresh.
    header('Location: ' . strtok($_SERVER["REQUEST_URI"], '?'));
    exit();
}


// --- BACKEND LOGIC ---
// This block of PHP code will only run when it receives a POST request from the JavaScript below.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Set the content type to JSON for the response
    header('Content-Type: application/json');

    // --- 1. CONNECT TO DATABASE ---
    require 'connect.php'; // Include your DB connection file

    // --- 2. FETCH AND FORMAT INVENTORY DATA ---
    // NOTE: For efficiency in a long conversation, you could consider fetching this once
    // and storing it in the session as well, refreshing it periodically.
    // For simplicity, we will fetch it on each request as in your original code.
    $inventoryData = "";
    // MODIFIED: Added 'serialNumber' to the SQL query
    $sql = "SELECT id, equipmentType, brand, serialNumber, specifications, yearAcquired, accountablePerson, actualUser, officeDivision, remarks FROM inv_inventory WHERE id > 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $inventoryData .= "This is the current inventory data:\n";
        while($row = $result->fetch_assoc()) {
            // MODIFIED: Added the 'serialNumber' to the formatted string
            $inventoryData .= "- Item ID: {$row['id']}, Type: {$row['equipmentType']}, Brand: {$row['brand']}, Serial Number: {$row['serialNumber']}, Specs: {$row['specifications']}, Year Acquired: {$row['yearAcquired']}, Accountable Person: {$row['accountablePerson']}, Actual User: {$row['actualUser']}, Division: {$row['officeDivision']}, Remarks: {$row['remarks']}\n";
        }
    } else {
        $inventoryData = "No inventory data is available in the database.";
    }
    $conn->close();

    // --- 3. PREPARE AND MAKE GEMINI API CALL ---
    $input = json_decode(file_get_contents('php://input'), true);
    $userQuery = $input['query'] ?? 'Tell me about the inventory.';

    // IMPORTANT: Keep your API key secure. Use environment variables instead of hardcoding.
    $apiKey = "AIzaSyCjQTWCw-mPpAh1LMcEw0xTWjzBWnVPyUs"; // Replace with your actual API key
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-lite:generateContent?key=" . $apiKey;


    // --- CONVERSATION HISTORY LOGIC ---
    // Initialize session history if it doesn't exist.
    if (!isset($_SESSION['gemini_conversation_history'])) {
        $_SESSION['gemini_conversation_history'] = [];
    }

    // Build the 'contents' payload for the Gemini API.
    $contents = [];

    // If the conversation is new, inject the system instructions and inventory data.
    if (empty($_SESSION['gemini_conversation_history'])) {
        $system_prompt = "You are an expert inventory assistant for an office in DENR Caraga Regional Office. Your task is to answer questions based *only* on the inventory data provided below. Be concise and helpful. If the information is not in the data, state that you cannot find the information in the inventory records.\n\n" .
                         "====================\n" .
                         "INVENTORY DATA:\n" . $inventoryData . "\n" .
                         "====================\n\n";
        // Combine the system prompt with the very first user question.
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $system_prompt . "Here is my first question: " . $userQuery]]
        ];
    } else {
        // If history exists, use it as the base.
        $contents = $_SESSION['gemini_conversation_history'];
        // Add the new user query to the end of the history.
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $userQuery]]
        ];
    }
    
    // Prepare the final data payload for the Gemini API
    $data = [
        'contents' => $contents, // Use the new multi-turn contents array
        'safetySettings' => [
            ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_NONE'],
            ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_NONE'],
            ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_NONE'],
            ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_NONE'],
        ]
    ];

    // Configure the HTTP request using cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    // --- 4. PROCESS THE API RESPONSE ---
    if ($curl_error) {
        echo json_encode(['error' => 'cURL Error: ' . $curl_error]);
        exit();
    }
    
    if ($response === false) {
        echo json_encode(['error' => 'Failed to communicate with the Gemini API. HTTP Code: ' . $httpcode]);
        exit();
    }

    $resultData = json_decode($response, true);

    if (isset($resultData['error'])) {
        echo json_encode(['error' => 'API Error: ' . $resultData['error']['message']]);
        exit();
    }

    $reply = $resultData['candidates'][0]['content']['parts'][0]['text'] ?? 'Sorry, I could not process the API response.';

    // --- UPDATE SESSION HISTORY ---
    // Save the conversation turn (user query + model reply) to the session.
    $current_turn_user = end($contents); // Get the last user part that was sent
    $_SESSION['gemini_conversation_history'][] = $current_turn_user;
    $_SESSION['gemini_conversation_history'][] = [
        'role' => 'model',
        'parts' => [['text' => $reply]]
    ];

    echo json_encode(['reply' => trim($reply)]);
    
    exit();
}

// --- FRONTEND LOGIC (HTML, CSS & JAVASCRIPT) ---
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Assistant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #d6e8f0 0%, #f1f8fb 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .chat-container {
            width: 100%;
            max-width: 800px;
            height: 90vh;
            max-height: 800px;
            background-color: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .chat-header {
            background: linear-gradient(to right, #07C160, #05AE54);
            color: white;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 10;
            flex-shrink: 0;
        }
        
        .chat-header-left {
            display: flex;
            align-items: center;
        }
        
        .back-button {
            margin-right: 15px;
            cursor: pointer;
        }
        
        .chat-title {
            font-size: 18px;
            font-weight: 600;
        }
        
        .header-icons {
            display: flex;
            gap: 15px;
        }
        
        .header-icons i {
            cursor: pointer;
            font-size: 18px;
        }
        
        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background-color: #f5f5f5;
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%2307c160' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E");
        }
        
        .message {
            max-width: 85%;
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
            animation: fadeIn 0.3s ease-out;
        }
        
        .message-content {
            padding: 12px 16px;
            border-radius: 18px;
            font-size: 16px;
            line-height: 1.4;
            position: relative;
            word-wrap: break-word; /* Ensure long words don't overflow */
        }
        
        .bot-message {
            align-self: flex-start;
        }
        
        .bot-message .message-content {
            background-color: #fff;
            color: #000;
            border-top-left-radius: 4px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }
        
        .user-message {
            align-self: flex-end;
        }
        
        .user-message .message-content {
            background: linear-gradient(to right, #95EC69, #07C160);
            color: white;
            border-top-right-radius: 4px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }
        
        .message-time {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
            padding: 0 5px;
        }
        
        .bot-message .message-time {
            text-align: left;
        }
        
        .user-message .message-time {
            text-align: right;
        }
        
        .chat-input-container {
            padding: 15px;
            background-color: #f5f5f5;
            border-top: 1px solid #e5e5e5;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }
        
        .input-action {
            width: 40px;
            height: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #7d7d7d;
            font-size: 20px;
            cursor: pointer;
            flex-shrink: 0;
        }
        
        .chat-input {
            flex: 1;
            background-color: #fff;
            border-radius: 24px;
            padding: 10px 18px;
            border: 1px solid #e5e5e5;
            font-size: 16px;
            outline: none;
            transition: border-color 0.2s;
        }
        
        .chat-input:focus {
            border-color: #07C160;
        }
        
        .send-button {
            width: 40px;
            height: 40px;
            background: linear-gradient(to right, #95EC69, #07C160);
            color: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            border: none;
            outline: none;
            transition: transform 0.2s;
            flex-shrink: 0;
        }
        
        .send-button:active {
            transform: scale(0.95);
        }
        
        .welcome-container {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .welcome-title {
            color: #07C160;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .welcome-text {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Scrollbar styling */
        .chat-messages::-webkit-scrollbar {
            width: 8px;
        }
        
        .chat-messages::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .chat-messages::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        
        .chat-messages::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        
        /* Loading dots animation */
        .typing-indicator-content {
            display: inline-flex;
            align-items: center;
            padding: 12px 16px;
            background-color: #fff;
            border-radius: 18px;
            border-top-left-radius: 4px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }
        
        .typing-dot {
            width: 8px;
            height: 8px;
            background-color: #999;
            border-radius: 50%;
            margin: 0 2px;
            animation: typingAnimation 1.4s infinite ease-in-out;
        }
        
        .typing-dot:nth-child(1) { animation-delay: 0s; }
        .typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dot:nth-child(3) { animation-delay: 0.4s; }
        
        @keyframes typingAnimation {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-5px); }
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            body { padding: 0; }
            .chat-container {
                height: 100vh;
                max-height: none;
                border-radius: 0;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="chat-container">
    <div class="chat-messages" id="chat-box">
        <div class="welcome-container">
            <div class="welcome-title">DENR Caraga Inventory Assistant</div>
            <div class="welcome-text">Ask me about equipment, specifications, accountable persons, or any other inventory information.</div>
        </div>
        
        <div class="message bot-message">
            <div class="message-content">Hello! I'm your inventory assistant powered by AMSOS. How can I help you today?</div>
            <div class="message-time"><?php echo date('H:i'); ?></div>
        </div>
    </div>
    
    <div class="chat-input-container">
        <div class="input-action"><i class="fas fa-plus-circle"></i></div>
        <input type="text" class="chat-input" id="user-input" placeholder="Type a message...">
        <div class="input-action"><i class="fas fa-smile"></i></div>
        <button class="send-button" id="send-btn">
            <i class="fas fa-paper-plane"></i>
        </button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const sendBtn = document.getElementById('send-btn');
    const userInput = document.getElementById('user-input');
    const chatBox = document.getElementById('chat-box');

    // Function to get current time in HH:MM format from the client's browser
    const getCurrentTime = () => {
        const now = new Date();
        const hours = now.getHours().toString().padStart(2, '0');
        const minutes = now.getMinutes().toString().padStart(2, '0');
        return `${hours}:${minutes}`;
    };

    // Function to append a complete message (user or bot) to the chat box
    const appendMessage = (text, type) => {
        const messageDiv = document.createElement('div');
        messageDiv.classList.add('message', type); 
        
        const messageContent = document.createElement('div');
        messageContent.classList.add('message-content');
        messageContent.textContent = text;
        
        const messageTime = document.createElement('div');
        messageTime.classList.add('message-time');
        messageTime.textContent = getCurrentTime();
        
        messageDiv.appendChild(messageContent);
        messageDiv.appendChild(messageTime);
        chatBox.appendChild(messageDiv);
        
        chatBox.scrollTop = chatBox.scrollHeight;
        return messageDiv;
    };

    // NEW: Function to render a table from data
    const renderTable = (data, type) => {
        const messageDiv = document.createElement('div');
        messageDiv.classList.add('message', type);
        
        const messageContent = document.createElement('div');
        messageContent.classList.add('message-content');

        // Create the table element
        const table = document.createElement('table');
        table.style.width = '100%';
        table.style.borderCollapse = 'collapse';
        table.innerHTML = `
            <thead>
                <tr>
                    <th style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;">ID</th>
                    <th style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;">Equipment Type</th>
                    <th style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;">Brand</th>
                    <th style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;">Accountable Person</th>
                </tr>
            </thead>
            <tbody>
                ${data.map(item => `
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px;">${item.id}</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">${item.equipmentType}</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">${item.brand}</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">${item.accountablePerson}</td>
                    </tr>
                `).join('')}
            </tbody>
        `;
        
        messageContent.appendChild(table);
        messageDiv.appendChild(messageContent);

        const messageTime = document.createElement('div');
        messageTime.classList.add('message-time');
        messageTime.textContent = getCurrentTime();
        messageDiv.appendChild(messageTime);

        chatBox.appendChild(messageDiv);
        chatBox.scrollTop = chatBox.scrollHeight;
    };

    // Function to show the typing indicator
    const showTypingIndicator = () => {
        const typingDiv = document.createElement('div');
        typingDiv.classList.add('message', 'bot-message');
        typingDiv.id = 'typing-indicator';
        
        const typingContent = document.createElement('div');
        typingContent.classList.add('typing-indicator-content');
        
        for (let i = 0; i < 3; i++) {
            const dot = document.createElement('div');
            dot.classList.add('typing-dot');
            typingContent.appendChild(dot);
        }
        
        typingDiv.appendChild(typingContent);
        chatBox.appendChild(typingDiv);
        chatBox.scrollTop = chatBox.scrollHeight;
    };

    // Function to remove the typing indicator
    const removeTypingIndicator = () => {
        const typingIndicator = document.getElementById('typing-indicator');
        if (typingIndicator) {
            chatBox.removeChild(typingIndicator);
        }
    };

    // Main function to handle sending a message
    const sendMessage = async () => {
        const query = userInput.value.trim();
        if (query === '') return;

        appendMessage(query, 'user-message');
        userInput.value = '';
        userInput.focus();  
        showTypingIndicator();

        try {
            const response = await fetch('', { 
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ query: query }),
            });

            if (!response.ok) {
                throw new Error(`Server error: ${response.status} ${response.statusText}`);
            }

            const data = await response.json();
            removeTypingIndicator();
            
            // Check for the new 'isTable' flag
            if (data.isTable && data.data) {
                renderTable(data.data, 'bot-message');
            } else if (data.reply) {
                appendMessage(data.reply, 'bot-message');
            } else if (data.error) {
                appendMessage(`Error: ${data.error}`, 'bot-message');
            } else {
                appendMessage("Sorry, I received an empty response.", 'bot-message');
            }
            
        } catch (error) {
            console.error('Fetch Error:', error);
            removeTypingIndicator();
            appendMessage('An error occurred. Please check the console for details and try again.', 'bot-message');
        }
    };

    sendBtn.addEventListener('click', sendMessage);
    userInput.addEventListener('keypress', (event) => {
        if (event.key === 'Enter') {
            sendMessage();
        }
    });
});
</script>

</body>
</html>