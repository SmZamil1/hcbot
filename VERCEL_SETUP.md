# HopeCoin Bot - Vercel Deployment Guide

## Complete Setup Instructions

### Step 1: Prerequisites
- GitHub account (free)
- Vercel account (free) - sign up at https://vercel.com
- Your bot token: `8717172949:AAE926lCYq48dOPP8I5Y3EFNSL-3m_dWWDs`
- Database credentials ready

### Step 2: Create GitHub Repository

1. Go to https://github.com/new
2. Create repository name: `hopecoin-bot`
3. Choose "Public" (important for Vercel)
4. Click "Create repository"

### Step 3: Upload Code to GitHub

**Option A: Via Git (Recommended)**
```bash
git clone https://github.com/YOUR_USERNAME/hopecoin-bot.git
cd hopecoin-bot
# Copy all project files here
git add .
git commit -m "Initial HopeCoin bot setup"
git push origin main
```

**Option B: Via Web Upload**
1. Go to your repository
2. Click "Add file" → "Upload files"
3. Upload all files from the project

### Step 4: Connect Vercel to GitHub

1. Go to https://vercel.com
2. Click "New Project"
3. Click "Import Git Repository"
4. Select your `hopecoin-bot` repository
5. Click "Import"

### Step 5: Configure Environment Variables

In Vercel Project Settings → Environment Variables, add:

```
BOT_TOKEN=8717172949:AAE926lCYq48dOPP8I5Y3EFNSL-3m_dWWDs
ADMIN_ID=6167568466
DB_HOST=sql202.infinityfree.com
DB_NAME=if0_37959419_hopecoin
DB_USER=if0_37959419
DB_PASS=SmZamil37
MINI_APP_URL=https://YOUR_VERCEL_DOMAIN.vercel.app
```

Replace `YOUR_VERCEL_DOMAIN` with your Vercel project URL (shown after deployment).

### Step 6: Deploy

1. Vercel auto-deploys when you push to GitHub
2. Wait for build to complete (usually 1-2 minutes)
3. You'll get a URL: `https://hopecoin-bot-xxxxx.vercel.app`

### Step 7: Set Telegram Webhook

Open this URL in your browser (replace with your actual URL):

```
https://hopecoin-bot-xxxxx.vercel.app/api/webhook/set
```

You should see:
```json
{"ok": true, "result": true}
```

### Step 8: Test the Bot

1. Open Telegram
2. Find your bot: `@hopenityappbot`
3. Send `/start`
4. Bot should respond immediately!

### Step 9: Set Up Web App (Mini App)

Your mini app is automatically deployed at:
```
https://hopecoin-bot-xxxxx.vercel.app
```

Update your bot code with this URL in environment variables.

---

## Project Structure

```
hopecoin-bot/
├── api/
│   ├── webhook/
│   │   ├── set.js          (Sets webhook)
│   │   └── route.js        (Receives webhook)
│   ├── games.js            (Game endpoints)
│   ├── user.js             (User endpoints)
│   └── leaderboard.js      (Leaderboard)
├── public/
│   └── index.html          (Mini app)
├── lib/
│   ├── telegram.js         (Telegram API)
│   ├── database.js         (DB connection)
│   └── users.js            (User logic)
├── .env.local              (Local dev)
├── package.json
└── vercel.json
```

---

## Important Notes

- **Auto-Deployment**: Every git push automatically deploys to Vercel
- **24/7 Uptime**: Vercel keeps your bot running 24/7 with no setup needed
- **SSL/HTTPS**: Automatically included
- **No Polling Needed**: Webhook works perfectly

---

## Troubleshooting

### Bot not responding?
1. Check webhook is set: https://hopecoin-bot-xxxxx.vercel.app/api/webhook/set
2. Check environment variables in Vercel dashboard
3. Check logs: Vercel Dashboard → Function Logs

### Database connection failing?
1. Verify credentials in environment variables
2. Make sure InfinityFree database is still active
3. Check IP whitelist in InfinityFree cPanel

### Mini app not loading?
1. Check MINI_APP_URL in environment variables
2. Verify static files are in `/public` folder
3. Check browser console for errors

---

## What to do Next

1. After successful deployment, test all commands:
   - `/start` → Should show welcome with Open App button
   - `/balance` → Shows coins
   - `/help` → Shows commands
   - `/leaderboard` → Shows top 10

2. Test mini app:
   - Click "Open App" in Telegram bot
   - Should load your gaming interface

3. Monitor performance:
   - Vercel Dashboard shows real-time stats
   - Check function logs for errors

---

**Your bot is now live 24/7 on Vercel!** 🎉
