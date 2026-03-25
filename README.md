# FlipBook

A Laravel web application for creating and sharing digital flipbooks, managing print/design orders, templates, and user credits. It includes a public marketing site, authenticated user panel, admin dashboard, REST APIs (Sanctum), payments (Stripe), optional Google sign-in, AI-assisted features (Google Gemini / Laravel AI), and optional session replay and click heatmaps.

## Requirements

- **PHP** 8.2+
- **Composer** 2.x
- **Node.js** 18+ and **npm** (for Vite / front-end assets)
- A database: **SQLite** (default in `.env.example`) or **MySQL** / **MariaDB**

## Quick start

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Create the database (SQLite example):

```bash
touch database/database.sqlite
```

Run migrations (and seeders if you use them):

```bash
php artisan migrate
# php artisan db:seed
```

Install JavaScript dependencies and build assets:

```bash
npm install
npm run build
```

Start the application:

```bash
php artisan serve
```

For local development, the project provides a combined dev script (HTTP server, queue worker, log tail, and Vite):

```bash
composer run dev
```

Or use the full setup script once (install, `.env`, key, migrate, npm install, production build):

```bash
composer run setup
```

## Configuration

Copy `.env.example` to `.env` and adjust at least:

| Area | Notes |
|------|--------|
| `APP_URL` | Must match how you access the app (affects OAuth redirects, links). |
| `DB_*` | Default is SQLite; uncomment MySQL settings if needed. |
| `QUEUE_CONNECTION` | Default `database` — run a worker for jobs (AI generation, mail, thumbnails, etc.). |
| `STRIPE_KEY` / `STRIPE_SECRET` | Stripe Checkout / billing (see `config/services.php`). |
| `GEMINI_API_KEY` | AI image/content features ([Google AI Studio](https://aistudio.google.com/apikey)). |
| `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` | Optional Google OAuth (`/auth/google`). |
| Mail | Set `MAIL_*` for real outbound email (default `log` driver logs only). |

**Queues:** Long-running jobs may need a worker timeout above Laravel’s default. Example:

```bash
php artisan queue:work --timeout=620
```

See comments in `.env.example` for `DB_QUEUE_RETRY_AFTER` and backup/session recording options.

**Scheduler:** The app schedules `scheduled-mail:process` every minute. In production, add a cron entry:

```cron
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## Testing

```bash
composer run test
```

## Project structure (high level)

- `app/Http/Controllers` — Web and feature controllers (designs, orders, flipbooks, admin, API).
- `app/Http/Controllers/Api` — Sanctum-protected JSON API under `/api`.
- `routes/web.php` — Public pages, auth, dashboard, admin routes.
- `routes/api.php` — API routes (mounted with `web` middleware stack and `/api` prefix; see `bootstrap/app.php`).
- `resources/views` — Blade templates.
- `resources/js`, `resources/css` — Vite entrypoints (Tailwind CSS 4).
- `modules/` — Custom modules (e.g. PayHere, Nano Banana) autoloaded via `composer.json`.

## Documentation

- [User panel API](docs/API-USER-PANEL.md) — `/api` endpoints, auth, profile, orders, credits.
- [Session recording](docs/SESSION-RECORDING.md) — optional rrweb replay (admin).
- [User heatmap](docs/USER_HEATMAP.md) — optional click heatmaps (admin).
- [Server timeouts](docs/SERVER-TIMEOUTS.md) — deployment notes for long requests/jobs.

Public in-app documentation is available at `/docs` when configured.

## Tech stack

- [Laravel 12](https://laravel.com/docs/12.x)
- [Laravel Sanctum](https://laravel.com/docs/sanctum) — API tokens
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission) — roles/permissions
- [DomPDF](https://github.com/barryvdh/laravel-dompdf) — PDF generation
- [Stripe PHP SDK](https://stripe.com/docs) — payments
- [Laravel Socialite](https://laravel.com/docs/socialite) — Google OAuth
- [Laravel AI](https://github.com/laravel/ai) — AI integrations
- Vite 7, Tailwind CSS 4, Axios

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT). Application-specific licensing is determined by the project owner.
