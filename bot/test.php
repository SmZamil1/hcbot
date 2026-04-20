<?php
// ============================================================
// HopeCoin Bot - Quick Test Script (fsockopen version)
// Open this in browser to verify everything works
// ============================================================

require_once __DIR__ . '/../config.php';

echo "<style>body { font-family: Arial; margin: 20px; } .success { color: green; } .error { color: red; } .warn { color: orange; } code { background: #f0f0f0; padding: 2px 5px; } hr { margin: 20px 0; }</style>";

echo "<h1>HopeCoin Bot - Diagnostic Test</h1>";
echo "<hr>";

// Test 1: Database Connection
echo "<h2>1. Database Connection</h2>";
try {
    $pdo = db();
    echo "<p class='success'>✓ Database connected successfully</p>";
    
    // Count users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "<p>Users in database: " . $result['count'] . "</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Database connection failed: " . $e->getMessage() . "</p>";
}

echo "<hr>";

// Test 2: Telegram API via fsockopen
echo "<h2>2. Telegram API Connection (fsockopen)</h2>";
$token = BOT_TOKEN;
echo "<p>Bot Token: " . substr($token, 0, 20) . "...</p>";

// Raw socket function
function testTgApi($method, $params = []) {
    $token = BOT_TOKEN;
    $host = 'api.telegram.org';
    $port = 443;
    
    $json = json_encode($params);
    $path = "/bot{$token}/{$method}";
    
    $out = "POST {$path} HTTP/1.1\r\n";
    $out .= "Host: {$host}\r\n";
    $out .= "Content-Type: application/json\r\n";
    $out .= "Content-Length: " . strlen($json) . "\r\n";
    $out .= "Connection: Close\r\n";
    $out .= "\r\n";
    $out .= $json;
    
    $fp = @fsockopen('ssl://' . $host, $port, $errno, $errstr, 10);
    
    if (!$fp) {
        return ['ok' => false, 'error' => 'fsockopen failed: ' . $errstr];
    }
    
    fwrite($fp, $out);
    $response = '';
    while (!feof($fp)) {
        $response .= fgets($fp, 128);
    }
    fclose($fp);
    
    $parts = explode("\r\n\r\n", $response, 2);
    $body = $parts[1] ?? '';
    
    return json_decode($body, true) ?: ['ok' => false, 'raw' => $body];
}

$result = testTgApi('getMe', []);

if ($result['ok'] ?? false) {
    echo "<p class='success'>✓ Telegram API working (fsockopen)</p>";
    echo "<p>Bot: @" . $result['result']['username'] . " (ID: " . $result['result']['id'] . ")</p>";
} else {
    echo "<p class='error'>✗ Telegram API failed</p>";
    echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>";
}

echo "<hr>";

// Test 3: Send test message to admin
echo "<h2>3. Send Test Message</h2>";
$adminId = ADMIN_ID;
echo "<p>Sending test message to admin (ID: $adminId)...</p>";

$msg = "🧪 HopeCoin bot test successful at " . date('Y-m-d H:i:s');
$sendResult = testTgApi('sendMessage', [
    'chat_id' => $adminId,
    'text' => $msg,
    'parse_mode' => 'HTML'
]);

if ($sendResult['ok'] ?? false) {
    echo "<p class='success'>✓ Test message sent successfully!</p>";
    echo "<p>Check your Telegram for the test message.</p>";
} else {
    echo "<p class='error'>✗ Failed to send test message</p>";
    echo "<pre>" . json_encode($sendResult, JSON_PRETTY_PRINT) . "</pre>";
}

echo "<hr>";
echo "<p><small>For security, delete test.php after testing. The webhook is now ready to receive messages.</small></p>";
?>
