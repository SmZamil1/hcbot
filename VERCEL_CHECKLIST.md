# Vercel Deployment Checklist

Copy this checklist and follow each step. Mark with ✅ as you go.

## PRE-DEPLOYMENT (Before pushing code)

- [ ] Create GitHub account (if you don't have one): https://github.com/signup
- [ ] Create Vercel account (free): https://vercel.com/signup
- [ ] Have bot token ready: `8717172949:AAE926lCYq48dOPP8I5Y3EFNSL-3m_dWWDs`
- [ ] Have database credentials ready (from InfinityFree)
- [ ] Have admin Telegram ID: `6167568466`
- [ ] All project files are in one folder

## GITHUB SETUP

- [ ] Create new repository at https://github.com/new
  - [ ] Name it: `hopecoin-bot`
  - [ ] Make it Public
- [ ] Upload all files to repository
  - [ ] Option A: Web upload (drag & drop)
  - [ ] Option B: Git command line
- [ ] Verify files are on GitHub (refresh page)

## VERCEL DEPLOYMENT

- [ ] Go to https://vercel.com
- [ ] Click "New Project"
- [ ] Click "Import Git Repository"
- [ ] Select `hopecoin-bot` repository
- [ ] Click "Import"
- [ ] Wait for "Deployment successful" message
- [ ] Note your Vercel domain: `https://hopecoin-bot-xxxxx.vercel.app`

## ENVIRONMENT VARIABLES

In Vercel Dashboard → Settings → Environment Variables:

- [ ] Add `BOT_TOKEN` = `8717172949:AAE926lCYq48dOPP8I5Y3EFNSL-3m_dWWDs`
- [ ] Add `ADMIN_ID` = `6167568466`
- [ ] Add `BOT_USERNAME` = `hopenityappbot`
- [ ] Add `DB_HOST` = `sql202.infinityfree.com`
- [ ] Add `DB_NAME` = `if0_37959419_hopecoin`
- [ ] Add `DB_USER` = `if0_37959419`
- [ ] Add `DB_PASS` = `SmZamil37`
- [ ] Add `MINI_APP_URL` = `https://YOUR_VERCEL_DOMAIN.vercel.app`
  - Replace `YOUR_VERCEL_DOMAIN` with your actual domain
- [ ] Add `NODE_ENV` = `production`
- [ ] Click "Deploy" to apply changes
- [ ] Wait for deployment to complete

## WEBHOOK SETUP

- [ ] Wait 2-3 minutes after environment variables are deployed
- [ ] Open in browser: `https://YOUR_DOMAIN.vercel.app/api/webhook/set`
- [ ] Should see: `{"ok": true, "result": true}`
- [ ] If yes: Webhook is set ✓
- [ ] If no: Check environment variables and try again

## TELEGRAM BOT TESTING

Open Telegram:

- [ ] Find your bot: `@hopenityappbot`
- [ ] Click "Start" button
- [ ] Send: `/start`
  - [ ] Bot responds with welcome message ✓
  - [ ] Contains coin balance
  - [ ] Has "Open App" button
- [ ] Send: `/help`
  - [ ] Shows list of all commands ✓
- [ ] Send: `/balance`
  - [ ] Shows your coin balance ✓
- [ ] Send: `/profile`
  - [ ] Shows user profile with stats ✓
- [ ] Send: `/leaderboard`
  - [ ] Shows top 10 players ✓
- [ ] Send: `/daily`
  - [ ] Gives you coins or says already claimed ✓

## MINI-APP TESTING

- [ ] In Telegram bot, send `/start`
- [ ] Click "🎮 Open App" button
- [ ] Mini-app loads (should see game interface) ✓
- [ ] Can see user balance ✓
- [ ] Can see games list ✓
- [ ] Can navigate around ✓

## ADMIN PANEL TESTING (Optional)

- [ ] Open: `https://YOUR_DOMAIN.vercel.app/admin`
- [ ] Should prompt for Telegram login (if using mini-app)
- [ ] Can see user management dashboard ✓
- [ ] Can see game statistics ✓

## VERCEL MONITORING

- [ ] Open Vercel Dashboard: https://vercel.com/dashboard
- [ ] Select `hopecoin-bot` project
- [ ] Click "Deployments" tab
  - [ ] Latest deployment shows "Ready" ✓
- [ ] Click "Logs" (in deployment)
  - [ ] No error messages ✓
  - [ ] Shows successful webhook calls ✓

## DATABASE VERIFICATION

- [ ] Go to InfinityFree Control Panel
- [ ] Open phpMyAdmin
- [ ] Select database `if0_37959419_hopecoin`
- [ ] Check tables exist:
  - [ ] `users` table has your test user ✓
  - [ ] `games` table exists ✓
  - [ ] `tasks` table exists ✓
  - [ ] `leaderboard` table exists ✓

## POST-DEPLOYMENT

- [ ] Bot is responding to all commands ✓
- [ ] Mini-app is accessible ✓
- [ ] Database is connected ✓
- [ ] No errors in Vercel logs ✓
- [ ] Webhook is active ✓

- [ ] Share bot link with friends: `https://t.me/hopenityappbot`
- [ ] Monitor Vercel logs daily for errors
- [ ] Test bot weekly to ensure it's working
- [ ] Check database growth periodically

## EVERYTHING COMPLETE! 🎉

Your HopeCoin bot is:
- ✅ Live on Vercel
- ✅ Running 24/7
- ✅ Connected to database
- ✅ Responding to all commands
- ✅ Ready for real users

---

**Deployment Status: COMPLETE** ✅

Next: Invite users and watch your bot grow!

For issues, check:
1. Vercel Dashboard Logs
2. Environment variables (must be exact)
3. InfinityFree database connectivity
4. Telegram bot token (no typos)
