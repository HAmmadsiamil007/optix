# Task 3 Report — Utility & Promotional Sections

**Status:** Complete

## Files Modified (6)

| Section | Schema Setting | data-aos Target |
|---|---|---|
| `sections/countdown.liquid` | Added to `settings[]` | `countdown-wrapper` div (line 13) |
| `sections/featured-video.liquid` | Added to `settings[]` | `page-width` div (line 3) |
| `sections/hotspots.liquid` | Added to `settings[]` | `index-section` div (line 52) |
| `sections/image-compare.liquid` | Added to `settings[]` | `index-section` div (line 69) |
| `sections/advanced-content.liquid` | Added to `settings[]` | `custom-content` div (line 7) |
| `sections/newsletter.liquid` | Added to `settings[]` (new `settings` block) | New wrapper div around `{% render %}` (line 12) |

## Verification

- All 6 files have the `entrance_animation` select setting with 7 options (existing + 6 animations)
- All 6 files have `data-aos="..."` on the correct wrapper element
- `newsletter.liquid` had no existing wrapper — added a `<div data-aos="...">` around the render call
- No files outside `sections/` were modified

## Commit

feat(phase4): Task 3 - wire entrance_animation to 6 utility sections
