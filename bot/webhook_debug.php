<?php
// ============================================================
// HopeCoin Telegram Bot Webhook Handler - DEBUG VERSION
// Use this to diagnose issues. Check webhook_logs.txt
// ============================================================

require_once __DIR__ . '/../config.php';

// Log all requests to file for debugging
$log = fopen(__DIR__ . '/webhook_logs.txt', 'a');

// Get webhook payload
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Log the incoming request
fwrite($log, "\n=== " . date('Y-m-d H:i:s') . " ===\n");
fwrite($log, "Raw Input: " . substr($input, 0, 500) . "\n");
fwrite($log, "Parsed Data: " . print_r($data, true) . "\n");

if (!$data) {
    fwrite($log, "ERROR: Could not parse JSON\n");
    fclose($log);
    http_response_code(200);
    exit('OK');
}

try {
    // Test database connection
    $test_db = db();
    fwrite($log, "Database: CONNECTED\n");
} catch (Exception $e) {
    fwrite($log, "Database: FAILED - " . $e->getMessage() . "\n");
    fclose($log);
    http_response_code(200);
    exit('OK');
}

// Extract user & chat info
$chatId = $data['message']['chat']['id'] ?? $data['callback_query']['from']['id'] ?? null;
$userId = $data['message']['from']['id'] ?? $data['callback_query']['from']['id'] ?? null;
$msgId = $data['message']['message_id'] ?? null;
$username = $data['message']['from']['username'] ?? 'user';
$firstName = $data['message']['from']['first_name'] ?? 'User';
$text = $data['message']['text'] ?? '';
$callbackData = $data['callback_query']['data'] ?? '';
$groupType = $data['message']['chat']['type'] ?? 'private';

fwrite($log, "Chat ID: $chatId | User ID: $userId | Text: $text | Command: " . (substr($text, 0, 1) === '/' ? 'YES' : 'NO') . "\n");

// ============================================================
// INITIALIZE USER
// ============================================================
if ($userId) {
    if (!getUser($userId)) {
        fwrite($log, "Creating new user: $userId\n");
        getOrCreateUser($userId, $username, $firstName);
    } else {
        fwrite($log, "User exists: $userId\n");
    }
} else {
    fwrite($log, "WARNING: No User ID found\n");
}

// ============================================================
// PRIVATE CHAT COMMANDS
// ============================================================
if (substr($text, 0, 1) === '/') {
    fwrite($log, "Processing command: $text\n");
    try {
        handleCommand($chatId, $userId, $text);
        fwrite($log, "Command handled successfully\n");
    } catch (Exception $e) {
        fwrite($log, "Command error: " . $e->getMessage() . "\n");
    }
    fclose($log);
    exit('OK');
}

fwrite($log, "No action taken\n");
fclose($log);
http_response_code(200);
exit('OK');

// Include command handlers from main webhook
include __DIR__ . '/webhook_commands.php';
?>
