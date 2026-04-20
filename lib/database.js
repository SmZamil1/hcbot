const mysql = require('mysql2/promise');

let pool = null;

function getPool() {
  if (pool) return pool;

  pool = mysql.createPool({
    host: process.env.DB_HOST,
    user: process.env.DB_USER,
    password: process.env.DB_PASS,
    database: process.env.DB_NAME,
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0,
    timezone: '+00:00'
  });

  return pool;
}

async function query(sql, values = []) {
  const pool = getPool();
  const conn = await pool.getConnection();
  try {
    const [rows] = await conn.execute(sql, values);
    return rows;
  } catch (err) {
    console.error('[DB Error]', err.message);
    throw err;
  } finally {
    conn.release();
  }
}

async function getUser(userId) {
  const rows = await query('SELECT * FROM users WHERE id = ?', [userId]);
  return rows[0] || null;
}

async function getOrCreateUser(userId, username, firstName) {
  let user = await getUser(userId);
  
  if (!user) {
    await query(
      'INSERT INTO users (id, username, full_name, coins, level, created_at) VALUES (?, ?, ?, ?, ?, NOW())',
      [userId, username || 'user', firstName || 'User', 0, 1]
    );
    user = await getUser(userId);
  }
  
  return user;
}

async function adjustCoins(userId, amount, addToTotal = false) {
  let sql = 'UPDATE users SET coins = coins + ? WHERE id = ?';
  const params = [amount, userId];
  
  if (addToTotal) {
    sql = 'UPDATE users SET coins = coins + ?, total_coins = total_coins + ? WHERE id = ?';
    params.splice(1, 0, amount);
  }
  
  await query(sql, params);
  return await getUser(userId);
}

async function getUserStats(userId) {
  const rows = await query(
    `SELECT 
      id, full_name, username, coins, level, 
      games_played, games_won, referrals, 
      daily_claimed_at, streak 
    FROM users WHERE id = ?`,
    [userId]
  );
  return rows[0] || null;
}

async function getLeaderboard(limit = 10, orderBy = 'coins') {
  const validOrders = ['coins', 'games_won', 'referrals', 'level'];
  const order = validOrders.includes(orderBy) ? orderBy : 'coins';
  
  return await query(
    `SELECT id, full_name, coins, level, games_won, referrals 
    FROM users 
    WHERE banned = 0 
    ORDER BY ${order} DESC 
    LIMIT ?`,
    [limit]
  );
}

module.exports = {
  query,
  getPool,
  getUser,
  getOrCreateUser,
  adjustCoins,
  getUserStats,
  getLeaderboard
};
