# Dual platform login (web + mobile)

Same user can stay logged in on **one web** and **one mobile** device at the same time.

## Columns (`users`)

| Column | Platform |
|--------|----------|
| `api_token` | Web (portal) / also mirrored on native app login for kick |
| `mobile_api_token` | Mobile / native app |
| `user_token` | FCM only (not login) |

## Login / OTP verify

Send platform so the correct token column is rotated:

- Body: `platform=mobile` (or `client` / `device_type`)
- Or header: `X-Client-Platform: mobile`

Accepted mobile values: `mobile`, `app`, `android`, `ios`.

If omitted:

- **Mobile User-Agent** (`Android`, `iPhone`, `iPad`, `Mobile`, …) → treated as **mobile**
- Otherwise → treated as **web** (desktop Netlify continues to work)

Clients should still send `platform=mobile` explicitly when possible.

Response still includes `"token": "..."` plus `"platform": "web"|"mobile"`.

### Portal OTP

- `POST` verify OTP / verify OTP login
- Web login rotates `api_token` only
- Mobile login (`platform=mobile` or mobile UA) rotates `mobile_api_token` only

### App password login (`POST /api/login`)

- Always stores a new token on **`mobile_api_token` and `api_token`** (same value)
- Previous phone’s token no longer matches → next protected call returns **401** with `session_expired: true`
- Response: `token` + `platform: mobile`

## Native app: send the token

Protected app routes use middleware `app.api.token`. Send the login token on each call:

- `Authorization: Bearer <token>`, or
- Header `X-API-TOKEN: <token>`, or
- Query/body `api_token` / `token`

Session probe: `GET /api/app/session` (same auth). Use this on app resume; if `session_expired`, force logout UI.

## Protected portal APIs

Send `Authorization: Bearer <token>` as today.

Auth is **platform-scoped**:

- Mobile request → only `mobile_api_token` is accepted
- Web request → only `api_token` is accepted

Platform comes from body/header, or from User-Agent when omitted. A leftover web token will not authenticate a mobile client (and vice versa).

## Logout

Clears only the token for the request platform (or web `api_token` on cookie-session logout). The other platform stays logged in.

## Same-platform kick

- Second web login → previous web token invalid; mobile unchanged
- Second mobile / app login → previous mobile token invalid; web portal unchanged
- Two phones on the same app account → only the latest login works (first gets 401)
