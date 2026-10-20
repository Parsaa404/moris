<?php
/**
 * Loads the rich text editor (TinyMCE, self-hosted GPL build via jsDelivr)
 * plus our init script. Include this near the end of any admin form page
 * that uses <textarea class="rich-ltr"> / <textarea class="rich-rtl">.
 *
 * Requires internet access to load TinyMCE from the CDN. If offline, the
 * textareas gracefully fall back to plain HTML editing.
 */

declare(strict_types=1);
?>
<script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js" referrerpolicy="origin"></script>
<script src="<?= e(url('assets/js/admin-editor.js')) ?>"></script>
