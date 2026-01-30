/**
 * Hebrew Latin Text Handler
 * Version safe WooCommerce / Gutenberg
 */

(function ($) {
    'use strict';

    /* ===============================
       Utils
    =============================== */

    function containsHebrew(text) {
        return /[\u0590-\u05FF]/.test(text);
    }

    function containsLatin(text) {
        return /[a-zA-Z]/.test(text);
    }

    /* ===============================
       Bind unique input handler
    =============================== */

    function bindInput($el) {
        // Évite les bindings multiples
        if ($el.data('hlth-bound')) return;
        $el.data('hlth-bound', true);

        $el.on('input.hlth keyup.hlth', function () {
            let text = '';

            if ($el.is('[contenteditable="true"]')) {
                text = $el.text();
            } else {
                text = $el.val();
            }

            const hasHebrew = containsHebrew(text);
            const hasLatin = containsLatin(text);

            if (hasHebrew && hasLatin) {
                $el.attr('dir', 'auto')
                   .addClass('hebrew-latin-mixed')
                   .removeClass('hebrew-only');
            } else if (hasHebrew) {
                $el.attr('dir', 'rtl')
                   .addClass('hebrew-only')
                   .removeClass('hebrew-latin-mixed');
            } else {
                $el.attr('dir', 'ltr')
                   .removeClass('hebrew-latin-mixed hebrew-only');
            }
        });
    }

    /* ===============================
       Apply on context only
    =============================== */

    function applyTextDirection(context) {
        context = context || document;

        $(context).find(
            'input[type="text"], input[type="email"], input[type="url"], textarea, [contenteditable="true"]'
        ).each(function () {
            bindInput($(this));
        });
    }

    /* ===============================
       DOM Ready
    =============================== */

    $(document).ready(function () {
        applyTextDirection();
    });

    /* ===============================
       MutationObserver (safe)
    =============================== */

    if (window.MutationObserver) {
        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (node.nodeType === 1) {
                        applyTextDirection(node);
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    /* ===============================
       Gutenberg support (throttled)
    =============================== */

    let gutenbergTimeout = null;

    if (window.wp && window.wp.data) {
        wp.data.subscribe(function () {
            if (gutenbergTimeout) return;

            gutenbergTimeout = setTimeout(function () {
                applyTextDirection();
                gutenbergTimeout = null;
            }, 300);
        });
    }

})(jQuery);
