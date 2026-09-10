<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Specs Suggestion Chatbot</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .chat-container {
            width: 400px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .chat-window {
            height: 400px;
            padding: 20px;
            border-bottom: 1px solid #ccc;
            overflow-y: auto;
        }

        .chat-message {
            margin-bottom: 10px;
        }

        .chat-message.bot p {
            background-color: #e1ffc7;
            padding: 10px;
            border-radius: 8px;
            display: inline-block;
            max-width: 70%;
            word-wrap: break-word;
        }

        .chat-message.user p {
            background-color: #dcf8c6;
            padding: 10px;
            border-radius: 8px;
            display: inline-block;
            max-width: 70%;
            word-wrap: break-word;
            margin-left: auto;
        }

        .chat-input {
            display: flex;
            padding: 10px;
        }

        .chat-input input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-right: 10px;
        }

        .chat-input button {
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .chat-input button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-window" id="chatWindow">
            <div class="chat-message bot">
                <p>Hello! I can help you suggest specifications for your equipment. Please type in the format: <b>equipment amount</b> (e.g., "desktop 30000").</p>
            </div>
        </div>
        <div class="chat-input">
            <input type="text" id="userInput" placeholder="e.g., desktop 30000">
            <button onclick="sendMessage()">Send</button>
        </div>
    </div>
    <script src="chatboot.js"></script>
</body>
</html>
