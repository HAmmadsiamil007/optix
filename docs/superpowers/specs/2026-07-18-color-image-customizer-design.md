# Customizer — Full Color & Image Controls

## Goal
Make every themed color and frontend image editable via Customizer. Replace hardcoded CSS colors and image paths with dynamic values.

## Architecture Fix
Auto-generate `get_css_var_map()` from each setting's `css_property` field instead of maintaining a separate hardcoded map. Any setting with a `css_property` is automatically added to the map. Legacy manual entries in `get_css_var_map()` are merged for backward compatibility.

## Sections

### Colors Section (~25 settings)
- Primary / Secondary / Accent
- Text (body, heading, link, link-hover)
- Backgrounds (body, header, footer, section-light)
- Buttons (primary bg/text/hover, secondary bg/text/hover)
- Borders (general, divider)
- Gradient overlay (start, end)
- Featured tag background
- Each setting gets `css_property`, e.g. `--color-primary`, `--color-text-body`, `--color-btn-primary-bg`

### Images Section (~10 settings, inline in existing sections)
- Site Logo URL (replaces logo.png)
- Hero Banner BG (replaces banner-bg-img.png)
- Footer Logo (replaces footer-logo.png)
- About Mission Image
- CTA Background Image
- Favicon (apple-touch-icon, favicon-32x32, favicon-16x16)

### Frontend Updates
- style.css: replace ~20 hardcoded colors with `var(--color-*)` + fallbacks
- Shell class: inject image URLs via `str_replace` on HTML templates (same pattern as `inject_seo`)
- Shell class: inject color CSS vars for initial render (already done, will auto-include new colors)

### Customizer Integration
- Color picker controls use `WP_Customize_Color_Control`
- Image upload controls use `WP_Customize_Media_Control` or `WP_Customize_Image_Control`
- Live preview via `postMessage` (existing `customizer-preview.js` auto-binds CSS vars)
- Images need manual `postMessage` in preview JS

## Files Changed
- `includes/class-settings-registry.php` — new `section_colors()`, refactor `get_css_var_map()`, image settings
- `frontend/assets/css/style.css` — replace hardcoded colors with `var()` refs
- `templates/shell.php` — image URL injection
- `admin/js/customizer-preview.js` — image live preview bindings

## Success Criteria
- All themed colors editable in Customizer with live preview
- Logo, hero banner, footer logo, about image replaceable via Customizer
- Backward compatible — no broken settings
- PHP syntax passes, Docker sync works
