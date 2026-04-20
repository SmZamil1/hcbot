<?php
// ============================================================
// HopeCoin Config — hopecoinbot.42web.io
// EDIT DB CREDENTIALS TO MATCH YOUR INFINITYFREE CPANEL
// ============================================================

define('DB_HOST', 'sql202.infinityfree.com'); // InfinityFree MySQL host
define('DB_NAME', 'if0_37959419_hopecoin');   // Your DB name from cPanel
define('DB_USER', 'if0_37959419');            // Your DB username
define('DB_PASS', 'SmZamil37');               // Your DB password

define('BOT_TOKEN', '8717172949:AAE926lCYq48dOPP8I5Y3EFNSL-3m_dWWDs');
define('ADMIN_ID',  '6167568466');
define('MINI_APP_URL', 'https://hopecoinbot.42web.io');
define('BOT_USERNAME', 'hopecoinappbot');
define('TG_API', 'https://api.telegram.org/bot' . BOT_TOKEN);

// Game settings
define('DAILY_PLAY_LIMIT', 10);    // Free plays per day per game
define('BET_MULTIPLIER', 1.8);     // Win = bet * multiplier
define('REFERRAL_REWARD', 500);

// Leaderboard prizes (top 10) — coins
define('DAILY_PRIZES',  [3000,2000,1500,1000,800,600,400,300,200,100]);
define('WEEKLY_PRIZES', [10000,7000,5000,3000,2000,1500,1000,700,500,300]);
define('REFS_PRIZES',   [5000,3000,2000,1500,1000,800,600,400,300,200]);

// ============================================================
// DB Connection (singleton)
// ============================================================
function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    try {
        $pdo = new PDO(
            'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
             PDO::ATTR_EMULATE_PREPARES => false]
        );
    } catch (PDOException $e) {
        http_response_code(500);
        die(json_encode(['error' => 'DB connection failed']));
    }
    return $pdo;
}

// ============================================================
// Settings Helpers
// ============================================================
function getSetting(string $key, string $default = ''): string {
    try {
        $s = db()->prepare('SELECT value FROM settings WHERE key_name=?');
        $s->execute([$key]);
        $r = $s->fetchColumn();
        return $r !== false ? $r : $default;
    } catch (\Exception $e) {
        return $default;
    }
}

function setSetting(string $key, string $value): void {
    db()->prepare('INSERT INTO settings (key_name,value) VALUES(?,?) ON DUPLICATE KEY UPDATE value=?')
        ->execute([$key, $value, $value]);
}

// ============================================================
// Telegram API Helpers
// ============================================================
function corsHeaders(): void {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;
}

function tgRequest(string $method, array $data = []): array {
    $url = TG_API . '/' . $method;
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($data),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res ?: '{}', true) ?: [];
}

function sendMsg(string|int $chatId, string $text, array $keyboard = null): array {
    $p = ['chat_id'=>$chatId,'text'=>$text,'parse_mode'=>'HTML'];
    if ($keyboard) $p['reply_markup'] = json_encode($keyboard);
    return tgRequest('sendMessage', $p);
}

function sendPhoto(string|int $chatId, string $photo, string $caption, array $keyboard = null): array {
    $p = ['chat_id'=>$chatId,'photo'=>$photo,'caption'=>$caption,'parse_mode'=>'HTML'];
    if ($keyboard) $p['reply_markup'] = json_encode($keyboard);
    return tgRequest('sendPhoto', $p);
}

function deleteMsg(string|int $chatId, int $msgId): void {
    tgRequest('deleteMessage', ['chat_id'=>$chatId,'message_id'=>$msgId]);
}

function inlineBtn(array $rows): array {
    return ['inline_keyboard' => $rows];
}

function jsonOut(array $data): never {
    header('Content-Type: application/json');
    die(json_encode($data, JSON_UNESCAPED_UNICODE));
}

// ============================================================
// User Functions
// ============================================================
function getUser(int $uid): ?array {
    $s = db()->prepare('SELECT * FROM users WHERE id=?');
    $s->execute([$uid]);
    $u = $s->fetch();
    return $u ?: null;
}

function getOrCreateUser(int $uid, string $un, string $name): array {
    $u = getUser($uid);
    if ($u) return $u;
    db()->prepare('INSERT IGNORE INTO users (id,username,full_name) VALUES(?,?,?)')
        ->execute([$uid, $un, $name]);
    return getUser($uid) ?? ['id'=>$uid,'username'=>$un,'full_name'=>$name,'coins'=>0,'total_coins'=>0,'level'=>1,'taps'=>0];
}

function adjustCoins(int $uid, int $amount, bool $isEarn): void {
    if ($isEarn) {
        db()->prepare('UPDATE users SET coins=coins+?, total_coins=total_coins+? WHERE id=?')
            ->execute([$amount, $amount, $uid]);
    } else {
        db()->prepare('UPDATE users SET coins=MAX(0,coins+?) WHERE id=?')
            ->execute([$amount, $uid]);
    }
}

function recordGamePlay(int $uid, string $game, int $bet, string $result, int $coins): void {
    db()->prepare('INSERT INTO game_plays (user_id,game,bet,result,coins_change) VALUES(?,?,?,?,?)')
        ->execute([$uid, $game, $bet, $result, $coins]);
    if ($result === 'win') {
        db()->prepare('UPDATE users SET games_won=games_won+1 WHERE id=?')->execute([$uid]);
    }
    db()->prepare('UPDATE users SET games_played=games_played+1 WHERE id=?')->execute([$uid]);
}

function canPlayGame(int $uid, string $game, int $bet): array {
    if ($bet > 0) return ['ok' => true, 'free_plays_left' => 0];
    
    $today = date('Y-m-d');
    $s = db()->prepare('SELECT COUNT(*) as cnt FROM game_plays WHERE user_id=? AND game=? AND DATE(created_at)=?');
    $s->execute([$uid, $game, $today]);
    $count = (int)($s->fetchColumn() ?? 0);
    $limit = DAILY_PLAY_LIMIT;
    
    if ($count >= $limit) {
        return ['ok' => false, 'limit' => $limit, 'free_plays_left' => 0];
    }
    return ['ok' => true, 'free_plays_left' => $limit - $count];
}

// ============================================================
// Leaderboard Functions
// ============================================================
function resetLeaderboard(string $type): void {
    $col = match($type) {
        'daily'  => 'day_coins',
        'weekly' => 'week_coins',
        'refs'   => 'refs',
        default  => 'week_coins'
    };
    
    if ($type === 'daily' || $type === 'weekly') {
        $reset_col = $type === 'daily' ? 'day_coins' : 'week_coins';
        db()->query("UPDATE users SET {$reset_col}=0");
    }
    
    $next_reset = $type === 'daily' 
        ? date('Y-m-d H:i:s', strtotime('+1 day'))
        : date('Y-m-d H:i:s', strtotime('+1 week'));
    
    db()->prepare('INSERT INTO leaderboard_config (board_type,next_reset) VALUES(?,?) ON DUPLICATE KEY UPDATE next_reset=?')
        ->execute([$type, $next_reset, $next_reset]);
}

// ============================================================
// Group Management Functions
// ============================================================
function logGroupEvent(int $groupId, int $userId, string $event, string $msg = ''): void {
    db()->prepare('INSERT INTO group_events (group_id,user_id,event,message) VALUES(?,?,?,?)')
        ->execute([$groupId, $userId, $event, $msg]);
}

function getGroupSettings(int $groupId): ?array {
    $s = db()->prepare('SELECT * FROM group_settings WHERE group_id=?');
    $s->execute([$groupId]);
    return $s->fetch() ?: null;
}

function updateGroupSettings(int $groupId, array $settings): void {
    db()->prepare('INSERT INTO group_settings (group_id, spam_filter, anti_flood, link_block, forward_block, auto_welcome) 
                  VALUES(?,?,?,?,?,?) 
                  ON DUPLICATE KEY UPDATE 
                  spam_filter=?, anti_flood=?, link_block=?, forward_block=?, auto_welcome=?')
        ->execute([
            $groupId,
            $settings['spam_filter'] ?? 1,
            $settings['anti_flood'] ?? 1,
            $settings['link_block'] ?? 0,
            $settings['forward_block'] ?? 0,
            $settings['auto_welcome'] ?? 1,
            $settings['spam_filter'] ?? 1,
            $settings['anti_flood'] ?? 1,
            $settings['link_block'] ?? 0,
            $settings['forward_block'] ?? 0,
            $settings['auto_welcome'] ?? 1,
        ]);
}

?>
