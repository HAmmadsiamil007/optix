# Optix Profile Creator Guide

## Overview

A **Profile** is a complete frontend presentation layer for the Optix Framework. Profiles live in `profiles/{name}/` and contain templates, assets, and configuration — with zero business logic.

## Profile Structure

```
profiles/{name}/
  header.php          — Override theme header
  footer.php          — Override theme footer
  index.php           — Override theme index
  single.php          — Single post template
  page.php            — Page template
  archive.php         — Archive template
  functions.php       — Profile-specific functions
  assets/
    css/
      style.css       — Profile stylesheet (auto-enqueued)
    js/
      main.js         — Profile scripts
    img/
      logo.svg        — Profile images
  woocommerce/        — WooCommerce template overrides
```

## Creating a Profile

1. Create a directory under `profiles/` with a sanitized name (lowercase, hyphens).
2. Add only the template files you need to override. Unprovided templates fall back to `profiles/default/`.
3. Add a `description.txt` file for display in the Setup Wizard.

## Profile Resolution Order

1. Active profile directory
2. Default profile directory (`profiles/default/`)
3. Plugin template directory (`phantom-core/templates/`)
4. Theme root template

## Theme API Functions

Use these in profile templates to access settings:

- `phantom_option( $key, $default )` — Any setting
- `optix_string( $key, $default )` — Cast to string
- `optix_int( $key, $default )` — Cast to int
- `optix_bool( $key, $default )` — Cast to bool
- `optix_color( $key, $default )` — Sanitized hex color
- `optix_image( $key, $size )` — Image URL
- `optix_img( $path, $fallback )` — Profile-relative image
- `optix_asset_url( $relative_path )` — Profile asset URL

## Best Practices

- **No business logic**: Profiles are presentation-only. All logic belongs in the plugin.
- **Semantic HTML**: Use accessible, semantic markup.
- **CSS scoping**: Prefix profile-specific styles to avoid conflicts.
- **Fallback aware**: Always provide sensible defaults for theme API calls.
