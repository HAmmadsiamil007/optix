# Phantom Core Settings Bus — Design Spec

**Date**: 2026-07-18
**Status**: Draft
**Approach**: Settings Bus (Approach 2)

---

## 1. Architecture Overview

The full pipeline separates **Backend (Phantom Core)** from **Swappable Frontend** via a formal data adapter layer:

```
PHP Backend (stays unchanged)
┌──────────────────────────────────────────────────────────┐
│  Settings_Registry ──► class-customizer.php               │
│       │                      │                            │
│       │            ┌─────────┴──────────┐                │
│       │            │ Custom Controls    │                │
│       │            │ (8 types, vanilla  │                │
│       │            └─────────┬──────────┘                │
│       ▼                      ▼                            │
│  Options DB ◄────────── WP_Customize::save()              │
│       │                                                  │
│       ▼                                                  │
│  REST Controller ──► /phantom/v1/settings                │
│       │               /phantom/v1/partial                 │
│       ▼                                                  │
│  Shell ──► phantom-bridge.js (wp_localize_script)        │
└───────┬──────────────────────────────────────────────────┘
        │  PhantomData JS object
        ▼
Frontend (fully swappable)
┌──────────────────────────────────────────────────────────┐
│  PhantomBridge.js                                         │
│    ├── init() → reads PhantomData                        │
│    ├── getSetting(key) → value                           │
│    ├── setSetting(key, value) → REST API                 │
│    ├── onSettingChange(key, cb) → subscribe              │
│    ├── getCssVars() → {--phantom-primary: #000}          │
│    └── Editor hooks (highlight, openEditor, saveChanges) │
│                                                          │
│  ┌────────────────────────────────────────────────────┐  │
│  │  Bootstrap  │  GSAP  │  Three.js  │  Swiper        │  │
│  │  Theme CSS  │  HTML  │  Animations                  │  │
│  └────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────┘
```

**Rule**: PHP backend architecture stays. Frontend HTML/CSS/JS is replaceable. `PhantomBridge.js` is the only required adapter — any frontend imports it and gets all settings, CSS vars, and live editing.

---

## 2. Settings Registry Enhancement

New optional metadata fields for `register_settings()`:

| Field | Type | Purpose | Default |
|-------|------|---------|---------|
| `responsive` | bool | Auto-create desktop/tablet/mobile variants | `false` |
| `control_type` | string | Which custom control type to render | `'text'` |
| `dependencies` | array | Show/hide based on other settings | `[]` |
| `partial` | array\|false | Selective refresh config | `false` |
| `subheading` | string | Section subheading text | `''` |
| `input_attrs` | array | Passed to control | `[]` |
| `priority` | int | Control order within section | `10` |

### Example Enhanced Settings

```php
// Responsive spacing
'header_padding' => [
    'label'       => 'Header Padding',
    'type'        => 'ast-responsive-spacing',
    'section'     => 'phantom_header',
    'default'     => ['desktop' => ['top'=>0,'right'=>20,'bottom'=>0,'left'=>20],
                      'tablet'  => ['top'=>0,'right'=>15,'bottom'=>0,'left'=>15],
                      'mobile'  => ['top'=>0,'right'=>10,'bottom'=>0,'left'=>10]],
    'transport'   => 'postMessage',
    'responsive'  => true,
    'input_attrs' => ['min' => 0, 'max' => 200, 'step' => 1],
],

// Conditional toggle with partial refresh
'display_header' => [
    'label'       => 'Display Header',
    'type'        => 'ast-toggle',
    'section'     => 'phantom_header',
    'default'     => true,
    'transport'   => 'postMessage',
],
'header_style' => [
    'label'        => 'Header Style',
    'type'         => 'ast-radio-image',
    'section'      => 'phantom_header',
    'default'      => 'layout-1',
    'transport'    => 'postMessage',
    'dependencies' => [['setting' => 'display_header', 'value' => true]],
    'partial'      => ['selector' => '.site-header',
                       'container_inclusive' => true,
                       'render_callback' => 'phantom_render_header_partial'],
    'choices'      => [
        'layout-1' => 'assets/images/header-layout-1.png',
        'layout-2' => 'assets/images/header-layout-2.png',
        'layout-3' => 'assets/images/header-layout-3.png',
    ],
],
```

---

## 3. Custom Control Types (8 Controls)

Each control has a PHP class in `includes/custom-controls/` + a vanilla JS file in `admin/js/custom-controls/`. No React dependency — controls use native DOM + the Iris color picker (bundled with WP Core).

### 3.1 `ast-color` — Alpha Color Picker
- **PHP**: `Phantom_Color_Control extends WP_Customize_Control`
- **JS**: `custom-controls/ast-color.js`
- **Storage**: hex `'#ff0000'` or rgba `'rgba(255,0,0,0.5)'`
- **UI**: Iris color picker with alpha slider + 9-color palette swatches
- **Upgrades**: Existing `color` and `ast-color` settings get this picker

### 3.2 `ast-responsive-slider` — Responsive Range Slider
- **PHP**: `Phantom_Responsive_Slider_Control`
- **JS**: `custom-controls/ast-responsive-slider.js`
- **Storage**: `{desktop: 50, tablet: 40, mobile: 30}`
- **UI**: 3 sliders (one per breakpoint), device tab switcher at top

### 3.3 `ast-responsive-spacing` — Responsive Spacing
- **PHP**: `Phantom_Responsive_Spacing_Control`
- **JS**: `custom-controls/ast-responsive-spacing.js`
- **Storage**: `{desktop: {top:0, right:20, bottom:0, left:20}, tablet:..., mobile:...}`
- **UI**: 4 directional inputs (top/right/bottom/left) per breakpoint, linked/unlinked toggle

### 3.4 `ast-typography` — Typography Set
- **PHP**: `Phantom_Typography_Control`
- **JS**: `custom-controls/ast-typography.js`
- **Storage**: `{family:'Inter', weight:'400', style:'normal', transform:'none', size:{desktop:16,tablet:14,mobile:12}, line_height:1.5, letter_spacing:0}`
- **UI**: Google Fonts dropdown with search + weight/style/transform + responsive size sliders

### 3.5 `ast-toggle` — Toggle Switch
- **PHP**: `Phantom_Toggle_Control`
- **JS**: `custom-controls/ast-toggle.js`
- **Storage**: `true` / `false`
- **UI**: iOS-style ON/OFF toggle

### 3.6 `ast-radio-image` — Image Radio Buttons
- **PHP**: `Phantom_Radio_Image_Control`
- **JS**: `custom-controls/ast-radio-image.js`
- **Storage**: string key
- **UI**: Horizontal row of clickable images, selected one has border highlight

### 3.7 `ast-gradient` — Gradient Builder
- **PHP**: `Phantom_Gradient_Control`
- **JS**: `custom-controls/ast-gradient.js`
- **Storage**: `{type:'linear', angle:45, color1:'#000', color2:'#fff'}`
- **UI**: Two color pickers + angle slider + preview swatch

### 3.8 `ast-select` — Enhanced Select
- **PHP**: `Phantom_Select_Control`
- **JS**: `custom-controls/ast-select.js`
- **Storage**: string value
- **UI**: Native `<select>` with optgroup support, optional search filter, optional icons

### Registration in Customizer

```php
// class-customizer.php
$wp_customize->register_control_type('Phantom_Color_Control');
$wp_customize->register_control_type('Phantom_Responsive_Slider_Control');
$wp_customize->register_control_type('Phantom_Responsive_Spacing_Control');
$wp_customize->register_control_type('Phantom_Typography_Control');
$wp_customize->register_control_type('Phantom_Toggle_Control');
$wp_customize->register_control_type('Phantom_Radio_Image_Control');
$wp_customize->register_control_type('Phantom_Gradient_Control');
$wp_customize->register_control_type('Phantom_Select_Control');
```

The existing `switch($type)` in `add_customizer_controls()` is extended to handle each new type, defaulting to the appropriate `WP_Customize_Control` instance.

---

## 4. Responsive System

### Setting Definition
A `responsive: true` flag in the Registry tells the Customizer to:
1. Store values as `{desktop: X, tablet: Y, mobile: Z}`
2. Render 3-column UI with device tab buttons
3. Output CSS with `@media` wrappers

### CSS Output

**Important**: Phantom Core stores settings as WordPress **options** (`get_option`), not theme mods. The CSS helper uses the Registry's `SITE_OPTION_PREFIX`:

```php
function phantom_responsive_css($setting_key, $property, $selector, $unit = 'px') {
    $key   = SITE_OPTION_PREFIX . $setting_key;
    $value = get_option($key, []);
    if (!is_array($value) || !isset($value['desktop'])) return '';

    $css = '';
    foreach (['desktop', 'tablet', 'mobile'] as $device) {
        if (!isset($value[$device])) continue;
        $val = $value[$device];
        if ($device === 'desktop') {
            $css .= "{$selector} { {$property}: {$val}{$unit}; }\n";
        } elseif ($device === 'tablet') {
            $css .= "@media (max-width: 768px) { {$selector} { {$property}: {$val}{$unit}; } }\n";
        } else {
            $css .= "@media (max-width: 544px) { {$selector} { {$property}: {$val}{$unit}; } }\n";
        }
    }
    return $css;
}
```

### Preview JS
`customizer-preview.js` binds responsive sliders so changing the Desktop slider updates the preview immediately; switching to Tablet tab shows/edits tablet values.

### Settings with responsive: true
`container_width`, `container_spacing`, `header_padding`, `body_font_size`, `heading_font_size_h1`, `heading_font_size_h2`, `heading_font_size_h3`, `button_padding`, `button_radius`, `box_radius`, `site_title_size`, `tagline_size`, `blog_image_width`, `product_image_width`.

---

## 5. Conditional Display

### Definition
Each control declares its dependencies in the Registry:

```php
'dependencies' => [
    ['setting' => 'display_header', 'value' => true, 'operator' => '==='],
    ['setting' => 'header_style',   'value' => 'sticky', 'operator' => '==='],
],
```

### Execution
`class-customizer.php` checks for `dependencies` and passes them as data attributes on the control wrapper. A lightweight JS watcher (`admin/js/customizer-conditionals.js`) listens to each dependency's `change` event and toggles `.customize-control` visibility with CSS `display: none/block`.

### Logic
Dependencies within a single array item use **AND** — all conditions must match. To express **OR**, use a separate array:
```php
// AND: display_header = true AND header_style = 'sticky'
'dependencies' => [
    ['setting' => 'display_header', 'value' => true],
    ['setting' => 'header_style', 'value' => 'sticky'],
],
// OR equivalent would need separate dependency groups (not supported in v1)
```

For v1, only AND logic within a single dependency array is supported. OR logic can be added later if needed.

### Operator support
- `===` (default) — strict equality
- `!==` — not equal
- `in` — value is in array (e.g., `['value' => ['sticky', 'transparent'], 'operator' => 'in']`)

### Settings with conditional display
| Setting | Depends On | Value |
|---------|-----------|-------|
| `header_style` | `display_header` | `true` |
| `header_sticky_bg` | `header_style` | `'sticky'` |
| `header_transparent_color` | `header_style` | `'transparent'` |
| `footer_layout` | `display_footer` | `true` |
| `blog_meta_layout` | `display_post_meta` | `true` |
| `product_quick_view` | `enable_quick_view` | `true` |
| `search_placeholder` | `enable_live_search` | `true` |

---

## 6. Selective Refresh

### Definition
Settings that affect HTML structure (not just CSS) get a `partial` metadata block:

```php
'partial' => [
    'selector'           => '.site-header',
    'container_inclusive' => true,
    'render_callback'    => 'phantom_render_header_partial',
],
```

### PHP Render Callback
```php
function phantom_render_header_partial() {
    get_template_part('template-parts/header/layout');
}
```

### Data Flow: Registry → Preview JS
The `partial` metadata from the Registry must reach the customizer preview. This happens via `wp_localize_script` in `class-customizer.php`:

```php
// class-customizer.php — enqueue preview script
$partials = [];
foreach ($settings as $key => $args) {
    if (!empty($args['partial'])) {
        $partials[$key] = $args['partial'];
    }
}
wp_localize_script('phantom-customizer-preview', 'PhantomPartials', $partials);
```

### REST Endpoint
New route at `GET /wp-json/phantom/v1/partial?partial={setting_key}`:

```php
register_rest_route('phantom/v1', '/partial', [
    'methods'             => 'GET',
    'permission_callback' => function() {
        return current_user_can('edit_theme_options');
    },
    'callback' => function($request) {
        $key = $request->get_param('partial');
        $setting = Settings_Registry::get_setting($key);
        if (!empty($setting['partial']['render_callback'])) {
            return call_user_func($setting['partial']['render_callback']);
        }
        return new WP_Error('invalid_partial', 'No render callback found', ['status' => 404]);
    },
]);
```

**Security**: The endpoint checks `edit_theme_options` capability. The `partial` parameter is validated against the known Registry keys to prevent arbitrary function injection.

### Preview JS
Enhanced `customizer-preview.js` reads `PhantomPartials` (localized above):
```js
var PhantomPartials = window.PhantomPartials || {};

api(settingKey, function(value) {
    value.bind(function(newValue) {
        var partial = PhantomPartials[settingKey];
        if (!partial) return;
        fetch('/wp-json/phantom/v1/partial?partial=' + settingKey + '&_wpnonce=' + wpApiSettings.nonce)
            .then(r => {
                if (!r.ok) throw new Error('Partial refresh failed');
                return r.text();
            })
            .then(html => {
                var sel = partial.selector;
                var el = document.querySelector(sel);
                if (el) el.outerHTML = html;
            })
            .catch(function(err) {
                console.warn('[Phantom] Partial refresh error:', err);
            });
    });
});
```

### Settings with partial refresh
`header_style`, `footer_layout`, `blog_layout`, `blog_meta_layout`, `product_grid_layout`, `search_results_layout`, `menu_location`.

---

## 7. PhantomBridge.js — Frontend Adapter

### Location
`frontend/assets/js/phantom-bridge.js`

### API

```js
const PhantomBridge = {
  // Initialize: reads PhantomData, sets CSS vars, injects styles
  init(options = {}) {
    // options.lazy — if true, don't inject CSS vars until DOM ready
    // options.prefix — CSS var prefix (default '--phantom-')
  },

  // Read a setting value
  getSetting(key),

  // Save a setting via REST API — returns Promise<response>
  // Handles auth errors: if 401 → refreshes nonce and retries once
  setSetting(key, value),

  // Subscribe to setting changes
  // v1: same-tab only (event bus within PhantomBridge)
  // v2 can add BroadcastChannel for cross-tab sync
  onSettingChange(key, callback),

  // Get all active CSS custom properties as { --phantom-x: val }
  getCssVars(),

  // Editor hooks (only active when user can edit)
  highlightElement(selector),   // show edit indicator
  openEditor(key),             // open customizer panel for setting
  saveChanges(changes),        // bulk save
};
```

### Integration in any frontend
```html
<script src="<?= phantom_asset('frontend/assets/js/phantom-bridge.js') ?>"></script>
<script>
  PhantomBridge.init().then(() => {
    // CSS vars like --phantom-primary-color, --phantom-body-font-size are on :root
    // Theme CSS uses var(--phantom-*) throughout
    // If admin is logged in, editable elements show hover indicators
  });
</script>
```

### Existing file changes
- `shell.php`: enqueue `phantom-bridge.js` (replaces or augments `phantom-data.js`)
- `phantom-data.js`: remains as the data source (Bridge reads from `window.PhantomData`)
- `phantom-editor.js`: refactored into Bridge's editor hooks

---

## 8. CSS Generation — Filter-Based Modular Output

### New File
`includes/class-custom-css.php`

### Core
```php
class Phantom_Custom_CSS {

    public function get_css() {
        $css = '';
        // Single filter — modules use priority ordering.
        // Responsive CSS hooks at priority 100 to run last.
        $css = apply_filters('phantom_dynamic_css', $css);
        return $css;
    }

    public function render_style() {
        $css = $this->get_css();
        if (!empty($css)) {
            return "\n<style id='phantom-inline-css'>\n" . $css . "</style>\n";
        }
        return '';
    }
}
```

### Module Organization

Each module registers a filter callback in its own file:

All modules hook into the single `phantom_dynamic_css` filter, ordered by priority:

| Module | Priority | File |
|--------|----------|------|
| Colors | 10 | `includes/custom-css/colors.php` |
| Typography | 20 | `includes/custom-css/typography.php` |
| Header | 30 | `includes/custom-css/header.php` |
| Footer | 40 | `includes/custom-css/footer.php` |
| Layout | 50 | `includes/custom-css/layout.php` |
| Buttons | 60 | `includes/custom-css/buttons.php` |
| Blog | 70 | `includes/custom-css/blog.php` |
| Product | 80 | `includes/custom-css/product.php` |
| Responsive | 100 | `includes/custom-css/responsive.php` (runs last) |

Example callback:
```php
// includes/custom-css/colors.php
add_filter('phantom_dynamic_css', function($css) {
    $prefix = SITE_OPTION_PREFIX;
    $bg     = get_option($prefix . 'header_bg_color', '#ffffff');
    $text   = get_option($prefix . 'theme_text_color', '#333333');
    $css   .= ".site-header { background-color: {$bg}; color: {$text}; }\n";
    return $css;
}, 10);
```

### Refactoring existing `generate_dynamic_css()`
The current hardcoded CSS var map in `shell.php` is split into these filter callbacks. The map entries become filter callbacks organized by module.

### Shell Integration
```php
// In shell.php — replace direct css() call:
$css  = new Phantom_Custom_CSS();
$html = $css->render_style();
if ($html) echo $html;
```

---

## 9. Storage — Same Mechanisms, Enhanced Formats

The existing dual storage pattern remains unchanged. No new option keys, no migration.

| Mechanism | Purpose | Format Change? |
|-----------|---------|---------------|
| `phantom_{key}` (get_option) | Direct reads | Yes — responsive/typography values store objects instead of scalars |
| `phantom_options` (get_option) | Batch reads | Same — reflects whatever per-key stores |
| `get_theme_mod()` | Customizer context | Same — WP Core handles serialization |
| `GET /phantom/v1/settings` | REST API | Same — returns JSON |
| `wp_localize_script(PhantomData)` | Frontend snapshot | Same — values are already JSON-serializable |

**What changes**: For `responsive: true` settings, the stored value changes from `int`/`string` to a structured object like `{desktop: 20, tablet: 15, mobile: 10}`. This happens transparently — the `sanitize_callback` or `default` in the Registry defines the shape.

### Backward Compatibility
Existing settings stored as scalars (e.g., `container_width = 1200`) need a compatibility layer when upgraded to responsive format:

```php
function phantom_get_responsive($key) {
    $raw = get_option(SITE_OPTION_PREFIX . $key);
    if (!is_array($raw)) {
        // Legacy scalar value — wrap in responsive format
        $raw = ['desktop' => $raw, 'tablet' => $raw, 'mobile' => $raw];
    }
    return $raw;
}
```

This ensures both old scalar values and new structured values work without explicit migration. The compatibility shim lives in the CSS generation layer.

**What does NOT change**: Option keys, get_option calls, REST response format, frontend data injection pattern, Customizer save mechanism.

### Migration & Upgrade Path
No active data migration is needed — the backward compat shim (`phantom_get_responsive`) handles old scalar values transparently. However, the following steps run on plugin update:

1. **Version bump**: `Settings_Registry::VERSION` incremented (e.g., `1.5.0`)
2. **Cache clear**: `delete_option(SITE_OPTION_PREFIX . 'version')` triggers fresh option reads on next load
3. **Admin notice** (one-time): New Customizer controls available — no action required
4. **Optional**: Site Health check verifies responsive settings return expected format

---

## 10. Files Changed (New + Extended)

| File | Change |
|------|--------|
| `includes/class-settings-registry.php` | Add new metadata fields to existing settings |
| `includes/class-customizer.php` | Register custom controls, add responsive/conditional/partial handling |
| `includes/class-rest-controller.php` | Add `/partial` endpoint |
| `includes/class-custom-css.php` | **New**: Filter-based CSS generation |
| `includes/custom-css/*.php` | **New**: Module CSS callbacks (8 files) |
| `includes/custom-controls/*.php` | **New**: 8 custom control PHP classes |
| `admin/js/customizer-preview.js` | Enhanced: responsive bindings, partial refresh, conditional watcher |
| `admin/js/customizer-conditionals.js` | **New**: Conditional display logic |
| `admin/js/custom-controls/*.js` | **New**: 8 custom control JS files (vanilla JS) |
| `frontend/assets/js/phantom-bridge.js` | **New**: Frontend adapter layer |
| `templates/shell.php` | Replace `generate_dynamic_css()` with `Phantom_Custom_CSS` |

Total: ~10 new files, ~8 existing files extended. Zero existing architectural changes.

---

## 11. Settings to Upgrade

### Phase 1 — Colors & Background (existing color/ast-color types)
`theme_primary_color`, `theme_secondary_color`, `theme_text_color`, `theme_accent_color`, `header_bg_color`, `footer_bg_color`, `body_bg_color`, `button_color`, `button_hover_color`, `button_text_color`, `button_text_hover_color`, `link_color`, `link_hover_color`, `box_bg_color`

### Phase 2 — Layout & Spacing (add responsive support)
`container_width`, `container_spacing`, `header_padding`, `button_padding`, `button_radius`, `box_radius`

### Phase 3 — Typography (add responsive + font selection)
`body_font_size`, `heading_font_size_h1`, `heading_font_size_h2`, `heading_font_size_h3`, `site_title_size`, `tagline_size`, `blog_excerpt_length`

### Phase 4 — Conditional Controls (add dependency metadata)
`header_style` → depends on `display_header`, `header_sticky_bg` → depends on `header_style === 'sticky'`, etc.

---

## 12. Critical Architectural Findings from Astra Deep Analysis

### 12.1 Duplicate CSS Var Names (Must Resolve Before Implementation)
The Settings Registry has **two parallel color systems with different CSS var names** for the same semantic purpose:

| Legacy System | New System | Conflict |
|--------------|------------|----------|
| `--primary--color` | `--color-primary` | Same purpose, different name |
| `--bg` | `--color-background` | Same purpose, different name |
| `--text--color` | `--color-text` | Same purpose, different name |
| `--heading--color` | `--color-heading` | Same purpose, different name |
| `--link` | `--color-link` | Same purpose, different name |
| `--link--hover` | `--color-link-hover` | Same purpose, different name |

**Decision**: The new system (`--color-*`) wins. Legacy vars get deprecation mapped to new names. Frontend CSS should use `--color-primary` (not `--primary--color`). Both will be emitted during a transition period.

### 12.2 Settings Page ↔ Customizer Overlap
The admin Settings Page (15 tabs, 450+ settings) manages the **same** settings as the Customizer. Plan:
- **Customizer** becomes the primary UI for visual settings (colors, layout, typography, header/footer)
- **Settings Page** becomes secondary/fallback for non-visual settings (performance, SEO, integrations, custom code, import/export)
- Both write to the same `phantom_{key}` options — no separate storage
- Settings Page must call `sync_options()` after save to keep `phantom_options` in sync

### 12.3 Dual Storage Synchronization Gap
`sync_options()` only runs on `customize_save_after` — NOT on Settings Page save. **Fix**: Add `update_option('phantom_options', ...)` call to the Settings Page save handler AND the REST batch save handler.

### 12.4 Google Font Loading Optimization
Current: Always loads ALL weights 100-900 for both fonts. **Fix**: Font control stores selected weight; only enqueue that weight + its italic variant. Default families (Archivo, Playfair Display) should NOT be enqueued unless overridden by user.

### 12.5 No CSS Minification
Add `phantom_minify_css($css)` helper that strips comments, whitespace, and newlines. Run at the end of `Phantom_Custom_CSS::get_css()`.

---

## 13. Global Color Palette System (New Feature)

Modeled after Astra's 9-color palette with 4 presets:

### Storage
```php
'global_palette' => [
    'default' => [
        'palette_1' => ['#7635d5', '#6a2fc0', '#5e2aab', '#522696'],
        'palette_2' => ['#ffffff', '#f8f5fd', '#f0ebf5', '#e8e1ed'],
        'palette_3' => ['#fcd668', '#fbe07a', '#faea8c', '#f9f49e'],
        'palette_4' => ['#222222', '#333333', '#4e4e4e', '#666666'],
    ],
    'current' => 'palette_1',
],
```

### CSS Output
```php
:root {
    --phantom-color-0: #7635d5;
    --phantom-color-1: #ffffff;
    --phantom-color-2: #fcd668;
    --phantom-color-3: #222222;
    /* ... 9 colors total */
}
```

### Integration
- Gutenberg: `add_theme_support('editor-color-palette', $palette)`
- Customizer: `ast-color-group` control showing all 9 colors with swatches
- Dark mode: Alternate palette (palette_4) detected via `is_dark_palette()`
- Frontend: All theme CSS uses `var(--phantom-color-N)` instead of hardcoded hex

### Settings to Replace
Individual color settings (`color_primary`, `color_secondary`, etc.) become derived from the palette:
```php
--color-primary: var(--phantom-color-0);
--color-secondary: var(--phantom-color-1);
--color-accent: var(--phantom-color-2);
--color-text: var(--phantom-color-3);
```

---

## 14. Version Compatibility Flags

Astra uses version-gated defaults via static methods. Phantom Core will adopt the same pattern:

```php
class Phantom_Version_Compatibility {
    public static function phantom_1_5_0_compatibility() {
        // Check if user has already seen the 1.5.0 update
        $flag = get_option('phantom_1_5_0_compatibility', 'unset');
        if ($flag === 'unset') {
            // First run — apply new defaults
            return true;
        }
        return $flag === 'enabled';
    }
}
```

Used in CSS generation and settings defaults to safely migrate values without breaking existing sites.

---

## 15. What Does NOT Change

- Settings Registry pattern (settings defined centrally in `class-settings-registry.php`)
- Option storage (individual + bulk `phantom_options`)
- REST API base (`/phantom/v1/`)
- Shell/SPA architecture
- Frontend data injection (`wp_localize_script`)
- The editor save flow (nonce, REST, validation)
- Menu system, widget system, WooCommerce integration
- Bootstrap, GSAP, Three.js, Lenis, Swiper inclusion
- Any existing template files or frontend HTML

---

## 16. Open Questions (Resolved)

1. **CSS var prefix**: **Resolved** — The plan uses `--phantom-*` prefix. Legacy `--primary--color` etc. deprecated in favor of `--color-primary` etc.
2. **Responsive breakpoints**: **Resolved** — Hardcoded at 768px (tablet) and 544px (mobile) initially, with `apply_filters('phantom_breakpoints', ...)` for overriding.
3. **Legacy vs new CSS vars**: **Resolved** — New system wins (`--color-primary`). Legacy mapped during transition period.
