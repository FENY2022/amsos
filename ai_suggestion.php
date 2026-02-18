<?php
// ai_suggestion.php
header('Content-Type: text/plain');
header("Access-Control-Allow-Origin: *"); // Optional: Allows CORS if frontend is on a different port

// --- CONFIGURATION ---
// Ensure this matches the model you pulled (deepseek-r1:8b)
$apiUrl = "http://localhost:11434/api/generate";
$modelName = "deepseek-r1:8b"; 

// Get data from the POST request
$requestType = isset($_POST['requestType']) ? trim($_POST['requestType']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';

// Quick validation
if (empty($requestType) || empty($description)) {
    http_response_code(400); 
    echo "Error: Missing request type or description.";
    exit;
}

// Construct a prompt specifically for R1
// We ask it to be brief to minimize the 'thinking' time, though R1 will always think a little.
$prompt = "Task: Provide a single, professional, and concise 'Action Taken' sentence for an IT Service Desk ticket.
Context:
- Request Type: $requestType
- Description: $description

Rules:
- Start directly with the action verb (e.g., 'Reset...', 'Scheduled...', 'Advised...').
- Do not include explanations or conversational filler.
- Output ONLY the action sentence.

Suggested Action:";

// Prepare the payload
$data = [
    'model' => $modelName,
    'prompt' => $prompt,
    'stream' => false, 
    'options' => [
        'temperature' => 0.1, // Very low temp for consistent, factual answers
        'num_predict' => 500, // CRITICAL: Must be high enough to allow R1 to "think" before answering
    ]
];

$jsonData = json_encode($data);

// Init cURL
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30); 

$response = curl_exec($ch);

// Error Handling
if (curl_errno($ch)) {
    http_response_code(500); 
    echo 'cURL error: ' . curl_error($ch);
    curl_close($ch);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $result = json_decode($response, true);
    
    if (isset($result['response'])) {
        $rawText = $result['response'];

        // --- DEEPSEEK CLEANUP ---
        // 1. Remove the <think>...</think> block which R1 always generates
        $cleanText = preg_replace('/<think>.*?<\/think>/s', '', $rawText);
        
        // 2. Remove markdown formatting if it adds it (like **Action:**)
        $cleanText = str_replace(['**', '##'], '', $cleanText);

        // 3. Trim whitespace
        $suggestion = trim($cleanText);
        
        echo $suggestion;
    } else {
        http_response_code(500);
        echo 'Error: AI returned empty response.';
    }
} else {
    http_response_code($httpCode);
    echo "Error: API responded with code {$httpCode}";
}
?>