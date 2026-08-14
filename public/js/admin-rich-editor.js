(() => {
    const define = () => {
        if (!window.Alpine) {
            return;
        }

        Alpine.data('ccfRichEditor', (config = {}) => ({
            blockId: config.blockId || '',
            _timer: null,

            init() {
                const initial = this.normalize(config.initial || '');
                this.$refs.editor.innerHTML = initial || '<p><br></p>';
            },

            normalize(html) {
                if (!html || !String(html).trim()) {
                    return '';
                }

                return this.clean(String(html));
            },

            clean(html) {
                const doc = new DOMParser().parseFromString(html, 'text/html');

                doc.querySelectorAll('script, style, iframe, object, embed, form, input, button, textarea, select').forEach((el) => el.remove());

                doc.querySelectorAll('*').forEach((el) => {
                    [...el.attributes].forEach((attr) => {
                        const name = attr.name.toLowerCase();
                        if (
                            name.startsWith('on')
                            || name === 'style'
                            || name === 'class'
                            || name === 'id'
                            || name.startsWith('data-')
                            || name === 'color'
                            || name === 'face'
                            || name === 'size'
                            || name === 'width'
                            || name === 'height'
                        ) {
                            el.removeAttribute(attr.name);
                        }
                    });

                    if (el.tagName === 'FONT') {
                        const span = doc.createElement('span');
                        span.innerHTML = el.innerHTML;
                        el.replaceWith(span);
                    }
                });

                let out = doc.body.innerHTML
                    .replace(/&nbsp;/g, ' ')
                    .replace(/\u00a0/g, ' ')
                    .trim();

                if (out === '' || out === '<br>' || out === '<p></p>' || out === '<p><br></p>') {
                    return '';
                }

                return out;
            },

            focusEditor() {
                this.$refs.editor.focus();
            },

            command(cmd, value = null) {
                this.focusEditor();
                document.execCommand(cmd, false, value);
                this.queueSync();
            },

            formatBlock(tag) {
                this.focusEditor();
                document.execCommand('formatBlock', false, tag);
                this.queueSync();
            },

            insertLink() {
                const url = window.prompt('أدخل الرابط (https://...)');
                if (!url) {
                    return;
                }
                this.command('createLink', url.trim());
            },

            onPaste(event) {
                event.preventDefault();
                const text = event.clipboardData?.getData('text/plain') || '';
                document.execCommand('insertText', false, text);
                this.queueSync();
            },

            queueSync() {
                clearTimeout(this._timer);
                this._timer = setTimeout(() => this.sync(), 280);
            },

            sync() {
                if (!this.blockId || !this.$wire) {
                    return;
                }
                const html = this.clean(this.$refs.editor.innerHTML);
                this.$wire.call('updateBlockContent', this.blockId, html);
            },
        }));
    };

    if (window.Alpine) {
        define();
    } else {
        document.addEventListener('alpine:init', define);
    }
})();
