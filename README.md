# tec_facetlimit — Faceted Search Rate Limiter

PrestaShop module that protects `ps_facetedsearch` against bot abuse and AI-scraper floods via the `?q=` parameter.

## Problem

Every unique `?q=` combination that misses the `ps_facetedsearch` block cache triggers a full database query chain across `ps_product`, `ps_layered_price_index`, and related tables. Distributed botnets and AI training crawlers generate millions of unique parameter combinations in a matter of days, saturating CPU without any single IP exceeding a reasonable threshold.

## Solution

### Shared shop and category limits

Select a single shop in the back office. In **Shared filter limits**, enter the sustainable shop rate per minute. Choose **Add category limit**, select one or more categories by name, enter their rate and confirm. Review the category limits and choose **Save** to apply them. Every supported listing request with a non-empty filter query shares the shop budget, including AJAX calls. Each listed category also has its own budget. The IP whitelist applies before these checks.

Choose **Observe** to simulate admission, then **Protect** to return HTTP 429 with a calculated Retry-After and Cache-Control: no-store. Existing installations start with this feature off. Each bucket refills continuously and can accumulate ten seconds of capacity (rounded up). A request consumes one token from each applicable budget only when both budgets admit it.

The dashboard shows the current minute, peak minute and cumulative excess for each configured scope that received requests. Shop excess includes all rejected decisions, including category limits. Category excess includes decisions caused by either limit. Mode or rate changes start fresh statistics for each affected bucket on its next request. Aggregate decisions use one file lock per shop in the writable shop cache directory. Workers on independent filesystems enforce independent quotas. A storage failure allows the request to proceed.

The existing bot and per-IP checks run after the shared limits:

### Layer 1 — Bot fingerprinting (effective against distributed botnets)

Each HTTP signal contributes a score. The request is blocked when the total score reaches the configured threshold (default: **4**).

| Signal | Score | Rationale |
|--------|------:|-----------|
| Missing `User-Agent` | **3** | The request does not identify its client software. |
| Missing `PrestaShop-*` cookie | **2** | A first-time visitor may have no shop cookie. |
| Missing `Referer` | **1** | Direct links and browser privacy settings can omit this header. |

### Layer 2 — Per-IP rate limiting (effective against aggressive single sources)

Counts `?q=` requests per IP within a fixed window (default: 30 req / 60 s). Uses **APCu** atomic increment when available; falls back to a file-based counter in `var/cache/` with `flock` for atomicity.

The file backend reuses up to 256 shards per shop. Each shard rotates on first use in the next time window. Requests do not scan the cache directory. **Clear log** also clears file-backed IP counts and removes old counter files from earlier module versions.

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
| Bot score threshold | 4 — Recommended · default | 1–6 | See examples below |
| IP Whitelist | _(empty)_ | — | IPs exempt from all checks (IPv4 and IPv6) |

### Bot score threshold presets

The module blocks a request when the sum reaches the configured threshold. At the default of **4**, a first-time visitor with a User-Agent opening a direct link scores 3 and passes this check. A request missing both User-Agent and Referer scores 4. At **3**, the first visit in the example also triggers a block. At **6**, all three signals must be missing. Existing thresholds from 1 through 6 retain their values. A previously saved threshold of 7 uses 6.

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
| `TEC_FL_BOT_SCORE` | int | `4` |
| `TEC_FL_AGGREGATE` | JSON | Off, 150/min, no category overrides |
| `TEC_FL_WHITELIST` | string | _(empty)_ |

## License

One paid licence per website using this module. No rental, resale, or redistribution.  
© 2009–2026 Tecnoacquisti.com — Arte e Informatica <helpdesk@tecnoacquisti.com>
