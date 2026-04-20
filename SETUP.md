# HopeCoin Bot - Complete Setup Guide

## Project Structure

```
hopecoinbot.42web.io/
├── config.php              ← Database & Bot config (EDIT THIS FIRST!)
├── index.html              ← Mini app main page
├── database.sql            ← Database schema (run in phpMyAdmin)
├── SETUP.md                ← This file
│
├── bot/
│   └── webhook.php         ← Telegram bot handler (24/7 webhook)
│
├── api/
│   └── index.php           ← Mini app API endpoints
│
└── admin/
    └── index.php           ← Admin control panel
```

---

## 📋 STEP-BY-STEP SETUP (15 minutes)

### STEP 1: Create InfinityFree Hosting Account

1. Go to https://infinityfree.com
2. Sign up with email
3. Create a website using your domain: `hopecoinbot.42web.io`
4. FTP details will be provided

### STEP 2: Upload All Files to InfinityFree

Use FTP (FileZilla) or File Manager in cPanel:

**Upload all files to `/public_html/`**

```
public_html/
├── config.php
├── index.html
├── database.sql
├── SETUP.md
├── bot/
│   └── webhook.php
├── api/
│   └── index.php
└── admin/
    └── index.php
```

### STEP 3: Create MySQL Database in cPanel

1. Go to InfinityFree cPanel → **MySQL Databases**
2. Create new database:
   - Name: `hopecoin_db` (or `if0_xxxxxx_hopecoin`)
   - Copy the full database name

3. Create MySQL user:
   - Username: Create new
   - Password: Create strong password
   - Add user to database with ALL privileges

4. Copy these 4 values:
   - Database Host: `sql200.infinityfree.com`
   - Database Name: `if0_xxxxxx_hopecoin`
   - Database User: `if0_xxxxxx`
   - Database Password: Your password

### STEP 4: Update config.php

Edit `/public_html/config.php` with your values:

```php
define('DB_HOST', 'sql200.infinityfree.com');  // Keep this
define('DB_NAME', 'if0_xxxxxx_hopecoin');      // Your DB name
define('DB_USER', 'if0_xxxxxx');               // Your DB user
define('DB_PASS', 'your_password');            // Your DB password

define('BOT_TOKEN', '8717172949:AAE926lCYq48dOPP8I5Y3EFNSL-3m_dWWDs');
define('ADMIN_ID',  '6167568466');             // Your Telegram user ID
define('MINI_APP_URL', 'https://hopecoinbot.42web.io');
define('BOT_USERNAME', 'hopecoinappbot');      // Your bot username (without @)
```

### STEP 5: Import Database Schema

1. Go to cPanel → **phpMyAdmin**
2. Click your database name on left
3. Click **Import** tab
4. Open `/database.sql` file
5. Drag & drop or select file
6. Click **Go**
7. ✅ All tables created!

### STEP 6: Set Telegram Webhook (CRUCIAL!)

This makes your bot respond 24/7.

**Open in browser or use curl:**

```
https://api.telegram.org/bot8717172949:AAE926lCYq48dOPP8I5Y3EFNSL-3m_dWWDs/setWebhook?url=https://hopecoinbot.42web.io/bot/webhook.php&drop_pending_updates=true
```

**Expected Response:**
```json
{"ok":true,"result":true,"description":"Webhook was set"}
```

**Or use cURL:**
```bash
curl -X POST https://api.telegram.org/bot8717172949:AAE926lCYq48dOPP8I5Y3EFNSL-3m_dWWDs/setWebhook \
  -d url=https://hopecoinbot.42web.io/bot/webhook.php
```

### STEP 7: Verify Webhook is Active

Open in browser:
```
https://api.telegram.org/bot8717172949:AAE926lCYq48dOPP8I5Y3EFNSL-3m_dWWDs/getWebhookInfo
```

Response should show:
```json
{
  "ok":true,
  "result":{
    "url":"https://hopecoinbot.42web.io/bot/webhook.php",
    "has_custom_certificate":false,
    "pending_update_count":0
  }
}
```

### STEP 8: Test Your Bot

1. Open Telegram
2. Find your bot: `@hopecoinappbot`
3. Send `/start`
4. ✅ Bot should respond with welcome message

---

## 🧪 TESTING CHECKLIST

- [ ] Bot responds to `/start`
- [ ] Bot responds to `/help`
- [ ] Bot responds to `/balance`
- [ ] Mini app loads: Click "Open App" button
- [ ] Can tap coin and earn coins
- [ ] Leaderboard shows users
- [ ] Admin panel accessible at `/admin/`
- [ ] Games can be played

---

## 🔄 KEEPING BOT RUNNING 24/7

InfinityFree keeps servers running, BUT may disable after 6 months of inactivity.

### Option 1: UptimeRobot Monitor (FREE - RECOMMENDED)

1. Go to https://uptimerobot.com
2. Create account
3. Click "Add Monitor":
   - Type: **HTTP(s)**
   - URL: `https://hopecoinbot.42web.io/bot/webhook.php`
   - Interval: **Every 5 minutes**
4. Save
5. ✅ Your bot gets pinged every 5 min = Always active!

### Option 2: Vercel Cron (If you migrate later)

If you move to Vercel, use cron jobs to ping your API.

---

## 🔐 SECURITY NOTES

1. **Bot Token** - Keep secret, don't share publicly
2. **Admin ID** - Only this user can access admin panel
3. **Database** - Password is on your server only
4. **Webhook** - Uses Telegram's HTTPS, secure by default

---

## ⚙️ FILE DESCRIPTIONS

### `config.php`
- Database connection
- Bot token & credentials  
- Game settings (rewards, multipliers)
- Helper functions for API calls

### `bot/webhook.php`
- Handles all Telegram messages
- Processes `/start`, `/help`, `/balance` commands
- Group management (join/leave/welcome messages)
- Spam filtering

### `api/index.php`
- Mini app API endpoints
- Game logic (merge, brain battle, coin flip)
- Leaderboard calculations
- Task verification
- User sync

### `admin/index.php`
- Admin dashboard
- User management
- Game statistics
- Broadcast messages
- Task management

### `index.html`
- Mini app frontend
- Tap game
- Game selection
- Leaderboard
- User profile
- Tasks

### `database.sql`
- All table schemas
- Indexes for performance
- Sample data

---

## 📱 BOT COMMANDS

| Command | Function |
|---------|----------|
| `/start` | Initialize bot, show welcome |
| `/help` | List all commands |
| `/balance` | Show current coins & stats |
| `/profile` | View full profile |
| `/tasks` | List available tasks |
| `/daily` | Claim daily bonus |
| `/leaderboard` | Show top 10 players |
| `/friend` | Get referral link |
| `/admin` | Admin panel (admin only) |
| `/broadcast MSG` | Send message to all users (admin only) |

---

## 🎮 GAMES

1. **Tap Game** - Tap coin, earn coins
2. **Merge Game** - Combine tiles for coins
3. **Brain Battle** - Trivia vs online opponent
4. **Coin Flip** - Heads or Tails
5. **Guess Number** - Guess 0-100

---

## 💬 GROUP FEATURES

- **Auto welcome** on user join
- **Spam filter** removes bad messages
- **Anti-flood** prevents spam
- **Link blocking** optional
- **Forward blocking** optional
- **Event logging** tracks joins/leaves

---

## 📊 DATABASE TABLES

### users
- Stores all user data (coins, level, stats)

### game_plays
- Records all game attempts

### tasks
- Available tasks users can complete

### done_tasks
- Tracks completed tasks per user

### merge_boards
- Saves merge game state

### brain_game_queue / brain_game_rooms
- Handles brain battle matchmaking

### leaderboard_config
- Reset times for leaderboards

### leaderboard_prizes
- Prize tracking & claiming

### group_settings / group_events
- Group management data

---

## 🐛 TROUBLESHOOTING

| Issue | Solution |
|-------|----------|
| Bot doesn't respond | Check webhook: `getWebhookInfo` |
| "DB connection failed" | Verify config.php has correct credentials |
| Mini app won't load | Check file path in config, check browser console |
| Admin says "Access Denied" | Make sure ADMIN_ID matches your Telegram ID |
| Games not saving coins | Check API permissions, ensure database is running |
| Tables not created | Run database.sql in phpMyAdmin Import tab |

---

## 🚀 NEXT STEPS

1. ✅ Complete setup above
2. ✅ Invite friends to test
3. ✅ Set up leaderboard prizes
4. ✅ Create custom tasks
5. ✅ Customize game rewards
6. ✅ Monitor bot in admin panel

---

## 📞 SUPPORT

- **Telegram Bot Issues**: @BotFather
- **InfinityFree Help**: https://forum.infinityfree.com
- **PHP Errors**: Check cPanel Error Logs

---

**Your HopeCoin bot is now running 24/7!** 🎉

For production, consider upgrading to paid hosting (Vercel, AWS, DigitalOcean) for better reliability.
