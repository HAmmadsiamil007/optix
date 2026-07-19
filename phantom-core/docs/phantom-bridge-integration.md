# PhantomBridge.js Integration Guide

## Standalone HTML Frontend
```html
<!DOCTYPE html>
<html>
<head>
    <script src="https://your-site.com/wp-content/plugins/phantom-core/frontend/assets/js/phantom-bridge.js"></script>
</head>
<body>
    <h1 id="site-title">My Site</h1>
    <script>
        PhantomBridge.init().then(function() {
            // CSS vars are now on :root
            var title = PhantomBridge.getSetting('blogname');
            if (title) {
                document.getElementById('site-title').textContent = title;
            }
        });
    </script>
</body>
</html>
```

## WordPress Integration
Already integrated in Shell. Bridge gets data from `window.PhantomData`.

## API Reference
- `PhantomBridge.init(options)` — Initialize. Options: `{lazy: bool, prefix: string}`
- `PhantomBridge.getSetting(key)` — Get setting value
- `PhantomBridge.setSetting(key, value)` — Save via REST API
- `PhantomBridge.onSettingChange(key, callback)` — Subscribe to changes
- `PhantomBridge.getCssVars()` — Get active CSS vars
- `PhantomBridge.highlightElement(selector)` — Show edit indicator
- `PhantomBridge.openEditor(key)` — Open Customizer panel
- `PhantomBridge.saveChanges(changes)` — Bulk save
