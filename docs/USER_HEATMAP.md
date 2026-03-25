# User-wise click heatmaps

Yes — implemented as **per-user aggregated click positions** (not a DOM screenshot).

## How it works

1. **Collection** (user app, `layouts.app`): on each click, the client records `path`, `x/y` as **percent of viewport** (works across screen sizes), and batches POSTs to `POST /user/heatmap/clicks`.
2. **Storage**: rows in `user_heatmap_clicks` keyed by `user_id` + `path`.
3. **Admin**: **Settings → User heatmaps** lists users with data; open a user, pick a **path**, view an intensity map built with [heatmap.js](https://www.patrick-wied.at/static/heatmapjs/) (CDN) on a fixed **900×560** canvas (relative positions, not a live page capture).

## Enable

- `.env`: `USER_HEATMAP_ENABLED=true`, or  
- **Admin → Settings → Features → Collect click heatmaps**

Same rules as session recording: **not collected for admin-role** users.

## Privacy

Disclose in your privacy policy; add `data-no-heatmap` on sensitive controls to skip capturing that click (ancestor check).

## Optional env

- `USER_HEATMAP_MAX_CLICKS_PER_INGEST` (default 40)  
- `USER_HEATMAP_ADMIN_MAX_POINTS` (default 8000) — cap points loaded for one path in admin  

## rrweb vs heatmap

Session **replay** (rrweb) and **heatmaps** are separate. You can enable either or both.
