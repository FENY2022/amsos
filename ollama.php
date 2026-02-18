<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DeepSeek R1 Chatbot</title>
    <style>
        /* --- CSS STYLES --- */
        :root {
            --bg-color: #f4f4f9;
            --chat-bg: #ffffff;
            --user-msg-bg: #007bff;
            --user-text: #ffffff;
            --bot-msg-bg: #e9ecef;
            --bot-text: #333333;
            --input-area-bg: #ffffff;
            --border-color: #ddd;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-color);
            margin: 0;
            display: flex;
            justify-content: center;
            height: 100vh;
        }

        .chat-container {
            width: 100%;
            max-width: 800px;
            background-color: var(--chat-bg);
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            height: 100%;
        }

        header {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        header h1 {
            margin: 0;
            font-size: 1.2rem;
            color: #333;
        }

        .status-indicator {
            font-size: 0.8rem;
            color: #28a745;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background-color: #28a745;
            border-radius: 50%;
        }

        #chat-history {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 15px;
            scroll-behavior: smooth;
        }

        .message {
            max-width: 80%;
            padding: 12px 16px;
            border-radius: 12px;
            line-height: 1.5;
            position: relative;
            word-wrap: break-word;
        }

        .message.user {
            align-self: flex-end;
            background-color: var(--user-msg-bg);
            color: var(--user-text);
            border-bottom-right-radius: 2px;
        }

        .message.bot {
            align-self: flex-start;
            background-color: var(--bot-msg-bg);
            color: var(--bot-text);
            border-bottom-left-radius: 2px;
        }

        .message.error {
            align-self: center;
            background-color: #ffdddd;
            color: #a00;
            font-size: 0.9rem;
        }

        /* Markdown-like simple styling for bot responses */
        .message.bot pre {
            background: #dcdcdc;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
        
        /* Thinking process styling for DeepSeek R1 */
        .thinking {
            font-style: italic;
            color: #666;
            font-size: 0.9em;
            border-left: 3px solid #ccc;
            padding-left: 10px;
            margin-bottom: 10px;
            display: block;
        }

        .input-area {
            padding: 20px;
            border-top: 1px solid var(--border-color);
            background-color: var(--input-area-bg);
            display: flex;
            gap: 10px;
        }

        textarea {
            flex: 1;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            resize: none;
            font-family: inherit;
            height: 50px;
            outline: none;
        }

        textarea:focus {
            border-color: var(--user-msg-bg);
        }

        button {
            padding: 0 25px;
            background-color: var(--user-msg-bg);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.2s;
        }

        button:hover {
            background-color: #0056b3;
        }

        button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
    </style>
</head>
<body>

    <div class="chat-container">
        <header>
            <h1>DeepSeek R1:8b</h1>
            <div class="status-indicator">
                <div class="status-dot"></div> Online
            </div>
        </header>
        
        <div id="chat-history">
            <div class="message bot">
                Hello! I am connected to your local DeepSeek R1 model. How can I help you today?
            </div>
        </div>

        <div class="input-area">
            <textarea id="user-input" placeholder="Type your message here..."></textarea>
            <button id="send-btn">Send</button>
        </div>
    </div>

    <script>
        /* --- JAVASCRIPT LOGIC --- */
        const chatHistory = document.getElementById('chat-history');
        const userInput = document.getElementById('user-input');
        const sendBtn = document.getElementById('send-btn');
        
        // Configuration: Pointing to your local Ollama instance
        const OLLAMA_API_URL = 'http://localhost:11434/api/generate';
        const MODEL_NAME = 'deepseek-r1:8b'; 

        // Auto-resize textarea
        userInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
            if(this.value === '') this.style.height = '50px';
        });

        // Handle Send
        async function sendMessage() {
            const text = userInput.value.trim();
            if (!text) return;

            // 1. Add User Message to UI
            addMessage(text, 'user');
            userInput.value = '';
            userInput.style.height = '50px';
            sendBtn.disabled = true;

            // 2. Prepare Bot Message Container (streaming support)
            const botMessageDiv = document.createElement('div');
            botMessageDiv.className = 'message bot';
            botMessageDiv.textContent = 'Thinking...';
            chatHistory.appendChild(botMessageDiv);
            scrollToBottom();

            let fullResponse = "";
            let isThinking = false;

            try {
                const response = await fetch(OLLAMA_API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        model: MODEL_NAME,
                        prompt: text,
                        stream: true // Enable streaming
                    })
                });

                if (!response.ok) throw new Error('Failed to connect to Ollama');

                const reader = response.body.getReader();
                const decoder = new TextDecoder();
                
                // Clear "Thinking..." text before streaming starts
                botMessageDiv.textContent = ''; 

                while (true) {
                    const { done, value } = await reader.read();
                    if (done) break;

                    const chunk = decoder.decode(value, { stream: true });
                    // Ollama sends multiple JSON objects in one chunk sometimes
                    const lines = chunk.split('\n');
                    
                    for (const line of lines) {
                        if (!line.trim()) continue;
                        try {
                            const json = JSON.parse(line);
                            if (json.response) {
                                // Basic formatting for DeepSeek thinking process
                                let content = json.response;
                                
                                // Detect <think> tags if DeepSeek outputs them raw
                                if (content.includes('<think>')) {
                                    isThinking = true;
                                    content = content.replace('<think>', '<span class="thinking">');
                                }
                                if (content.includes('</think>')) {
                                    isThinking = false;
                                    content = content.replace('</think>', '</span><br/>');
                                }

                                fullResponse += content;
                                botMessageDiv.innerHTML = fullResponse; // Using innerHTML to render the span
                                scrollToBottom();
                            }
                            if (json.done) {
                                console.log("Generation complete");
                            }
                        } catch (e) {
                            console.error("Error parsing JSON chunk", e);
                        }
                    }
                }

            } catch (error) {
                botMessageDiv.className = 'message error';
                botMessageDiv.textContent = 'Error: Could not connect to Ollama. Make sure "ollama serve" is running with OLLAMA_ORIGINS="*"';
            } finally {
                sendBtn.disabled = false;
            }
        }

        function addMessage(text, sender) {
            const div = document.createElement('div');
            div.className = `message ${sender}`;
            div.textContent = text;
            chatHistory.appendChild(div);
            scrollToBottom();
        }

        function scrollToBottom() {
            chatHistory.scrollTop = chatHistory.scrollHeight;
        }

        // Event Listeners
        sendBtn.addEventListener('click', sendMessage);
        userInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
    </script>
</body>
</html>