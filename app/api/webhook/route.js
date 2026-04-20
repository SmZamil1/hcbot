import { handleUpdate } from '../../../api/webhook';

export const config = {
  api: {
    bodyParser: {
      sizeLimit: '1.5mb',
    },
  },
};

export async function POST(request) {
  try {
    const update = await request.json();
    
    // Handle the Telegram update
    const result = await handleUpdate(update);
    
    return new Response(JSON.stringify(result), {
      status: 200,
      headers: { 'Content-Type': 'application/json' }
    });
  } catch (error) {
    console.error('[Webhook Error]', error);
    return new Response(JSON.stringify({ error: 'Internal server error' }), {
      status: 500,
      headers: { 'Content-Type': 'application/json' }
    });
  }
}

// Respond to GET requests
export async function GET(request) {
  return new Response(JSON.stringify({ 
    status: 'Webhook is active',
    timestamp: new Date().toISOString()
  }), {
    status: 200,
    headers: { 'Content-Type': 'application/json' }
  });
}
