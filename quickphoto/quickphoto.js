(function() {
    const BBCodePattern = /\[url=(.*?)\]\[img=(.*?)\](.*?)\[\/img\]\[\/url\]/gi;
    const shorthandPattern = /\[img\](.*?)\|(.*?)\[\/img\]/gi;
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

    const getOrCreateEditBar = (textarea) => {
        if (textarea._qpBar) return textarea._qpBar;

        const bar = document.createElement('div');
        bar.className = 'qp-edit-bar';
        bar.innerHTML = `
            <div class="qp-thumb-container">
                <img class="qp-preview-thumb" src="" alt="Preview">
            </div>
            <div class="qp-input-wrapper">
                <input type="text" class="qp-alt-input" placeholder="${i18nDesc}">
            </div>
        `;

        textarea.parentNode.insertBefore(bar, textarea.nextSibling);
        textarea._qpBar = bar;
        return bar;
    };

    const checkCursorContext = (textarea) => {
        if (isSubmitting) return;

        const pos = textarea.selectionStart;
        const text = textarea.value;
        const bar = getOrCreateEditBar(textarea);

        const openTag = text.lastIndexOf('[img]', pos);
        const closeTag = text.indexOf('[/img]', pos);

        if (openTag !== -1 && closeTag !== -1 && openTag < pos && closeTag >= (pos - 5)) {
            const tagContent = text.substring(openTag + 5, closeTag);

            if (tagContent.includes('|')) {
                const [fileName, ...descParts] = tagContent.split('|');
                const currentDesc = descParts.join('|');
                const metadata = getMetadata(textarea, fileName);

                if (metadata) {
                    const img = bar.querySelector('.qp-preview-thumb');
                    const input = bar.querySelector('.qp-alt-input');

                    bar.classList.add('active');
                    img.src = metadata.img;

                    if (document.activeElement !== input) {
                        input.value = (currentDesc === i18nDesc) ? '' : currentDesc;
                    }

                    input.onkeydown = (e) => {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            textarea.focus();
                            bar.classList.remove('active');
                        }
                    };

                    input.oninput = (e) => {
                        const newDesc = e.target.value.replace(/[\[\]]/g, '');
                        const newTag = `[img]${fileName}|${newDesc || i18nDesc}[/img]`;

                        const start = textarea.selectionStart;
                        const end = textarea.selectionEnd;

                        textarea.value = text.substring(0, openTag) + newTag + text.substring(closeTag + 6);
                        textarea.setSelectionRange(start, end);
                    };
                    return;
                }
            }
        }
        bar.classList.remove('active');
    };

    const simplify = (textarea) => {
        if (!textarea || !textarea.value.includes('[url=')) return;

        const current = textarea.value;
        const simple = current.replace(BBCodePattern, (match, urlPart, imgPart, existingDesc) => {
            const fileName = imgPart.split('/').pop();

            storeMetadata(textarea, fileName, {
                url: urlPart,
                img: imgPart
            });

            let userDesc = existingDesc.trim();
            if (userDesc === '' || userDesc === i18nDesc) userDesc = i18nDesc;

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
        if (!textarea) return '';
        return textarea.value.replace(shorthandPattern, (match, fileName, desc) => {
            const metadata = getMetadata(textarea, fileName);
            if (metadata) {
                const finalDesc = (desc === i18nDesc) ? '' : desc;
                return `[url=${metadata.url}][img=${metadata.img}]${finalDesc}[/img][/url]`;
            }
            return match;
        });
    };

    const applySimplify = (textarea) => {
        if (!isSubmitting) simplify(textarea);
    };

    document.addEventListener('focusin', (e) => {
        if (e.target.tagName === 'TEXTAREA') applySimplify(e.target);
    });

    document.addEventListener('submit', (e) => {
        isSubmitting = true;
        const textareas = e.target.querySelectorAll('textarea');
        textareas.forEach(textarea => {
            textarea.value = reconstruct(textarea);
        });
    }, true);

    document.addEventListener('keyup', (e) => {
        if (e.target.tagName === 'TEXTAREA') checkCursorContext(e.target);
    });

    document.addEventListener('click', (e) => {
        if (e.target.tagName === 'TEXTAREA') checkCursorContext(e.target);
    });

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
            throttleTimer = setTimeout(() => {
                applySimplify(e.target);
                checkCursorContext(e.target);
            }, 500);
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
