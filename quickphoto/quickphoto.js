(function() {
    const BBCodePattern = /\[url=(.*?)\]\[img=(.*?)\](.*?)\[\/img\]\[\/url\]/gi;
    const shorthandPattern = /\[img\](.*?)\|([\s\S]*?)\[\/img\]/gi;
    let throttleTimer;
    let isSubmitting = false;

    window._qpMetadataCache = window._qpMetadataCache || {};

    const i18nDesc = window.qp_i18n?.imageDesc || "Image description";

    const resetSPAState = () => {
        isSubmitting = false;
        window._qpMetadataCache = {};
        document.querySelectorAll('.qp-edit-bar').forEach(bar => {
            if (bar.previousElementSibling?.tagName !== 'TEXTAREA') bar.remove();
        });
    };

    ['pjax:end', 'page-changed', 'popstate'].forEach(evt =>
        document.addEventListener(evt, resetSPAState)
    );

    const storeMetadata = (imgPath, data) => { window._qpMetadataCache[imgPath] = data; };
    const getMetadata = (imgPath) => window._qpMetadataCache[imgPath] || null;

    const getOrCreateEditBar = (textarea) => {

        let bar = textarea.parentNode.querySelector('.qp-edit-bar');

        if (!bar) {
            bar = document.createElement('div');
            bar.className = 'qp-edit-bar';

            // Ensures that the CSS Flexbox layout works correctly in SPA mode and Darkmode
            bar.style.cssText = 'display:none; align-items:center; gap:10px; margin-top:8px; padding:8px; width:100%; box-sizing:border-box; border:1px solid var(--border-color, #ccc); border-radius:4px;';

            bar.innerHTML = `
                <div class="qp-thumb-container" style="flex-shrink:0; width:48px; height:48px; min-width:48px; min-height:48px; max-width:48px; max-height:48px; display:flex; align-items:center; justify-content:center; overflow:hidden; border:1px solid var(--border-color, #ddd); border-radius:4px;">
                    <img class="qp-preview-thumb" src="" alt="Preview" style="width:100% !important; height:100% !important; max-width:48px !important; max-height:48px !important; object-fit:cover !important; display:block;">
                </div>
                <div class="qp-input-wrapper" style="flex-grow:1;">
                    <input type="text" class="qp-alt-input" placeholder="${i18nDesc}" style="width:100%; padding:6px 10px; border:1px solid var(--border-color, #ccc); border-radius:4px; box-sizing:border-box; color:inherit;">
                </div>`;
            textarea.parentNode.insertBefore(bar, textarea.nextSibling);
        }
        return bar;
    };

    const checkCursorContext = (textarea) => {
        if (isSubmitting) return;

        const text = textarea.value;
        const bar = textarea.parentNode.querySelector('.qp-edit-bar');

        if (!text.includes('[img]')) {
            if (bar) bar.style.display = 'none';
            return;
        }

        const pos = textarea.selectionStart;
        const openTag = text.lastIndexOf('[img]', pos);
        const closeTag = text.indexOf('[/img]', pos);

        if (openTag !== -1 && closeTag !== -1 && pos >= openTag && pos <= (closeTag + 6)) {
            const tagContent = text.substring(openTag + 5, closeTag);

            if (tagContent.includes('|')) {
                const [imgPath, ...descParts] = tagContent.split('|');
                const currentDesc = descParts.join('|');
                const editBar = getOrCreateEditBar(textarea);

                const img = editBar.querySelector('.qp-preview-thumb');
                const thumbContainer = editBar.querySelector('.qp-thumb-container');
                const input = editBar.querySelector('.qp-alt-input');

                editBar.style.display = 'flex';

                const isValidUrl = /^(https?:\/\/|\/|data:)/i.test(imgPath.trim());
                if (isValidUrl) {
                    img.src = imgPath.trim();
                    if (thumbContainer) thumbContainer.style.display = 'flex';
                } else {
                    img.src = '';
                    if (thumbContainer) thumbContainer.style.display = 'none';
                }

                if (document.activeElement !== input) {
                    input.value = (currentDesc === i18nDesc) ? '' : currentDesc;
                }

                input.onkeydown = (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        textarea.focus();
                        editBar.style.display = 'none';
                    }
                };

                input.oninput = (e) => {
                    const newDesc = e.target.value.replace(/[\[\]]/g, '');
                    const newTag = `[img]${imgPath}|${newDesc || i18nDesc}[/img]`;

                    const start = textarea.selectionStart;
                    const end = textarea.selectionEnd;

                    textarea.value = text.substring(0, openTag) + newTag + text.substring(closeTag + 6);
                    textarea.setSelectionRange(start, end);
                };
                return;
            }
        }

        if (bar) bar.style.display = 'none';
    };

    const simplify = (textarea) => {
        if (!textarea.value.includes('[url=')) return;

        const current = textarea.value;
        const simple = current.replace(BBCodePattern, (match, urlPart, imgPart, existingDesc) => {
            storeMetadata(imgPart, { url: urlPart, img: imgPart });
            let userDesc = existingDesc.trim();
            if (!userDesc || userDesc === i18nDesc) userDesc = i18nDesc;
            return `[img]${imgPart}|${userDesc}[/img]`;
        });

        if (current !== simple) {
            const { selectionStart: start, selectionEnd: end } = textarea;
            textarea.value = simple;
            textarea.setSelectionRange(start, end);
        }
    };

    const reconstruct = (textarea) => {
        if (!textarea) return '';
        return textarea.value.replace(shorthandPattern, (match, imgPath, desc) => {
            const metadata = getMetadata(imgPath);
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
        e.target.querySelectorAll('textarea').forEach(textarea => {
            textarea.value = reconstruct(textarea);
        });
    }, true);

    document.addEventListener('keyup', (e) => {
        if (e.target.tagName === 'TEXTAREA') checkCursorContext(e.target);
    });

    document.addEventListener('click', (e) => {
        if (e.target.tagName === 'TEXTAREA') {
            checkCursorContext(e.target);
            return;
        }

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

    if (typeof jQuery !== 'undefined') {
        const originalVal = jQuery.fn.val;
        jQuery.fn.val = function(value) {
            if (!this.is('textarea')) return originalVal.apply(this, arguments);

            if (arguments.length === 0) {
                return reconstruct(this[0]);
            }
            const result = originalVal.call(this, value);
            if (this[0]) simplify(this[0]);
            return result;
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

    setInterval(() => {
        if (document.hidden || isSubmitting) return;
        document.querySelectorAll('textarea').forEach(textarea => {
            if (textarea.offsetParent !== null) applySimplify(textarea);
        });
    }, 2500);
})();
