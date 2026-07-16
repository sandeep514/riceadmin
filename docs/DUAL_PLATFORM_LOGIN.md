# Dual platform login (web + mobile)

Same user can stay logged in on **one web** and **one mobile** device at the same time.

## Columns (`users`)

| Column | Platform |
|--------|----------|
| `api_token` | Web (portal) / mirrored on native app login |
| `mobile_api_token` | Mobile / native app |
| `session_version` | Native app session counter (bumped on password login and OTP send) |
| `user_token` | FCM only (not login) |

## Login / OTP verify

Send platform so the correct token column is rotated:

- Body: `platform=mobile` (or `client` / `device_type`)
- Or header: `X-Client-Platform: mobile`

Accepted mobile values: `mobile`, `app`, `android`, `ios`.

If omitted:

- **Mobile User-Agent** (`Android`, `iPhone`, `iPad`, `Mobile`, …) → treated as **mobile**
- Otherwise → treated as **web** (desktop Netlify continues to work)

Clients should send platform explicitly:

- **Native app (`sntcApp`):** `platform=mobile` / `X-Client-Platform: mobile`
- **Website portal (`sntc-website`):** `platform=web` / `X-Client-Platform: web` (required so phone browsers do not rotate `mobile_api_token`)

### Portal OTP

- Web login rotates `api_token` only
- Mobile login (`platform=mobile` or mobile UA) rotates `mobile_api_token` only

### App password login / OTP (`POST /api/login`, sendOTP, resendOTP)

On app password login **or** when OTP is sent/resent for an app user (`userType=1`), the server:

1. Rotates **`mobile_api_token` and `api_token`** to the same new value (old session expires immediately)
2. Increments **`session_version`**
3. Sends FCM data push `type=force_logout` to the previous `user_token` (if any)

`verifyOTP` / `verifyUser` return the current session token (issued at OTP send) so the verifying device can complete login.

Response fields (legacy-friendly):

```json
{
  "status": "success",
  "token": "<token>",
  "api_token": "<token>",
  "session_version": 3,
  "platform": "mobile",
  "user": {
    "id": 123,
    "api_token": "<token>",
    "token": "<token>",
    "session_version": 3
  }
}
```

Persist `token` / `api_token` and `session_version` on the device.

## Native app soft auth (`app.api.token`)

- **No token** → request allowed (legacy apps that only send `userId`)
- **Stale / wrong token** → `401` with `session_expired: true` (previous phone is kicked)
- Valid token → request allowed; ownership checks apply when user ids are present

Send token when available:

- `Authorization: Bearer <token>`, or
- Header `X-API-TOKEN: <token>`, or
- Query/body `api_token` / `token`

### Session probes

- `GET /api/app/session`
- `GET /api/check/user/expired/{id}` (optional `api_token` / `session_version`)

Both return JSON (HTTP 200 when allowed) including:

- `session_expired` (bool)
- `session_version` (int)

If `session_expired` is true → clear local session and show login.

### FCM force logout

Data payload (no notification body required):

- `type`: `force_logout`
- `session_expired`: `true`

App should clear local auth when this arrives.

### Portal notification FCM (dual login)

When a portal notification is broadcast on Reverb (`WebPortalNotificationEvent` → `web-user.{id}`), the server also queues FCM to that user's `user_token` (if set) with:

- `type`: `portal_notification`
- `notification_id`, `user_id`

Web users see the bell update via Reverb; the same account on the mobile app gets a push even when the app is not subscribed to the private channel.

Legacy app-only users (`userType` 1) still receive trade FCM via `TradeWebNotificationService::eligibleAppUserIdsForFcm`.

## Protected portal APIs

Send `Authorization: Bearer <token>` as today.

Auth is **platform-scoped**:

- Mobile request → only `mobile_api_token` is accepted
- Web request → only `api_token` is accepted

## Logout

Clears only the token for the request platform (or web `api_token` on cookie-session logout). The other platform stays logged in.

## Same-platform kick

- Second web login → previous web token invalid; mobile unchanged
- Second app login **or OTP send** → previous app token + session_version invalid; FCM `force_logout` to old device; web portal unchanged
- Two phones on the same app account → only the latest login / OTP session stays
