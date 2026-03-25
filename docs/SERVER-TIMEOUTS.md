# Fixing 504 Gateway Timeout for AI Features

AI content generation (e.g. design/ai-content-templates generate) can take 30–60+ seconds. If you see **504 Gateway Time-out** from nginx, the request is being cut off by the server before the app can respond.

## 1. Nginx

Increase the proxy/upstream timeouts so nginx waits longer for PHP/Laravel.

**If using nginx as reverse proxy** (e.g. to PHP-FPM), in your server or `location` block:

```nginx
location ~ \.php$ {
    # ... your fastcgi params ...
    fastcgi_read_timeout 120s;
    fastcgi_send_timeout 120s;
}
```

**If nginx proxies to another server**:

```nginx
proxy_connect_timeout 120s;
proxy_send_timeout 120s;
proxy_read_timeout 120s;
```

Then reload nginx: `sudo nginx -t && sudo systemctl reload nginx` (Linux).

## 2. Optional: Allow longer AI requests from the app

The app limits the Gemini request to 50 seconds by default so it usually responds before a 60s gateway timeout. If you have increased nginx (and PHP) timeouts above (e.g. 120s), you can allow longer AI requests by setting in `.env`:

```env
GEMINI_REQUEST_TIMEOUT=90
```

Do not set this higher than your nginx/PHP timeouts.

## 3. PHP (php.ini)

Ensure PHP allows long-running scripts (only needed if you increased nginx and use GEMINI_REQUEST_TIMEOUT):

```ini
max_execution_time = 120
```

## 4. Queue worker: `FAIL` + `zsh: killed` (exit 137)

If `GenerateAiContentFromTemplateJob` shows **FAIL** after ~2 minutes and the shell prints **`zsh: killed`** with **exit code 137**, the worker process was **SIGKILL**’d—usually because the job hit Laravel’s **per-job timeout** (defaults were 120s while multi-page AI can take longer).

1. The job timeout is **600 seconds** in `GenerateAiContentFromTemplateJob`. Run the worker with a **higher** process timeout:

   ```bash
   php artisan queue:work --timeout=620
   ```

2. For the **database** queue driver, set `DB_QUEUE_RETRY_AFTER` in `.env` **above** your longest job (default in `config/queue.php` is 660). If `retry_after` is too low, jobs can be released while still running.

3. **OOM**: If RAM is tight, the OS may still kill PHP (also 137). Check memory while a job runs, or reduce template page count / model load.
