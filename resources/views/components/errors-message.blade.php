@props(['key'])

@if(session()->has($key))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @php
            $errMessages = session($key);
        @endphp
        @if(is_array($errMessages))
            @foreach($errMessages as $errMsg)
                if (typeof showToast === 'function') {
                    showToast('error', {!! json_encode($errMsg) !!}, 'Import Error');
                }
            @endforeach
        @else
            if (typeof showToast === 'function') {
                showToast('error', {!! json_encode($errMessages) !!}, 'Import Error');
            }
        @endif
    });
</script>
@endif