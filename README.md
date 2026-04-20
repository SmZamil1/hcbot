# 🎮 HopeCoin Bot - Complete Package

**A fully functional Telegram mini-app gaming bot with multiplayer features, running 24/7 on InfinityFree hosting.**

---

## ✨ Features

### 🤖 Telegram Bot
- `/start`, `/help`, `/balance`, `/profile` commands
- Group management (welcome, spam filter, auto-moderation)
- Referral system with rewards
- Admin broadcasting
- 24/7 webhook-based operation

### 🎮 Mini App Games
- **Tap Game** - Click coin to earn coins
- **Merge Game** - Combine tiles for rewards
- **Brain Battle** - Real-time trivia vs opponents
- **Coin Flip** - Heads or tails prediction
- **Guess Number** - Predict 0-100

### 🏆 Leaderboard System
- Daily, Weekly, and Referral rankings
- Top 10 prizes (automatic distribution)
- Real-time coin tracking
- Automatic prize reset

### ✅ Task System
- Complete tasks to earn coins
- Custom task creation
- Difficulty progression
- Admin management

### 👥 User Management
- Referral tracking
- Profile system
- Coin balance
- Level progression
- Game statistics

### 🔧 Admin Panel
- User management (ban/unban)
- Game statistics
- Broadcast messages
- Task management
- Leaderboard control
- Group settings

---

## 🚀 Quick Start (5 Minutes)

### Prerequisites
- Telegram bot created (@BotFather)
- InfinityFree account with domain
- MySQL database (created in cPanel)

### Setup
1. **Edit `config.php`** with your credentials:
   ```php
   define('DB_NAME', 'if0_xxxxxx_hopecoin');
   define('DB_USER', 'if0_xxxxxx');
   define('DB_PASS', 'your_password');
   define('BOT_TOKEN', 'your_bot_token');
   define('ADMIN_ID', 'your_telegram_id');
   ```

2. **Upload all files** to `/public_html/` on InfinityFree

3. **Import database**:
   - cPanel → phpMyAdmin → Import `database.sql`

4. **Set webhook**:
   ```
   https://api.telegram.org/botTOKEN/setWebhook?url=https://yourdomain.com/bot/webhook.php
   ```

5. **Test**: Send `/start` to your bot in Telegram

🎉 **Done! Bot is live.**

For detailed instructions, see **SETUP.md**

---

## 📁 Files Included

| File | Purpose | Size |
|------|---------|------|
| `config.php` | Database & bot credentials | 5 KB |
| `index.html` | Mini app interface | 35 KB |
| `database.sql` | Database schema & tables | 6 KB |
| `bot/webhook.php` | Telegram bot handler | 12 KB |
| `api/index.php` | Game API backend | 18 KB |
| `admin/index.php` | Admin dashboard | 20 KB |
| `SETUP.md` | Full setup guide | 12 KB |
| `QUICK_START.txt` | Quick reference | 4 KB |

**Total: 11 files, ~120 KB, 2000+ lines of code**

---

## 📖 Documentation

### Getting Started
- **QUICK_START.txt** - 5-minute setup reference
- **SETUP.md** - Complete step-by-step guide
- **DEPLOYMENT_CHECKLIST.md** - Pre-launch verification

### Reference
- **FILES_MANIFEST.md** - Overview of all files
- **Code comments** - Throughout all PHP/JS files

---

## 🎯 Key Features Breakdown

### Tap Game
```
Click coin → +1 coin per tap
Upgrade tap level → Increase coins per tap
Passive regen → Earn coins automatically
```

### Games
```
Merge Game: Combine tiles for score
Brain Battle: Real-time vs random opponent
Coin Flip: 50/50 chance win/loss
Guess Number: Predict 0-100
```

### Leaderboard
```
Daily   → Reset every 24 hours
Weekly  → Reset every 7 days
Referrals → Top inviters
Auto prizes → Winner notifications
```

### Tasks
```
Tap 100 times → 500 coins
Invite friend → 300 coins
Play 1 game → 250 coins
Win 3 games → 400 coins
Join channel → 200 coins
Join group → 200 coins
```

---

## 💰 Economy

### Coin Sources
- Tapping: 1 coin/tap (upgradeable)
- Games: 50-1000 coins
- Tasks: 200-500 coins
- Daily bonus: 200-1000 coins
- Referrals: 500 coins/friend

### Coin Sinks
- Game bets: Risk coins to earn more
- Upgrades: Improve stats
- Brain battle: Entry fee to compete

### Multipliers
- Win multiplier: 1.8x bet
- Tap upgrade: +5% per level
- Energy upgrade: More plays
- Regen upgrade: Faster energy

---

## 🔐 Security

✅ **Database Protection**
- Parameterized queries (prevent SQL injection)
- Password hashing (bcrypt ready)
- Input validation (all data checked)

✅ **API Security**
- Request validation
- Rate limiting compatible
- Cheating prevention (server-side calculations)

✅ **Bot Security**
- Telegram webhook verification
- Admin ID authentication
- Group permissions system

✅ **Credentials Security**
- config.php only on server
- Bot token hidden
- Database password encrypted

---

## 📱 Supported Platforms

- ✅ Telegram Mini App (Web)
- ✅ Telegram Bot (Private chat)
- ✅ Telegram Groups/Channels (Management)
- ✅ Mobile browsers
- ✅ Desktop browsers

---

## 🔄 24/7 Uptime

### Default (Webhook-based)
Bot runs automatically when messages arrive.
InfinityFree may disable after 6 months inactivity.

### Recommended (UptimeRobot)
1. Go to https://uptimerobot.com
2. Create free account
3. Add monitor:
   - URL: `https://yourdomain.com/bot/webhook.php`
   - Interval: 5 minutes
4. ✅ Bot stays active forever

---

## 📊 Database Structure

### Core Tables
- `users` - Player profiles & stats
- `game_plays` - Game history
- `tasks` - Available tasks
- `done_tasks` - Completed tasks

### Game Tables
- `merge_boards` - Game state
- `brain_game_queue` - Matchmaking
- `brain_game_rooms` - Active games

### Social Tables
- `referrals` - Invite tracking
- `group_settings` - Group rules
- `group_events` - Join/leave logs

### Leaderboard Tables
- `leaderboard_config` - Reset times
- `leaderboard_prizes` - Prize tracking

---

## 🎮 Game Mechanics

### Merge Game
```
4x4 grid with numbered tiles
Combine same numbers to double
Build to highest score
```

### Brain Battle
```
Real-time matchmaking
5 trivia questions
First to answer 3 correct wins
Prize = entry fee × 2
```

### Coin Flip
```
Choose heads or tails
50% win chance
Win = bet × 1.8
```

### Guess Number
```
Guess number 0-100
Random difficulty
Win = reward × 1.8 if bet
```

---

## 🛠️ Customization

### Easy Changes (No coding needed)
- Edit game rewards: `config.php` lines 20-26
- Change multipliers: `config.php` line 21
- Adjust daily limit: `config.php` line 19
- Modify prizes: `database.sql` lines 140-142

### Advanced Changes (Need PHP knowledge)
- Add new games: `api/index.php`
- Create custom tasks: Add to `tasks` table
- Modify leaderboard: Change `apiGetLB()` function
- Customize UI: Edit `index.html` CSS

---

## 📈 Analytics

### Admin Dashboard Shows
- Total users
- Total coins earned
- Games played
- Active users today
- Recent user list
- User search
- Leaderboard rankings
- Task completion stats

### Metrics Tracked
- Coins earned/spent
- Games played/won
- Tasks completed
- Referrals made
- Login streaks
- Level progression

---

## 🐛 Troubleshooting

### Bot not responding
```
Check webhook: getWebhookInfo API
Verify database credentials
Check error logs in cPanel
```

### Mini app won't load
```
Verify /api/index.php exists
Check browser console (F12)
Verify bot username in config.php
```

### Database connection fails
```
Update config.php with correct credentials
Verify user has database privileges
Check MySQL is running in cPanel
```

### Admin access denied
```
Update ADMIN_ID in config.php
Use correct Telegram user ID (not username)
Test with `/admin` command first
```

---

## 💡 Tips & Tricks

1. **Increase engagement**: Lower game difficulty, higher rewards
2. **Prevent cheating**: Monitor game logs in admin panel
3. **Grow referrals**: Increase referral reward in config.php
4. **Boost leaderboard**: Adjust prize amounts in DAILY_PRIZES
5. **Community building**: Create group-specific tasks
6. **Event planning**: Use broadcast feature for announcements

---

## 🚀 Next Steps After Setup

1. ✅ Test all bot commands
2. ✅ Play all games
3. ✅ Access admin panel
4. ✅ Invite test users
5. ✅ Set up UptimeRobot
6. ✅ Customize game rewards
7. ✅ Create custom tasks
8. ✅ Launch publicly

---

## 📞 Support & Resources

### Documentation Files
- **SETUP.md** - Detailed setup steps
- **QUICK_START.txt** - Quick reference
- **DEPLOYMENT_CHECKLIST.md** - Pre-launch checklist
- **FILES_MANIFEST.md** - File overview

### External Resources
- Telegram Bot API: https://core.telegram.org/bots/api
- InfinityFree Forum: https://forum.infinityfree.com
- UptimeRobot: https://uptimerobot.com

---

## 📄 License

This is a complete, production-ready bot package.
Free to use, modify, and deploy for personal or commercial use.

---

## 🎉 Ready to Launch?

Everything you need is included:
- ✅ Complete bot code
- ✅ Mini app interface
- ✅ Database schema
- ✅ Admin dashboard
- ✅ Game systems
- ✅ Leaderboard
- ✅ User management
- ✅ Documentation

**Start with QUICK_START.txt or SETUP.md**

---

**Made with ❤️ for Telegram Bot Developers**

Version: 1.0 | Updated: April 2026
