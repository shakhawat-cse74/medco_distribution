@props(['key'])

@if(session()->has($key) && !in_array($key, ['warning']))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof showToast === 'function') {
            showToast('warning', {!! json_encode(session()->get($key)) !!});
        }
    });
</script>
@endif