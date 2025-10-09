<?php
// chat.php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Load API Key securely (set via environment variable in production)
$api_key = getenv("OPENAI_API_KEY");
// $api_key = "sk-your-key-here"; // ONLY for testing

// Get user message
$data = json_decode(file_get_contents("php://input"), true);
$userMessage = strtolower(trim($data["message"] ?? ""));

// Predefined FAQ answers
$faq = [
    "requirements" => "📌 To apply for the scholarship, you need:\n- Transcript of Records\n- Certificate of Enrollment\n- Recommendation Letter\n- Completed Application Form",
    "deadline" => "📅 The scholarship application deadline is **October 30, 2025**.",
    "contact" => "☎ You can reach the Scholarship Office:\n- Email: scholarship-office@example.com\n- Phone: (0935) 724 8593\n- FB: facebook.com/CORE.MTEP",
    "amount" => "💰 The scholarship covers full tuition fees and provides a monthly allowance of ₱3,000.",
    "eligibility" => "✅ The scholarship is open to all college students with at least a GPA of 85% and no failing grades."
];

// Check if message matches FAQ keywords
$reply = null;
foreach ($faq as $keyword => $answer) {
    if (strpos($userMessage, $keyword) !== false) {
        $reply = $answer;
        break;
    }
}

// If not in FAQ, call OpenAI API
if (!$reply) {
    $ch = curl_init("https://api.openai.com/v1/chat/completions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $api_key",
        "Content-Type: application/json"
    ]);

    $payload = [
        "model" => "gpt-4o-mini",
        "messages" => [
            ["role" => "system", "content" => "You are a helpful FAQ assistant for scholarship services."],
            ["role" => "user", "content" => $userMessage]
        ]
    ];

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    $reply = $data["choices"][0]["message"]["content"] ?? "⚠ Sorry, I couldn’t get a response right now.";
}

// Return JSON
echo json_encode(["reply" => $reply]);
