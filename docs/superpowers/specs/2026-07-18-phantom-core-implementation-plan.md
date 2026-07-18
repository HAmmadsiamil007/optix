# Phantom Core Settings Bus — Implementation Plan

**Date**: 2026-07-18
**Spec**: `docs/superpowers/specs/2026-07-18-phantom-core-settings-bus-design.md`
**Approach**: Settings Bus (Approach 2)
**Framework**: PHANTOM CORE — no architecture changes, only extensions

---

## Phase 0: WordPress Core Integration (Week 1)
**Goal**: Fix fundamental WordPress integration gaps. Register menu locations, widget areas, fix shell bypass holes, fix plugin compatibility.

### Tasks

- [ ] **0.1 Register nav menu locations**
  - Add `register_nav_menus()` in `Plugin::init()` with 4+ locations: primary, secondary, footer, mobile
  - Update Shell bypass to not intercept menu management pages
  - Add filter for custom menu locations

- [ ] **0.2 Register widget areas/sidebars**
  - Add `register_sidebar()` for 3+ areas: main sidebar, footer widgets 1-4, shop sidebar, blog sidebar
  - Hook into `widgets_init` with proper sidebar definitions
  - Add `dynamic_sidebar()` output to frontend HTML templates (data attributes for injection)

- [ ] **0.3 Fix shell bypass holes**
  - Add bypass for: `robots.txt`, `sitemap.xml`, `feed/`, `wp-cron` (no .php), `.well-known/`, `.txt`, `.xml`
  - Ensure WordPress feeds, sitemaps, and cron jobs are NOT intercepted
  - Add `is_feed()`, `is_robots()`, `is_trackback()` checks at priority 0

- [ ] **0.4 Restore plugin compatibility (wp_head/wp_footer)**
  - Add critical `wp_head` hooks back: `wp_enqueue_scripts` (for essential queue), `rest_output_link_wp_head`, `wp_generator`
  - Add `do_action('phantom_before_head_close')` filter for plugins to hook into
  - Add `do_action('phantom_before_body_close')` filter before `</body>`
  - Document the compatibility approach: plugins hook into `phantom_*` instead of `wp_head`

- [ ] **0.5 Fix dual storage sync gap**
  - Add `update_option('phantom_options', ...)` to Settings Page save handler
  - Add same to REST batch save endpoint (`POST /settings`)
  - Ensure `sync_options()` is called on ALL save paths (Customizer, Settings Page, REST API)

- [ ] **0.6 Add canonical URL + hreflang + proper SEO**
  - Generate `<link rel="canonical">` from actual WordPress permalink structure
  - Add `hreflang` tags from site language settings
  - Add page-type-specific JSON-LD (BlogPosting for blog, Product for products, etc.)

- [ ] **0.7 Fix Customizer color control syntax**
  - Replace deprecated `'wp_color_picker'` string with `new WP_Customize_Color_Control()` instantiation

- [ ] **0.8 Resolve duplicate CSS vars**
  - Map all legacy CSS var names (`--primary--color`, `--bg`, `--text--color`, `--link`, `--link--hover`, etc.) to new `--color-*` names
  - Emit BOTH during transition period with deprecation notice

- [ ] **0.9 Add version compatibility flags**
  - Create `class-phantom-version-compatibility.php`
  - Add `phantom_1_5_0_compatibility()` method
  - Gate new settings defaults behind version flag

- [ ] **0.10 Add CSS minification**
  - Create `phantom_minify_css($css)` helper
  - Strip comments, whitespace, newlines

---

## Phase 1: WooCommerce Fixes (Week 1-2)
**Goal**: Fix critical WooCommerce bugs, add missing features, ensure cart/checkout works properly.

### Tasks

- [ ] **1.1 Fix `create_product()` critical bug**
  - Remove the line that creates a new empty `WC_Product_Simple()` when type is variable/grouped/external (line 908-910)
  - Use proper product type factory: `WC_Product_Factory::get_product_type()` or direct class instantiation

- [ ] **1.2 Fix `get_woo_reviews()` post type filter**
  - Use `WP_Comment_Query` with `post__in` or proper meta query instead of `get_comments()`
  - Ensure only product reviews are returned

- [ ] **1.3 Fix REST permission for public Woo data**
  - Change `/woo/attributes`, `/woo/variations`, `/woo/reviews` to `__return_true` for GET (read) operations
  - Keep `manage_options` for write operations

- [ ] **1.4 Fix checkout field name mismatch**
  - Update checkout HTML form field names to match WooCommerce expectations:
    - `fname` → `billing_first_name`, `lname` → `billing_last_name`
    - `comapny` → `billing_company`, `address` → `billing_address_1`
    - `street` → `billing_address_2`, `area` → `billing_city`
    - `number` → `billing_phone`, `email` → `billing_email`
  - Add state/country fields for shipping calculation

- [ ] **1.5 Add variable product support**
  - Render variation attribute dropdowns in `renderProduct()`
  - Pass `variation_id` + selected attributes in add-to-cart calls
  - Enqueue `wc-add-to-cart-variation.js` when on product detail page

- [ ] **1.6 Add WooCommerce hooks/filters**
  - Register essential WooCommerce filters for SPA shell compatibility
  - Ensure `wc_ajax` handlers are registered before shell removes actions

- [ ] **1.7 Add product sort/filter parameters to REST API**
  - Add: `orderby` (price, date, rating, popularity, title), `order` (ASC/DESC)
  - Add: `min_price`, `max_price`, `on_sale`, `stock_status`, `featured`
  - Add product tag filtering

- [ ] **1.8 Add cart operation feedback**
  - Add loading spinners to add-to-cart, quantity update, remove buttons
  - Add success/error toasts or inline messages
  - Add coupon submission handler

- [ ] **1.9 Implement unused WooCommerce settings**
  - `shop_catalog_mode` → remove add-to-cart buttons
  - `shop_wishlist_enable` → add wishlist toggle (localStorage-based)
  - `shop_product_image_zoom` → load zoom library
  - `card_quick_view` → add quick view modal
  - `shop_minicart_enable` → add minicart dropdown

- [ ] **1.10 Add WooCommerce template overrides (minimal)**
  - Override only critical templates: `cart/cart.php`, `checkout/checkout.php`, `loop/add-to-cart.php`
  - Add `woocommerce/templates/` directory with SPA-compatible templates

- [ ] **1.11 Add WooCommerce structured data (Product schema)**
  - Add JSON-LD Product schema on product detail pages
  - Add Offer, Review, AggregateRating schemas
  - Add product OG meta tags

---

## Phase 2: API & Data Layer Cleanup (Week 2)
**Goal**: Clean up REST API inconsistencies, fix data layer issues, optimize performance.

### Tasks

- [ ] **2.1 Consolidate duplicate endpoints**
  - Remove `/settings/batch` (identical to `/settings` POST)
  - Ensure single consistent endpoint for each operation

- [ ] **2.2 Fix REST inconsistency (WP_Error vs plain array)**
  - Standardize all error responses to use `WP_Error` in `WP_REST_Response`
  - Add consistent `code`, `message`, `status` fields

- [ ] **2.3 Add pagination validation and headers**
  - Add `per_page` validation (min 1, max 100)
  - Add `X-WP-Total` and `X-WP-TotalPages` headers to all paginated endpoints
  - Add `Cache-Control` headers to public endpoints

- [ ] **2.4 Fix permission callbacks**
  - Settings write → `edit_theme_options` (not `manage_options`)
  - Public read → `__return_true`
  - Admin write → `manage_options`

- [ ] **2.5 Add product endpoint features**
  - Add `cross_sell_ids` and `up_sell_ids` to `format_product()`
  - Add `stock_status`, `stock_quantity`, `backorders` to product response
  - Add category images and descriptions to category response
  - Fix `home_products_count` default consistency (8 vs 6)

- [ ] **2.6 Add cache invalidation strategy**
  - Add `Cache-Control: max-age=3600, must-revalidate` to public endpoints
  - Add ETag headers based on last-updated timestamp
  - Transient cache invalidation on settings save

- [ ] **2.7 Fix /export to use GET**
  - Change export endpoint from POST to GET (read operation)
  - Add nonce check for GET export

- [ ] **2.8 Add rate limiting to import endpoint**
  - Limit import to 1 request per 60 seconds per user
  - Validate payload size (max 5MB)

- [ ] **2.9 Remove unused settings**
  - Audit all 33+ settings that are defined but never read
  - Archive or remove `woocommerce` section settings that overlap with `shop_page`/`product_page`
  - Consolidate duplicate social link settings

---

## Phase 3: Custom Control Types (Week 3-4)
**Goal**: Build and register 8 custom control types

### Tasks

- [ ] **3.1 Create control base class**
  - `Phantom_Control_Base` with `add_control()`, `get_control_instance()`, `get_sanitize_callback()`
  - Auto-register with `$wp_customize->register_control_type()`

- [ ] **3.2 Build `ast-color` (alpha picker)**
  - PHP: `Phantom_Color_Control extends WP_Customize_Control`
  - JS: Iris color picker + alpha slider + 9-color palette swatches
  - Storage: hex `'#ff0000'` or rgba `'rgba(255,0,0,0.5)'`
  - Upgrade all existing `color` and `ast-color` settings

- [ ] **3.3 Build `ast-toggle` (iOS switch)**
  - PHP: `Phantom_Toggle_Control`
  - JS: Custom toggle with ON/OFF label
  - Storage: `true` / `false`

- [ ] **3.4 Build `ast-radio-image` (image selector)**
  - PHP: `Phantom_Radio_Image_Control`
  - JS: Clickable images with border highlight
  - Storage: string key

- [ ] **3.5 Build `ast-responsive-slider` (3-device slider)**
  - PHP: `Phantom_Responsive_Slider_Control`
  - JS: 3 sliders with device tab switcher
  - Storage: `{desktop: 50, tablet: 40, mobile: 30}`

- [ ] **3.6 Build `ast-responsive-spacing` (4-direction per device)**
  - PHP: `Phantom_Responsive_Spacing_Control`
  - JS: 4 directional inputs per breakpoint + linked/unlinked toggle
  - Storage: `{desktop: {top:0,right:20,bottom:0,left:20}, tablet:..., mobile:...}`

- [ ] **3.7 Build `ast-typography` (font family + extras)**
  - PHP: `Phantom_Typography_Control`
  - JS: Google Fonts dropdown + weight + style + transform + responsive size sliders + line-height + letter-spacing
  - Storage: composite object `{family, weight, style, transform, size:{desktop,tablet,mobile}, line_height, letter_spacing}`
  - Font data loaded from generated PHP array (like Astra's `google-fonts.php`)

- [ ] **3.8 Build `ast-gradient` and `ast-select`**
  - Gradient: Two color pickers + angle slider + preview swatch
  - Select: Native select with optgroup support

- [ ] **3.9 Register all controls in Customizer**
  - Add `$wp_customize->register_control_type()` calls
  - Extend `switch($type)` in `add_customizer_controls()`
  - Create sanitize callbacks for each control type (alpha color, responsive spacing, responsive slider, typography composite)

### Files Created
`includes/custom-controls/class-control-base.php`, `includes/custom-controls/class-color-control.php`, `includes/custom-controls/class-toggle-control.php`, `includes/custom-controls/class-radio-image-control.php`, `includes/custom-controls/class-responsive-slider-control.php`, `includes/custom-controls/class-responsive-spacing-control.php`, `includes/custom-controls/class-typography-control.php`, `includes/custom-controls/class-gradient-control.php`, `includes/custom-controls/class-select-control.php`

`admin/js/custom-controls/ast-color.js`, `admin/js/custom-controls/ast-toggle.js`, `admin/js/custom-controls/ast-radio-image.js`, `admin/js/custom-controls/ast-responsive-slider.js`, `admin/js/custom-controls/ast-responsive-spacing.js`, `admin/js/custom-controls/ast-typography.js`, `admin/js/custom-controls/ast-gradient.js`, `admin/js/custom-controls/ast-select.js`

### Files Touched
`class-customizer.php`, `class-settings-registry.php`, `class-custom-css.php`

---

## Phase 4: Responsive System (Week 4)
**Goal**: Add responsive support to all layout/typography settings

### Tasks

- [ ] **4.1 Add responsive metadata to settings**
  - Mark 14 settings as `responsive: true` (container_width, container_spacing, header_padding, body_font_size, heading sizes h1-h3, button_padding, button_radius, box_radius, site_title_size, tagline_size, blog_image_width, product_image_width)

- [ ] **4.2 Build responsive CSS helper**
  - `phantom_responsive_css($setting_key, $property, $selector, $unit)` — uses `get_option()` with `SITE_OPTION_PREFIX`
  - Outputs desktop + tablet (768px) + mobile (544px) media queries
  - Includes backward compat shim for legacy scalar values

- [ ] **4.3 Add responsive preview bindings**
  - Extend `customizer-preview.js` to bind responsive slider changes
  - When desktop changes → update CSS var immediately
  - When tablet changes → update within `@media (max-width: 768px)` block

- [ ] **4.4 Add breakpoint filter**
  - `apply_filters('phantom_breakpoints', ['tablet' => 768, 'mobile' => 544])`
  - Used by both CSS helper and preview JS

### Files Touched
`class-settings-registry.php`, `class-customizer.php`, `class-custom-css.php`, `customizer-preview.js`

---

## Phase 5: Conditional Display (Week 4-5)
**Goal**: Show/hide controls based on other values

### Tasks

- [ ] **5.1 Add `dependencies` metadata processing in Customizer**
  - Read `dependencies` from setting args
  - Pass as `data-dependency-*` attributes on control wrapper

- [ ] **5.2 Build conditional display JS**
  - `admin/js/customizer-conditionals.js`
  - Watches dependency settings via `wp.customize` events
  - Toggles `.customize-control` display
  - Supports 3 operators: `===`, `!==`, `in`
  - AND logic within array; no OR in v1

- [ ] **5.3 Add dependency metadata to 7 settings**
  - `header_style` → depends on `display_header = true`
  - `header_sticky_bg` → depends on `header_style = 'sticky'`
  - `header_transparent_color` → depends on `header_style = 'transparent'`
  - `footer_layout` → depends on `display_footer = true`
  - `blog_meta_layout` → depends on `display_post_meta = true`
  - `product_quick_view` → depends on `enable_quick_view = true`
  - `search_placeholder` → depends on `enable_live_search = true`

### Files Created
`admin/js/customizer-conditionals.js`

### Files Touched
`class-customizer.php`, `class-settings-registry.php`, `customizer-preview.js`

---

## Phase 6: Selective Refresh (Week 5)
**Goal**: Live preview for HTML structure changes (not just CSS vars)

### Tasks

- [ ] **6.1 Add `partial` metadata data flow**
  - In `class-customizer.php`, iterate settings, build `$partials` array
  - `wp_localize_script('phantom-customizer-preview', 'PhantomPartials', $partials)`

- [ ] **6.2 Create partial render callbacks**
  - `phantom_render_header_partial()` → `get_template_part('template-parts/header/layout')`
  - `phantom_render_footer_partial()`
  - `phantom_render_blog_partial()`
  - `phantom_render_search_partial()`

- [ ] **6.3 Add `/partial` REST endpoint**
  - `GET /wp-json/phantom/v1/partial?partial={setting_key}`
  - `permission_callback`: `current_user_can('edit_theme_options')`
  - Validates key against known Registry entries
  - Calls render callback, returns HTML fragment

- [ ] **6.4 Add selective refresh preview bindings**
  - Extend `customizer-preview.js` to read `PhantomPartials`
  - On setting change: fetch partial HTML from endpoint
  - Replace DOM element at `partial.selector`
  - Error handling: silent fail + console warning

- [ ] **6.5 Add `partial` metadata to 7 settings**
  - `header_style`, `footer_layout`, `blog_layout`, `blog_meta_layout`, `product_grid_layout`, `search_results_layout`, `menu_location`

### Files Touched
`class-customizer.php`, `class-rest-controller.php`, `customizer-preview.js`, `templates/shell.php`

---

## Phase 7: PhantomBridge.js (Week 5-6)
**Goal**: Frontend adapter layer for swappable HTML frontends

### Tasks

- [ ] **7.1 Build PhantomBridge.js core**
  - `init()` — reads `window.PhantomData`, sets CSS vars on `:root`, injects dynamic `<style>` blocks
  - `getSetting(key)` — returns current value from data cache
  - `setSetting(key, value)` — saves via `PUT /settings/{key}` with REST API, retries on 401
  - `onSettingChange(key, callback)` — subscribe to setting changes (same-tab event bus)
  - `getCssVars()` — returns all active CSS vars as object

- [ ] **7.2 Build editor hooks**
  - `highlightElement(selector)` — shows edit indicator on hover
  - `openEditor(key)` — opens Customizer panel for that setting
  - `saveChanges(changes)` — bulk save pending edits

- [ ] **7.3 Integrate into Shell**
  - `shell.php`: enqueue `phantom-bridge.js` (replaces `phantom-data.js`)
  - `phantom-data.js`: remains as data source (Bridge reads `window.PhantomData`)
  - `phantom-editor.js`: refactored into Bridge's editor hooks

- [ ] **7.4 Create integration example**
  - Document how any HTML frontend uses Bridge: `<script src="phantom-bridge.js"><script>PhantomBridge.init()</script>`
  - Note: Bridge is the ONLY required adapter — no WordPress coupling in frontend

### Files Created
`frontend/assets/js/phantom-bridge.js`

### Files Touched
`templates/shell.php`, `frontend/assets/js/phantom-data.js`, `frontend/assets/js/phantom-editor.js`

---

## Phase 8: CSS Generation Engine (Week 6)
**Goal**: Filter-based modular CSS output

### Tasks

- [ ] **8.1 Create `Phantom_Custom_CSS` class**
  - `get_css()` → applies `phantom_dynamic_css` filter, returns CSS string
  - `render_style()` → returns `<style id="phantom-inline-css">` tag or empty string

- [ ] **8.2 Split existing CSS into modular files**
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
  | Responsive | 100 | `includes/custom-css/responsive.php` |

- [ ] **8.3 Add array-based CSS builder (like Astra's `astra_parse_css()`)**
  - `phantom_parse_css($css_array, $min_breakpoint = null, $max_breakpoint = null)`
  - Converts `['selector' => ['property' => 'value']]` to CSS string
  - Optional media query wrapping

- [ ] **8.4 Integrate into Shell**
  - Replace direct CSS generation in `shell.php` with `Phantom_Custom_CSS::render_style()`

### Files Created
`includes/class-custom-css.php`, `includes/custom-css/colors.php`, `includes/custom-css/typography.php`, `includes/custom-css/header.php`, `includes/custom-css/footer.php`, `includes/custom-css/layout.php`, `includes/custom-css/buttons.php`, `includes/custom-css/blog.php`, `includes/custom-css/product.php`, `includes/custom-css/responsive.php`

### Files Touched
`templates/shell.php`

---

## Phase 9: Global Color Palette (Week 6-7)
**Goal**: 9-color palette system with presets and dark mode

### Tasks

- [ ] **9.1 Add palette storage**
  - `global_palette` setting with 4 presets and `current` selection
  - 9 colors per palette with semantic labels (Brand, Alternate Brand, Heading, Text, Background, etc.)

- [ ] **9.2 Add palette CSS output**
  - `:root { --phantom-color-0: #xxx; ... --phantom-color-8: #xxx; }`
  - Re-map individual color settings to palette vars: `--color-primary: var(--phantom-color-0)`

- [ ] **9.3 Add palette control to Customizer**
  - `ast-color-group` showing all 9 colors as expandable group
  - Each color editable individually within group

- [ ] **9.4 Add Gutenberg integration**
  - `add_theme_support('editor-color-palette', $palette_array)`
  - Maps palette colors to editor color picker

- [ ] **9.5 Add dark mode detection**
  - Alternate palette detection via `is_dark_palette()`
  - Plugin-specific dark palette CSS (WooCommerce compatibility)

- [ ] **9.6 Add dark mode toggle**
  - Color Switcher component in header (JStoggle)
  - Stores preference in `localStorage` + session

### Files Created
`includes/class-phantom-global-palette.php`, `admin/js/custom-controls/ast-color-group.js`

### Files Touched
`class-settings-registry.php`, `class-customizer.php`, `class-custom-css.php`, `includes/custom-css/colors.php`

---

## Phase 10: Control Enhancements (Week 7)
**Goal**: Add remaining control types from Astra analysis

### Tasks

- [ ] **10.1 Build `ast-color-group` control**
  - Groups related colors (normal/hover) into expandable parent
  - Used for: link colors, button colors, border colors

- [ ] **10.2 Build `ast-background` control**
  - Color picker + image upload + position + repeat + size + overlay opacity + gradient option
  - Responsive variant: `ast-responsive-background`

- [ ] **10.3 Build `ast-border` control**
  - Width (top/right/bottom/left) + color picker + radius (linked/unlinked)
  - Per-device responsive variants

- [ ] **10.4 Build settings group UI**
  - Tabbed groups (General / Design tabs) via context system
  - Expandable/collapsible sections for related controls

- [ ] **10.5 Add section divider/separator system**
  - `'divider' => ['ast_class' => 'ast-top-divider']` in config arrays
  - CSS classes for visual separation between controls

### Files Created
`includes/custom-controls/class-color-group-control.php`, `includes/custom-controls/class-background-control.php`, `includes/custom-controls/class-border-control.php`, `admin/js/custom-controls/ast-background.js`, `admin/js/custom-controls/ast-border.js`

### Files Touched
`class-customizer.php`, `class-settings-registry.php`

---

## Phase 11: Font Loading System (Week 7-8)
**Goal**: Full font management with system/Google/custom fonts

### Tasks

- [ ] **11.1 Create font families system**
  - `Phantom_Font_Families` class with system fonts + fallback stacks + available weights
  - Google fonts loaded from generated PHP array
  - Custom fonts via `phantom_custom_fonts` filter

- [ ] **11.2 Create font collection system**
  - `Phantom_Fonts::add_font($name, $variants)` — accumulates across `phantom_get_fonts` action
  - Only enqueue fonts that are actually used

- [ ] **11.3 Add self-hosted Google Fonts option**
  - Download Google Fonts to `wp-content/uploads/phantom-fonts/`
  - Toggle in Performance settings tab
  - Preload hint for local fonts

- [ ] **11.4 Add font subset support**
  - Font subset selection in typography control
  - Passed to Google Fonts URL

### Files Created
`includes/class-phantom-font-families.php`, `includes/class-phantom-fonts.php`, `includes/class-phantom-webfont-loader.php`

### Files Touched
`class-settings-registry.php`, `phantom-core.php` (font enqueue)

---

## Phase 12: Settings Upgrade & Migration (Week 8)
**Goal**: Apply all metadata enhancements to existing settings

### Tasks

- [ ] **12.1 Upgrade color settings**
  - 14 color settings: upgrade control to `ast-color` (alpha)
  - Add to CSS generation modules

- [ ] **12.2 Upgrade layout settings (responsive)**
  - 6 layout settings: add `responsive: true`, change default to object format
  - Add backward compat shim

- [ ] **12.3 Upgrade typography settings (composite)**
  - Convert individual font-size settings to composite typography objects
  - Add font family, weight, line-height, letter-spacing

- [ ] **12.4 Add dependency metadata to conditional settings**
  - 7 settings with dependency conditions

- [ ] **12.5 Add partial metadata to HTML-changing settings**
  - 7 settings with selective refresh callbacks

### Files Touched
`class-settings-registry.php` (all sections)

---

## Phase 13: Performance & Polish (Week 8-9)
**Goal**: Optimize, test, and finalize

### Tasks

- [ ] **13.1 CSS file caching**
  - Option to cache generated CSS to static files (uploads directory)
  - Auto-invalidate on settings save
  - Inline CSS fallback in Customizer preview

- [ ] **13.2 JS/CSS minification**
  - Minify all custom control JS files
  - Minify all CSS output

- [ ] **13.3 Settings page ↔ Customizer sync**
  - Ensure Settings Page save triggers `sync_options()`
  - Ensure REST batch save triggers `sync_options()`

- [ ] **13.4 Version bump**
  - Increment `PHANTOM_CORE_VERSION` to `1.5.0`
  - Run version compatibility checks
  - Admin notice for new features

- [ ] **13.5 Documentation**
  - PhantomBridge.js API docs
  - How to create a custom control
  - How to add responsive support to a setting
  - How to add conditional display to a setting

---

## Dependency Graph

```
Phase 0 (WP Core Integration)
  └── Phase 1 (WooCommerce Fixes)
       └── Phase 2 (API Cleanup)
            └── Phase 3 (Controls) ──┐
            └── Phase 4 (Responsive) ─┤
            └── Phase 5 (Conditional) ─┤
                 └── Phase 6 (Refresh) ─┤
                      └── Phase 7 (Bridge) ──┐
                           └── Phase 8 (CSS) ─┤
                                └── Phase 9 (Palette) ─┐
                                     └── Phase 10 (Enhance) ─┐
                                          └── Phase 11 (Fonts) ─┐
                                               └── Phase 12 (Upgrade) ─┐
                                                    └── Phase 13 (Polish)
```

Phases 0, 1, 2 are sequential (each builds on the fix). Phases 3, 4, 5 can run in parallel.

---

## Timeline Estimate

| Phase | Duration | Dependencies |
|-------|----------|-------------|
| Phase 0: WP Core Integration | 5 days | None |
| Phase 1: WooCommerce Fixes | 4 days | Phase 0 |
| Phase 2: API Cleanup | 3 days | Phase 1 |
| Phase 3: Custom Controls | 5 days | Phase 2 |
| Phase 4: Responsive System | 3 days | Phase 2 |
| Phase 5: Conditional Display | 2 days | Phase 2 |
| Phase 6: Selective Refresh | 3 days | Phase 5 |
| Phase 7: PhantomBridge.js | 3 days | Phase 3, 4, 6 |
| Phase 8: CSS Engine | 3 days | Phase 3, 4 |
| Phase 9: Global Palette | 3 days | Phase 3, 8 |
| Phase 10: Enhancements | 4 days | Phase 3, 9 |
| Phase 11: Font System | 3 days | Phase 3 |
| Phase 12: Settings Upgrade | 3 days | All above |
| Phase 13: Polish | 3 days | Phase 12 |

**Total**: ~44 working days (9 weeks)

---

## Verification Checklist

After each phase, verify:
- [ ] Customizer panel/section/control renders without JS errors
- [ ] Setting saves and retrieves correct value
- [ ] Frontend reflects the setting value (CSS var or DOM change)
- [ ] Selective refresh works (if applicable)
- [ ] Responsive values render correctly at all breakpoints (if applicable)
- [ ] Conditional display shows/hides correctly (if applicable)
- [ ] No PHP notices or warnings
- [ ] No JS console errors
- [ ] REST API returns correct data
- [ ] PhantomBridge.js injects settings correctly (Phase 5+)
