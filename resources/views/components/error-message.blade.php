@props(['key'])

@if(session()->has($key) && !in_array($key, ['not_permitted', 'error', 'message']))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof showToast === 'function') {
            showToast('error', {!! json_encode(session()->get($key)) !!});
        }
    });
</script>
@endif