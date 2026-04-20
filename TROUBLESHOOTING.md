# HopeCoin Bot - Troubleshooting Guide

## Problem: Bot Not Responding

### Step 1: Verify Configuration (DONE ✓)
Your credentials are now updated:
- Host: sql202.infinityfree.com
- Database: if0_37959419_hopecoin
- User: if0_37959419
- Pass: SmZamil37

### Step 2: Upload Updated Files
Re-upload these updated files to your hosting:
1. **config.php** - Now with your actual credentials
2. **bot/test.php** - New diagnostic tool
3. **bot/webhook_debug.php** - For logging issues

### Step 3: Run Diagnostic Test
1. Open in browser: `https://hopecoinbot.42web.io/bot/test.php`
2. Check the results:
   - **Database Connection**: Should be GREEN
   - **Telegram API**: Should be GREEN  
   - **Webhook Status**: Should show your webhook URL
   - **Test Message**: Should say message was sent

### Step 4: Check Webhook Logs (Optional)
If you enabled debug version, logs go to: `https://hopecoinbot.42web.io/bot/webhook_logs.txt`

### Step 5: Test the Bot
Send a message to @hopenityappbot:
- `/start`
- `/help`
- `/balance`

---

## Common Issues & Fixes

### Issue 1: "DB connection failed"
**Cause**: Wrong database credentials
**Fix**: 
- Go to InfinityFree cPanel
- Check MySQL credentials match in config.php
- Verify your IP is whitelisted (usually automatic)

### Issue 2: "Unknown command" response
**Good news!** Bot is responding, but commands might not be set up. 
- Commands are handled in webhook.php
- Check webhook.php is included properly

### Issue 3: No response at all
**Check webhook delivery**:
1. Run test.php and look for errors
2. Check webhook_logs.txt for incoming updates
3. Look in Telegram BotFather @BotFather > /mybots > select bot > Debug info

### Issue 4: "Webhook URL has invalid certificate"
**Cause**: InfinityFree uses shared SSL
**Fix**: 
- This is normal and safe
- You can add `&drop_pending_updates=true` when setting webhook (already done)

### Issue 5: Database tables don't exist
**Fix**: 
1. Go to cPanel > phpMyAdmin
2. Select your database
3. Click **Import** tab
4. Open database.sql
5. Click Go
6. Tables created!

---

## Advanced Debugging

### View Bot's Webhook History
```
https://api.telegram.org/botYOUR_TOKEN/getWebhookInfo
```

Replace YOUR_TOKEN with your bot token to see:
- Webhook URL
- Last error messages
- Pending updates count

### Manually Test Webhook
```bash
curl -X POST https://hopecoinbot.42web.io/bot/webhook.php \
  -H "Content-Type: application/json" \
  -d '{
    "message": {
      "message_id": 1,
      "from": {"id": 123456, "first_name": "Test", "username": "test"},
      "chat": {"id": 123456, "type": "private"},
      "text": "/start"
    }
  }'
```

### Check PHP Error Logs
In InfinityFree cPanel:
- **Logs** > **Error Log**
- Look for recent PHP errors
- Search for "webhook" or "config"

---

## Quick Checklist

- [ ] Database credentials updated in config.php
- [ ] Files uploaded: config.php, bot/webhook.php, bot/test.php
- [ ] Run bot/test.php in browser
- [ ] Check all tests are GREEN
- [ ] Send /start to bot in Telegram
- [ ] Bot responds with welcome message
- [ ] Open mini app from bot message
- [ ] App loads correctly

---

## Still Having Issues?

1. Check test.php output carefully
2. Look at webhook_logs.txt (if debug enabled)
3. Verify database tables exist in phpMyAdmin
4. Check InfinityFree error logs
5. Visit Telegram BotFather to see webhook errors

**Next Step**: After uploading files, open: `https://hopecoinbot.42web.io/bot/test.php`
