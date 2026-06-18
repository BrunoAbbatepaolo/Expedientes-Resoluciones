// Quill initialization for Livewire
(function() {
    'useStrict';

    window.placeholderHtml = '';
    window.actualizarPlantilla = function(html) {
        window.placeholderHtml = html;
    };

    window.actualizarQuill = function(html) {
        var q = window.__quillEditor;
        if (!q) {
            return;
        }
        if (html && html !== window.__lastQuillHtml) {
            q.clipboard.once('text-change', function() {});
            var root = q.root;
            root.innerHTML = html;
            q.setContents(q.clipboard.convert(html), 'silent');
            window.__lastQuillHtml = html;
        }
    };

    window.quillInit = function() {
        return {
            quill: null,
            placeholderHtml: '',
            init: function() {
                var self = this;
                var container = this.$refs['quill-' + (window.__quillCount = (window.__quillCount || 0) + 1)];
                if (!container) {
                    console.log('Quill container not found:', 'quill-' + window.__quillCount);
                    return;
                }

                console.log('Quill init for container:', container);

                var input = document.getElementById('plantilla-input');
                if (input && input.value) {
                    this.placeholderHtml = input.value;
                }

                this.$nextTick(function() {
                    if (!self.quill) {
                        var editorDiv = container.querySelector('[data-quill-editor]') || container;
                        editorDiv.id = editorDiv.id || 'ql-editor-' + Math.random().toString(36).slice(2);

                        if (typeof Quill === 'undefined') {
                            console.error('Quill library not loaded!');
                            return;
                        }

                        self.quill = new Quill(editorDiv, {
                            theme: 'snow',
                            modules: {
                                toolbar: {
                                    container: [
                                        ['bold', 'italic', 'underline', 'strike'],
                                        [{ 'align': ['justify', 'center', 'right', ''] }],
                                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                        [{ 'header': [2, 3, false] }],
                                        [{ 'indent': '-1'}, { 'indent': '+1' }],
                                        ['blockquote'],
                                        ['clean']
                                    ],
                                    handlers: {
                                        'align': function(value) {
                                            if (value === '') {
                                                self.quill.format('align', false);
                                            } else {
                                                self.quill.format('align', value);
                                            }
                                        }
                                    }
                                }
                            }
                        });

                        console.log('Quill initialized:', self.quill);
                        window.__quillEditor = self.quill;

                        if (self.placeholderHtml) {
                            self.actualizar(self.placeholderHtml);
                        }

                        self.quill.on('text-change', function() {
                            var html = self.quill.root.innerHTML;
                            var input = document.getElementById('plantilla-input');
                            if (input) {
                                input.value = html;
                            }
                            if (input) {
                                input.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                            if (input) {
                                input.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        });
                    }
                });
            },
            actualizar: function(html) {
                if (!this.quill || !html) {
                    return;
                }
                this.quill.clipboard.once('text-change', function() {});
                this.quill.root.innerHTML = html;
                this.quill.setContents(this.quill.clipboard.convert(html), 'silent');
                this.quill.once('text-change', function() {});
            }
        };
    };

    console.log('Quill init script loaded');
})();