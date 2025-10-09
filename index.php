<?php
session_start();

// Initialize chat history
if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [
        [
            'type' => 'bot',
            'message' => "Hello! I'm your SJC Scholarship Assistant. How can I help you today?",
            'time' => date('H:i')
        ]
    ];
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    if ($_POST['action'] === 'send_message') {
        $userMessage = trim($_POST['message']);

        $_SESSION['chat_history'][] = [
            'type' => 'user',
            'message' => $userMessage,
            'time' => date('H:i')
        ];

        $botResponse = getBotResponse($userMessage);

        $_SESSION['chat_history'][] = [
            'type' => 'bot',
            'message' => $botResponse,
            'time' => date('H:i')
        ];

        echo json_encode([
            'success' => true,
            'response' => $botResponse,
            'time' => date('H:i')
        ]);
        exit;
    }

    if ($_POST['action'] === 'clear_chat') {
        $_SESSION['chat_history'] = [
            [
                'type' => 'bot',
                'message' => 'Chat cleared. How can I help you?',
                'time' => date('H:i')
            ]
        ];
        echo json_encode(['success' => true]);
        exit;
    }
}

function getBotResponse($query) {
    $q = strtolower($query);

    if (preg_match('/\b(hi|hello|hey|good morning|good afternoon|good evening)\b/', $q)) {
        return "Hello! I'm here to help you with scholarship information at Saint Joseph College. What would you like to know?";
    }

    if (strpos($q, 'director') !== false) {
        return "The Director of the Scholarship Office is <strong>Joenisa M. Hoyla</strong>.";
    }

    if (strpos($q, 'contact') !== false) {
        return "You can reach us at:<br>📱 <strong>0947-3855424</strong><br>☎️ (053) 570-8448 local 110";
    }

    if (strpos($q, 'types') !== false) {
        return "SJC offers 4 main types of scholarships:<br>1️⃣ Government<br>2️⃣ Diocese<br>3️⃣ School<br>4️⃣ Alumni";
    }

    if (strpos($q, 'help') !== false || strpos($q, 'menu') !== false) {
        return "Try asking about:<br>• Types of scholarships<br>• CHED Scholarship<br>• How to apply<br>• Grade requirements<br>• Contact information";
    }

    if (strpos($q, 'thank') !== false) {
        return "You're welcome! 😊 Keep aiming high, Josephinian scholar!";
    }

    return "I'm not sure about that specific question. Try typing 'help' for available topics!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SJC Scholarship Assistant</title>
  <style>
    :root {
      --primary: #2563eb;
      --gradient: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
      --bg-light: #f8fafc;
      --text-dark: #1e293b;
      --text-gray: #64748b;
      --shadow: rgba(0, 0, 0, 0.1);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Inter', sans-serif;
    }

    body {
      background: var(--bg-light);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .container {
      width: 100%;
      max-width: 600px;
      height: 90vh;
      display: flex;
      flex-direction: column;
      background: white;
      border-radius: 20px;
      box-shadow: 0 10px 40px var(--shadow);
      overflow: hidden;
    }

    .chat-header {
      background: var(--gradient);
      color: #fff;
      padding: 1.2rem 1.5rem;
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .chat-header h1 {
      font-size: 1.2rem;
      font-weight: 600;
    }

    .header-icon {
      font-size: 1.8rem;
    }

    .messages-container {
      flex: 1;
      overflow-y: auto;
      padding: 1rem;
      background: var(--bg-light);
    }

    .message {
      display: flex;
      align-items: flex-start;
      margin-bottom: 1rem;
    }

    .message.user {
      justify-content: flex-end;
    }

    .message .message-content {
      max-width: 75%;
      padding: 0.8rem 1rem;
      border-radius: 12px;
      box-shadow: 0 2px 6px var(--shadow);
      font-size: 0.95rem;
      line-height: 1.5;
    }

    .message.bot .message-content {
      background: white;
      color: var(--text-dark);
    }

    .message.user .message-content {
      background: var(--primary);
      color: white;
    }

    .message-time {
      display: block;
      font-size: 0.75rem;
      color: var(--text-gray);
      margin-top: 0.3rem;
      text-align: right;
    }

    .bot-avatar {
      font-size: 1.6rem;
      margin-right: 0.5rem;
    }

    .input-container {
      display: flex;
      align-items: center;
      padding: 0.8rem;
      background: white;
      border-top: 1px solid #e2e8f0;
    }

    #messageInput {
      flex: 1;
      padding: 0.8rem 1rem;
      border-radius: 10px;
      border: 1px solid #e2e8f0;
      outline: none;
      font-size: 0.95rem;
      transition: 0.3s;
    }

    #messageInput:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 2px rgba(37,99,235,0.2);
    }

    #sendButton {
      background: var(--gradient);
      color: white;
      border: none;
      border-radius: 50%;
      width: 45px;
      height: 45px;
      margin-left: 0.5rem;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: 0.3s;
    }

    #sendButton:hover {
      transform: scale(1.05);
    }

    .quick-actions {
      padding: 0.5rem 1rem;
      background: white;
      border-top: 1px solid #e2e8f0;
      display: flex;
      flex-wrap: wrap;
      gap: 0.4rem;
      justify-content: center;
    }

    .quick-action {
      background: var(--bg-light);
      border: none;
      padding: 0.4rem 0.8rem;
      border-radius: 8px;
      cursor: pointer;
      font-size: 0.85rem;
      color: var(--text-dark);
      transition: 0.3s;
    }

    .quick-action:hover {
      background: var(--primary);
      color: white;
    }

    /* Scrollbar */
    .messages-container::-webkit-scrollbar {
      width: 6px;
    }
    .messages-container::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 3px;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="chat-header">
      <div class="header-icon">🎓</div>
      <div>
        <h1>SJC Scholarship Assistant</h1>
        <small>Online • Ready to help</small>
      </div>
    </div>

    <div class="messages-container" id="messagesContainer">
      <?php foreach ($_SESSION['chat_history'] as $msg): ?>
        <div class="message <?php echo $msg['type']; ?>">
          <?php if ($msg['type'] === 'bot'): ?><div class="bot-avatar">🤖</div><?php endif; ?>
          <div class="message-content">
            <?php echo $msg['message']; ?>
            <span class="message-time"><?php echo $msg['time']; ?></span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="quick-actions">
      <button class="quick-action" onclick="sendQuickMessage('Types of scholarships')">📋 Types</button>
      <button class="quick-action" onclick="sendQuickMessage('CHED scholarship')">🏛️ CHED</button>
      <button class="quick-action" onclick="sendQuickMessage('How to apply')">✍️ Apply</button>
      <button class="quick-action" onclick="sendQuickMessage('Contact info')">📞 Contact</button>
      <button class="quick-action" onclick="sendQuickMessage('Grade requirements')">📊 Grades</button>
      <button class="quick-action" onclick="sendQuickMessage('All scholarships')">📚 All</button>
    </div>

    <div class="input-container">
      <input id="messageInput" type="text" placeholder="Ask me about scholarships..." onkeypress="handleKeyPress(event)">
      <button id="sendButton" onclick="sendMessage()">➤</button>
    </div>
  </div>

  <script>
    const messagesContainer = document.getElementById('messagesContainer');
    const input = document.getElementById('messageInput');

    function handleKeyPress(e) {
      if (e.key === 'Enter') sendMessage();
    }

    function sendMessage() {
      const message = input.value.trim();
      if (!message) return;

      fetch('', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ action: 'send_message', message })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) location.reload();
      });
    }

    function sendQuickMessage(msg) {
      input.value = msg;
      sendMessage();
    }

    messagesContainer.scrollTop = messagesContainer.scrollHeight;
  </script>
</body>
</html>
