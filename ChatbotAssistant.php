<?php
// Load environment variables from .env file
function loadEnv($path = '.env') {
    if (!file_exists($path)) {
        // Don't die for AJAX requests - return false instead
        if (isset($_POST['ajax_message'])) {
            return false;
        }
        die("Error: .env file not found. Please create a .env file with your GEMINI_API_KEY.\n");
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // Skip comments
        if (strpos($line, '=') === false) continue; // Skip invalid lines
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        putenv("$name=$value");
        $_ENV[$name] = $value;
    }
    return true;
}

// Load the .env file - try multiple paths
$envPaths = ['.env', __DIR__ . '/.env', __DIR__ . '/../.env'];
$envLoaded = false;
foreach ($envPaths as $envPath) {
    if (file_exists($envPath)) {
        $envLoaded = loadEnv($envPath);
        if ($envLoaded) {
            break;
        }
    }
}
if (!$envLoaded && isset($_POST['ajax_message'])) {
    // For AJAX requests, return JSON error instead of dying
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Configuration error',
        'response' => 'Sorry, the chatbot service is not properly configured. Please contact support.'
    ]);
    exit;
}

// Function to call Gemini API
function callGemini($prompt, $model = 'gemini-2.5-flash', $jsonResponse = false) {
    $apiKey = getenv('GEMINI_API_KEY');
    if (!$apiKey) {
        // Don't die for AJAX requests - return error string instead
        if (isset($_POST['ajax_message'])) {
            return "Error: GEMINI_API_KEY not set in .env file.";
        }
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

// Function to load gym information from gym_info.txt
function loadGymInfo() {
    $gymInfoPaths = ['gym_info.txt', __DIR__ . '/gym_info.txt', __DIR__ . '/../gym_info.txt'];
    foreach ($gymInfoPaths as $path) {
        if (file_exists($path)) {
            $content = file_get_contents($path);
            if ($content !== false) {
                return $content;
            }
        }
    }
    // Return default info if file not found
    return "Power Gym - A fitness space focused on real progress with technology integration.";
}

// Start output buffering to catch any errors
ob_start();

// Include DB.php - try multiple paths
$dbPaths = ['DB.php', __DIR__ . '/DB.php', __DIR__ . '/../DB.php'];
$dbLoaded = false;
foreach ($dbPaths as $dbPath) {
    if (file_exists($dbPath)) {
        require_once $dbPath;
        $dbLoaded = true;
        break;
    }
}
if (!$dbLoaded) {
    if (isset($_POST['ajax_message'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Database file not found',
            'response' => 'Sorry, the chatbot service is not properly configured. Please contact support.'
        ]);
        exit;
    }
    die("Error: DB.php file not found.\n");
}

// Get or create bot user ID
function getBotUserId() {
    global $conn;
    
    // Check if bot user exists (email = 'bot@powergym.com' or similar)
    $botUser = safeSelect('UserProfile', 'User_ID', ['Email' => 'bot@powergym.com']);
    if ($botUser && !empty($botUser) && is_array($botUser)) {
        return (int)$botUser[0]['User_ID'];
    }
    
    // Create bot user if it doesn't exist
    $botData = [
        'Email' => 'bot@powergym.com',
        'Password' => password_hash('bot_user_no_login', PASSWORD_DEFAULT),
        'First_Name' => 'Bubbly',
        'Last_Name' => 'Bot',
        'Role' => 'Bot',
        'Created_at' => date('Y-m-d H:i:s'),
        'Updated_at' => date('Y-m-d H:i:s')
    ];
    $result = safeInsert('UserProfile', $botData);
    if ($result && isset($result['success']) && $result['success'] && isset($result['insert_id'])) {
        return (int)$result['insert_id'];
    }
    
    // Fallback: return 1 if bot user creation fails (assuming user 1 exists)
    error_log('Warning: Failed to create bot user, using fallback ID 1');
    return 1;
}

// Safe wrapper functions that return false instead of dying
function safeSelect($table, $columns = '*', $where = null, $order = '') {
    global $conn;
    try {
        // Check connection first
        if (!isset($conn) || $conn->connect_error) {
            return false;
        }
        
        // Manually build query to avoid die() calls
        if (is_array($columns)) {
            $columns = implode(',', $columns);
        }
        $sql = "SELECT $columns FROM `$table`";
        $params = [];
        $types = '';
        $conditions = [];
        
        if ($where) {
            foreach ($where as $col => $val) {
                $conditions[] = "`$col` = ?";
                $params[] = $val;
            }
        }
        
        // Check if is_deleted column exists
        $checkDeleted = $conn->query("SHOW COLUMNS FROM `$table` LIKE 'is_deleted'");
        if ($checkDeleted && $checkDeleted->num_rows > 0) {
            $conditions[] = "is_deleted = 0";
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        if ($order) {
            // Remove "ORDER BY" if it's already in the string
            $order = trim($order);
            if (stripos($order, 'ORDER BY') === 0) {
                $order = trim(substr($order, 8)); // Remove "ORDER BY"
            }
            // Add backticks around column names for safety
            // Split by comma for multiple columns, then process each
            $orderParts = explode(',', $order);
            $processedParts = [];
            foreach ($orderParts as $part) {
                $part = trim($part);
                // Split by space to separate column name from ASC/DESC
                $partSplit = preg_split('/\s+/', $part, 2);
                $colName = trim($partSplit[0]);
                $direction = isset($partSplit[1]) ? ' ' . trim($partSplit[1]) : '';
                
                // Validate column name (alphanumeric and underscores only)
                if (preg_match('/^[a-zA-Z0-9_]+$/', $colName)) {
                    $processedParts[] = "`$colName`" . $direction;
                } else {
                    error_log("Invalid column name in ORDER BY: " . $colName);
                    return false; // Invalid column name
                }
            }
            if (!empty($processedParts)) {
                $sql .= " ORDER BY " . implode(', ', $processedParts);
            }
        }
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Safe select prepare failed: " . $conn->error);
            return false;
        }
        
        if (!empty($params)) {
            $types = '';
            foreach ($params as $val) {
                if (is_int($val)) $types .= 'i';
                elseif (is_float($val)) $types .= 'd';
                else $types .= 's';
            }
            $stmt->bind_param($types, ...$params);
        }
        
        if (!$stmt->execute()) {
            error_log("Safe select execute failed: " . $stmt->error);
            $stmt->close();
            return false;
        }
        
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    } catch (Exception $e) {
        error_log("Safe select exception: " . $e->getMessage());
        return false;
    }
}

function safeInsert($table, $data) {
    global $conn;
    $errorMsg = '';
    try {
        // Check connection first
        if (!isset($conn) || $conn->connect_error) {
            $errorMsg = 'Database connection failed: ' . ($conn->connect_error ?? 'Unknown error');
            error_log("Safe insert: " . $errorMsg);
            return ['success' => false, 'error' => $errorMsg];
        }
        
        $columns = implode(',', array_map(function($col) { return "`$col`"; }, array_keys($data)));
        $placeholders = str_repeat('?,', count($data) - 1) . '?';
        $sql = "INSERT INTO `$table` ($columns) VALUES ($placeholders)";
        
        // Log SQL for debugging (remove in production)
        error_log("Safe insert SQL: $sql");
        error_log("Safe insert data: " . json_encode($data));
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $errorMsg = "Prepare failed: " . $conn->error . " | SQL: " . $sql;
            error_log("Safe insert prepare failed: " . $errorMsg);
            return ['success' => false, 'error' => $errorMsg];
        }
        
        $types = '';
        $values = array_values($data);
        foreach ($values as $val) {
            if (is_int($val)) $types .= 'i';
            elseif (is_float($val)) $types .= 'd';
            else $types .= 's';
        }
        
        if (!empty($values)) {
            $stmt->bind_param($types, ...$values);
        }
        
        if (!$stmt->execute()) {
            $errorMsg = "Execute failed: " . $stmt->error;
            error_log("Safe insert execute failed: " . $errorMsg);
            $stmt->close();
            return ['success' => false, 'error' => $errorMsg];
        }
        
        $insertId = $conn->insert_id;
        $stmt->close();
        return ['success' => true, 'insert_id' => $insertId];
    } catch (Exception $e) {
        $errorMsg = "Exception: " . $e->getMessage();
        error_log("Safe insert exception: " . $errorMsg);
        return ['success' => false, 'error' => $errorMsg];
    } catch (Error $e) {
        $errorMsg = "Fatal error: " . $e->getMessage();
        error_log("Safe insert fatal error: " . $errorMsg);
        return ['success' => false, 'error' => $errorMsg];
    }
}

// Check database connection before proceeding
if (!isset($conn) || $conn->connect_error) {
    if (isset($_POST['ajax_message'])) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Database connection failed',
            'response' => 'Sorry, the chatbot service is temporarily unavailable. Please try again later.'
        ]);
        exit;
    }
}

session_start();

// Assume user is logged in, get Member_Id
// Try both 'user_id' (from login) and 'member_id' (legacy)
$memberId = $_SESSION['user_id'] ?? $_SESSION['member_id'] ?? null;

// If no user ID, return error for AJAX requests
if (isset($_POST['ajax_message']) && !$memberId) {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'User not logged in',
        'response' => 'Please log in to use the chatbot.'
    ]);
    exit;
}

// Fallback for testing (only if not in production)
if (!$memberId) {
    $memberId = 2; // Default for testing only
}

if (isset($_POST['ajax_message'])) {
    // Clear any output that might have been generated
    ob_clean();
    
    // Set JSON header early
    header('Content-Type: application/json');
    
    // Handle AJAX message
    $userMessage = trim($_POST['ajax_message']);
    
    if (empty($userMessage)) {
        echo json_encode(['error' => 'Message cannot be empty', 'response' => 'Please enter a message.']);
        exit;
    }
    
    // Check if database connection is available
    if (!isset($conn) || $conn->connect_error) {
        echo json_encode([
            'error' => 'Database connection failed',
            'response' => 'Sorry, the chatbot service is temporarily unavailable. Please try again later.'
        ]);
        exit;
    }
    
    try {
        // Get or create conversation using safe functions
        $conversationId = isset($_POST['conv_id']) ? (int)$_POST['conv_id'] : null;
        
        // If conversation ID provided, verify it exists and belongs to this user
        if ($conversationId) {
            $existingConv = safeSelect('Conversation', 'Conversation_ID', [
                'Conversation_ID' => $conversationId,
                'Member_Id' => $memberId,
                'Conversation_Type' => 'chatbot',
                'is_deleted' => 0
            ]);
            if ($existingConv === false || empty($existingConv)) {
                // Invalid conversation ID, reset it
                $conversationId = null;
                error_log('Invalid conversation ID provided: ' . $conversationId);
            }
        }
        
        // Create or get conversation
        if (!$conversationId) {
            $conversation = safeSelect('Conversation', '*', [
                'Member_Id' => $memberId, 
                'Conversation_Type' => 'chatbot', 
                'Is_archived' => 0, 
                'is_deleted' => 0
            ]);
            if ($conversation === false) {
                throw new Exception('Database query failed while checking for conversation');
            }
            if (empty($conversation)) {
                // Need to get bot user ID first for Staff_User_ID (or use 0 if allowed)
                // Check if Staff_User_ID can be 0 or needs a valid user
                $convData = [
                    'Conversation_Type' => 'chatbot',
                    'Is_archived' => 0,
                    'Last_message_at' => date('Y-m-d'),
                    'unread_count_member' => 0,
                    'unread_count_staff' => 0,
                    'Member_Id' => $memberId,
                    'Staff_User_ID' => getBotUserId() // Use bot user ID instead of 0
                ];
                $convResult = safeInsert('Conversation', $convData);
                if (!$convResult || !isset($convResult['success']) || !$convResult['success']) {
                    $errorDetail = isset($convResult['error']) ? $convResult['error'] : ($conn->error ?? 'Unknown error');
                    error_log('Failed to create conversation. Error: ' . $errorDetail . ' | Data: ' . json_encode($convData));
                    throw new Exception('Failed to create conversation. Database error: ' . $errorDetail);
                }
                if (!isset($convResult['insert_id']) || !$convResult['insert_id']) {
                    throw new Exception('Failed to create conversation: No insert ID returned');
                }
                $conversationId = (int)$convResult['insert_id'];
                error_log('Created new conversation with ID: ' . $conversationId);
            } else {
                $conversationId = (int)$conversation[0]['Conversation_ID'];
                error_log('Using existing conversation ID: ' . $conversationId);
            }
        }
        
        // Verify conversation exists before proceeding
        if (!$conversationId || $conversationId <= 0) {
            throw new Exception('Invalid conversation ID: ' . $conversationId);
        }

        // Verify conversation exists in database before inserting message
        $verifyConv = safeSelect('Conversation', 'Conversation_ID', ['Conversation_ID' => $conversationId, 'is_deleted' => 0]);
        if ($verifyConv === false || empty($verifyConv)) {
            error_log('Conversation ID ' . $conversationId . ' does not exist in database');
            throw new Exception('Conversation not found. Please start a new conversation.');
        }
        
        // Save user message
        // Message table schema: Message_ID, Message_Text, Attachment_Url, Attachment_Type, Sent_at (int timestamp), is_read, read_at, is_deleted, deleted_at, Conversation_ID, User_ID
        $msgData = [
            'Message_Text' => $userMessage,
            'Attachment_Url' => 0,
            'Attachment_Type' => 0,
            'Sent_at' => time(), // Unix timestamp
            'is_read' => 0,
            'read_at' => 0,
            'is_deleted' => 0,
            'deleted_at' => 0,
            'Conversation_ID' => $conversationId,
            'User_ID' => $memberId
        ];
        $messageResult = safeInsert('Message', $msgData);
        if (!$messageResult || !isset($messageResult['success']) || !$messageResult['success']) {
            $errorDetail = isset($messageResult['error']) ? $messageResult['error'] : ($conn->error ?? 'Unknown error');
            error_log('Failed to save user message. Error: ' . $errorDetail . ' | Data: ' . json_encode($msgData) . ' | Conversation ID: ' . $conversationId);
            throw new Exception('Failed to save user message. Database error: ' . $errorDetail);
        }

        // Get history
        $messages = safeSelect('Message', '*', ['Conversation_ID' => $conversationId], 'ORDER BY Sent_at ASC');
        if ($messages === false) {
            throw new Exception('Failed to retrieve message history. Database error: ' . ($conn->error ?? 'Unknown error'));
        }
        // Get bot user ID for comparison
        $botUserId = getBotUserId();
        $history = "";
        foreach ($messages as $msg) {
            // Determine sender: if User_ID matches member_id, it's user; if it matches bot user ID, it's bot
            $sender = ($msg['User_ID'] == $memberId) ? 'User' : 'Assistant';
            $history .= "$sender: {$msg['Message_Text']}\n";
        }
        $history .= "User: $userMessage\nAssistant:";

        // Load gym information
        $gymInfo = loadGymInfo();
        
        $prompt = "You are Bubbly, a helpful gym virtual assistant for Power Gym. You have knowledge about fitness, workouts, nutrition, gym equipment, exercise techniques, and general health advice. Respond helpfully and accurately. Keep responses concise but informative.\n\nIMPORTANT - Power Gym Information:\n$gymInfo\n\nUse this information to answer questions about Power Gym's services, membership plans, pricing, hours, coaches, and website features. Always refer to the specific details provided above when answering questions about the gym.\n\nConversation history:\n$history";

        $response = callGemini($prompt);
        
        // Check if response contains an error
        if (is_string($response) && strpos($response, 'Error:') === 0) {
            error_log('Gemini API error: ' . $response);
            
            // More specific error messages based on error type
            $userMessage = 'Sorry, I encountered an error while processing your message. Please try again later.';
            
            if (strpos($response, 'API request failed') !== false) {
                $userMessage = 'Sorry, the AI service is temporarily unavailable. Please try again in a few moments.';
            } elseif (strpos($response, 'GEMINI_API_KEY') !== false) {
                $userMessage = 'Sorry, the chatbot service is not properly configured. Please contact support.';
            } elseif (strpos($response, 'Invalid JSON') !== false) {
                $userMessage = 'Sorry, there was an error processing the AI response. Please try again.';
            }
            
            echo json_encode([
                'error' => $response,
                'response' => $userMessage,
                'conv_id' => $conversationId
            ]);
            exit;
        }
        
        // Check if response is empty or invalid
        if (empty($response) || !is_string($response)) {
            error_log('Invalid response from Gemini: ' . var_export($response, true));
            echo json_encode([
                'error' => 'Invalid response from AI',
                'response' => 'Sorry, I received an invalid response. Please try again.',
                'conv_id' => $conversationId
            ]);
            exit;
        }

        // Save bot message
        // Get bot user ID (create if doesn't exist)
        $botUserId = getBotUserId();
        $botMsgData = [
            'Message_Text' => $response,
            'Attachment_Url' => 0,
            'Attachment_Type' => 0,
            'Sent_at' => time(), // Unix timestamp
            'is_read' => 0,
            'read_at' => 0,
            'is_deleted' => 0,
            'deleted_at' => 0,
            'Conversation_ID' => $conversationId,
            'User_ID' => $botUserId // Bot user ID
        ];
        $botMsgResult = safeInsert('Message', $botMsgData);
        if (!$botMsgResult || !isset($botMsgResult['success']) || !$botMsgResult['success']) {
            error_log('Failed to save bot message. Error: ' . (isset($botMsgResult['error']) ? $botMsgResult['error'] : 'Unknown error'));
            // Don't throw - bot message save failure shouldn't prevent response
        }

        // Return JSON - ensure output is clean
        ob_clean();
        echo json_encode(['response' => $response, 'conv_id' => $conversationId]);
        exit;
        
    } catch (Exception $e) {
        ob_clean();
        $errorMsg = $e->getMessage();
        $errorTrace = $e->getTraceAsString();
        error_log('Chatbot error: ' . $errorMsg . ' | Trace: ' . $errorTrace);
        
        // More specific error messages
        $userMessage = 'Sorry, there was an error processing your message. Please try again later.';
        
        if (strpos($errorMsg, 'Failed to create conversation') !== false) {
            $userMessage = 'Sorry, there was an error creating the conversation. Please try again.';
        } elseif (strpos($errorMsg, 'Failed to save') !== false) {
            $userMessage = 'Sorry, there was an error saving your message. Please try again.';
        } elseif (strpos($errorMsg, 'database') !== false || strpos($errorMsg, 'DB') !== false || strpos($errorMsg, 'Database') !== false) {
            $userMessage = 'Sorry, there was a database error. Please try again later.';
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'error' => $errorMsg,
            'response' => $userMessage,
            'conv_id' => $conversationId ?? null
        ]);
        exit;
    } catch (Error $e) {
        // Catch PHP 7+ fatal errors
        ob_clean();
        $errorMsg = $e->getMessage();
        error_log('Chatbot fatal error: ' . $errorMsg . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine());
        
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Fatal error occurred',
            'response' => 'Sorry, there was an unexpected error. Please try again later.',
            'conv_id' => $conversationId ?? null
        ]);
        exit;
    } catch (Throwable $e) {
        // Catch any other errors
        ob_clean();
        $errorMsg = $e->getMessage();
        error_log('Chatbot throwable error: ' . $errorMsg . ' | Type: ' . get_class($e));
        
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Unexpected error',
            'response' => 'Sorry, there was an unexpected error. Please try again later.',
            'conv_id' => $conversationId ?? null
        ]);
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
            'Staff_User_ID' => 0 // 0 = no staff assigned (chatbot conversation)
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
        // Message table schema: Message_ID, Message_Text, Attachment_Url, Attachment_Type, Sent_at (int timestamp), is_read, read_at, is_deleted, deleted_at, Conversation_ID, User_ID
        $msgData = [
            'Message_Text' => $userMessage,
            'Attachment_Url' => 0,
            'Attachment_Type' => 0,
            'Sent_at' => time(), // Unix timestamp
            'is_read' => 0,
            'read_at' => 0,
            'is_deleted' => 0,
            'deleted_at' => 0,
            'Conversation_ID' => $conversationId,
            'User_ID' => $memberId
        ];
        insert('Message', $msgData);

        // Get bot user ID for comparison
        $botUserId = getBotUserId();
        // Build prompt with history
        $history = "";
        foreach ($messages as $msg) {
            // Determine sender: if User_ID matches member_id, it's user; if it matches bot user ID, it's bot
            $sender = ($msg['User_ID'] == $memberId) ? 'User' : 'Assistant';
            $history .= "$sender: {$msg['Message_Text']}\n";
        }
        $history .= "User: $userMessage\nAssistant:";

        // Load gym information
        $gymInfo = loadGymInfo();
        
        $prompt = "You are Bubbly, a helpful gym virtual assistant for Power Gym. You have knowledge about fitness, workouts, nutrition, gym equipment, exercise techniques, and general health advice. Respond helpfully and accurately. Keep responses concise but informative.\n\nIMPORTANT - Power Gym Information:\n$gymInfo\n\nUse this information to answer questions about Power Gym's services, membership plans, pricing, hours, coaches, and website features. Always refer to the specific details provided above when answering questions about the gym.\n\nConversation history:\n$history";

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
            <?php 
            // Get bot user ID for comparison
            $botUserId = getBotUserId();
            foreach ($messages as $msg): ?>
                <?php 
                // Determine sender: if User_ID matches member_id, it's user; if it matches bot user ID, it's bot
                $sender = ($msg['User_ID'] == $memberId) ? 'user' : 'bot';
                $senderName = ($msg['User_ID'] == $memberId) ? 'User' : 'Bubbly';
                ?>
                <div class="message <?php echo $sender; ?>">
                    <strong><?php echo $senderName; ?>:</strong> <?php echo htmlspecialchars($msg['Message_Text']); ?>
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