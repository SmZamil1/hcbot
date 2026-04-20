require('dotenv').config();
const { sendMessage, sendMessageWithKeyboard, answerCallbackQuery } = require('../lib/telegram');
const { getOrCreateUser, getUser, adjustCoins, getLeaderboard, query } = require('../lib/database');

const MINI_APP_URL = process.env.MINI_APP_URL || 'https://hopecoin-bot.vercel.app';
const ADMIN_ID = parseInt(process.env.ADMIN_ID);
const BOT_USERNAME = process.env.BOT_USERNAME || 'hopenityappbot';

// Main webhook handler
async function handleUpdate(update) {
  try {
    // Handle message
    if (update.message) {
      const message = update.message;
      const chatId = message.chat.id;
      const userId = message.from.id;
      const username = message.from.username || 'user';
      const firstName = message.from.first_name || 'User';
      const text = message.text || '';

      // Create or get user
      await getOrCreateUser(userId, username, firstName);

      // Handle commands
      if (text.startsWith('/')) {
        await handleCommand(chatId, userId, text);
      }
      
      // Handle group messages
      if (message.chat.type === 'group' || message.chat.type === 'supergroup') {
        await handleGroupMessage(message);
      }
    }

    // Handle callback query (button clicks)
    if (update.callback_query) {
      const callbackQuery = update.callback_query;
      const userId = callbackQuery.from.id;
      const queryId = callbackQuery.id;
      const data = callbackQuery.data;

      await handleCallback(userId, queryId, data);
    }

    return { status: 'ok' };
  } catch (err) {
    console.error('[Webhook Error]', err);
    return { status: 'error' };
  }
}

// Command handler
async function handleCommand(chatId, userId, text) {
  const cmd = text.split(' ')[0].toLowerCase();

  switch (cmd) {
    case '/start':
      return await cmdStart(chatId, userId);
    case '/help':
      return await cmdHelp(chatId);
    case '/balance':
      return await cmdBalance(chatId, userId);
    case '/profile':
      return await cmdProfile(chatId, userId);
    case '/leaderboard':
      return await cmdLeaderboard(chatId);
    case '/daily':
      return await cmdDaily(chatId, userId);
    case '/tasks':
      return await cmdTasks(chatId);
    case '/friend':
      return await cmdFriend(chatId, userId);
    case '/admin':
      return await cmdAdmin(chatId, userId);
    default:
      return await sendMessage(chatId, '❓ Unknown command. Use /help for available commands.');
  }
}

// Start command
async function cmdStart(chatId, userId) {
  const user = await getUser(userId);
  
  const welcome = `🎮 <b>Welcome to HopeCoin!</b>\n\n` +
    `🪙 Play games, earn coins, and climb the leaderboard!\n\n` +
    `💰 <b>Your Balance:</b> ${user.coins} 🪙\n` +
    `🏆 <b>Level:</b> ${user.level}\n\n` +
    `📱 <b>Open the App Below to Play and Earn!</b>\n\n` +
    `<i>Use /help for all available commands</i>`;

  const keyboard = [
    [{ text: '🎮 Open App', web_app: { url: MINI_APP_URL } }],
    [{ text: '👥 Invite Friends' }, { text: '🏆 Leaderboard' }]
  ];

  return await sendMessageWithKeyboard(chatId, welcome, keyboard);
}

// Help command
async function cmdHelp(chatId) {
  const help = `<b>🎮 HopeCoin Commands</b>\n\n` +
    `<b>/start</b> - Welcome & Open App\n` +
    `<b>/balance</b> - Check your coins\n` +
    `<b>/profile</b> - View your profile\n` +
    `<b>/leaderboard</b> - Top 10 players\n` +
    `<b>/daily</b> - Claim daily bonus\n` +
    `<b>/tasks</b> - View available tasks\n` +
    `<b>/friend</b> - Get referral link\n\n` +
    `🎮 <b>Play in the app to earn more!</b>`;

  return await sendMessage(chatId, help);
}

// Balance command
async function cmdBalance(chatId, userId) {
  const user = await getUser(userId);

  const msg = `💰 <b>Your Balance</b>\n\n` +
    `🪙 Coins: <b>${user.coins}</b>\n` +
    `📊 Total Earned: <b>${user.total_coins || 0}</b>\n` +
    `🏆 Level: <b>${user.level}</b>\n` +
    `🎮 Games Played: <b>${user.games_played || 0}</b>\n` +
    `🏅 Games Won: <b>${user.games_won || 0}</b>`;

  return await sendMessage(chatId, msg);
}

// Profile command
async function cmdProfile(chatId, userId) {
  const user = await getUser(userId);

  const msg = `👤 <b>Your Profile</b>\n\n` +
    `<b>${user.full_name}</b> (@${user.username})\n` +
    `ID: <code>${userId}</code>\n\n` +
    `🪙 Balance: <b>${user.coins} coins</b>\n` +
    `⭐ Level: <b>${user.level}</b>\n` +
    `👥 Referrals: <b>${user.referrals || 0}</b>\n` +
    `🎮 Games: <b>${user.games_played || 0} played, ${user.games_won || 0} won</b>\n` +
    `🔥 Streak: <b>${user.streak || 0}</b>`;

  return await sendMessage(chatId, msg);
}

// Leaderboard command
async function cmdLeaderboard(chatId) {
  try {
    const users = await getLeaderboard(10);

    let msg = `🏆 <b>Top 10 Leaderboard</b>\n\n`;
    const medals = ['🥇', '🥈', '🥉'];

    users.forEach((user, index) => {
      const emoji = medals[index] || `${index + 1}.`;
      msg += `${emoji} <b>${user.full_name}</b> - ${user.coins} 🪙 (Lvl ${user.level})\n`;
    });

    return await sendMessage(chatId, msg);
  } catch (err) {
    return await sendMessage(chatId, '❌ Could not load leaderboard.');
  }
}

// Daily bonus command
async function cmdDaily(chatId, userId) {
  try {
    const user = await getUser(userId);
    const now = Date.now();
    const lastClaimed = user.daily_claimed_at ? new Date(user.daily_claimed_at).getTime() : 0;
    const oneDay = 24 * 60 * 60 * 1000;

    if (lastClaimed && (now - lastClaimed) < oneDay) {
      const hoursLeft = Math.ceil((oneDay - (now - lastClaimed)) / (60 * 60 * 1000));
      return await sendMessage(chatId, `⏳ Daily bonus already claimed! Come back in <b>${hoursLeft} hours</b>`);
    }

    const streak = user.daily_claimed_at ? (user.streak || 0) + 1 : 1;
    const bonus = Math.min(200 + (streak - 1) * 50, 1000);

    // Update user
    await query('UPDATE users SET daily_claimed_at = NOW(), streak = ? WHERE id = ?', [streak, userId]);
    await adjustCoins(userId, bonus, true);

    const msg = `🎉 <b>Daily Bonus Claimed!</b>\n\n` +
      `🪙 +<b>${bonus} coins</b>\n` +
      `🔥 Streak: <b>${streak} days</b>\n\n` +
      `Come back tomorrow for more!`;

    return await sendMessage(chatId, msg);
  } catch (err) {
    return await sendMessage(chatId, '❌ Error claiming daily bonus.');
  }
}

// Tasks command
async function cmdTasks(chatId) {
  try {
    const tasks = await query('SELECT * FROM tasks WHERE active = 1 LIMIT 10');

    let msg = `✅ <b>Available Tasks</b>\n\n`;
    if (tasks.length > 0) {
      tasks.forEach(task => {
        msg += `• ${task.title}\n   💰 Reward: ${task.reward} coins\n`;
      });
    } else {
      msg += `No tasks available right now.`;
    }
    msg += `\n\n📱 Claim tasks in the app!`;

    return await sendMessage(chatId, msg);
  } catch (err) {
    return await sendMessage(chatId, '❌ Could not load tasks.');
  }
}

// Friend/Referral command
async function cmdFriend(chatId, userId) {
  const refLink = `https://t.me/${BOT_USERNAME}?start=ref_${userId}`;

  const msg = `👥 <b>Invite Friends!</b>\n\n` +
    `Share your link and earn <b>500 coins</b> per friend!\n\n` +
    `<code>${refLink}</code>\n\n` +
    `Tap to copy the link 👆`;

  return await sendMessage(chatId, msg);
}

// Admin command
async function cmdAdmin(chatId, userId) {
  if (userId !== ADMIN_ID) {
    return await sendMessage(chatId, '❌ Only admin can use this command.');
  }

  const msg = `🔧 <b>Admin Panel</b>\n\n` +
    `🌐 <a href="${MINI_APP_URL}/admin">Open Admin Dashboard</a>`;

  return await sendMessage(chatId, msg);
}

// Callback query handler
async function handleCallback(userId, queryId, data) {
  const parts = data.split('_');
  const action = parts[0];

  switch (action) {
    case 'daily':
      return await answerCallbackQuery(queryId, '✅ Daily bonus button clicked!');
    default:
      return await answerCallbackQuery(queryId, 'Processing...');
  }
}

// Group message handler
async function handleGroupMessage(message) {
  const groupId = message.chat.id;
  const userId = message.from.id;
  const text = message.text || '';

  // Welcome new members
  if (message.new_chat_members) {
    for (const member of message.new_chat_members) {
      const welcome = `👋 Welcome <b>${member.first_name}</b> to HopeCoin Bot Group!\n\n` +
        `🎮 Play games and earn coins!\n` +
        `📱 Use /start to open the app!`;
      
      await sendMessage(groupId, welcome);
    }
  }
}

module.exports = { handleUpdate, handleCommand };
