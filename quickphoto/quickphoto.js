(function() {
    const monsterPattern = /\[url=(.*?)\]\[img=(.*?)\](.*?)\[\/img\]\[\/url\]/gi;
    let throttleTimer;
    let isSubmitting = false;

    const i18nDesc = (window.qp_i18n && window.qp_i18n.imageDesc) ? window.qp_i18n.imageDesc : "Image description";

    const storeMetadata = (textarea, fileName, data) => {
        let metadata = {};
        try {
            const raw = textarea.getAttribute('data-qp-metadata');
            if (raw) metadata = JSON.parse(raw);
        } catch (e) { metadata = {}; }

        metadata[fileName] = data;
        textarea.setAttribute('data-qp-metadata', JSON.stringify(metadata));
    };

    const getMetadata = (textarea, fileName) => {
        try {
            const raw = textarea.getAttribute('data-qp-metadata');
            if (!raw) return null;
            const metadata = JSON.parse(raw);
            return metadata[fileName] || null;
        } catch (e) { return null; }
    };

    const simplify = (textarea) => {
        if (!textarea || !textarea.value.includes('[url=')) return;

        const current = textarea.value;
        const simple = current.replace(monsterPattern, (match, urlPart, imgPart, existingDesc) => {
            const fileName = imgPart.split('/').pop();

            storeMetadata(textarea, fileName, {
                url: urlPart,
                img: imgPart
            });

            let userDesc = existingDesc.trim() || i18nDesc;
            return `[img]${fileName}|${userDesc}[/img]`;
        });

        if (current !== simple) {
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            textarea.value = simple;
            textarea.setSelectionRange(start, end);
        }
    };

    const reconstruct = (textarea) => {
        if (!textarea || !textarea.value.includes('[img]')) return textarea.value;

        return textarea.value.replace(/\[img\](.*?)\|(.*?)\[\/img\]/g, (match, fileName, desc) => {
            const data = getMetadata(textarea, fileName);
            if (data) {
                // Falls die Beschreibung dem Standard-Platzhalter entspricht, leeren wir sie für den Server
                const finalDesc = (desc === i18nDesc) ? "" : desc;
                return `[url=${data.url}][img=${data.img}]${finalDesc}[/img][/url]`;
            }
            return match;
        });
    };

    const applySimplify = (textarea) => {
        if (isSubmitting || !textarea || !textarea.value || !textarea.value.includes('[/img]')) return;

        (window.requestIdleCallback || function(cb) { return setTimeout(cb, 1); })(() => {
            if (!isSubmitting) simplify(textarea);
        });
    };

    document.addEventListener('submit', function(e) {
        isSubmitting = true;
        const textareas = e.target.querySelectorAll('textarea');
        textareas.forEach(textarea => {
            textarea.value = reconstruct(textarea);
        });
    }, true);

    if (typeof jQuery !== 'undefined') {
        const originalVal = jQuery.fn.val;
        jQuery.fn.val = function(value) {
            if (arguments.length === 0 && this.is('textarea')) {
                return reconstruct(this[0]);
            }
            if (arguments.length > 0 && this.is('textarea')) {
                const result = originalVal.call(this, value);
                simplify(this[0]);
                return result;
            }
            return originalVal.apply(this, arguments);
        };
    }

    document.addEventListener('input', (e) => {
        if (e.target.tagName === 'TEXTAREA') {
            clearTimeout(throttleTimer);
            throttleTimer = setTimeout(() => applySimplify(e.target), 500);
        }
    });

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('#wall-submit-preview, [id^="comment-edit-preview-link-"]');
        if (btn) {
            document.querySelectorAll('textarea').forEach(textarea => {
                setTimeout(() => {
                    isSubmitting = false;
                    applySimplify(textarea);
                }, 1000);
            });
        }
    }, true);

    setInterval(() => {
        if (document.hidden || isSubmitting) return;
        document.querySelectorAll('textarea').forEach(textarea => {
            if (textarea.offsetParent !== null) applySimplify(textarea);
        });
    }, 2500);

})();
