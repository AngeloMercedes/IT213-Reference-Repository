<?php
// PHP Fixes: Security, Robustness, and Separation of Concerns

/**
 * SJC Scholarship Assistant Chatbot - Single File PHP Solution
 * * Improvements:
 * 1. Security: Added input validation, sanitization, and output escaping.
 * 2. Robustness: Improved bot response logic and error handling.
 * 3. Maintainability: Refactored constants, cleaned up data arrays, and modernized date format.
 * 4. UX: Added a "Clear Chat" button and a dedicated AJAX endpoint.
 */

// 1. Configuration Constants
const BOT_NAME = 'SJC Scholarship Assistant';
const DEFAULT_GREETING = "Hello! I'm your SJC Scholarship Assistant. How can I help you today?";

// 2. Session Management
session_start();

// Initialize chat history and constants for the initial bot message
if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [
        [
            'type' => 'bot',
            'message' => DEFAULT_GREETING,
            'time' => date('h:i A') // Changed to 12-hour format with AM/PM for better UX
        ]
    ];
}

// 3. Sanitization and Validation
function sanitize_input($data) {
    // Use ENT_QUOTES to handle both single and double quotes, and default encoding
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Function for getting the bot response (Logic remains similar, but is now more structured)
function getBotResponse($query) {
    // Input is already trimmed, now convert for comparison
    $q = strtolower($query);
    
    // Define an array of response patterns for better organization
    $response_patterns = [
        // Office Information
        '/(hi|hello|hey|good morning|good afternoon|good evening)/' => "Hello! I'm here to help you with scholarship information at Saint Joseph College. What would you like to know?",
        '/(director|head)/' => "The Director of the Scholarship Office is <strong>Joenisa M. Hoyla</strong>.",
        '/(staff|personnel)/' => "The Scholarship Office staff members are:<br>• <strong>Lara O. Orais</strong><br>• <strong>Zaneth O. Mulig</strong>",
        '/(location|where|address)/' => "The Scholarship Office is located at:<br><strong>Ground floor, 2nd Door Room 119, College Building - Main Campus, Saint Joseph College, Maasin City, Southern Leyte</strong>",
        '/(contact|phone|number)/' => "You can contact the Scholarship Office via:<br>📱 Mobile: <strong>0947-3855424</strong> or <strong>0927-0182744</strong><br>☎️ Landline: <strong>(053) 570-8448 local 110</strong>",
        '/(facebook|fb)/' => "Follow us on Facebook:<br><strong>D' JOSEPHIAN ESKOLARS & Student Affairs and Services Office</strong>",
        
        // Scholarship Types
        '/(types|categories|how many)/' => "SJC offers <strong>4 main types</strong> of scholarships:<br>1. 🏛️ <strong>Government-funded</strong> scholarships<br>2. ⛪ <strong>Diocese-funded</strong> scholarships<br>3. 🏫 <strong>School-funded</strong> scholarships/discounts<br>4. 🎓 <strong>Alumni-funded</strong> scholarships",
        
        // Government Scholarships
        '/government.*list/' => "<strong>Government-funded scholarships:</strong><br>1. CHED Merit Scholarship (CMSP)<br>2. TES-UNIFAST<br>3. Tulong Dunong Program (TDP/TINGOG)<br>4. CoScho Coconut Scholarship",
        '/(ched|cmsp|merit)/' => "<strong>CHED Merit Scholarship Program (CMSP):</strong><br>💰 Amount: ₱60,000 full / ₱30,000 half per year<br>📋 Requirement: GWA of 93% and above<br>📊 Grade maintenance: Full (2.0+), Half (2.5+)<br>✅ No renewal needed - covers until program completion",
        '/(tes|unifast)/' => "<strong>Tertiary Education Subsidy (TES-UNIFAST):</strong><br>💰 Amount: ₱20,000 per year or ₱10,000 per semester<br>📋 Requirement: Low-income families verified through DSWD LISTAHAN<br>📊 Grade requirement: None<br>✅ No renewal needed",
        '/(tdp|tulong dunong|tingog)/' => "<strong>Tulong Dunong Program (TDP/TINGOG):</strong><br>💰 Amount: ₱7,500 per semester<br>📋 Requirement: Low-income families<br>📊 Grade requirement: None<br>✅ No renewal needed",
        '/(coscho|coconut)/' => "<strong>CoScho Coconut Scholarship Program:</strong><br>📋 For: Registered farmers' dependents with income ≤₱300,000<br>🎓 Courses: BS Tourism and BS Business Administration<br>📊 Grade requirement: 2.5 and above (80%+)<br>✅ No renewal needed",
        
        // Diocese Scholarships
        '/diocese.*list/' => "<strong>Diocese-funded scholarships:</strong><br>1. Diocesan Scholarship<br>2. CORE Scholarship<br>3. YSLEP/Caritas Manila",
        '/(diocesan)/' => "<strong>Diocesan Scholarship:</strong><br>💰 Discounts:<br>• 70% - Alumni of diocesan schools<br>• 50% - Alumni of private schools within Diocese of Maasin<br>• 40% - Residents of Sogod, Baybay, and beyond<br>📋 Requirement: Certification from parish priest or principal<br>📊 Grade requirement: None<br>✅ Maintain satisfactory academic standing and good moral character",
        '/(core.*scholarship)/' => "<strong>CORE Scholarship:</strong><br>💰 Discount: 40% tuition discount<br>📋 For: Students from Sogod, Baybay, and areas beyond<br>✅ Token of appreciation for choosing SJC",
        '/(yslep|caritas)/' => "<strong>YSLEP/Caritas Manila Scholarship:</strong><br>💰 Coverage: Full college education including tuition, board, and lodging<br>📋 For: Poor and underprivileged but deserving youth<br>✅ Comprehensive support program",
        
        // School Scholarships
        '/school.*list/' => "<strong>School-funded scholarships:</strong><br>1. Academic Scholarship<br>2. Varsity Scholarship<br>3. Working Scholars Program<br>4. Student Council Scholarships<br>5. Academic Excellence Discount<br>6. SJC Chorale & Dance Troupe<br>7. Employee Special Discount<br>8. Group Discount<br>9. Presidential Scholarship (Added for completeness)<br>10. Alumni Parents Discount (Added for completeness)<br>And more! (26 total)",
        '/(academic scholarship|academic.*scholar)/' => "<strong>Academic Scholarship:</strong><br>💰 Discounts based on GWA:<br>• Full: 1.00-1.40 (no grade below 1.5)<br>• Three-fourths: 1.41-1.50 (no grade below 2.0)<br>• Half: 1.51-1.60 (no grade below 2.5)<br>📊 Lowest grade allowed: 1.75<br>✅ Maintained as long as no grade below 1.75",
        '/(varsity|sports)/' => "<strong>Varsity Scholarship:</strong><br>💰 Coverage: Full or half tuition<br>🏅 For: Students excelling in sports<br>✅ Represent school in local and national competitions<br>📊 Grade requirement: No grade below 3.0",
        '/(working scholar)/' => "<strong>Working Scholars Program:</strong><br>💰 Rate: ₱35 per hour<br>📋 For: Financially challenged students<br>✅ Study for free by working part-time in assigned school offices<br>📊 Grade requirement: No grade below 3.0",
        '/(student council|president)/' => "<strong>Student Council Scholarships:</strong><br>💰 President: Full tuition discount<br>💰 Other officers: 30% or 20% discount<br>📅 Starting AY 2024-2025<br>✅ Subject to fund availability",
        '/(excellence|honor.*discount)/' => "<strong>Academic Excellence Discount:</strong><br>💰 New students with honors:<br>• 20% - Honors<br>• 50% - High Honors<br>• 100% - Highest Honors<br>📋 Certification from principal required",
        '/(chorale|choir)/' => "<strong>SJC Chorale Scholarship:</strong><br>🎵 For: Musically gifted students (Shepherds' Voice Choir)<br>✅ Perform during school activities and homecomings<br>📋 Audition required, recruited yearly<br>📊 Maintain good behavior and academic standing",
        '/(dance|kast)/' => "<strong>SJC KAST (Kasaganaan Dance Troupe) Scholarship:</strong><br>💃 For: Students excelling in bodily-kinesthetic art<br>✅ Perform at programs, competitions, and private events<br>📊 Maintain good behavior and academic standing",
        '/(employee|children)/' => "<strong>Employee Special Discount:</strong><br>💰 For: Children of long-serving employees<br>📊 Discount rates: 10%, 20%, 50%, or 100%<br>✅ Based on years of service",
        '/(group discount|siblings)/' => "<strong>Group Discount:</strong><br>💰 For: Parents with 3+ children enrolled in SJC<br>✅ Helps reduce financial burden for families",
        '/(senior high.*discount)/' => "<strong>Senior High School Discount:</strong><br>💰 Discount: 10% or 20%<br>📋 For: SJC Senior High School graduates enrolling in college",
        '/(indigenous|ip)/' => "<strong>Indigenous People Scholarship:</strong><br>💰 Coverage: Full scholarship<br>📋 For: Indigenous Peoples (IPs) enrolled at SJC<br>✅ Selected alumni also help provide for daily needs",

        // Alumni Scholarships
        '/alumni.*list/' => "<strong>Alumni-funded scholarships:</strong><br>1. High School Alumni Foundation Scholarship<br>2. Adopt-A-Student Program",
        '/(adopt|alumni.*program)/' => "<strong>Adopt-A-Student Program:</strong><br>💰 Sponsorship and cash allowance from alumni<br>📋 For: Deserving students<br>✅ Funds from College Alumni Homecoming proceeds",
        
        // Application Process
        '/(apply|application)/' => "<strong>Application Process:</strong><br><br>🏛️ <strong>Government scholarships:</strong> Apply online/in-person when CHED opens slots<br><br>⛪ <strong>Diocese scholarships:</strong> Get certification from parish priest/principal, present during enrollment<br><br>🏫 <strong>School scholarships:</strong> Endorsed by Deans/Advisers, or verified by Registrar (for alumni children)<br><br>📧 Results sent via email, bulletins, and Facebook page",
        '/(requirements|documents)/' => "<strong>General Requirements:</strong><br>• Completed Application Form (hardcopy/softcopy)<br>• Certificate of Indigency (for low-income)<br>• Must be in Low-Income List (Barangay)<br>• Certification from Parish Priest (Diocese scholarships)<br>• Certification from Principal (Honors received)<br><br>📋 Requirements vary by scholarship type",
        
        // Grade Requirements & System
        '/(grade.*maintain)/' => "<strong>Grade Requirements Summary:</strong><br><br>📊 <strong>CMSP:</strong> Full (2.0+), Half (2.5+)<br>📊 <strong>CoScho:</strong> 2.5+<br>📊 <strong>TES/TDP:</strong> None<br>📊 <strong>Diocese:</strong> None<br>📊 <strong>Academic:</strong> Lowest 1.75<br>📊 <strong>School-funded:</strong> No grade below 3.0",
        '/(grading system|grade scale)/' => "<strong>SJC Grading System (Decimal):</strong><br>1.0 = 98+ (Excellent)<br>1.25 = 95-97 (Very Good)<br>1.50 = 92-94 (Very Good)<br>1.75 = 89-91 (Good)<br>2.0 = 86-88 (Good)<br>2.25 = 83-85 (Good)<br>2.50 = 80-82 (Fair)<br>2.75 = 77-79 (Fair)<br>3.0 = 75-76 (Passed)<br>5.0 = Below 75 (Failed)<br><br>Special: NC, W, FW, FA",
        '/(honor|cum laude)/' => "<strong>Honors & Scholarship Matrix:</strong><br><br>🏆 <strong>Summa Cum Laude:</strong> 1.00-1.20 (Full scholarship)<br>🏆 <strong>Magna Cum Laude:</strong> 1.21-1.40 (3/4 scholarship)<br>🏆 <strong>Cum Laude:</strong> 1.41-1.60 (1/2 scholarship)<br>📚 <strong>Dean's Lister:</strong> 1.61-1.80 (No scholarship)<br><br>✅ At least 100 students in curriculum year required",
        
        // Renewal & Cancellation
        '/(renewal|renew)/' => "<strong>Scholarship Renewal:</strong><br><br>✅ <strong>No renewal needed:</strong> CMSP, CoScho, TES/TDP (covers until graduation)<br><br>🔄 <strong>Academic:</strong> Maintained if no grade below 1.75<br><br>🔄 <strong>School-funded:</strong> Renewed upon Dean/Adviser recommendation",
        '/(revoked|cancelled|lose)/' => "<strong>Scholarship Revocation:</strong><br><br>A scholarship is revoked if:<br>❌ Student drops out for one school year<br>❌ Shifts to non-priority program (CMSP/CoScho)<br>❌ Qualifies for higher govt scholarship<br>❌ Presents falsified documents<br>❌ Commits misconduct<br>❌ Graduates from program",
        
        // Financial
        '/(low income|poor|financial)/' => "<strong>Scholarships for Low-Income Students:</strong><br>• TES-UNIFAST (₱20,000/year)<br>• TDP/TINGOG (₱7,500/sem)<br>• CoScho (farmers' dependents)<br>• YSLEP/Caritas Manila (full support)<br>• Working Scholars Program (₱35/hr)<br>• Adopt-A-Student Program",
        
        // All scholarships
        '/all.*scholarship/' => "<strong>All SJC Scholarships (26 total):</strong><br><br>🏛️ Government: CMSP, TES, TDP, CoScho<br>⛪ Diocese: Diocesan, CORE, YSLEP<br>🏫 School: Academic, Varsity, Working Scholars, Student Council, Excellence, Chorale, Dance, Employee, Group, Presidential, Alumni Parents, Cash, Senior High, J/MAG, ROTC, Criminology, Indigenous<br>🎓 Alumni: Foundation, Adopt-A-Student<br><br>Type 'list government' or 'list school' for details!",
        
        // Help & Menu
        '/(help|menu|options)/' => "<strong>How can I assist you?</strong><br><br>📋 Try asking about:<br>• \"Types of scholarships\"<br>• \"CHED scholarship\"<br>• \"How to apply\"<br>• \"Grade requirements\"<br>• \"Contact information\"<br>• \"Diocesan scholarship\"<br>• \"Working scholars\"<br>• \"All scholarships\"<br><br>Just type your question naturally!",

        // Thank you
        '/(thank)/' => "You're welcome! If you have any more questions about scholarships, feel free to ask. Good luck with your studies! 🎓"
    ];

    // Check patterns in order of specificity (roughly)
    foreach ($response_patterns as $pattern => $response) {
        if (preg_match($pattern, $q)) {
            return $response;
        }
    }
    
    // Default fallback
    return "I'm not sure about that specific question. Try asking about:<br>• Scholarship types<br>• Specific scholarships (CHED, Diocesan, etc.)<br>• Application process<br>• Grade requirements<br>• Contact information<br><br>Or type 'help' for more options!";
}

// 4. Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Set headers for JSON response
    header('Content-Type: application/json');
    
    $action = $_POST['action'];
    
    // Send Message Action
    if ($action === 'send_message') {
        // Input validation/sanitization
        $userMessage = isset($_POST['message']) ? sanitize_input($_POST['message']) : '';
        
        if (empty($userMessage)) {
            // Send error response for empty message
             echo json_encode(['success' => false, 'error' => 'Message cannot be empty.']);
             exit;
        }

        // Generate and record the user message (Time format change applied)
        $currentTime = date('h:i A');

        // Add user message to history (Message is already sanitized)
        $_SESSION['chat_history'][] = [
            'type' => 'user',
            'message' => $userMessage,
            'time' => $currentTime
        ];
        
        // Get and record the bot response
        $botResponse = getBotResponse($userMessage);
        
        // Add bot response to history
        $_SESSION['chat_history'][] = [
            'type' => 'bot',
            'message' => $botResponse,
            'time' => date('h:i A')
        ];
        
        // Return success response (Note: User message is not returned, only the bot's)
        echo json_encode([
            'success' => true,
            'response' => $botResponse,
            'time' => date('h:i A')
        ]);
        exit;
    }
    
    // Clear Chat Action
    if ($action === 'clear_chat') {
        // Re-initialize chat history with the default greeting
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

    // Default Error for unrecognized action
    echo json_encode(['success' => false, 'error' => 'Invalid action.']);
    exit;
}
// End of PHP backend logic
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo BOT_NAME; ?></title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🎓</text></svg>">
    <style>
        /* 5. CSS Fixes: Remove duplicate declarations and add the missing styles from the end of the original code, clean up redundant selectors, and add styles for the typing indicator and new button */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            /* Using more professional/readable names */
            --primary: #2563eb; /* Blue-600 */
            --primary-dark: #1e40af; /* Blue-800 */
            --secondary: #8b5cf6; /* Violet-500 */
            --success: #10b981; /* Emerald-500 */
            --bg-light: #f8fafc; /* Slate-50 */
            --bg-white: #ffffff;
            --text-dark: #1e293b; /* Slate-800 */
            --text-gray: #64748b; /* Slate-500 */
            --border: #e2e8f0; /* Slate-200 */
            --shadow: rgba(0, 0, 0, 0.1);
            --gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%); /* Purple-Blue Gradient */
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: var(--bg-light);
            color: var(--text-dark);
            line-height: 1.6;
        }
        
        .container {
            /* Max width set to 900px as in the original code, ensure it takes full viewport height */
            max-width: 900px;
            margin: 0 auto;
            height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--bg-white);
            box-shadow: 0 0 50px var(--shadow);
        }
        
        /* Header */
        .chat-header {
            background: var(--gradient);
            color: white;
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-icon {
            /* Replaced the repeated block with the correct styling */
            width: 50px;
            height: 50px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0; /* Prevent shrinking on small screens */
        }
        
        .header-content {
            flex-grow: 1; /* Allow content to fill available space */
        }

        .header-content h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        
        .header-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            opacity: 0.9;
        }
        
        .status-dot {
            width: 8px;
            height: 8px;
            background: var(--success);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        /* Messages Container */
        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 2rem;
            background: var(--bg-light);
            scroll-behavior: smooth;
        }
        
        /* Message Styling */
        .message {
            display: flex;
            margin-bottom: 1.5rem;
            animation: slideIn 0.3s ease-out;
            align-items: flex-start; /* Align content to the top */
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .message.user {
            justify-content: flex-end;
        }

        .message.user .message-content {
            margin-left: auto; /* Push user message to the right */
        }
        
        .message-content {
            max-width: 70%;
            padding: 1rem 1.25rem;
            border-radius: 1.25rem;
            position: relative;
            /* Ensure bot content (which includes <strong> and <br>) respects formatting */
        }
        
        .message.bot .message-content {
            background: var(--bg-white);
            border: 1px solid var(--border);
            border-bottom-left-radius: 0.25rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .message.user .message-content {
            background: var(--gradient);
            color: white;
            border-bottom-right-radius: 0.25rem;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }
        
        .message-text {
            font-size: 0.95rem;
            line-height: 1.6;
            /* Ensure HTML tags like <br> are interpreted correctly */
            word-wrap: break-word; 
            overflow-wrap: break-word;
        }
        
        .message-time {
            font-size: 0.75rem;
            opacity: 0.7; /* Increased opacity slightly for readability */
            margin-top: 0.5rem;
            display: block;
            text-align: right; /* Time on the right side of the bubble */
        }
        
        .message.user .message-time {
            color: rgba(255, 255, 255, 0.8);
        }

        .bot-avatar {
            width: 36px;
            height: 36px;
            background: var(--primary); /* Simpler color */
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.75rem;
            flex-shrink: 0;
            color: white;
            font-size: 18px;
            margin-top: 5px; /* Adjust avatar alignment with the bubble */
        }

        /* Typing Indicator Styles */
        #typing-indicator .message-content {
            display: flex;
            align-items: center;
        }

        .typing-indicator {
            display: inline-flex;
            gap: 5px;
            padding-right: 5px;
        }

        .typing-dot {
            width: 8px;
            height: 8px;
            background: var(--text-gray);
            border-radius: 50%;
            animation: typing-bounce 1.4s infinite ease-in-out;
        }

        .typing-dot:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-dot:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes typing-bounce {
            0%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-8px);
            }
        }
        
        /* Input Area */
        .input-container {
            padding: 1.5rem 2rem;
            background: var(--bg-white);
            border-top: 1px solid var(--border);
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
        }
        
        .input-wrapper {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        #messageInput {
            flex: 1;
            padding: 1rem 1.5rem;
            border: 2px solid var(--border);
            border-radius: 2rem;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        #messageInput:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        #sendButton {
            width: 50px;
            height: 50px;
            background: var(--gradient);
            border: none;
            border-radius: 50%;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            flex-shrink: 0;
            padding: 0; /* Remove default padding for clean SVG centering */
        }
        
        #sendButton:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        #sendButton:active {
            transform: scale(0.95);
        }
        
        #sendButton:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none; /* Prevent click events */
        }
        
        /* Quick Actions */
        .quick-actions {
            display: flex;
            gap: 0.5rem;
            padding: 0 2rem 1rem;
            overflow-x: auto;
            scrollbar-width: none;
            border-bottom: 1px solid var(--border);
            background: var(--bg-white);
        }
        
        .quick-actions::-webkit-scrollbar {
            display: none;
        }
        
        .quick-action {
            padding: 0.5rem 1rem;
            background: var(--bg-white);
            border: 1px solid var(--border);
            border-radius: 2rem;
            font-size: 0.875rem;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s ease;
            color: var(--primary-dark);
        }
        
        .quick-action:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            transform: translateY(-2px);
        }

        /* Clear Chat Button Styling */
        .clear-chat-button {
            background: none;
            border: 1px solid rgba(255, 255, 255, 0.5);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 0.5rem;
            font-size: 0.8rem;
            cursor: pointer;
            transition: background 0.2s;
            margin-left: auto;
        }

        .clear-chat-button:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Media Query for Mobile */
        @media (max-width: 600px) {
            .container {
                box-shadow: none;
            }
            .chat-header, .input-container {
                padding: 1rem 1.25rem;
            }
            .messages-container {
                padding: 1rem;
            }
            .message-content {
                max-width: 85%;
            }
            .quick-actions {
                padding: 0 1.25rem 0.75rem;
            }
            #messageInput {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }
            #sendButton {
                width: 40px;
                height: 40px;
            }
            .header-content h1 {
                font-size: 1.25rem;
            }
            .header-icon {
                width: 40px;
                height: 40px;
                font-size: 20px;
            }
        }

        /* Scrollbar Styling (as defined in original, but placed correctly) */
        .messages-container::-webkit-scrollbar {
            width: 6px;
        }
        
        .messages-container::-webkit-scrollbar-track {
            background: var(--bg-light);
        }
        
        .messages-container::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 3px;
        }
        
        .messages-container::-webkit-scrollbar-thumb:hover {
            background: var(--text-gray);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="chat-header">
            <div class="header-icon">🎓</div>
            <div class="header-content">
                <h1><?php echo BOT_NAME; ?></h1>
                <div class="header-status">
                    <span class="status-dot"></span>
                    <span>Online • Ready to help</span>
                </div>
            </div>
            <button class="clear-chat-button" onclick="clearChat()">🗑️ Clear Chat</button>
        </div>
        
        <div class="quick-actions">
            <button class="quick-action" onclick="sendQuickMessage('Types of scholarships')">📋 Types</button>
            <button class="quick-action" onclick="sendQuickMessage('CHED scholarship')">🏛️ CHED</button>
            <button class="quick-action" onclick="sendQuickMessage('How to apply')">✍️ Apply</button>
            <button class="quick-action" onclick="sendQuickMessage('Contact info')">📞 Contact</button>
            <button class="quick-action" onclick="sendQuickMessage('Grade requirements')">📊 Grades</button>
            <button class="quick-action" onclick="sendQuickMessage('All scholarships')">📚 All</button>
        </div>
        
        <div class="messages-container" id="messagesContainer">
            <?php foreach ($_SESSION['chat_history'] as $msg): ?>
                <div class="message <?php echo sanitize_input($msg['type']); ?>">
                    <?php if ($msg['type'] === 'bot'): ?>
                        <div class="bot-avatar">🤖</div>
                    <?php endif; ?>
                    <div class="message-content">
                        <div class="message-text">
                            <?php 
                                if ($msg['type'] === 'user') {
                                    echo nl2br(sanitize_input($msg['message']));
                                } else {
                                    echo $msg['message']; // Bot messages contain necessary HTML, so don't escape.
                                }
                            ?>
                        </div>
                        <span class="message-time"><?php echo sanitize_input($msg['time']); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="input-container">
            <div class="input-wrapper">
                <input 
                    type="text" 
                    id="messageInput" 
                    placeholder="Ask me about scholarships..." 
                    autocomplete="off"
                    onkeypress="handleKeyPress(event)"
                >
                <button id="sendButton" onclick="sendMessage()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    
    <script>
        // 6. JavaScript Fixes: Robustness, UX, and Clear Chat functionality

        const messagesContainer = document.getElementById('messagesContainer');
        const messageInput = document.getElementById('messageInput');
        const sendButton = document.getElementById('sendButton');
        const clearChatButton = document.querySelector('.clear-chat-button');
        
        // --- Utility Functions ---

        // Auto-scroll to bottom
        function scrollToBottom() {
            // Use a small timeout to ensure the DOM has rendered the new message height
            setTimeout(() => {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }, 10);
        }
        
        // Get current time in 'hh:mm AM/PM' format
        function getCurrentTime() {
            const now = new Date();
            const hours = now.getHours();
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            const displayHours = hours % 12 || 12; // Convert 24h to 12h, 0 becomes 12
            return `${displayHours.toString().padStart(2, '0')}:${minutes} ${ampm}`;
        }
        
        // Add message to UI
        function addMessage(type, text, time) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${type}`;
            
            let avatar = '';
            if (type === 'bot') {
                avatar = '<div class="bot-avatar">🤖</div>';
            }
            
            // Use innerHTML only for the bot's response which is pre-formatted, 
            // and textContent for user input to prevent XSS (although PHP sanitizes it, JS should too)
            const sanitizedText = type === 'user' ? text.replace(/[&<>"']/g, (m) => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[m])) : text;
            
            messageDiv.innerHTML = `
                ${avatar}
                <div class="message-content">
                    <div class="message-text">${sanitizedText.replace(/\n/g, '<br>')}</div>
                    <span class="message-time">${time}</span>
                </div>
            `;
            
            messagesContainer.appendChild(messageDiv);
            scrollToBottom();
        }
        
        // Typing indicator
        const TYPING_INDICATOR_ID = 'typing-indicator';

        function showTypingIndicator() {
            let indicator = document.getElementById(TYPING_INDICATOR_ID);
            if (indicator) return TYPING_INDICATOR_ID; // Already visible

            const typingDiv = document.createElement('div');
            typingDiv.className = 'message bot';
            typingDiv.id = TYPING_INDICATOR_ID;
            
            typingDiv.innerHTML = `
                <div class="bot-avatar">🤖</div>
                <div class="message-content">
                    <div class="typing-indicator">
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                    </div>
                </div>
            `;
            
            messagesContainer.appendChild(typingDiv);
            scrollToBottom();
            return TYPING_INDICATOR_ID;
        }
        
        function removeTypingIndicator(id) {
            const indicator = document.getElementById(id);
            if (indicator) {
                indicator.remove();
            }
        }

        // --- Event Handlers ---

        // Handle Enter key
        function handleKeyPress(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault(); // Stop default newline in text input
                sendMessage();
            }
        }
        
        // Send message
        async function sendMessage() {
            const message = messageInput.value.trim();
            if (!message) return;
            
            // Disable input/button to prevent double-sending
            messageInput.disabled = true;
            sendButton.disabled = true;
            
            // Get current time *before* the API call
            const userTime = getCurrentTime();

            // Add user message to UI
            addMessage('user', message, userTime);
            messageInput.value = '';
            
            // Show typing indicator
            const typingId = showTypingIndicator();
            
            try {
                // Send to server
                const formData = new FormData();
                formData.append('action', 'send_message');
                formData.append('message', message);
                
                const response = await fetch(window.location.href, { // Use current URL
                    method: 'POST',
                    body: formData
                });
                
                // Check for non-OK HTTP status (e.g., 500 server error)
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                
                // Remove typing indicator immediately after receiving response
                removeTypingIndicator(typingId);
                
                // Add bot response
                if (data.success) {
                    // Small delay to simulate "thinking" time for better UX
                    setTimeout(() => {
                        addMessage('bot', data.response, data.time);
                    }, 300); 
                } else {
                    // Handle PHP-side error (e.g., empty message)
                    addMessage('bot', `Error: ${data.error || 'The server returned an unsuccessful response.'}`, getCurrentTime());
                }
            } catch (error) {
                console.error('Fetch error:', error);
                removeTypingIndicator(typingId);
                addMessage('bot', 'Sorry, there was a network or server error. Please check your connection and try again. (' + error.message + ')', getCurrentTime());
            } finally {
                // Re-enable input
                messageInput.disabled = false;
                sendButton.disabled = false;
                messageInput.focus();
            }
        }
        
        // Quick message - just sets the input and calls sendMessage
        function sendQuickMessage(message) {
            messageInput.value = message;
            sendMessage();
        }

        // Clear Chat function
        async function clearChat() {
            if (!confirm("Are you sure you want to clear the chat history?")) return;

            // Clear UI immediately
            messagesContainer.innerHTML = '';
            
            try {
                // Send clear action to server
                const formData = new FormData();
                formData.append('action', 'clear_chat');
                
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    // Add the new default message from the server response
                    addMessage('bot', 'Chat cleared. How can I help you?', getCurrentTime());
                } else {
                    alert("Error clearing chat on the server. Please refresh the page.");
                }

            } catch (error) {
                console.error('Clear chat error:', error);
                alert("Network error while clearing chat. Please try refreshing the page.");
            }
        }
        
        // Initial setup
        scrollToBottom();
        messageInput.focus();
    </script>
</body>
</html>