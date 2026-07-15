# Dual platform login (web + mobile)

Same user can stay logged in on **one web** and **one mobile** device at the same time.

## Columns (`users`)

| Column | Platform |
|--------|----------|
| `api_token` | Web |
| `mobile_api_token` | Mobile |
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

### App password login

- `POST /api/login` always stores/returns `mobile_api_token`

## Protected APIs

Send `Authorization: Bearer <token>` as today.

Auth is **platform-scoped**:

- Mobile request → only `mobile_api_token` is accepted
- Web request → only `api_token` is accepted

Platform comes from body/header, or from User-Agent when omitted. A leftover web token will not authenticate a mobile client (and vice versa).

## Logout

Clears only the token for the request platform (or web `api_token` on cookie-session logout). The other platform stays logged in.

## Same-platform kick

- Second web login → previous web token invalid; mobile unchanged
- Second mobile login → previous mobile token invalid; web unchanged
- Two phones on the same account → only the latest mobile session works
