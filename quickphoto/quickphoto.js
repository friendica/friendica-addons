(function() {
    const monsterPattern = /\[url=(.*?)\]\[img=(.*?)\](.*?)\[\/img\]\[\/url\]/gi;
    let throttleTimer;

    const i18nDesc = (window.qp_i18n && window.qp_i18n.imageDesc) ? window.qp_i18n.imageDesc : "Image description";

    const cleanupOldEntries = () => {
        const now = Date.now();
        const twelveHours = 12 * 60 * 60 * 1000;
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key && key.startsWith('qp_')) {
                try {
                    const data = JSON.parse(localStorage.getItem(key));
                    if (data && data.timestamp && (now - data.timestamp > twelveHours)) {
                        localStorage.removeItem(key);
                    }
                } catch (e) { localStorage.removeItem(key); }
            }
        }
    };

    const simplify = (text) => {
        if (!text || !text.includes('[url=')) return text;
        return text.replace(monsterPattern, (match, urlPart, imgPart, existingDesc) => {
            const fileName = imgPart.split('/').pop();
            const storageKey = `qp_${fileName}`;

            localStorage.setItem(storageKey, JSON.stringify({
                url: urlPart,
                img: imgPart,
                timestamp: Date.now()
            }));

            let userDesc = existingDesc.trim() || i18nDesc;
            return `[img]${fileName}|${userDesc}[/img]`;
        });
    };

    const reconstruct = (text) => {
        if (!text || !text.includes('[img]')) return text;
        return text.replace(/\[img\](.*?)\|(.*?)\[\/img\]/g, (match, fileName, desc) => {
            const data = localStorage.getItem(`qp_${fileName}`);
            if (data) {
                const parsed = JSON.parse(data);
                const finalDesc = (desc === i18nDesc) ? "" : desc;
                return `[url=${parsed.url}][img=${parsed.img}]${finalDesc}[/img][/url]`;
            }
            return match;
        });
    };

    const applySimplify = (textarea) => {
        if (!textarea || !textarea.value || !textarea.value.includes('[/img]')) return;

        (window.requestIdleCallback || function(cb) { return setTimeout(cb, 1); })(() => {
            const current = textarea.value;
            const simple = simplify(current);
            if (current !== simple) {
                const start = textarea.selectionStart;
                const end = textarea.selectionEnd;
                textarea.value = simple;
                textarea.setSelectionRange(start, end);
            }
        });
    };

    if (typeof jQuery !== 'undefined') {
        const originalVal = jQuery.fn.val;
        jQuery.fn.val = function(value) {
            if (arguments.length === 0 && this.is('textarea')) {
                return reconstruct(originalVal.call(this));
            }
            if (arguments.length > 0 && this.is('textarea')) {
                return originalVal.call(this, simplify(value));
            }
            return originalVal.apply(this, arguments);
        };
    }

    document.addEventListener('drop', (e) => {
        if (e.target.tagName === 'TEXTAREA') {
            setTimeout(() => applySimplify(e.target), 150);
        }
    }, true);

    document.addEventListener('input', (e) => {
        if (e.target.tagName === 'TEXTAREA') {
            clearTimeout(throttleTimer);
            throttleTimer = setTimeout(() => applySimplify(e.target), 500);
        }
    });

    document.addEventListener('click', (e) => {
        const btn = e.target.closest(
            '#wall-submit-preview, #profile-jot-submit, #wall-submit-submit, #jot-submit, ' +
            '[id^="comment-edit-submit-"], [id^="comment-edit-preview-link-"]'
        );

        if (btn) {
            const textareas = document.querySelectorAll('textarea');
            if (textareas.length > 0) {
                textareas.forEach(textarea => {
                    textarea.value = reconstruct(textarea.value);
                    if (btn.id.includes('preview')) {
                        setTimeout(() => applySimplify(textarea), 1000);
                    }
                });
            }
        }
    }, true);

    setInterval(() => {
        if (document.hidden) return;
        const textareas = document.querySelectorAll('textarea');
        if (textareas.length === 0) return;

        textareas.forEach(textarea => {
            if (textarea.offsetParent !== null) {
                applySimplify(textarea);
            }
        });
    }, 2500);

    cleanupOldEntries();
})();
