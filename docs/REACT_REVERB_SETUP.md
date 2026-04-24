# React app – receive Reverb events from admin

Use this so your React app receives events sent from the admin dashboard (e.g. "Send Reverb notification to React").

## 1. Env (must match Laravel `.env`)

In the **React app** `.env` or `.env.local`:

```env
# Vite
VITE_REVERB_APP_KEY=ru9fetnneiub1ezhirqo
VITE_REVERB_HOST=localhost
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=http

# Or Create React App
REACT_APP_REVERB_APP_KEY=ru9fetnneiub1ezhirqo
REACT_APP_REVERB_HOST=localhost
REACT_APP_REVERB_PORT=8080
REACT_APP_REVERB_SCHEME=http
```

- **Same machine:** `REVERB_HOST=localhost` is fine.
- **React on another machine:** use the machine’s IP/hostname where Reverb runs (e.g. `VITE_REVERB_HOST=192.168.1.10`).

## 2. Install and create Echo

```bash
npm install laravel-echo pusher-js
```

**`src/echo.js`** (or `src/config/echo.js`):

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const scheme = import.meta.env?.VITE_REVERB_SCHEME ?? process.env.REACT_APP_REVERB_SCHEME ?? 'http';
const host = import.meta.env?.VITE_REVERB_HOST ?? process.env.REACT_APP_REVERB_HOST ?? 'localhost';
const port = import.meta.env?.VITE_REVERB_PORT ?? process.env.REACT_APP_REVERB_PORT ?? 8080;
const key = import.meta.env?.VITE_REVERB_APP_KEY ?? process.env.REACT_APP_REVERB_APP_KEY;

export default new Echo({
  broadcaster: 'reverb',
  key: key,
  wsHost: host,
  wsPort: port,
  wssPort: port,
  forceTLS: scheme === 'https',
  enabledTransports: ['ws', 'wss'],
});
```

## 3. Subscribe and listen (channel + event name)

- **Channel:** `admin-events`
- **Event name:** `AdminEvent` (listen as `.AdminEvent` in Echo).

Example in a component or hook:

```javascript
import { useEffect } from 'react';
import echo from './echo';  // or path to your echo.js

useEffect(() => {
  const channel = echo.channel('admin-events');
  channel.listen('.AdminEvent', (e) => {
    console.log('Reverb event:', e);
    // e.type, e.payload, e.timestamp
    if (e.type === 'admin_notification') {
      console.log('Admin says:', e.payload?.message);
    }
  });
  return () => {
    echo.leave('admin-events');
  };
}, []);
```

## 4. If React still doesn’t receive events

1. **Reverb running:** In the Laravel project run `php artisan reverb:start` and leave it running.
2. **Same key/host/port:** React env must use the same `REVERB_APP_KEY`, `REVERB_HOST`, `REVERB_PORT` as Laravel’s `.env`.
3. **Browser console:** Check for WebSocket errors (e.g. connection refused, CORS). Reverb allows all origins by default (`*`); if you set `REVERB_ALLOWED_ORIGINS` in Laravel, include your React origin (e.g. `http://localhost:3000`).
4. **Event name:** Listener must be `.AdminEvent` (with the dot). Not `AdminEvent` without dot, and not a different name.
5. **Channel:** Must be exactly `admin-events` (public channel, no auth).

## 5. Quick connection test

Log when Echo connects:

```javascript
echo.connector.pusher.connection.bind('connected', () => {
  console.log('Reverb connected');
});
```

Then send a notification from the admin dashboard and watch the `.AdminEvent` callback and `console.log('Reverb event:', e)`.

---

## 6. Production / Netlify (`sntc.netlify.app`) and `wss://snjtradelink.com:6001`

If the browser shows **WebSocket** requests stuck with **“Provisional headers are shown”** and **no response headers**, the TCP/TLS handshake never finished. That is **not** the same as HTTP CORS (the WS URL is opened directly by the browser to your Reverb host).

### Common causes

1. **Nothing listening** on `6001`, or Reverb is bound to `127.0.0.1` only (not reachable from the internet).
2. **TLS on a custom port** (`wss://…:6001`) often fails unless you terminate TLS correctly for that port (valid cert + Reverb TLS config, or a reverse proxy).
3. **Firewall / hosting** blocks inbound `6001` (very common on VPS unless you open it).
4. **`REVERB_ALLOWED_ORIGINS`** on the server is a **comma-separated list** that does **not** include `https://sntc.netlify.app` (Reverb will reject the socket). If unset, Reverb defaults to `*` (allow all) — see `config/reverb.php`.

### Recommended: WebSocket on **443** behind Nginx (avoid raw `:6001`)

Run Reverb locally on **8080** (or similar), no public TLS. Put **Nginx** on **443** with your normal certificate and **upgrade** WebSocket traffic to Reverb:

```nginx
# Inside your HTTPS server block for snjtradelink.com
location /app {
    proxy_http_version 1.1;
    proxy_set_header Host $http_host;
    proxy_set_header Scheme $scheme;
    proxy_set_header SERVER_PORT $server_port;
    proxy_set_header REMOTE_ADDR $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";
    proxy_pass http://127.0.0.1:8080;
}
```

Then in the **Netlify** (or Vite) env use **no custom WS port** — same host and **443**:

```env
VITE_REVERB_APP_KEY=<same as REVERB_APP_KEY in Laravel .env>
VITE_REVERB_HOST=snjtradelink.com
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https
```

Echo / Pusher-js will use `wss://snjtradelink.com/app/...` on port **443**, which uses your normal HTTPS certificate.

### If you insist on public **port 6001**

- Open the port in the firewall and ensure `php artisan reverb:start` listens on `0.0.0.0:6001`.
- Configure **TLS for Reverb** in `config/reverb.php` under `servers.reverb.options.tls` (cert/key paths), **or** terminate TLS with **stunnel**/Nginx in front of Reverb.
- Without valid TLS, **`wss://`** will fail; use **`ws://`** only on **localhost** / non-HTTPS pages (not on `https://sntc.netlify.app`).

### Laravel must actually broadcast

In production `.env`:

```env
BROADCAST_DRIVER=reverb
```

If this is `log` or `null`, **no events** are pushed to Reverb (nothing to receive on the SPA).

### Private channels (`web-user.{id}`) — auth URL

For `Echo.private('web-user.' + userId)` the client must POST to your API **broadcasting auth** URL with the **same Bearer token** as the portal. Point `authEndpoint` at your deployed API, e.g.:

```javascript
authEndpoint: 'https://snjtradelink.com/api/broadcasting/auth',
auth: {
  headers: {
    Authorization: `Bearer ${apiToken}`,
    Accept: 'application/json',
  },
},
```

Ensure that route is reachable from the Netlify origin (CORS for `api/*` is already set up for `https://sntc.netlify.app` in `config/cors.php`).
