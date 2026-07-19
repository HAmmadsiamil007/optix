/**
 * Phantom Core Frontend Editor v1.0
 * Inline editing for logged-in admin users.
 * Click any text element on the frontend to edit and save.
 */
(function () {
  'use strict';

  const apiBase = '/index.php?rest_route=/phantom/v1';
  let editMode = false;
  let toolbar = null;
  let restNonce = '';

  function getNonce() {
    var meta = document.querySelector('meta[name="wp-rest-nonce"]');
    return meta ? meta.getAttribute('content') : '';
  }

  function isAdmin() {
    return document.body && document.body.classList.contains('phantom-editor-enabled');
  }

  function saveSetting(key, value) {
    const url = apiBase + '/settings/' + encodeURIComponent(key);
    var headers = { 'Content-Type': 'application/json' };
    if (restNonce) headers['X-WP-Nonce'] = restNonce;
    return fetch(url, {
      method: 'PUT',
      credentials: 'same-origin',
      headers: headers,
      body: JSON.stringify({ value: value })
    }).then(function (r) {
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
    });
  }

  function showToast(msg, type) {
    var t = document.createElement('div');
    t.className = 'phantom-editor-toast phantom-editor-toast-' + (type || 'success');
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(function () {
      t.style.opacity = '0';
      setTimeout(function () { t.remove(); }, 300);
    }, 2000);
  }

  function onEditableBlur(e) {
    var el = e.currentTarget;
    var key = el.getAttribute('data-phantom-key');
    if (!key) return;
    var val = el.tagName === 'A' ? el.getAttribute('href') : el.innerHTML.replace(/<br\s*\/?>/g, '\n');
    el.removeAttribute('contenteditable');
    el.classList.remove('phantom-editing');
    saveSetting(key, val).then(function () {
      showToast('Saved: ' + key, 'success');
    }).catch(function () {
      showToast('Save failed: ' + key, 'error');
    });
  }

  function onEditableKeydown(e) {
    if (e.key === 'Escape') {
      e.preventDefault();
      e.target.blur();
    }
  }

  function activateEdit(el) {
    if (el.getAttribute('contenteditable') === 'true') return;
    el.setAttribute('contenteditable', 'true');
    el.classList.add('phantom-editing');
    el.focus();
    el.addEventListener('blur', onEditableBlur, { once: true });
    el.addEventListener('keydown', onEditableKeydown);
  }

  function onElementClick(e) {
    if (!editMode) return;
    var el = e.target.closest('[data-phantom-key]');
    if (!el) return;
    e.preventDefault();
    e.stopPropagation();
    activateEdit(el);
  }

  function toggleEditMode() {
    editMode = !editMode;
    var btn = toolbar.querySelector('.phantom-editor-toggle');
    if (editMode) {
      btn.textContent = 'Exit Edit Mode';
      btn.classList.add('active');
      document.body.classList.add('phantom-edit-mode');
    } else {
      btn.textContent = 'Edit Page';
      btn.classList.remove('active');
      document.body.classList.remove('phantom-edit-mode');
      // Remove any active editing
      document.querySelectorAll('[contenteditable="true"]').forEach(function (el) {
        el.removeAttribute('contenteditable');
        el.classList.remove('phantom-editing');
      });
    }
  }

  function createToolbar() {
    toolbar = document.createElement('div');
    toolbar.className = 'phantom-editor-toolbar';
    var container = document.createElement('div');
    container.className = 'phantom-editor-inner';

    var label = document.createElement('span');
    label.className = 'phantom-editor-label';
    label.textContent = 'Frontend Editor';
    container.appendChild(label);

    var btn = document.createElement('button');
    btn.className = 'phantom-editor-toggle';
    btn.textContent = 'Edit Page';
    btn.setAttribute('title', 'Toggle frontend edit mode');
    btn.addEventListener('click', toggleEditMode);
    container.appendChild(btn);

    var help = document.createElement('span');
    help.className = 'phantom-editor-help';
    help.textContent = 'Click any text to edit inline';
    container.appendChild(help);

    toolbar.appendChild(container);
    document.body.appendChild(toolbar);
  }

  function init() {
    if (!isAdmin()) return;
    restNonce = getNonce();
    createToolbar();
    document.addEventListener('click', onElementClick);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
