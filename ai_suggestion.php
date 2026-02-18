<?php
header('Content-Type: text/plain');

// --- CONFIGURATION ---
// Ollama runs locally by default on port 11434.
// If your Ollama instance is on a different server, replace 'localhost' with that IP.
$apiUrl = "http://localhost:11434/api/generate";
$modelName = "deepseek-r1:latest";

// Get data from the POST request sent by JavaScript
$requestType = isset($_POST['requestType']) ? trim($_POST['requestType']) : 'N/A';
$description = isset($_POST['description']) ? trim($_POST['description']) : 'N/A';

// If data is missing, exit gracefully
if ($requestType === 'N/A' && $description === 'N/A') {
    http_response_code(400); // Bad Request
    echo "Error: Missing request type and description.";
    exit;
}

// Construct a clear and specific prompt for the AI
// Note: We instruct the model explicitly to avoid conversational filler.
$prompt = "You are an IT service desk dispatcher in the Philippines. Your task is to provide a concise, professional, and actionable suggestion for an 'Action Taken' field. This field documents the *initial step* for a service request. The suggestion should be a single, clear action.

For example:
- For 'Password Reset', suggest 'Sent password reset link to user.'
- For 'PC not booting', suggest 'Scheduled remote session to diagnose boot issue.'
- For 'Printer not working', suggest 'Advised user to restart the printer and check connections.'

Now, based on the following service request, provide ONLY the suggested 'Action Taken' text. Do not provide explanations.

Request Type: '{$requestType}'
Description: '{$description}'

Suggested Action Taken:";

// Prepare the data payload for the Ollama API
$data = [
    'model' => $modelName,
    'prompt' => $prompt,
    'stream' => false, // Important: We want the full response at once, not a stream
    'options' => [
        'temperature' => 0.3, // Lower temperature for more deterministic/professional results
        'num_predict' => 60,  // Limit output length
    ]
];

$jsonData = json_encode($data);

// Use cURL to make the API request
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Optional: Set a timeout if Ollama is slow

$response = curl_exec($ch);

// Handle cURL errors
if (curl_errno($ch)) {
    http_response_code(500); // Internal Server Error
    echo 'cURL error: ' . curl_error($ch);
    curl_close($ch);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Process the AI's response
if ($httpCode === 200) {
    $result = json_decode($response, true);
    
    // Check if the 'response' key exists (Standard Ollama output)
    if (isset($result['response'])) {
        $rawText = $result['response'];

        // --- DEEPSEEK CLEANUP ---
        // DeepSeek-R1 often includes a <think>...</think> block. We must remove it.
        $cleanText = preg_replace('/<think>.*?<\/think>/s', '', $rawText);
        
        // Final cleanup of whitespace and quotes
        $suggestion = trim($cleanText, " \t\n\r\0\x0B\"'");
        
        echo $suggestion;
    } else {
        http_response_code(500);
        echo 'Error: Could not parse the AI response.';
    }
} else {
    http_response_code($httpCode);
    echo "Error: API request failed. Code: {$httpCode}";
}
?>