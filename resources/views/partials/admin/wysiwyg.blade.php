@php
    $editorId = 'be-wysiwyg-' . preg_replace('/[^a-zA-Z0-9_-]/', '-', $model);
    $uploadUrl = $uploadUrl ?? null;
@endphp

<div wire:ignore>
    <textarea
        id="{{ $editorId }}"
        class="admin-control be-wysiwyg-target"
        data-livewire-property="{{ $model }}"
        data-direction="{{ $direction ?? 'rtl' }}"
        data-language="{{ $language ?? 'ar' }}"
        data-height="{{ $height ?? 320 }}"
        @if ($uploadUrl) data-upload-url="{{ $uploadUrl }}" @endif
    >{!! $value ?? '' !!}</textarea>
</div>

@once
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js"></script>
        <script>
            window.domainWysiwyg = window.domainWysiwyg || {
                csrfToken() {
                    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                },

                boot() {
                    if (typeof tinymce === 'undefined') {
                        setTimeout(() => this.boot(), 80);
                        return;
                    }
                    this.initAll(document);
                },

                initAll(root) {
                    root.querySelectorAll('textarea.be-wysiwyg-target:not([data-tinymce-ready])').forEach((el) => {
                        this.initElement(el);
                    });
                },

                initElement(el) {
                    if (!el.id || el.dataset.tinymceReady || typeof tinymce === 'undefined') {
                        return;
                    }

                    const host = el.closest('[wire\\:id]');
                    if (!host) {
                        return;
                    }

                    const component = Livewire.find(host.getAttribute('wire:id'));
                    if (!component) {
                        return;
                    }

                    const property = el.dataset.livewireProperty;
                    if (!property) {
                        return;
                    }

                    if (tinymce.get(el.id)) {
                        tinymce.get(el.id).remove();
                    }

                    el.dataset.tinymceReady = '1';

                    const uploadUrl = el.dataset.uploadUrl || '';
                    const plugins = uploadUrl
                        ? 'lists link table code directionality image media'
                        : 'lists link table code directionality';
                    const toolbar = uploadUrl
                        ? 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table | ltr rtl | removeformat | code'
                        : 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | link table | ltr rtl | code';

                    const config = {
                        selector: '#' + el.id,
                        height: parseInt(el.dataset.height || '320', 10),
                        directionality: el.dataset.direction || 'rtl',
                        language: el.dataset.language || 'ar',
                        license_key: 'gpl',
                        menubar: false,
                        promotion: false,
                        branding: false,
                        plugins,
                        toolbar,
                        relative_urls: false,
                        remove_script_host: false,
                        convert_urls: true,
                        extended_valid_elements: 'iframe[src|width|height|frameborder|allow|allowfullscreen|class|style]',
                        setup(editor) {
                            editor.on('change input blur Undo Redo', () => {
                                component.set(property, editor.getContent());
                            });
                        },
                    };

                    if (uploadUrl) {
                        config.automatic_uploads = true;
                        config.file_picker_types = 'image media';
                        config.images_upload_handler = (blobInfo, progress) => new Promise((resolve, reject) => {
                            const formData = new FormData();
                            formData.append('file', blobInfo.blob(), blobInfo.filename());

                            const xhr = new XMLHttpRequest();
                            xhr.open('POST', uploadUrl);
                            xhr.setRequestHeader('X-CSRF-TOKEN', window.domainWysiwyg.csrfToken());
                            xhr.setRequestHeader('Accept', 'application/json');

                            xhr.upload.onprogress = (event) => {
                                if (event.lengthComputable) {
                                    progress((event.loaded / event.total) * 100);
                                }
                            };

                            xhr.onload = () => {
                                if (xhr.status < 200 || xhr.status >= 300) {
                                    reject('فشل رفع الصورة (' + xhr.status + ')');
                                    return;
                                }

                                try {
                                    const json = JSON.parse(xhr.responseText);
                                    if (json.location) {
                                        resolve(json.location);
                                    } else {
                                        reject('استجابة غير صالحة من الخادم');
                                    }
                                } catch (error) {
                                    reject('استجابة غير صالحة من الخادم');
                                }
                            };

                            xhr.onerror = () => reject('تعذّر الاتصال بالخادم');
                            xhr.send(formData);
                        });
                    }

                    tinymce.init(config);
                },
            };

            function bootdomainWysiwyg() {
                window.domainWysiwyg.boot();
            }

            function registerWysiwygMorphHook() {
                Livewire.hook('morph.updated', ({ el }) => {
                    window.domainWysiwyg.initAll(el);
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bootdomainWysiwyg);
            } else {
                bootdomainWysiwyg();
            }

            if (window.Livewire) {
                registerWysiwygMorphHook();
            }
            document.addEventListener('livewire:init', registerWysiwygMorphHook);
        </script>
    @endpush
@endonce
