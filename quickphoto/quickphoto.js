(function() {
    const BBCodePattern = /\[url=(.*?)\]\[img=(.*?)\](.*?)\[\/img\]\[\/url\]/gi;
    const shorthandPattern = /\[img\](.*?)\|(.*?)\[\/img\]/gi;
    let throttleTimer;
    let isSubmitting = false;

    window._qpMetadataCache = window._qpMetadataCache || {};

    const i18nDesc = (window.qp_i18n && window.qp_i18n.imageDesc) ? window.qp_i18n.imageDesc : "Image description";

    const resetSPAState = () => {
        isSubmitting = false;
        document.querySelectorAll('.qp-edit-bar').forEach(bar => {
            if (!bar.previousElementSibling || bar.previousElementSibling.tagName !== 'TEXTAREA') {
                bar.remove();
            }
        });
    };

    document.addEventListener('pjax:end', resetSPAState);
    document.addEventListener('page-changed', resetSPAState);
    window.addEventListener('popstate', resetSPAState);

    const storeMetadata = (fileName, data) => {
        window._qpMetadataCache[fileName] = data;
    };

    const getMetadata = (fileName) => {
        return window._qpMetadataCache[fileName] || null;
    };

    const getOrCreateEditBar = (textarea) => {
        if (!textarea || !document.body.contains(textarea)) return null;

        let bar = textarea.parentNode.querySelector('.qp-edit-bar');

        if (!bar) {
            bar = document.createElement('div');
            bar.className = 'qp-edit-bar';

            // Ensures that the CSS Flexbox layout works correctly in SPA mode
            bar.style.cssText = 'display: none; align-items: center; gap: 10px; margin-top: 8px; padding: 8px; background: rgba(0,0,0,0.03); border-radius: 4px; border: 1px solid #ccc; width: 100%; box-sizing: border-box;';

            bar.innerHTML = `
                <div class="qp-thumb-container" style="flex-shrink: 0; width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; overflow: hidden; background: #fff; border: 1px solid #ddd; border-radius: 4px;">
                    <img class="qp-preview-thumb" src="" alt="Preview" style="max-width: 100%; max-height: 100%; object-fit: cover;">
                </div>
                <div class="qp-input-wrapper" style="flex-grow: 1;">
                    <input type="text" class="qp-alt-input" placeholder="${i18nDesc}" style="width: 100%; padding: 6px 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                </div>
            `;
            textarea.parentNode.insertBefore(bar, textarea.nextSibling);
        }
        return bar;
    };

    const checkCursorContext = (textarea) => {
        if (isSubmitting || !textarea || !document.body.contains(textarea)) return;

        const text = textarea.value;

        if (!text.includes('[img]')) {
            const existingBar = textarea.parentNode.querySelector('.qp-edit-bar');
            if (existingBar) {
                existingBar.style.display = 'none';
            }
            return;
        }

        const pos = textarea.selectionStart;
        const openTag = text.lastIndexOf('[img]', pos);
        const closeTag = text.indexOf('[/img]', pos);

        if (openTag !== -1 && closeTag !== -1 && pos >= openTag && pos <= (closeTag + 6)) {
            const tagContent = text.substring(openTag + 5, closeTag);

            if (tagContent.includes('|')) {
                const [fileName, ...descParts] = tagContent.split('|');
                const currentDesc = descParts.join('|');
                const metadata = getMetadata(fileName);

                const bar = getOrCreateEditBar(textarea);
                if (!bar) return;

                const img = bar.querySelector('.qp-preview-thumb');
                const input = bar.querySelector('.qp-alt-input');

                bar.style.display = 'flex';

                if (metadata && metadata.img) {
                    img.src = metadata.img;
                } else {
                    img.src = '/photo/' + fileName;
                }

                if (document.activeElement !== input) {
                    input.value = (currentDesc === i18nDesc) ? '' : currentDesc;
                }

                input.onkeydown = (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        textarea.focus();
                        bar.style.display = 'none';
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

        const bar = textarea.parentNode.querySelector('.qp-edit-bar');
        if (bar) {
            bar.style.display = 'none';
        }
    };

    const simplify = (textarea) => {
        if (!textarea || !document.body.contains(textarea) || !textarea.value.includes('[url=')) return;

        const current = textarea.value;
        const simple = current.replace(BBCodePattern, (match, urlPart, imgPart, existingDesc) => {
            const fileName = imgPart.split('/').pop();

            storeMetadata(fileName, {
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
            const metadata = getMetadata(fileName);
            if (metadata) {
                const finalDesc = (desc === i18nDesc) ? '' : desc;
                return `[url=${metadata.url}][img=${metadata.img}]${finalDesc}[/img][/url]`;
            }
            return match;
        });
    };

    const applySimplify = (textarea) => {
        if (!isSubmitting && textarea && document.body.contains(textarea)) {
            simplify(textarea);
        }
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
                if (this[0] && document.body.contains(this[0])) {
                    simplify(this[0]);
                }
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
            }, 300);
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
            if (textarea.offsetParent !== null && document.body.contains(textarea)) {
                applySimplify(textarea);
            }
        });
    }, 2500);

})();