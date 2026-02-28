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
