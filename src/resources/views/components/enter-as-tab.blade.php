@props(['formId'])
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById(@json($formId));
    if (!form) return;

    form.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter' || event.ctrlKey || event.altKey || event.metaKey) return;
        if (event.target.tagName === 'TEXTAREA' && event.shiftKey) return;

        const fields = Array.from(form.querySelectorAll(
            'input:not([type="hidden"]):not([type="submit"]), select, textarea'
        )).filter(function (field) {
            return !field.disabled && !field.readOnly && field.offsetParent !== null;
        });

        const position = fields.indexOf(event.target);
        if (position < 0) return;

        if (position < fields.length - 1) {
            event.preventDefault();
            fields[position + 1].focus();
            if (typeof fields[position + 1].select === 'function' &&
                fields[position + 1].tagName === 'INPUT' &&
                !['date', 'checkbox', 'radio'].includes(fields[position + 1].type)) {
                fields[position + 1].select();
            }
        }
        if (position === fields.length - 1) {
            event.preventDefault();
            if (form.reportValidity()) form.requestSubmit();
        }
    });
});
</script>
@endpush
