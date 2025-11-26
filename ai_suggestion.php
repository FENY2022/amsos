<?php
header('Content-Type: text/plain');

// --- SECURITY WARNING ---
// Storing API keys directly in the code is highly insecure.
// In a production environment, use environment variables or a secure secrets management system.
$apiKey = 'AIzaSyCjQTWCw-mPpAh1LMcEw0xTWjzBWnVPyUs';
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-lite:generateContent?key=" . $apiKey;


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
$prompt = "You are an IT service desk dispatcher in the Philippines. Your task is to provide a concise, professional, and actionable suggestion for an 'Action Taken' field. This field documents the *initial step* for a service request. The suggestion should be a single, clear action.

For example:
- For 'Password Reset', suggest 'Sent password reset link to user.'
- For 'PC not booting', suggest 'Scheduled remote session to diagnose boot issue.'
- For 'Printer not working', suggest 'Advised user to restart the printer and check connections.'

Now, based on the following service request, provide the suggested 'Action Taken':

Request Type: '{$requestType}'
Description: '{$description}'

Suggested Action Taken:";


// Prepare the data payload for the Gemini API
$data = [
    'contents' => [
        [
            'parts' => [
                ['text' => $prompt]
            ]
        ]
    ],
    'generationConfig' => [
        'temperature' => 0.5,
        'topK' => 1,
        'topP' => 1,
        'maxOutputTokens' => 60,
        'stopSequences' => [],
    ],
];

$jsonData = json_encode($data);

// Use cURL to make the API request
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // It's good practice to verify SSL certs
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
    // Navigate through the JSON structure to find the text
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        $suggestion = $result['candidates'][0]['content']['parts'][0]['text'];
        // Clean up the response (remove quotes if AI adds them)
        echo trim($suggestion, " \t\n\r\0\x0B\"'");
    } else {
        http_response_code(500);
        echo 'Error: Could not parse the AI response.';
    }
} else {
    http_response_code($httpCode);
    echo "Error: API request failed. Response: {$response}";
}
?>