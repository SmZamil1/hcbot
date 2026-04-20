import { setWebhook, getWebhookInfo } from '../../../../lib/telegram';

export async function GET(request) {
  try {
    const protocol = request.headers.get('x-forwarded-proto') || 'https';
    const host = request.headers.get('x-forwarded-host') || request.headers.get('host');
    const webhookUrl = `${protocol}://${host}/api/webhook`;

    console.log('[Webhook Setup] Setting webhook to:', webhookUrl);

    // Set the webhook
    const result = await setWebhook(webhookUrl);

    if (!result) {
      return new Response(JSON.stringify({ 
        ok: false, 
        error: 'Failed to set webhook' 
      }), {
        status: 500,
        headers: { 'Content-Type': 'application/json' }
      });
    }

    // Get webhook info to confirm
    const info = await getWebhookInfo();

    return new Response(JSON.stringify({ 
      ok: true,
      result: result,
      webhook_url: info?.url,
      pending_updates: info?.pending_update_count
    }), {
      status: 200,
      headers: { 'Content-Type': 'application/json' }
    });
  } catch (error) {
    console.error('[Webhook Setup Error]', error);
    return new Response(JSON.stringify({ 
      ok: false, 
      error: error.message 
    }), {
      status: 500,
      headers: { 'Content-Type': 'application/json' }
    });
  }
}
