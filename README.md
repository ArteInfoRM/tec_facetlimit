# tec_facetlimit — Faceted Search Rate Limiter

PrestaShop module that protects `ps_facetedsearch` against bot abuse and AI-scraper floods via the `?q=` parameter.

## Problem

Every unique `?q=` combination that misses the `ps_facetedsearch` block cache triggers a full database query chain across `ps_product`, `ps_layered_price_index`, and related tables. Distributed botnets and AI training crawlers generate millions of unique parameter combinations in a matter of days, saturating CPU without any single IP exceeding a reasonable threshold.

## Solution

Two independent defence layers, evaluated on every frontend request that carries `?q=`:

### Layer 1 — Bot fingerprinting (effective against distributed botnets)

Each HTTP signal contributes a score. The request is blocked when the total score reaches the configured threshold (default: **4**).

| Signal | Score | Rationale |
|--------|------:|-----------|
| `HTTP/1.0` protocol | **4** | No real browser has used HTTP/1.0 since 1996 — blocks alone. **Auto-disabled** when a reverse-proxy header is detected (`X-Forwarded-For`, `X-Real-IP`, `CF-Connecting-IP`) — Nginx/Apache and Cloudflare setups use HTTP/1.0 internally. |
| Missing `User-Agent` | **3** | Non-browser client — blocks alone |
| No `PrestaShop-*` cookie | **2** | Real users always acquire the session cookie on first page view |
| Missing `Referer` | **1** | Browsers always send `Referer` on navigation and same-origin AJAX |

**Threshold logic (default = 4):**
- `HTTP/1.0` alone → score 4 → **blocked**
- Missing UA alone → score 3 → **passes** at T=4 (blocks at T=3)
- No cookie + no Referer → score 3 → **passes** at T=4 (blocks at T=3 — use during active attacks)
- No cookie only → score 2 → **passes**
- No Referer only → score 1 → **passes**

### Layer 2 — Per-IP rate limiting (effective against aggressive single sources)

Counts `?q=` requests per IP within a fixed window (default: 30 req / 60 s). Uses **APCu** atomic increment when available; falls back to a file-based counter in `var/cache/` with `flock` for atomicity.

Counter files (`tec_fl_*.cnt`) are automatically rotated: ~1% of requests trigger a cleanup pass that removes files older than `window × 3` seconds. All counter files are also deleted when the **Clear log** action is triggered from the back-office, which resets all rate-limit counters instantly — useful to restore normal access after an attack.

### Bonus — `?q=` parameter normalisation

Filter tokens inside `?q=` are sorted alphabetically before `ps_facetedsearch` computes its MD5 cache key. This collapses permutation variants (`Brand-Nike/Color-Red` and `Color-Red/Brand-Nike`) into the same cache entry, significantly reducing cache misses caused by enumeration attacks.

## Requirements

| Component | Version |
|-----------|---------|
| PrestaShop | 1.7 – 9.x |
| PHP | 8.1+ |
| APCu | optional (recommended for best rate-limit accuracy) |

## Installation

1. Upload the `tec_facetlimit/` directory to `<shop_root>/modules/`.
2. Go to **Back-office → Modules → Module Manager**, search for *Faceted Search Rate Limiter* and click **Install**.

## Configuration

Navigate to **Modules → Faceted Search Rate Limiter → Configure**.

| Setting | Default | Range | Description |
|---------|--------:|-------|-------------|
| Enable protection | On | — | Master kill-switch |
| Max requests per window | 30 | 1–10 000 | Per-IP `?q=` request cap |
| Time window | 60 s | 1–3 600 | Window duration for the rate limiter |
| Bot score threshold | 4 — Recommended · default | 1–7 | See presets table below |
| IP Whitelist | _(empty)_ | — | IPs exempt from all checks (IPv4 and IPv6) |

### Bot score threshold presets

A request is blocked when its total score **≥ threshold**. A higher threshold means fewer blocked requests (more permissive). The default **4** allows first-time visitors with direct links to pass; switch to **3** during active attacks.

| Combination | Score | T=1 | T=2 | T=3 | T=4 ★ | T=5 | T=6 | T=7 |
|---|---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| No `Referer` only | 1 | 🚫 | — | — | — | — | — | — |
| No cookie only | 2 | 🚫 | 🚫 | — | — | — | — | — |
| No `User-Agent` only | 3 | 🚫 | 🚫 | 🚫 | — | — | — | — |
| No cookie + no `Referer` | 3 | 🚫 | 🚫 | 🚫 | — | — | — | — |
| `HTTP/1.0` only | 4 | 🚫 | 🚫 | 🚫 | 🚫 | — | — | — |
| No `User-Agent` + no `Referer` | 4 | 🚫 | 🚫 | 🚫 | 🚫 | — | — | — |
| No cookie + no `User-Agent` | 5 | 🚫 | 🚫 | 🚫 | 🚫 | 🚫 | — | — |
| `HTTP/1.0` + no `Referer` | 5 | 🚫 | 🚫 | 🚫 | 🚫 | 🚫 | — | — |
| `HTTP/1.0` + no cookie | 6 | 🚫 | 🚫 | 🚫 | 🚫 | 🚫 | 🚫 | — |
| No cookie + no UA + no `Referer` | 6 | 🚫 | 🚫 | 🚫 | 🚫 | 🚫 | 🚫 | — |
| `HTTP/1.0` + no `User-Agent` | 7 | 🚫 | 🚫 | 🚫 | 🚫 | 🚫 | 🚫 | 🚫 |
| `HTTP/1.0` + no cookie + no `Referer` | 7 | 🚫 | 🚫 | 🚫 | 🚫 | 🚫 | 🚫 | 🚫 |

🚫 = blocked · — = passes · ★ = default

## Blocked response

**AJAX requests** (ps_facetedsearch filter calls):
```
HTTP/1.1 429 Too Many Requests
Content-Type: application/json; charset=utf-8
Retry-After: <window_seconds>   (rate-limit blocks only)

{"error":"rate_limited"}
```

**Direct browser navigation** (typing the URL in the address bar):  
A minimal HTML page is returned with a friendly message and a back-link to the category — no raw JSON shown to the user.

## Log file

Blocks are appended to `<shop_root>/var/logs/tec_facetlimit.log` (tab-separated):

```
2026-04-21 10:45:30   BOT         1.2.3.4   no_ps_cookie,no_referer   /en/women?q=Brand-Nike   curl/7.68.0
2026-04-21 10:45:31   RATELIMIT   5.6.7.8   ip_exceeded               /en/men?q=Color-Blue      Mozilla/5.0 …
```

Columns: `timestamp · type · ip · signals · uri · user-agent`

The log can be viewed and cleared directly from the back-office configuration page.

## File structure

```
tec_facetlimit/
├── tec_facetlimit.php                  Main module class
├── index.php                           PS directory security
├── README.md
├── CHANGELOG.md
└── views/
    ├── index.php
    └── templates/
        ├── index.php
        └── admin/
            ├── index.php
            └── configure.tpl           Back-office configuration page
```

## Configuration keys

| Key | Type | Default |
|-----|------|--------:|
| `TEC_FL_ENABLED` | bool | `1` |
| `TEC_FL_LIMIT` | int | `30` |
| `TEC_FL_WINDOW` | int | `60` |
| `TEC_FL_BOT_SCORE` | int | `3` |
| `TEC_FL_WHITELIST` | string | _(empty)_ |

## License

One paid licence per website using this module. No rental, resale, or redistribution.  
© 2009–2026 Tecnoacquisti.com — Arte e Informatica <helpdesk@tecnoacquisti.com>
