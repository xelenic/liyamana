# User Panel API

JSON APIs that mirror the authenticated user panel. Use token auth (Sanctum) or session cookie (same-origin).

**Base URL:** `/api`

**Headers:** `Accept: application/json` for JSON responses.

**Authentication (protected routes):** Send `Authorization: Bearer {token}` (from login/register) or session cookie when using the app from the same origin.

---

## Auth (public)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/login` | Login. Body: `email`, `password`; optional: `device_name`. Returns `token` and `user`. |
| POST | `/api/register` | Register. Body: `name`, `email`, `password`, `password_confirmation`; optional: `device_name`. Returns `token` and `user`. Respects admin "allow registration" setting. |

**Auth response shape:** `{ "success": true, "message": "...", "data": { "user": { "id", "name", "email", "balance" }, "token": "...", "token_type": "Bearer" } }`

## Auth (protected)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/logout` | Revoke current token (or end session). |
| GET | `/api/me` | Current user (id, name, email, balance, avatar). |

Use the returned `token` in subsequent requests: `Authorization: Bearer {token}`.

---

## Endpoints (protected)

### Profile

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/user/profile` | Get current user profile (id, name, email, balance, avatar, etc.) |
| PUT | `/api/user/profile` | Update name and email |
| PUT | `/api/user/password` | Update password (body: `current_password`, `password`, `password_confirmation`) |

### Address Book

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/user/address-book` | List all address book entries |
| POST | `/api/user/address-book` | Create entry (label, contact_name, email, phone, address_line1, address_line2, city, state, postal_code, country) |
| PUT | `/api/user/address-book/{id}` | Update entry |
| DELETE | `/api/user/address-book/{id}` | Delete entry |

### Orders

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/orders` | List orders (paginated). Query: `per_page` (default 15, max 50) |
| GET | `/api/orders/{id}` | Get single order with checkout_data and invoice_url |

### Credits

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/credits` | Balance, top-up config (min/max amount, payment methods), currency symbol |
| GET | `/api/credits/transactions` | List credit transactions (paginated). Query: `per_page` |

### Notifications

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/user/notifications` | List notifications. Query: `limit` (default 20, max 50) |
| POST | `/api/user/notifications/read-all` | Mark all as read |
| POST | `/api/user/notifications/{id}/read` | Mark one as read |

---

## Response format

- Success: `{ "success": true, "data": ... }` or `{ "success": true, "message": "..." }`
- Error (4xx/5xx): `{ "success": false, "message": "..." }` or validation `{ "message": "...", "errors": { "field": ["..."] } }`

## Token auth

1. **Get a token:** `POST /api/login` with `email` and `password` (or `POST /api/register`). Response includes `data.token`.
2. **Call protected endpoints:** Send header `Authorization: Bearer {token}` on every request.
3. **Logout:** `POST /api/logout` with the same Bearer token to revoke it.
