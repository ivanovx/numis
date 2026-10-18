import Quill from 'quill';
import 'quill/dist/quill.snow.css';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-rich-text-editor]').forEach((editorElement) => {
        const input = document.getElementById(editorElement.dataset.richTextEditor);

        if (!(input instanceof HTMLInputElement || input instanceof HTMLTextAreaElement)) {
            return;
        }

        const editor = new Quill(editorElement, {
            theme: 'snow',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ header: [2, 3, false] }],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    ['blockquote'],
                    ['clean'],
                ],
            },
        });

        if (input.value) {
            editor.clipboard.dangerouslyPasteHTML(input.value);
        }

        editor.on('text-change', () => {
            input.value = editor.root.innerHTML;
        });

        editorElement.closest('form')?.addEventListener('submit', () => {
            input.value = editor.root.innerHTML;
        });
    });
});
