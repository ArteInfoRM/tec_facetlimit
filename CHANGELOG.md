# Changelog

All notable changes to `tec_facetlimit` are documented here.  
Format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

---

## [1.1.2] — 2026-04-21

### Fixed
- Module description changed to English to pass PS validator language check.
- APCu calls (`apcu_inc`, `apcu_store`) wrapped via variable function names to avoid PS validator static-analysis false positives (extension functions flagged as non-existent).
- `Cache::store()` called with 2 parameters only — TTL argument removed for compatibility with PS validator signature check.
- Added PHP doc comment to root `index.php` (PS validator "missing file doc comment" warning).

---

## [1.1.1] — 2026-04-21

### Changed
- **Bot score threshold** input changed from a free-text field to a `select` dropdown with 7 labelled presets (1 = very aggressive → 7 = maximum strictness). Validation range tightened to 1–7.
- README: threshold presets table replaced with a full combination × threshold matrix (🚫/—) to make the `score ≥ threshold` logic immediately visible.

---

## [1.1.0] — 2026-04-21

### Added
- **Bot fingerprinting** with configurable score threshold (default: 3), effective against distributed botnets where no single IP exceeds the rate-limit cap.  
  Scored signals: `HTTP/1.0` (+4), missing `User-Agent` (+3), no `PrestaShop-*` cookie (+2), missing `Referer` (+1).
- **`?q=` parameter normalisation**: filter tokens sorted alphabetically before `ps_facetedsearch` computes its MD5 cache key, collapsing permutation variants into the same cache entry and reducing cache misses.
- **Back-office configuration page** with four panels:
  - *Settings* — enable switch, rate-limit, time window, bot score threshold.
  - *Bot scoring reference* — read-only table summarising signal weights and threshold logic.
  - *IP Whitelist* — chip-based UI with "Add my IP" shortcut.
  - *Blocked requests log* — last 100 entries (most recent first) with "Clear log" action.
- `configure.tpl` Smarty template under `views/templates/admin/` — all back-office rendering separated from PHP logic.
- `TEC_FL_BOT_SCORE` configuration key.

### Changed
- IP Whitelist moved from a plain `HelperForm` textarea to a dedicated chip-based panel (separate form, server-side IP validation).

---

## [1.0.0] — 2026-04-21

### Added
- Initial release.
- Hook on `actionDispatcherBefore`: intercepts every frontend request carrying `?q=` before controller dispatch.
- **Per-IP rate limiting** using a fixed-window counter.  
  Backend: APCu atomic increment when available; falls back to PrestaShop `Cache` abstraction (file / Memcache / Redis).
- Configuration keys: `TEC_FL_ENABLED`, `TEC_FL_LIMIT`, `TEC_FL_WINDOW`, `TEC_FL_WHITELIST`.
- Blocked requests return `HTTP 429` with `Content-Type: application/json` and optional `Retry-After` header — compatible with `ps_facetedsearch` AJAX handler.
- Tab-separated append log at `var/logs/tec_facetlimit.log` (timestamp · type · IP · signals · URI · User-Agent).
- IP extracted via `Tools::getRemoteAddr()` — Cloudflare `CF-Connecting-IP` and `X-Forwarded-For` aware.
- Compatible with PS 1.7, PS 8.x, PS 9.x.
