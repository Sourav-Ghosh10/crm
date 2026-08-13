import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Debug logging — shows connection status in browser console
Pusher.logToConsole = true;

const pusherKey     = import.meta.env.VITE_PUSHER_APP_KEY     || window.__PUSHER_KEY__;
const pusherCluster = import.meta.env.VITE_PUSHER_APP_CLUSTER || window.__PUSHER_CLUSTER__;

// Use the PHP-injected URL so sub-path deployments (e.g. /crm) work correctly
const authEndpoint = window.__PUSHER_AUTH_URL__ || '/broadcasting/auth';
const csrfToken    = window.__CSRF_TOKEN__       || document.querySelector('meta[name="csrf-token"]')?.content || '';

if (pusherKey) {
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: pusherKey,
        cluster: pusherCluster,
        forceTLS: true,
        authEndpoint: authEndpoint,
        auth: {
            headers: {
                'X-CSRF-TOKEN':      csrfToken,
                'X-Requested-With':  'XMLHttpRequest',
            },
        },
    });

    console.log('[Echo] Initialized ✅');
    console.log('[Echo] Key:', pusherKey, '| Cluster:', pusherCluster);
    console.log('[Echo] Auth URL:', authEndpoint);
} else {
    console.error('[Echo] ❌ Pusher key not found — real-time disabled.');
}
