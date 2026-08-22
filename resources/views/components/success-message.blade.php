@props(['key'])

@if(session()->has($key) && !in_array($key, ['create_message', 'edit_message', 'import_message', 'message', 'message1', 'message2', 'message3', 'success', 'customMessage', 'toast_notify']))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof showToast === 'function') {
            showToast('success', {!! json_encode(session()->get($key)) !!});
        }
    });
</script>
@endif