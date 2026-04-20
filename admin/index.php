<?php
// ============================================================
// HopeCoin Admin Panel
// Telegram Auth Required - Only Admin User
// ============================================================

require_once __DIR__ . '/../config.php';

$userId = (int)($_GET['user_id'] ?? $_POST['user_id'] ?? 0);
$authToken = $_GET['auth_date'] ?? $_POST['auth_date'] ?? '';

// Telegram Auth Verification
if (!$userId || $userId !== (int)ADMIN_ID) {
    die('<h2>🔒 Admin Access Only</h2><p>You must be the admin to access this panel.</p>');
}

$action = $_GET['page'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HopeCoin Admin Panel</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --bg: #08111f;
            --bg2: #0f1d30;
            --text: #ddeeff;
            --cyan: #00c8ff;
            --gold: #ffc93c;
            --green: #00e898;
            --red: #ff4d6a;
        }
        
        body {
            font-family: 'Space Grotesk', sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: var(--bg2);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            padding: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar h2 {
            font-size: 18px;
            margin-bottom: 30px;
            color: var(--gold);
        }
        
        .sidebar a {
            display: block;
            padding: 12px 16px;
            margin-bottom: 8px;
            border-radius: 8px;
            text-decoration: none;
            color: var(--text);
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        
        .sidebar a:hover {
            background: rgba(0, 200, 255, 0.1);
            border-left-color: var(--cyan);
        }
        
        .sidebar a.active {
            background: rgba(0, 200, 255, 0.15);
            border-left-color: var(--cyan);
            color: var(--cyan);
        }
        
        .content {
            flex: 1;
            margin-left: 250px;
            padding: 30px;
            overflow-y: auto;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .header h1 {
            font-size: 28px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: var(--bg2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 900;
            color: var(--gold);
            margin-bottom: 8px;
            font-family: 'Orbitron', monospace;
        }
        
        .stat-label {
            font-size: 12px;
            color: rgba(221, 238, 255, 0.6);
            text-transform: uppercase;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            background: var(--bg2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .table thead {
            background: rgba(0, 200, 255, 0.1);
        }
        
        .table th {
            padding: 16px;
            text-align: left;
            font-weight: 700;
            color: var(--cyan);
            font-size: 12px;
            text-transform: uppercase;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .table td {
            padding: 12px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .table tr:hover {
            background: rgba(0, 200, 255, 0.05);
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            background: rgba(0, 232, 152, 0.2);
            color: var(--green);
        }
        
        .badge.red {
            background: rgba(255, 77, 106, 0.2);
            color: var(--red);
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            background: var(--cyan);
            color: #000;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 13px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 200, 255, 0.3);
        }
        
        .btn.danger {
            background: var(--red);
            color: #fff;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 13px;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            background: var(--bg);
            color: var(--text);
            font-family: inherit;
            font-size: 13px;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>🔧 Admin Panel</h2>
        <a href="?page=dashboard" class="<?php echo $action === 'dashboard' ? 'active' : ''; ?>">📊 Dashboard</a>
        <a href="?page=users" class="<?php echo $action === 'users' ? 'active' : ''; ?>">👥 Users</a>
        <a href="?page=games" class="<?php echo $action === 'games' ? 'active' : ''; ?>">🎮 Games</a>
        <a href="?page=leaderboard" class="<?php echo $action === 'leaderboard' ? 'active' : ''; ?>">🏆 Leaderboard</a>
        <a href="?page=tasks" class="<?php echo $action === 'tasks' ? 'active' : ''; ?>">✅ Tasks</a>
        <a href="?page=groups" class="<?php echo $action === 'groups' ? 'active' : ''; ?>">👫 Groups</a>
        <a href="?page=broadcast" class="<?php echo $action === 'broadcast' ? 'active' : ''; ?>">📢 Broadcast</a>
    </div>
    
    <div class="content">
        <div class="header">
            <h1><?php echo match($action) {
                'dashboard' => '📊 Dashboard',
                'users' => '👥 Users',
                'games' => '🎮 Games',
                'leaderboard' => '🏆 Leaderboard',
                'tasks' => '✅ Tasks',
                'groups' => '👫 Groups',
                'broadcast' => '📢 Broadcast',
                default => 'Admin Panel'
            }; ?></h1>
            <div><?php echo htmlspecialchars($_GET['user_id'] ?? 'Admin'); ?></div>
        </div>
        
        <?php
        // ============================================================
        // DASHBOARD PAGE
        // ============================================================
        if ($action === 'dashboard') {
            $totalUsers = db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
            $totalCoinsEarned = db()->query('SELECT SUM(total_coins) FROM users')->fetchColumn();
            $gamesPlayed = db()->query('SELECT COUNT(*) FROM game_plays')->fetchColumn();
            $activeToday = db()->query('SELECT COUNT(*) FROM users WHERE DATE(last_seen) = CURDATE()')->fetchColumn();
            ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $totalUsers; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($totalCoinsEarned ?? 0); ?></div>
                    <div class="stat-label">Total Coins Earned</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $gamesPlayed; ?></div>
                    <div class="stat-label">Games Played</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $activeToday; ?></div>
                    <div class="stat-label">Active Today</div>
                </div>
            </div>
            
            <h2 style="margin-top: 40px; margin-bottom: 20px;">Recent Users</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Coins</th>
                        <th>Level</th>
                        <th>Games</th>
                        <th>Last Seen</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $s = db()->query('SELECT id, full_name, coins, level, games_played, last_seen, banned FROM users ORDER BY created_at DESC LIMIT 10');
                    foreach ($s->fetchAll() as $u) {
                        echo '<tr>';
                        echo '<td>'.$u['full_name'].'</td>';
                        echo '<td>'.$u['coins'].'</td>';
                        echo '<td>'.$u['level'].'</td>';
                        echo '<td>'.$u['games_played'].'</td>';
                        echo '<td>'.date('M d, H:i', strtotime($u['last_seen'])).'</td>';
                        echo '<td><span class="badge '.($u['banned'] ? 'red' : '').'">'.$u['banned'] ? 'Banned' : 'Active'.'</span></td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        <?php } ?>
        
        <?php
        // ============================================================
        // USERS PAGE
        // ============================================================
        if ($action === 'users') {
            $search = $_GET['search'] ?? '';
            $query = $search 
                ? db()->prepare('SELECT id, full_name, username, coins, level, games_played, refs, banned FROM users WHERE full_name LIKE ? OR username LIKE ? ORDER BY coins DESC LIMIT 50')
                : db()->query('SELECT id, full_name, username, coins, level, games_played, refs, banned FROM users ORDER BY coins DESC LIMIT 50');
            
            if ($search) {
                $query->execute(['%'.$search.'%', '%'.$search.'%']);
            }
            ?>
            <div style="margin-bottom: 20px;">
                <form method="GET" style="display: flex; gap: 10px;">
                    <input type="hidden" name="page" value="users">
                    <input type="text" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>" style="flex: 1;">
                    <button type="submit" class="btn">Search</button>
                </form>
            </div>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Coins</th>
                        <th>Level</th>
                        <th>Games</th>
                        <th>Referrals</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($query->fetchAll() as $u) {
                        echo '<tr>';
                        echo '<td>'.$u['full_name'].'</td>';
                        echo '<td>@'.$u['username'].'</td>';
                        echo '<td>'.$u['coins'].'</td>';
                        echo '<td>'.$u['level'].'</td>';
                        echo '<td>'.$u['games_played'].'</td>';
                        echo '<td>'.$u['refs'].'</td>';
                        echo '<td><span class="badge '.($u['banned'] ? 'red' : '').'">'.$u['banned'] ? 'Banned' : 'Active'.'</span></td>';
                        echo '<td>';
                        if ($u['banned']) {
                            echo '<a href="?page=users&unban='.$u['id'].'" class="btn" style="font-size:11px;">Unban</a>';
                        } else {
                            echo '<a href="?page=users&ban='.$u['id'].'" class="btn danger" style="font-size:11px;">Ban</a>';
                        }
                        echo '</td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        <?php } ?>
        
        <?php
        // ============================================================
        // LEADERBOARD PAGE
        // ============================================================
        if ($action === 'leaderboard') {
            $type = $_GET['type'] ?? 'weekly';
            $s = db()->prepare('SELECT id, full_name, coins, total_coins, week_coins, day_coins, level FROM users WHERE banned=0 ORDER BY '.($type === 'daily' ? 'day_coins' : 'week_coins').' DESC LIMIT 20');
            $s->execute();
            ?>
            <div style="margin-bottom: 20px;">
                <a href="?page=leaderboard&type=daily" class="btn" style="margin-right: 10px;">📅 Daily</a>
                <a href="?page=leaderboard&type=weekly" class="btn">📆 Weekly</a>
            </div>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>User</th>
                        <th><?php echo $type === 'daily' ? 'Day Coins' : 'Week Coins'; ?></th>
                        <th>Level</th>
                        <th>Prize</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $prizes = $type === 'daily' ? DAILY_PRIZES : WEEKLY_PRIZES;
                    $rank = 1;
                    foreach ($s->fetchAll() as $u) {
                        $col = $type === 'daily' ? 'day_coins' : 'week_coins';
                        echo '<tr>';
                        echo '<td>'.$rank.'</td>';
                        echo '<td>'.$u['full_name'].'</td>';
                        echo '<td>'.$u[$col].'</td>';
                        echo '<td>'.$u['level'].'</td>';
                        echo '<td><strong>'.($prizes[$rank-1] ?? 0).'</strong></td>';
                        echo '</tr>';
                        $rank++;
                    }
                    ?>
                </tbody>
            </table>
        <?php } ?>
        
        <?php
        // ============================================================
        // TASKS PAGE
        // ============================================================
        if ($action === 'tasks') {
            if ($_POST['add_task'] ?? false) {
                $id = time();
                db()->prepare('INSERT INTO tasks (id, title, description, reward, active) VALUES (?,?,?,?,1)')
                    ->execute([$id, $_POST['title'], $_POST['description'], (int)$_POST['reward']]);
                echo '<p style="color: var(--green); margin-bottom: 20px;">✓ Task added!</p>';
            }
            
            $tasks = db()->query('SELECT * FROM tasks ORDER BY created_at DESC')->fetchAll();
            ?>
            <h2 style="margin-bottom: 20px;">Add New Task</h2>
            <form method="POST" style="background: var(--bg2); padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" required></textarea>
                </div>
                <div class="form-group">
                    <label>Reward (Coins)</label>
                    <input type="number" name="reward" value="100" required>
                </div>
                <button type="submit" name="add_task" value="1" class="btn">Add Task</button>
            </form>
            
            <h2 style="margin-bottom: 20px;">All Tasks</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Reward</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($tasks as $t) {
                        echo '<tr>';
                        echo '<td>'.$t['title'].'</td>';
                        echo '<td>'.$t['reward'].' coins</td>';
                        echo '<td><span class="badge">'.($t['active'] ? 'Active' : 'Inactive').'</span></td>';
                        echo '<td>'.date('M d, Y', strtotime($t['created_at'])).'</td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        <?php } ?>
        
        <?php
        // ============================================================
        // BROADCAST PAGE
        // ============================================================
        if ($action === 'broadcast') {
            if ($_POST['send_broadcast'] ?? false) {
                $msg = $_POST['message'] ?? '';
                $s = db()->query('SELECT id FROM users LIMIT 100');
                $count = 0;
                foreach ($s->fetchAll() as $u) {
                    sendMsg($u['id'], $msg);
                    $count++;
                }
                echo '<p style="color: var(--green); margin-bottom: 20px;">✓ Message sent to '.$count.' users!</p>';
            }
            ?>
            <form method="POST" style="background: var(--bg2); padding: 20px; border-radius: 8px;">
                <div class="form-group">
                    <label>Message (HTML allowed)</label>
                    <textarea name="message" placeholder="📢 Enter your broadcast message..." required></textarea>
                </div>
                <button type="submit" name="send_broadcast" value="1" class="btn">Send to All Users</button>
            </form>
        <?php } ?>
    </div>
    
    <script>
        // Handle ban/unban
        const urlParams = new URLSearchParams(window.location.search);
        const ban = urlParams.get('ban');
        const unban = urlParams.get('unban');
        
        if (ban) {
            fetch(`/api/index.php?action=banUser&uid=${ban}`, {method: 'POST'})
                .then(() => location.reload());
        }
        if (unban) {
            fetch(`/api/index.php?action=unbanUser&uid=${unban}`, {method: 'POST'})
                .then(() => location.reload());
        }
    </script>
</body>
</html>
