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

If omitted → treated as **web** (Netlify continues to work without changes).

Response still includes `"token": "..."` plus `"platform": "web"|"mobile"`.

### Portal OTP

- `POST` verify OTP / verify OTP login
- Web login rotates `api_token` only
- Mobile login (`platform=mobile`) rotates `mobile_api_token` only

### App password login

- `POST /api/login` always stores/returns `mobile_api_token`

## Protected APIs

Send `Authorization: Bearer <token>` as today. Middleware accepts either column. No `platform` needed on normal APIs.

## Logout

Clears only the token that was used (or web `api_token` on cookie-session logout). The other platform stays logged in.

## Same-platform kick

- Second web login → previous web token invalid; mobile unchanged
- Second mobile login → previous mobile token invalid; web unchanged
