<?php
// api.php
// Back-End: Handles LLM proxy and chat session logic

// 1. Configuration Constants
const BOT_NAME = 'SJC Scholarship Assistant';
const DEFAULT_GREETING = "Hello! I'm your SJC Scholarship Assistant. How can I help you today?";
const SYSTEM_PROMPT = "You are the helpful and knowledgeable Saint Joseph College (SJC) Scholarship Assistant. Your role is to answer questions ONLY about SJC's scholarships, requirements, and office contact information. If asked about any other topic (e.g., current events, philosophy, other schools), politely state that your expertise is limited to SJC scholarships.";

// 2. Session Management
session_start();

// Initialize chat history and constants
if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [
        [
            'type' => 'bot',
            'message' => DEFAULT_GREETING,
            'time' => date('h:i A')
        ]
    ];
}

// 3. Sanitization and Validation
function sanitize_input($data) {
    // Sanitize input data before use (especially for display)
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * PLACEHOLDER: Function to call the External LLM API (e.g., Gemini or OpenAI)
 * NOTE: You must implement the actual API call logic here.
 *
 * @param array $chatHistory Full conversation history.
 * @param string $latestMessage The latest message from the user.
 * @return string The detailed response from the LLM.
 */
function callExternalApi($chatHistory, $latestMessage) {
    // --- START: Your Custom API Call Implementation ---
    
    // 1. Format the conversation for the LLM using $chatHistory and SYSTEM_PROMPT.
    // 2. Send the HTTP request to your chosen LLM endpoint (e.g., using cURL or Guzzle).
    // 3. Extract the text response from the API result.

    // For demonstration, we'll return a stub response
    // Replace this stub with the real LLM response:
    $botText = "I've processed your request using the external LLM system. I am ready to provide detailed information about SJC scholarships!";
    
    // --- END: Your Custom API Call Implementation ---
    
    return $botText;
}


// 4. Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'];
    
    if ($action === 'load_history') {
        // Returns initial chat history for the front-end to render on load
        echo json_encode(['success' => true, 'history' => $_SESSION['chat_history']]);
        exit;
    }
    
    if ($action === 'send_message') {
        $userMessage = isset($_POST['message']) ? sanitize_input($_POST['message']) : '';
        
        if (empty($userMessage)) {
             echo json_encode(['success' => false, 'error' => 'Message cannot be empty.']);
             exit;
        }

        $currentTime = date('h:i A');

        // Add user message to history
        $_SESSION['chat_history'][] = [
            'type' => 'user',
            'message' => $userMessage,
            'time' => $currentTime
        ];
        
        // **KEY STEP:** Call the external API function
        $botResponse = callExternalApi($_SESSION['chat_history'], $userMessage);
        
        // Add bot response to history
        $_SESSION['chat_history'][] = [
            'type' => 'bot',
            'message' => $botResponse,
            'time' => date('h:i A')
        ];
        
        echo json_encode([
            'success' => true,
            'response' => $botResponse,
            'time' => date('h:i A')
        ]);
        exit;
    }
    
    if ($action === 'clear_chat') {
        // Re-initialize chat history
        $_SESSION['chat_history'] = [
            [
                'type' => 'bot',
                'message' => 'Chat cleared. How can I help you?',
                'time' => date('h:i A')
            ]
        ];
        echo json_encode(['success' => true]);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Invalid action.']);
    exit;
}
// If a user navigates directly to api.php, redirect them or display an error
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.0 403 Forbidden');
    echo json_encode(['error' => 'Direct access forbidden.']);
    exit;
}
?>