<?php
// ============================================================
// HopeCoin Mini App API
// Handles all game, user, and leaderboard operations
// ============================================================

require_once __DIR__ . '/../config.php';
corsHeaders();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

if (!$action && $method === 'POST') {
    $body   = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = $body['action'] ?? '';
} else {
    $body = $_POST ?: [];
}

$uid = (int)($_GET['uid'] ?? $body['uid'] ?? 0);

switch ($action) {
    case 'getUser':         jsonOut(apiGetUser($uid)); break;
    case 'registerUser':    jsonOut(apiRegister($body)); break;
    case 'syncUser':        jsonOut(apiSync($body)); break;
    case 'getLeaderboard':  jsonOut(apiGetLB($_GET['type'] ?? 'weekly')); break;
    case 'getTasks':        jsonOut(apiGetTasks($uid)); break;
    case 'claimTask':       jsonOut(apiClaimTask($body)); break;
    case 'claimDaily':      jsonOut(apiClaimDaily($uid)); break;
    case 'buyUpgrade':      jsonOut(apiBuyUpgrade($body)); break;
    case 'playGame':        jsonOut(apiPlayGame($body)); break;
    case 'getMergeBoard':   jsonOut(apiGetMergeBoard($uid)); break;
    case 'saveMergeBoard':  jsonOut(apiSaveMergeBoard($body)); break;
    case 'joinBrainGame':   jsonOut(apiJoinBrainGame($body)); break;
    case 'brainGameAction': jsonOut(apiBrainGameAction($body)); break;
    case 'getPrizes':       jsonOut(apiGetPrizes($uid)); break;
    case 'claimPrize':      jsonOut(apiClaimPrize($body)); break;
    case 'getFriends':      jsonOut(apiGetFriends($uid)); break;
    case 'getReferralLink': jsonOut(apiGetReferralLink($uid)); break;
    case 'checkPlayLimit':  jsonOut(canPlayGame($uid, $_GET['game'] ?? '', 0)); break;
    default: jsonOut(['error'=>'Unknown action']);
}

// ══════════════════════════════════════════════════════════════
// API FUNCTIONS
// ══════════════════════════════════════════════════════════════

function apiGetUser(int $uid): array {
    if (!$uid) return ['found'=>false];
    $u = getUser($uid);
    if (!$u) return ['found'=>false];
    return array_merge($u, ['found'=>true]);
}

function apiRegister(array $b): array {
    $uid  = (int)($b['uid'] ?? 0);
    $un   = $b['username'] ?? '';
    $name = $b['name'] ?? 'Player';
    if (!$uid) return ['success'=>false,'error'=>'No uid'];
    $u = getOrCreateUser($uid, $un, $name);
    return ['success'=>true,'user'=>$u];
}

function apiSync(array $b): array {
    $uid = (int)($b['uid'] ?? 0);
    if (!$uid) return ['success'=>false];
    $u = getUser($uid);
    if (!$u) return ['success'=>false,'error'=>'User not found'];
    
    $nc = max((int)$u['coins'],      (int)($b['coins']       ?? 0));
    $nt = max((int)$u['total_coins'],(int)($b['total_coins'] ?? 0));
    $np = max((int)$u['taps'],       (int)($b['taps']        ?? 0));
    $nl = max((int)$u['level'],      min((int)($b['level']   ?? 1), 50));
    $ng = max((int)$u['games_played'],(int)($b['games_played']??0));
    $nw = max((int)$u['games_won'],  (int)($b['games_won']   ?? 0));
    $tl = max((int)$u['tap_lvl'],    (int)($b['tap_lvl']     ?? 1));
    $el = max((int)$u['en_lvl'],     (int)($b['en_lvl']      ?? 1));
    $rl = max((int)$u['regen_lvl'],  (int)($b['regen_lvl']   ?? 1));
    
    $weekAdd = max(0, $nt - (int)$u['total_coins']);
    
    db()->prepare('UPDATE users SET coins=?,total_coins=?,taps=?,level=?,games_played=?,games_won=?,tap_lvl=?,en_lvl=?,regen_lvl=?,week_coins=week_coins+?,last_seen=NOW() WHERE id=?')
        ->execute([$nc,$nt,$np,$nl,$ng,$nw,$tl,$el,$rl,$weekAdd,$uid]);
    
    return ['success'=>true,'serverCoins'=>$nc,'serverTotal'=>$nt];
}

function apiGetLB(string $type): array {
    $col = match($type) {
        'daily'  => 'day_coins',
        'weekly' => 'week_coins',
        'refs'   => 'refs',
        default  => 'week_coins'
    };
    
    $rows = db()->prepare("SELECT id,username,full_name,coins,total_coins,week_coins,day_coins,level,refs FROM users WHERE banned=0 ORDER BY {$col} DESC LIMIT 100");
    $rows->execute();
    $users = $rows->fetchAll();
    
    $cfg = db()->prepare('SELECT * FROM leaderboard_config WHERE board_type=?');
    $cfg->execute([$type === 'refs' ? 'refs' : $type]);
    $lbCfg = $cfg->fetch();
    
    $prizes = match($type) {
        'daily'  => DAILY_PRIZES,
        'refs'   => REFS_PRIZES,
        default  => WEEKLY_PRIZES
    };
    
    return [
        'users'      => array_map(fn($u,$i) => array_merge($u,['rank'=>$i+1,'prize'=>$prizes[$i]??0]), $users, array_keys($users)),
        'type'       => $type,
        'next_reset' => $lbCfg ? $lbCfg['next_reset'] : null
    ];
}

function apiGetTasks(int $uid): array {
    $rows = db()->query("SELECT * FROM tasks WHERE active=1")->fetchAll();
    $done = [];
    if ($uid) {
        $s = db()->prepare('SELECT task_id FROM done_tasks WHERE user_id=?');
        $s->execute([$uid]);
        $done = $s->fetchAll(PDO::FETCH_COLUMN);
    }
    return ['tasks'=>$rows,'completed'=>$done];
}

function apiClaimTask(array $b): array {
    $uid    = (int)($b['uid']    ?? 0);
    $taskId = $b['taskId'] ?? '';
    $reward = (int)($b['reward'] ?? 0);
    if (!$uid || !$taskId) return ['success'=>false,'error'=>'Missing params'];
    
    $s = db()->prepare('SELECT id FROM done_tasks WHERE user_id=? AND task_id=?');
    $s->execute([$uid, $taskId]);
    if ($s->fetch()) return ['success'=>false,'error'=>'Already claimed','alreadyDone'=>true];
    
    $check = checkTaskCriteria($uid, $taskId);
    if (!$check['ok']) return ['success'=>false,'error'=>$check['reason']];
    
    db()->prepare('INSERT IGNORE INTO done_tasks (user_id,task_id) VALUES(?,?)')->execute([$uid,$taskId]);
    adjustCoins($uid, $reward, true);
    db()->prepare('UPDATE users SET tasks_done=tasks_done+1 WHERE id=?')->execute([$uid]);
    
    $u = getUser($uid);
    return ['success'=>true,'coinsAwarded'=>$reward,'newBalance'=>(int)($u['coins']??0)];
}

function checkTaskCriteria(int $uid, string $tid): array {
    $u = getUser($uid);
    if (!$u) return ['ok'=>false,'reason'=>'User not found'];
    
    if (in_array($tid, ['t_tap','task_tap_100'])) {
        if ((int)$u['taps'] < 100) return ['ok'=>false,'reason'=>'Tap '.max(0,100-(int)$u['taps']).' more times'];
    }
    if (in_array($tid, ['t_inv','task_invite_1'])) {
        if ((int)$u['refs'] < 1) return ['ok'=>false,'reason'=>'Invite 1 friend first'];
    }
    if (in_array($tid, ['t_gp','task_play_1'])) {
        if ((int)$u['games_played'] < 1) return ['ok'=>false,'reason'=>'Play 1 game first'];
    }
    if (in_array($tid, ['t_gw','task_win_3'])) {
        $need = max(0, 3-(int)$u['games_won']);
        if ($need > 0) return ['ok'=>false,'reason'=>"Win {$need} more games"];
    }
    if (in_array($tid, ['t_ch','task_join_channel'])) return verifyTgJoin($uid, '@hopenity');
    if (in_array($tid, ['t_grp','task_join_group']))  return verifyTgJoin($uid, '@hopenitychat');
    return ['ok'=>true];
}

function verifyTgJoin(int $uid, string $chat): array {
    $r = tgRequest('getChatMember', ['chat_id'=>$chat,'user_id'=>$uid]);
    if (!empty($r['ok'])) {
        $st = $r['result']['status'] ?? '';
        if (in_array($st, ['member','administrator','creator'])) return ['ok'=>true];
        return ['ok'=>false,'reason'=>"Please join {$chat} first"];
    }
    return ['ok'=>true];
}

function apiClaimDaily(int $uid): array {
    if (!$uid) return ['success'=>false];
    $u = getUser($uid);
    if (!$u) return ['success'=>false];
    
    $last = $u['daily_claimed_at'];
    $now  = time();
    if ($last && ($now - strtotime($last)) < 86400) {
        $nextH = ceil((86400 - ($now - strtotime($last))) / 3600);
        return ['success'=>false,'nextHours'=>$nextH];
    }
    $streak = $last ? ((int)$u['streak'] + 1) : 1;
    $bonus  = min(200 + ($streak - 1) * 50, 1000);
    db()->prepare('UPDATE users SET daily_claimed_at=NOW(), streak=? WHERE id=?')->execute([$streak,$uid]);
    adjustCoins($uid, $bonus, true);
    return ['success'=>true,'bonus'=>$bonus,'streak'=>$streak];
}

function apiBuyUpgrade(array $b): array {
    $uid     = (int)($b['uid']     ?? 0);
    $upgrade = $b['upgrade'] ?? '';
    $cost    = (int)($b['cost']    ?? 0);
    $u = getUser($uid);
    if (!$u) return ['success'=>false,'error'=>'Not found'];
    if ((int)$u['coins'] < $cost) return ['success'=>false,'error'=>'Not enough coins'];
    
    $colMap = ['tap'=>'tap_lvl','energy'=>'en_lvl','regen'=>'regen_lvl'];
    $col = $colMap[$upgrade] ?? null;
    if (!$col) return ['success'=>false,'error'=>'Unknown upgrade'];
    
    db()->prepare("UPDATE users SET coins=coins-?, {$col}={$col}+1 WHERE id=?")->execute([$cost,$uid]);
    return ['success'=>true,'newCoins'=>(int)$u['coins']-$cost];
}

function apiPlayGame(array $b): array {
    $uid  = (int)($b['uid']  ?? 0);
    $game = $b['game'] ?? '';
    $bet  = (int)($b['bet'] ?? 0);
    $result = $b['result'] ?? 'loss';
    if (!$uid || !$game) return ['success'=>false];
    
    $u = getUser($uid);
    if (!$u) return ['success'=>false,'error'=>'User not found'];
    
    $canPlay = canPlayGame($uid, $game, $bet);
    if (!$canPlay['ok'] && $bet === 0) {
        return ['success'=>false,'limitReached'=>true,'freePlaysLeft'=>0,
                'message'=>"You&apos;ve used your ".DAILY_PLAY_LIMIT." free plays for {$game} today! Bet coins to play more."];
    }
    
    $coinsChange = 0;
    if ($bet > 0) {
        if ((int)$u['coins'] < $bet) return ['success'=>false,'error'=>'Not enough coins'];
        adjustCoins($uid, -$bet, false);
        if ($result === 'win') {
            $winAmount = (int)round($bet * (float)BET_MULTIPLIER);
            adjustCoins($uid, $winAmount, true);
            $coinsChange = $winAmount - $bet;
        } else {
            $coinsChange = -$bet;
        }
    } else {
        if ($result === 'win') {
            $freeReward = (int)($b['reward'] ?? 50);
            adjustCoins($uid, $freeReward, true);
            $coinsChange = $freeReward;
        }
    }
    
    recordGamePlay($uid, $game, $bet, $result, $coinsChange);
    $u2 = getUser($uid);
    return ['success'=>true,'coinsChange'=>$coinsChange,'newCoins'=>(int)($u2['coins']??0),'freePlaysLeft'=>max(0,($canPlay['free_plays_left']??0)-1)];
}

function apiGetMergeBoard(int $uid): array {
    if (!$uid) return ['board'=>null];
    $s = db()->prepare('SELECT * FROM merge_boards WHERE user_id=?');
    $s->execute([$uid]);
    $r = $s->fetch();
    if (!$r) return ['board'=>null,'score'=>0];
    return ['board'=>json_decode($r['board_data'],true),'score'=>(int)$r['score'],'coins_earned'=>(int)$r['coins_earned']];
}

function apiSaveMergeBoard(array $b): array {
    $uid   = (int)($b['uid']   ?? 0);
    $board = $b['board'] ?? [];
    $score = (int)($b['score'] ?? 0);
    $coinsEarned = (int)($b['coins_earned'] ?? 0);
    if (!$uid) return ['success'=>false];
    
    $s = db()->prepare('INSERT INTO merge_boards (user_id,board_data,score,coins_earned) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE board_data=?,score=?,coins_earned=?');
    $boardJson = json_encode($board);
    $s->execute([$uid,$boardJson,$score,$coinsEarned,$boardJson,$score,$coinsEarned]);
    
    if ($coinsEarned > 0) {
        adjustCoins($uid, $coinsEarned, true);
    }
    return ['success'=>true];
}

function apiJoinBrainGame(array $b): array {
    $uid      = (int)($b['uid']      ?? 0);
    $gameType = $b['gameType'] ?? 'trivia';
    $entryFee = (int)($b['entryFee'] ?? 0);
    if (!$uid) return ['success'=>false];
    
    $u = getUser($uid);
    if (!$u) return ['success'=>false,'error'=>'User not found'];
    if ($entryFee > 0 && (int)$u['coins'] < $entryFee) return ['success'=>false,'error'=>'Not enough coins'];
    
    if ($entryFee > 0) adjustCoins($uid, -$entryFee, false);
    
    $s = db()->prepare('SELECT * FROM brain_game_queue WHERE game_type=? AND status=\'waiting\' AND user_id!=? LIMIT 1');
    $s->execute([$gameType, $uid]);
    $opponent = $s->fetch();
    
    if ($opponent) {
        $roomId = uniqid('bg_', true);
        $questions = generateBrainQuestions($gameType, 5);
        db()->prepare('INSERT INTO brain_game_rooms (id,game_type,player1_id,player2_id,entry_fee,status,questions) VALUES(?,?,?,?,?,\'active\',?)')
            ->execute([$roomId,$gameType,(int)$opponent['user_id'],$uid,$entryFee,json_encode($questions)]);
        db()->prepare('UPDATE brain_game_queue SET status=\'matched\', room_id=? WHERE id=?')->execute([$roomId,$opponent['id']]);
        db()->prepare('DELETE FROM brain_game_queue WHERE user_id=? AND game_type=?')->execute([$uid,$gameType]);
        
        return ['success'=>true,'status'=>'matched','roomId'=>$roomId,'questions'=>$questions,'opponentId'=>(int)$opponent['user_id']];
    }
    
    db()->prepare('INSERT INTO brain_game_queue (user_id,game_type,entry_fee) VALUES(?,?,?) ON DUPLICATE KEY UPDATE status=\'waiting\',entry_fee=?,joined_at=NOW()')
        ->execute([$uid,$gameType,$entryFee,$entryFee]);
    return ['success'=>true,'status'=>'waiting','message'=>'Searching for opponent...'];
}

function apiBrainGameAction(array $b): array {
    $uid    = (int)($b['uid']    ?? 0);
    $roomId = $b['roomId'] ?? '';
    $action = $b['action'] ?? '';
    $answer = $b['answer'] ?? '';
    
    $s = db()->prepare('SELECT * FROM brain_game_rooms WHERE id=?');
    $s->execute([$roomId]);
    $room = $s->fetch();
    if (!$room) return ['success'=>false,'error'=>'Room not found'];
    
    if ($action === 'answer') {
        $questions = json_decode($room['questions'], true);
        $qIdx = (int)$room['current_question'];
        $isPlayer1 = (int)$room['player1_id'] === $uid;
        $correct = $questions[$qIdx]['answer'] ?? '';
        $isCorrect = strtolower(trim($answer)) === strtolower(trim($correct));
        
        if ($isPlayer1 && $isCorrect) {
            db()->prepare('UPDATE brain_game_rooms SET player1_score=player1_score+1, current_question=current_question+1 WHERE id=?')->execute([$roomId]);
        } elseif (!$isPlayer1 && $isCorrect) {
            db()->prepare('UPDATE brain_game_rooms SET player2_score=player2_score+1, current_question=current_question+1 WHERE id=?')->execute([$roomId]);
        } else {
            db()->prepare('UPDATE brain_game_rooms SET current_question=current_question+1 WHERE id=?')->execute([$roomId]);
        }
        
        $s->execute([$roomId]);
        $room = $s->fetch();
        $total = count($questions);
        
        if ((int)$room['current_question'] >= $total) {
            $p1s = (int)$room['player1_score'];
            $p2s = (int)$room['player2_score'];
            $winnerId = $p1s > $p2s ? (int)$room['player1_id'] : ($p2s > $p1s ? (int)$room['player2_id'] : 0);
            $prize = (int)$room['entry_fee'] * 2;
            if ($winnerId && $prize > 0) {
                adjustCoins($winnerId, $prize, true);
                sendMsg((string)$winnerId, "🧠 <b>Brain Battle Won!</b>\n🏆 You won <b>+{$prize} 🪙</b>!");
            }
            db()->prepare('UPDATE brain_game_rooms SET status=\'finished\', winner_id=? WHERE id=?')->execute([$winnerId,$roomId]);
            return ['success'=>true,'gameOver'=>true,'winnerId'=>$winnerId,'p1Score'=>$p1s,'p2Score'=>$p2s,'prize'=>$prize];
        }
        return ['success'=>true,'correct'=>$isCorrect,'nextQuestion'=>$questions[(int)$room['current_question']],'p1Score'=>(int)$room['player1_score'],'p2Score'=>(int)$room['player2_score']];
    }
    
    if ($action === 'poll') {
        $s->execute([$roomId]);
        $room = $s->fetch();
        $questions = json_decode($room['questions'],true);
        return ['success'=>true,'status'=>$room['status'],'currentQuestion'=>$questions[(int)$room['current_question']] ?? null,'p1Score'=>(int)$room['player1_score'],'p2Score'=>(int)$room['player2_score']];
    }
    return ['success'=>false,'error'=>'Unknown action'];
}

function generateBrainQuestions(string $type, int $count): array {
    $all = [
        ['q'=>'What is 15 × 8?','options'=>['100','120','115','130'],'answer'=>'120'],
        ['q'=>'Capital of France?','options'=>['Berlin','Madrid','Paris','Rome'],'answer'=>'Paris'],
        ['q'=>'7 + 7 × 7?','options'=>['98','56','77','49'],'answer'=>'56'],
        ['q'=>'Which planet is largest?','options'=>['Saturn','Neptune','Jupiter','Uranus'],'answer'=>'Jupiter'],
        ['q'=>'Square root of 144?','options'=>['10','12','14','11'],'answer'=>'12'],
        ['q'=>'Water formula?','options'=>['H2O','CO2','O2','H2'],'answer'=>'H2O'],
        ['q'=>'How many continents?','options'=>['5','6','7','8'],'answer'=>'7'],
        ['q'=>'25% of 200?','options'=>['40','50','60','45'],'answer'=>'50'],
        ['q'=>'Largest ocean?','options'=>['Atlantic','Indian','Pacific','Arctic'],'answer'=>'Pacific'],
        ['q'=>'Speed of light (km/s)?','options'=>['200000','300000','400000','150000'],'answer'=>'300000'],
    ];
    shuffle($all);
    return array_slice($all, 0, $count);
}

function apiGetPrizes(int $uid): array {
    if (!$uid) return ['prizes'=>[]];
    $s = db()->prepare('SELECT * FROM leaderboard_prizes WHERE user_id=? AND claimed=0');
    $s->execute([$uid]);
    return ['prizes'=>$s->fetchAll()];
}

function apiClaimPrize(array $b): array {
    $uid = (int)($b['uid'] ?? 0);
    $pid = (int)($b['prizeId'] ?? 0);
    if (!$uid || !$pid) return ['success'=>false];
    $s = db()->prepare('SELECT * FROM leaderboard_prizes WHERE id=? AND user_id=? AND claimed=0');
    $s->execute([$pid,$uid]);
    $prize = $s->fetch();
    if (!$prize) return ['success'=>false,'error'=>'Prize not found'];
    
    adjustCoins($uid, (int)$prize['amount'], true);
    db()->prepare('UPDATE leaderboard_prizes SET claimed=1, claimed_at=NOW() WHERE id=?')->execute([$pid]);
    
    return ['success'=>true,'amount'=>(int)$prize['amount']];
}

function apiGetFriends(int $uid): array {
    if (!$uid) return ['friends'=>[]];
    $s = db()->prepare('SELECT u.id,u.full_name,u.username,u.coins,u.level FROM referrals r JOIN users u ON u.id=r.new_user_id WHERE r.referrer_id=? ORDER BY u.coins DESC LIMIT 50');
    $s->execute([$uid]);
    return ['friends'=>$s->fetchAll()];
}

function apiGetReferralLink(int $uid): array {
    if (!$uid) return ['link'=>''];
    $refLink = "https://t.me/".BOT_USERNAME."?startapp=ref_{$uid}";
    return ['link'=>$refLink, 'userId'=>$uid];
}

?>
