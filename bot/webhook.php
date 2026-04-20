<?php
// ============================================================
// HopeCoin Telegram Bot Webhook Handler
// Uses file_get_contents() instead of cURL for InfinityFree compatibility
// ============================================================

require_once __DIR__ . '/../config.php';

// Get webhook payload
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
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
$groupId = $data['message']['chat']['id'] ?? null;
$groupType = $data['message']['chat']['type'] ?? 'private';

// ============================================================
// TELEGRAM API FUNCTION (uses fsockopen - NO cURL needed)
// ============================================================
function tgApi($method, $params = []) {
    $token = BOT_TOKEN;
    $host = 'api.telegram.org';
    $port = 443;
    
    $json = json_encode($params);
    $path = "/bot{$token}/{$method}";
    
    // Build raw HTTPS request
    $out = "POST {$path} HTTP/1.1\r\n";
    $out .= "Host: {$host}\r\n";
    $out .= "Content-Type: application/json\r\n";
    $out .= "Content-Length: " . strlen($json) . "\r\n";
    $out .= "Connection: Close\r\n";
    $out .= "\r\n";
    $out .= $json;
    
    // Connect via SSL socket
    $fp = @fsockopen('ssl://' . $host, $port, $errno, $errstr, 10);
    
    if (!$fp) {
        return ['ok' => false, 'error' => 'Connection failed'];
    }
    
    // Send request
    fwrite($fp, $out);
    
    // Read response
    $response = '';
    while (!feof($fp)) {
        $response .= fgets($fp, 128);
    }
    fclose($fp);
    
    // Parse HTTP response body
    $parts = explode("\r\n\r\n", $response, 2);
    $body = $parts[1] ?? '';
    
    return json_decode($body, true) ?: ['ok' => false];
}

// ============================================================
// MESSAGE FUNCTIONS
// ============================================================
function sendMsg($chatId, $text, $keyboard = null) {
    $params = [
        'chat_id'    => $chatId,
        'text'       => $text,
        'parse_mode' => 'HTML'
    ];
    
    if ($keyboard) {
        $params['reply_markup'] = json_encode($keyboard);
    }
    
    return tgApi('sendMessage', $params);
}

function inlineBtn($rows) {
    return ['inline_keyboard' => $rows];
}

function deleteMsg($chatId, $msgId) {
    return tgApi('deleteMessage', ['chat_id' => $chatId, 'message_id' => $msgId]);
}

// ============================================================
// INITIALIZE USER
// ============================================================
if ($userId && !getUser($userId)) {
    getOrCreateUser($userId, $username, $firstName);
}

// ============================================================
// GROUP MANAGEMENT
// ============================================================
if ($groupType === 'group' || $groupType === 'supergroup') {
    handleGroupMessage($groupId, $userId, $text, $data);
    http_response_code(200);
    exit('OK');
}

// ============================================================
// PRIVATE CHAT COMMANDS
// ============================================================
if (substr($text, 0, 1) === '/') {
    handleCommand($chatId, $userId, $text);
    http_response_code(200);
    exit('OK');
}

// ============================================================
// CALLBACK QUERIES (Button Clicks)
// ============================================================
if (!empty($callbackData)) {
    handleCallback($userId, $callbackData, $data['callback_query']['id'] ?? null);
    http_response_code(200);
    exit('OK');
}

http_response_code(200);
exit('OK');

// ============================================================
// COMMAND HANDLERS
// ============================================================
function handleCommand($chatId, $userId, $text) {
    $cmd = explode(' ', $text)[0];
    
    switch ($cmd) {
        case '/start':
            cmdStart($chatId, $userId);
            break;
        case '/help':
            cmdHelp($chatId);
            break;
        case '/balance':
            cmdBalance($chatId, $userId);
            break;
        case '/profile':
            cmdProfile($chatId, $userId);
            break;
        case '/tasks':
            cmdTasks($chatId, $userId);
            break;
        case '/daily':
            cmdDaily($chatId, $userId);
            break;
        case '/leaderboard':
            cmdLeaderboard($chatId, $userId);
            break;
        case '/friend':
            cmdFriend($chatId, $userId);
            break;
        case '/admin':
            cmdAdmin($chatId, $userId);
            break;
        default:
            sendMsg($chatId, "❓ Unknown command. Use /help for commands.");
    }
}

function cmdStart($chatId, $userId) {
    $u = getUser($userId);
    $appUrl = MINI_APP_URL;
    
    $welcome = "🎮 <b>Welcome to HopeCoin!</b>\n\n";
    $welcome .= "🪙 Play games, earn coins, and climb the leaderboard!\n\n";
    $welcome .= "💰 Your Balance: <b>".$u['coins']." 🪙</b>\n";
    $welcome .= "🏆 Level: <b>".$u['level']."</b>\n\n";
    $welcome .= "📱 <b>Open the App Below to Play!</b>\n\n";
    $welcome .= "<i>Use /help for all commands</i>";
    
    $keyboard = [
        [['text' => '🎮 Open App', 'web_app' => ['url' => $appUrl]]],
        [['text' => '👥 Referral'], ['text' => '🏆 Leaderboard']],
    ];
    
    sendMsg($chatId, $welcome, inlineBtn($keyboard));
}

function cmdHelp($chatId) {
    $help = "<b>🎮 HopeCoin Commands</b>\n\n";
    $help .= "<b>/start</b> - Welcome message\n";
    $help .= "<b>/balance</b> - Check coins\n";
    $help .= "<b>/profile</b> - Your profile\n";
    $help .= "<b>/tasks</b> - View tasks\n";
    $help .= "<b>/daily</b> - Daily bonus\n";
    $help .= "<b>/leaderboard</b> - Top 10 players\n";
    $help .= "<b>/friend</b> - Referral link\n\n";
    $help .= "🎮 <b>Play in the app to earn more!</b>";
    
    sendMsg($chatId, $help);
}

function cmdBalance($chatId, $userId) {
    $u = getUser($userId);
    $msg = "💰 <b>Your Balance</b>\n\n";
    $msg .= "🪙 Coins: <b>".$u['coins']."</b>\n";
    $msg .= "📊 Total Earned: <b>".$u['total_coins']."</b>\n";
    $msg .= "🏆 Level: <b>".$u['level']."</b>\n";
    $msg .= "🎮 Games Played: <b>".$u['games_played']."</b>\n";
    $msg .= "🏅 Games Won: <b>".$u['games_won']."</b>";
    
    sendMsg($chatId, $msg);
}

function cmdProfile($chatId, $userId) {
    $u = getUser($userId);
    
    $msg = "👤 <b>Your Profile</b>\n\n";
    $msg .= "<b>".$u['full_name']."</b> (@".$u['username'].")\n";
    $msg .= "ID: <code>".$userId."</code>\n\n";
    $msg .= "🪙 Balance: <b>".$u['coins']." coins</b>\n";
    $msg .= "⭐ Level: <b>".$u['level']."</b>\n";
    $msg .= "👥 Referrals: <b>".$u['referrals']."</b>\n";
    $msg .= "🎮 Games: <b>".$u['games_played']." played, ".$u['games_won']." won</b>";
    
    sendMsg($chatId, $msg);
}

function cmdTasks($chatId, $userId) {
    try {
        $s = db()->prepare('SELECT * FROM tasks WHERE active=1 LIMIT 10');
        $s->execute();
        $tasks = $s->fetchAll();
        
        $msg = "✅ <b>Available Tasks</b>\n\n";
        if (count($tasks) > 0) {
            foreach ($tasks as $t) {
                $msg .= "• ".$t['title']."\n";
                $msg .= "   💰 Reward: ".$t['reward']." coins\n";
            }
        } else {
            $msg .= "No tasks available right now.";
        }
        $msg .= "\n\n📱 Claim tasks in the app!";
    } catch (Exception $e) {
        $msg = "❌ Could not load tasks.";
    }
    
    sendMsg($chatId, $msg);
}

function cmdDaily($chatId, $userId) {
    try {
        $u = getUser($userId);
        $last = $u['daily_claimed_at'] ?? null;
        $now = time();
        
        if ($last && ($now - strtotime($last)) < 86400) {
            $nextH = ceil((86400 - ($now - strtotime($last))) / 3600);
            sendMsg($chatId, "⏳ Daily bonus already claimed! Come back in <b>".$nextH." hours</b>");
            return;
        }
        
        $streak = $last ? ((int)$u['streak'] + 1) : 1;
        $bonus = min(200 + ($streak - 1) * 50, 1000);
        
        db()->prepare('UPDATE users SET daily_claimed_at=NOW(), streak=? WHERE id=?')->execute([$streak, $userId]);
        adjustCoins($userId, $bonus, true);
        
        $msg = "🎉 <b>Daily Bonus Claimed!</b>\n\n";
        $msg .= "🪙 +<b>".$bonus." coins</b>\n";
        $msg .= "🔥 Streak: <b>".$streak." days</b>\n\n";
        $msg .= "Come back tomorrow for more!";
        
        sendMsg($chatId, $msg);
    } catch (Exception $e) {
        sendMsg($chatId, "❌ Error claiming daily bonus.");
    }
}

function cmdLeaderboard($chatId, $userId) {
    try {
        $s = db()->prepare('SELECT id, full_name, coins, level FROM users WHERE banned=0 ORDER BY coins DESC LIMIT 10');
        $s->execute();
        $users = $s->fetchAll();
        
        $msg = "🏆 <b>Top 10 Leaderboard</b>\n\n";
        $i = 1;
        foreach ($users as $user) {
            $medal = ['🥇', '🥈', '🥉'];
            $emoji = $medal[$i-1] ?? ($i.'.');
            $msg .= $emoji." ".$user['full_name']." - <b>".$user['coins']." 🪙</b>\n";
            $i++;
        }
    } catch (Exception $e) {
        $msg = "❌ Could not load leaderboard.";
    }
    
    sendMsg($chatId, $msg);
}

function cmdFriend($chatId, $userId) {
    $refLink = "https://t.me/".BOT_USERNAME."?start=ref_{$userId}";
    $msg = "👥 <b>Invite Friends!</b>\n\n";
    $msg .= "Share your link and earn <b>500 coins</b> per friend!\n\n";
    $msg .= "<code>".$refLink."</code>\n\n";
    $msg .= "Tap to copy the link 👆";
    
    sendMsg($chatId, $msg);
}

function cmdAdmin($chatId, $userId) {
    if ((int)$userId !== (int)ADMIN_ID) {
        sendMsg($chatId, "❌ Only admin can use this.");
        return;
    }
    
    $msg = "🔧 <b>Admin Panel</b>\n\n";
    $msg .= "🌐 <a href='".MINI_APP_URL."/admin'>Open Admin Dashboard</a>";
    
    sendMsg($chatId, $msg);
}

// ============================================================
// CALLBACK HANDLER
// ============================================================
function handleCallback($userId, $data, $queryId) {
    $parts = explode('_', $data);
    $action = $parts[0] ?? '';
    
    tgApi('answerCallbackQuery', [
        'callback_query_id' => $queryId,
        'text' => 'Processing...'
    ]);
}

// ============================================================
// GROUP MESSAGE HANDLER
// ============================================================
function handleGroupMessage($groupId, $userId, $text, $data) {
    // Welcome on join
    if (!empty($data['message']['new_chat_members'])) {
        foreach ($data['message']['new_chat_members'] as $member) {
            $welcome = "👋 Welcome <b>".$member['first_name']."</b> to HopeCoin Bot!\n\n";
            $welcome .= "🎮 Play games and earn coins!\n";
            $welcome .= "📱 Use /start to open the app!";
            sendMsg($groupId, $welcome);
        }
    }
}

?>
