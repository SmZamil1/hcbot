# HopeCoin Bot - Vercel Deployment Guide

## Complete 10-Minute Setup

### Step 1: Prepare Your Code (2 minutes)

Your project files are ready:
- ✅ `lib/telegram.js` - Telegram API wrapper
- ✅ `lib/database.js` - MySQL database functions
- ✅ `api/webhook.js` - Bot command handler
- ✅ `app/api/webhook/route.js` - Webhook endpoint
- ✅ `app/api/webhook/set/route.js` - Webhook setup endpoint
- ✅ `.env.example` - Environment variables template

All files configured for Next.js on Vercel.

### Step 2: Deploy to Vercel (3 minutes)

#### Option A: Deploy from v0 Project

1. Download this project as ZIP from v0 (top right → Download)
2. Go to https://vercel.com
3. Click "New Project"
4. Click "Import Git Repository"
   - Create new GitHub repo or use existing one
   - Upload project files
5. Select your repository
6. Click "Import"

#### Option B: Deploy from GitHub

1. Push this project to GitHub:
   ```bash
   git init
   git add .
   git commit -m "Initial HopeCoin bot"
   git branch -M main
   git remote add origin https://github.com/YOUR_USERNAME/hopecoin-bot.git
   git push -u origin main
   ```

2. Go to https://vercel.com
3. Click "New Project" → Select your GitHub repo
4. Click "Import"

Vercel will detect Next.js automatically and build successfully.

### Step 3: Add Environment Variables (2 minutes)

In Vercel Project Settings → Environment Variables, add:

```
BOT_TOKEN = 8717172949:AAE926lCYq48dOPP8I5Y3EFNSL-3m_dWWDs
ADMIN_ID = 6167568466
BOT_USERNAME = hopenityappbot
DB_HOST = sql202.infinityfree.com
DB_NAME = if0_37959419_hopecoin
DB_USER = if0_37959419
DB_PASS = SmZamil37
MINI_APP_URL = https://YOUR_VERCEL_DOMAIN.vercel.app
NODE_ENV = production
```

Replace `YOUR_VERCEL_DOMAIN` with your actual Vercel domain (shown in dashboard).

**Click "Deploy" to apply variables.**

### Step 4: Set Telegram Webhook (2 minutes)

After deployment completes, your app has a URL like:
```
https://hopecoin-bot-xxxxx.vercel.app
```

Open this link in your browser:
```
https://YOUR_DOMAIN.vercel.app/api/webhook/set
```

You should see:
```json
{
  "ok": true,
  "result": true,
  "webhook_url": "https://YOUR_DOMAIN.vercel.app/api/webhook",
  "pending_updates": 0
}
```

✅ **Webhook is now set!**

### Step 5: Test Your Bot (1 minute)

1. Open Telegram
2. Find your bot: `@hopenityappbot`
3. Send `/start`
4. Bot should respond with welcome message

Test more commands:
- `/help` - Shows all commands
- `/balance` - Shows coins
- `/profile` - Shows profile
- `/leaderboard` - Top 10 players
- `/daily` - Claim daily bonus

### Step 6: Access Admin Panel (Optional)

Only works if you're logged in as admin in mini app:

```
https://YOUR_DOMAIN.vercel.app/admin
```

---

## Project Structure

```
hopecoin-bot/
├── lib/
│   ├── telegram.js          ← Telegram API functions
│   └── database.js          ← MySQL database helpers
├── api/
│   └── webhook.js           ← Bot command handlers
├── app/
│   └── api/
│       ├── webhook/
│       │   ├── route.js     ← Main webhook endpoint
│       │   └── set/
│       │       └── route.js ← Setup endpoint
│       └── ...              ← Other API routes
├── public/
│   └── index.html           ← Mini app (React app)
├── package.json             ← Dependencies
├── vercel.json              ← Vercel config
└── .env.example             ← Environment template
```

---

## How It Works

1. **Telegram sends updates** → `POST /api/webhook`
2. **Next.js handles it** → `app/api/webhook/route.js`
3. **Calls bot handler** → `api/webhook.js` (command logic)
4. **Access database** → `lib/database.js`
5. **Send response** → `lib/telegram.js` (Telegram API)
6. **User gets reply** ← All in Telegram

All outbound Telegram API calls work perfectly on Vercel (no restrictions).

---

## Troubleshooting

### Bot doesn't respond after `/start`

1. **Check webhook is set:**
   - Open: `https://YOUR_DOMAIN.vercel.app/api/webhook/set`
   - Should show `"ok": true`

2. **Check environment variables:**
   - Vercel Dashboard → Settings → Environment Variables
   - All 8 variables must be set
   - BOT_TOKEN and DB credentials must be exact

3. **Check logs:**
   - Vercel Dashboard → Deployments → Latest → Logs
   - Look for any JavaScript errors

4. **Restart deployment:**
   - Vercel Dashboard → Deployments
   - Click your latest deployment
   - Click "..." → "Redeploy"

### Mini app not loading

1. Check `MINI_APP_URL` environment variable
2. Ensure it's your actual Vercel domain
3. Check `/public/index.html` exists
4. Browser console for errors (F12)

### Database connection error

1. Verify credentials in environment variables
2. Check InfinityFree database is active
3. Ensure your IP isn't restricted in cPanel
4. Test with a simple query in InfinityFree phpMyAdmin

### Webhook URL wrong in logs

1. Environment variable `MINI_APP_URL` must be correct
2. No trailing slash: `https://domain.vercel.app` NOT `https://domain.vercel.app/`

---

## Advanced: Auto-Deploy Updates

Every time you push to GitHub, Vercel automatically redeploys:

```bash
# Make changes locally
git add .
git commit -m "Update bot commands"
git push origin main

# Vercel automatically rebuilds and deploys
```

No manual deployment needed!

---

## Important Notes

✅ **Free tier includes:**
- Unlimited deployments
- 24/7 uptime
- HTTPS/SSL included
- No IP restrictions for API calls
- Automatic scaling

❌ **Free tier limits:**
- 100 Function invocations per day (plenty for a bot)
- 6GB bandwidth per month (more than enough)

For higher limits, upgrade to Pro ($20/month) anytime.

---

## Next Steps After Deployment

1. **Test all commands** in Telegram
2. **Open mini app** to test games
3. **Check admin panel** to manage users/games
4. **Set up monitoring** (optional):
   - Vercel has built-in analytics
   - Check Dashboard → Analytics

---

**Your bot is now live 24/7 on Vercel!** 🎉

Need help? Check logs: Vercel Dashboard → Function Logs
