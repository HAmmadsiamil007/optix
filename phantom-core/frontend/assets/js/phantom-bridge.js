/**
 * PhantomBridge.js v1.0
 * Frontend adapter layer for Phantom Core.
 * Reads window.PhantomData and provides a decoupled API.
 *
 * Usage:
 *   <script src="phantom-bridge.js"></script>
 *   <script>PhantomBridge.init();</script>
 */
(function (root) {
  'use strict';

  var bridge = {
    _data: {},
    _cssVarMap: {},
    _listeners: {},
    _styleEl: null,
    _initialized: false,

    init: function (opts) {
      opts = opts || {};
      var data = root.PhantomData || opts.data || {};
      this._cssVarMap = opts.cssVarMap || data._cssVarMap || {};

      this._data = {};
      for (var key in data) {
        if (data.hasOwnProperty(key) && key.indexOf('_') !== 0) {
          this._data[key] = data[key];
        }
      }

      this._injectCssVars();
      this._initialized = true;
      return this;
    },

    _injectCssVars: function () {
      var cssVars = this._cssVarMap;
      var rootEl = document.documentElement;

      for (var key in cssVars) {
        if (!cssVars.hasOwnProperty(key)) continue;
        var val = this._data[key];
        if (val === undefined || val === null) continue;
        rootEl.style.setProperty(cssVars[key], String(val));
      }

      var existing = document.getElementById('phantom-bridge-style');
      if (existing) existing.remove();
    },

    getSetting: function (key) {
      if (!this._initialized) return undefined;
      return this._data[key];
    },

    setSetting: function (key, value) {
      if (!this._initialized) return Promise.reject(new Error('Bridge not initialized'));
      this._data[key] = value;

      var cssVar = this._cssVarMap[key];
      if (cssVar) {
        document.documentElement.style.setProperty(cssVar, String(value));
      }

      var self = this;
      return fetch('/index.php?rest_route=/phantom/v1/settings/' + encodeURIComponent(key), {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ value: value }),
        credentials: 'same-origin'
      }).then(function (r) {
        if (r.status === 401) {
          return fetch('/index.php?rest_route=/phantom/v1/settings/' + encodeURIComponent(key), {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ value: value }),
            credentials: 'same-origin'
          });
        }
        return r;
      }).then(function (r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        self._emit(key, value);
        return r.json();
      });
    },

    onSettingChange: function (key, callback) {
      if (!this._listeners[key]) this._listeners[key] = [];
      this._listeners[key].push(callback);
      return this;
    },

    offSettingChange: function (key, callback) {
      if (!this._listeners[key]) return;
      this._listeners[key] = this._listeners[key].filter(function (fn) {
        return fn !== callback;
      });
      return this;
    },

    _emit: function (key, value) {
      var fns = this._listeners[key] || [];
      for (var i = 0; i < fns.length; i++) {
        try { fns[i](value, key); } catch (e) { console.warn('[PhantomBridge] listener error', e); }
      }
    },

    getCssVars: function () {
      if (!this._initialized) return {};
      var result = {};
      for (var key in this._cssVarMap) {
        if (!this._cssVarMap.hasOwnProperty(key)) continue;
        result[this._cssVarMap[key]] = this._data[key];
      }
      return result;
    },

    highlightElement: function (selector) {
      var el = document.querySelector(selector);
      if (!el) return;
      el.classList.add('phantom-highlight');
      setTimeout(function () { el.classList.remove('phantom-highlight'); }, 2000);
    },

    openEditor: function (key) {
      var customizerUrl = '/wp-admin/customize.php?autofocus[control]=phantom_' + encodeURIComponent(key);
      window.open(customizerUrl, '_blank');
    },

    saveChanges: function (changes) {
      if (!this._initialized) return Promise.reject(new Error('Bridge not initialized'));
      var self = this;
      for (var key in changes) {
        if (!changes.hasOwnProperty(key)) continue;
        self._data[key] = changes[key];
      }
      return fetch('/index.php?rest_route=/phantom/v1/settings', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ settings: changes }),
        credentials: 'same-origin'
      }).then(function (r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
      }).then(function (data) {
        Object.keys(changes).forEach(function (k) { self._emit(k, changes[k]); });
        return data;
      });
    }
  };

  root.PhantomBridge = bridge;

})(window);
