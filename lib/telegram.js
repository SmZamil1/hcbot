const axios = require('axios');

const TOKEN = process.env.BOT_TOKEN;
const TELEGRAM_API = `https://api.telegram.org/bot${TOKEN}`;

// Make HTTP request to Telegram API
async function tgRequest(method, params = {}) {
  try {
    const response = await axios.post(`${TELEGRAM_API}/${method}`, params, {
      timeout: 10000,
      headers: { 'Content-Type': 'application/json' }
    });
    
    if (!response.data.ok) {
      console.error(`[Telegram Error] ${method}:`, response.data.description);
      return null;
    }
    
    return response.data.result;
  } catch (err) {
    console.error(`[Telegram API Error] ${method}:`, err.message);
    return null;
  }
}

// Send message to chat
async function sendMessage(chatId, text, options = {}) {
  const params = {
    chat_id: chatId,
    text: text,
    parse_mode: 'HTML',
    ...options
  };

  return await tgRequest('sendMessage', params);
}

// Send message with inline keyboard
async function sendMessageWithKeyboard(chatId, text, keyboard) {
  return await sendMessage(chatId, text, {
    reply_markup: {
      inline_keyboard: keyboard
    }
  });
}

// Answer callback query
async function answerCallbackQuery(queryId, text, showAlert = false) {
  return await tgRequest('answerCallbackQuery', {
    callback_query_id: queryId,
    text: text,
    show_alert: showAlert
  });
}

// Delete message
async function deleteMessage(chatId, messageId) {
  return await tgRequest('deleteMessage', {
    chat_id: chatId,
    message_id: messageId
  });
}

// Get bot info
async function getMe() {
  return await tgRequest('getMe');
}

// Set webhook
async function setWebhook(url) {
  return await tgRequest('setWebhook', {
    url: url,
    drop_pending_updates: true,
    allowed_updates: ['message', 'callback_query']
  });
}

// Get webhook info
async function getWebhookInfo() {
  return await tgRequest('getWebhookInfo');
}

// Delete webhook
async function deleteWebhook() {
  return await tgRequest('deleteWebhook');
}

module.exports = {
  tgRequest,
  sendMessage,
  sendMessageWithKeyboard,
  answerCallbackQuery,
  deleteMessage,
  getMe,
  setWebhook,
  getWebhookInfo,
  deleteWebhook
};
