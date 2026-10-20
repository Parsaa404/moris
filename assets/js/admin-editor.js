/**
 * Moris admin — rich text (WYSIWYG) editor setup.
 *
 * Turns any <textarea class="rich-ltr"> or <textarea class="rich-rtl">
 * into a visual editor with buttons (bold, headings, lists, links).
 * No HTML knowledge needed. TinyMCE writes clean HTML back to the textarea.
 *
 * TinyMCE is loaded (self-hosted GPL build) from jsDelivr in the page.
 */
(function () {
  'use strict';

  // Toolbar and plugins shared by both editors.
  function baseConfig(direction) {
    return {
      menubar: false,
      plugins: 'lists link autolink',
      toolbar:
        'undo redo | blocks | bold italic underline | ' +
        'bullist numlist | link | removeformat',
      block_formats:
        'Paragraph=p; Heading=h2; Subheading=h3; Quote=blockquote',
      height: 320,
      directionality: direction, // 'ltr' or 'rtl'
      branding: false,
      promotion: false,
      license_key: 'gpl',
      content_style:
        'body{font-family:system-ui,-apple-system,Segoe UI,sans-serif;font-size:15px;line-height:1.6;}',
      // Keep the underlying textarea in sync as the user types.
      setup: function (editor) {
        editor.on('change keyup', function () {
          editor.save();
        });
      },
    };
  }

  function initEditors() {
    if (typeof window.tinymce === 'undefined') {
      // TinyMCE failed to load (e.g. no internet). Textareas still work as plain HTML.
      return;
    }

    if (document.querySelector('textarea.rich-ltr')) {
      window.tinymce.init(
        Object.assign({ selector: 'textarea.rich-ltr' }, baseConfig('ltr'))
      );
    }

    if (document.querySelector('textarea.rich-rtl')) {
      window.tinymce.init(
        Object.assign({ selector: 'textarea.rich-rtl' }, baseConfig('rtl'))
      );
    }

    // Safety net: flush editor content to textareas right before any form submits.
    document.addEventListener(
      'submit',
      function () {
        if (window.tinymce) {
          window.tinymce.triggerSave();
        }
      },
      true
    );
  }

  if (document.readyState !== 'loading') {
    initEditors();
  } else {
    document.addEventListener('DOMContentLoaded', initEditors);
  }
})();
