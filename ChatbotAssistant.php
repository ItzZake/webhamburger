<?php
// Load environment variables from .env file
function loadEnv($path = '.env') {
    if (!file_exists($path)) {
        die("Error: .env file not found. Please create a .env file with your GEMINI_API_KEY.\n");
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // Skip comments
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        putenv("$name=$value");
        $_ENV[$name] = $value;
    }
}

// Load the .env file
loadEnv();

// Function to call Gemini API
function callGemini($prompt, $model = 'gemini-2.5-flash', $jsonResponse = false) {
    $apiKey = getenv('GEMINI_API_KEY');
    if (!$apiKey) {
        die("Error: GEMINI_API_KEY not set in .env file.\n");
    }

    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ],
        'generationConfig' => [
            'maxOutputTokens' => 1000
        ]
    ];

    if ($jsonResponse) {
        $data['generationConfig']['responseMimeType'] = 'application/json';
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        return "Error: API request failed with status {$httpCode}. Response: {$response}";
    }

    $result = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return "Error: Invalid JSON response from API.";
    }

    $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? 'Error: No response text found.';

    if ($jsonResponse) {
        // Parse the JSON response
        $parsed = json_decode($text, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return "Error: Invalid JSON from LLM.";
        }
        return $parsed;
    }

    return $text;
}

require_once 'DB.php'; // Include the DB functions

session_start();

// Assume user is logged in, get Member_Id
$memberId = $_SESSION['member_id'] ?? 2; // Default to 1 for testing

if (isset($_POST['ajax_message'])) {
    // Handle AJAX message
    $userMessage = trim($_POST['ajax_message']);
    if (!empty($userMessage)) {
        // Get or create conversation
        $conversationId = isset($_POST['conv_id']) ? (int)$_POST['conv_id'] : null;
        if (!$conversationId) {
            $conversation = select('Conversation', '*', ['Member_Id' => $memberId, 'Conversation_Type' => 'chatbot', 'Is_archived' => 0, 'is_deleted' => 0]);
            if (empty($conversation)) {
                $convData = [
                    'Conversation_Type' => 'chatbot',
                    'Is_archived' => 0,
                    'Last_message_at' => date('Y-m-d'),
                    'unread_count_member' => 0,
                    'unread_count_staff' => 0,
                    'Member_Id' => $memberId,
                    'Staff_User_ID' => null
                ];
                $conversationId = insert('Conversation', $convData);
            } else {
                $conversationId = $conversation[0]['Conversation_ID'];
            }
        }

        // Save user message
        $msgData = [
            'Sender' => 'user',
            'Message_Text' => $userMessage,
            'Sent_at' => date('Y-m-d H:i:s'),
            'Conversation_ID' => $conversationId,
            'User_ID' => $memberId
        ];
        insert('Message', $msgData);

        // Get history
        $messages = select('Message', '*', ['Conversation_ID' => $conversationId], 'ORDER BY Sent_at ASC');
        $history = "";
        foreach ($messages as $msg) {
            $sender = $msg['Sender'] === 'user' ? 'User' : 'Assistant';
            $history .= "$sender: {$msg['Message_Text']}\n";
        }
        $history .= "User: $userMessage\nAssistant:";

        $prompt = "You are a helpful gym virtual assistant called Bubbly. You have knowledge about fitness, workouts, nutrition, gym equipment, exercise techniques, and general health advice. Respond helpfully and accurately. Keep responses concise but informative.\n\nConversation history:\n$history";

        $response = callGemini($prompt);

        // Save bot message
        $botMsgData = [
            'Sender' => 'Bubbly',
            'Message_Text' => $response,
            'Sent_at' => date('Y-m-d H:i:s'),
            'Conversation_ID' => $conversationId,
            'User_ID' => null
        ];
        insert('Message', $botMsgData);

        // Return JSON
        header('Content-Type: application/json');
        echo json_encode(['response' => $response, 'conv_id' => $conversationId]);
        exit;
    }
}

// Get conversation ID from URL or find/create
$conversationId = isset($_GET['conv']) ? (int)$_GET['conv'] : null;
if (!$conversationId) {
    $conversation = select('Conversation', '*', ['Member_Id' => $memberId, 'Conversation_Type' => 'chatbot', 'Is_archived' => 0, 'is_deleted' => 0]);
    if (empty($conversation)) {
        // Create new conversation
        $convData = [
            'Conversation_Type' => 'chatbot',
            'Is_archived' => 0,
            'Last_message_at' => date('Y-m-d'),
            'unread_count_member' => 0,
            'unread_count_staff' => 0,
            'Member_Id' => $memberId,
            'Staff_User_ID' => null
        ];
        $conversationId = insert('Conversation', $convData);
    } else {
        $conversationId = $conversation[0]['Conversation_ID'];
    }
}

$messages = [];
if ($conversationId) {
    $messages = select('Message', '*', ['Conversation_ID' => $conversationId], 'ORDER BY Sent_at ASC');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_conversation'])) {
    // Create new conversation
    $convData = [
        'Conversation_Type' => 'chatbot',
        'Is_archived' => 0,
        'Last_message_at' => date('Y-m-d'),
        'unread_count_member' => 0,
        'unread_count_staff' => 0,
        'Member_Id' => $memberId,
        'Staff_User_ID' => null
    ];
    $newConversationId = insert('Conversation', $convData);
    // Redirect to the new conversation
    header("Location: " . $_SERVER['PHP_SELF'] . "?conv=" . $newConversationId);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_conversation'])) {
    // Soft delete current conversation
    update('Conversation', ['is_deleted' => 1], ['Conversation_ID' => $conversationId]);
    // Redirect to start new
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $userMessage = trim($_POST['message']);
    if (!empty($userMessage)) {
        // Save user message
        $msgData = [
            'Sender' => 'user',
            'Message_Text' => $userMessage,
            'Sent_at' => date('Y-m-d H:i:s'),
            'Conversation_ID' => $conversationId,
            'User_ID' => $memberId
        ];
        insert('Message', $msgData);

        // Build prompt with history
        $history = "";
        foreach ($messages as $msg) {
            $sender = $msg['Sender'] === 'user' ? 'User' : 'Assistant';
            $history .= "$sender: {$msg['Message_Text']}\n";
        }
        $history .= "User: $userMessage\nAssistant:";

        $prompt = "You are a helpful gym virtual assistant called Bubbly. You have knowledge about fitness, workouts, nutrition, gym equipment, exercise techniques, and general health advice. Respond helpfully and accurately. Keep responses concise but informative.\n\nConversation history:\n$history";

        $response = callGemini($prompt);

        // Save bot message
        $botMsgData = [
            'Sender' => 'Bubbly',
            'Message_Text' => $response,
            'Sent_at' => date('Y-m-d H:i:s'),
            'Conversation_ID' => $conversationId,
            'User_ID' => null
        ];
        insert('Message', $botMsgData);

        // Redirect to avoid resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym Virtual Assistant</title>
    <link rel="stylesheet" href="Home.css">
    <style>
        .chat-container { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ccc; border-radius: 10px; }
        .message { margin: 10px 0; padding: 10px; border-radius: 5px; background: #d169e1ff; }
        .user { background: #e1f5fe; text-align: right; }
        .bot { background: #d169e1ff; }
        .input-form { display: flex; margin-top: 20px; }
        .input-form input { flex: 1; padding: 10px; }
        .input-form button { padding: 10px; background: #9a6afbff; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <div class="chat-container">
        <h1>Gym Virtual Assistant</h1>
        <form method="post" style="margin-bottom: 10px;">
            <button type="submit" name="new_conversation" value="1" style="background: #007bff; color: white; border: none; padding: 5px 10px; cursor: pointer;">Start New Conversation</button>
            <?php if ($conversationId): ?>
                <button type="submit" name="delete_conversation" value="1" style="background: #dc3545; color: white; border: none; padding: 5px 10px; cursor: pointer; margin-left: 10px;" onclick="return confirm('Are you sure you want to delete this conversation?')">Delete Conversation</button>
            <?php endif; ?>
        </form>
        <div id="chat">
            <?php foreach ($messages as $msg): ?>
                <div class="message <?php echo $msg['Sender']; ?>">
                    <strong><?php echo ucfirst($msg['Sender']); ?>:</strong> <?php echo htmlspecialchars($msg['Message_Text']); ?>
                </div>
            <?php endforeach; ?>
        </div>
        <form class="input-form" method="post">
            <input type="text" name="message" placeholder="Ask me anything about fitness..." required>
            <button type="submit">Send</button>
        </form>
    </div>
</body>
</html>