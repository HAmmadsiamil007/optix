# Phantom Core Framework

A **decoupled WordPress framework** that replaces traditional PHP template hierarchy with a static HTML SPA architecture. Dynamic data is injected client-side via a custom REST API.

## Quick Stats

| Metric | Value |
|--------|-------|
| Version | **1.5.0** |
| Settings | **555** across 44 sections |
| Customizer Panels | 14 panels, 49 sections |
| REST API Endpoints | **21** routes |
| PHP Files | **38** |
| HTML Templates | 21 (static, replaceable) |
| CSS Var Tokens | **65** |
| WooCommerce | Template overrides + REST endpoints |
| Backend Health | **98/100** — Code Quality 97, Security 100 |

## Architecture

```
WordPress Core ─── WooCommerce
       │                │
       └────────────────┘
              │
     Phantom Core Plugin
       │              │
  Settings Registry   │
  (555 settings)      │
       │              │
  ┌────┴────┬─────────┴──┐
  │         │            │
Customizer  Admin Page   REST API
(visual)    (form)       (21 routes)
  │         │            │
  └─────────┴────────────┘
              │
      Shell (SPA Router)
   template_redirect → HTML
              │
     ┌────────┴────────┐
     │                  │
  Static HTML       phantom-data.js
  Templates         (data injection)
```

## Key Concepts

- **No standard WordPress themes** — Plugin-based architecture. No `wp-content/themes/` exists.
- **Static HTML SPA** — 21 static HTML files. All dynamic data via REST API
- **Attribute-based binding** — `data-phantom="key"` on HTML elements drives data injection
- **CSS Variable architecture** — 65 design tokens as CSS custom properties on `:root`
- **Three-way customization** — Customizer (visual) + Admin (form) + REST API (programmatic)
- **WooCommerce via Store API** — Modern cart/checkout integration

## Documentation

| File | Contents |
|------|----------|
| `ARCHITECTURE.md` | Complete system architecture, data flow, component relationships |
| `FEATURES.md` | Full feature list — WordPress, WooCommerce, Theme Settings |
| `CUSTOMIZATION.md` | 555+ controls guide — Customizer, Admin, REST API, CSS vars |
| `FORENSIC-AUDIT.md` | Full backend audit results — 19 bugs fixed, health scores |
| `FRONTEND-GUIDE.md` | How to edit/replace frontend, data binding reference, WooCommerce hooks |
| `FRONTEND-REPLACE-GUIDE.md` | Step-by-step guide for complete frontend replacement |

## Backend Health (Post-Audit)

| Domain | Score | Status |
|--------|-------|--------|
| Code Quality | 97/100 | ✅ Dead code removed, no syntax errors, proper typing |
| Security | 100/100 | ✅ Nonce, sanitization, escaping, capabilities all verified |
| Performance | 98/100 | ✅ Efficient options-based storage, CSS caching |
| **Aggregate** | **98/100** | ✅ Production-ready |

### 19 Issues Fixed Across 2 Commits
- Removed dead `Phantom_Fonts` class + test files from production
- Fixed font loading bug — default Google Fonts not skipped
- **Critical: Fixed nonce corruption** — `sanitize_key()` was mutating nonce before `wp_verify_nonce()`
- Fixed 5 hardcoded `'1.0.0'` versions → `PHANTOM_CORE_VERSION`
- Fixed duplicate body class in `inject_editor()`
- Fixed `get_template_part()` crash without theme
- Fixed header_padding_x/y dead CSS keys + responsive array handling
- Added `esc_attr()` to responsive-helper CSS output
- Fixed permissive regex in color-group sanitize

## Quick Start

```bash
# Theme is activated in WordPress
# Settings managed via:
# - Customizer: /wp-admin/customize.php
# - Admin: /wp-admin/themes.php?page=phantom-core-settings
# - REST API: /wp-json/phantom/v1

# To push local changes to Docker:
docker cp phantom-core optix_wordpress:/var/www/html/wp-content/plugins/phantom-core

# To pull from Docker:
docker cp optix_wordpress:/var/www/html/wp-content/plugins/phantom-core ./phantom-core
```

## Requirements

- WordPress 6.4+
- PHP 8.1+
- WooCommerce (optional, for shop features)

## GitHub

- **Repo:** `github.com/HAmmadsiamil007/PHANTOM-CORE`
- **Branch:** `master` (primary)
- **Frontend:** To be replaced with React/Vue/Next.js — backend stays as-is
