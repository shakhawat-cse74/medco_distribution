<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600&display=swap" rel="stylesheet">

<style>
    :root {
        --theme-color: {{ $theme_color ?? '#7c5cc4' }};
    }
    
    @if(($theme_font ?? 'inter') == 'inter')
        body { font-family: 'Inter', sans-serif !important; }
    @elseif(($theme_font ?? '') == 'nunito')
        body { font-family: 'Nunito', sans-serif !important; }
    @elseif(($theme_font ?? '') == 'fira')
        body { font-family: 'Fira Code', monospace !important; }
    @elseif(($theme_font ?? '') == 'roboto')
        body { font-family: 'Roboto', sans-serif !important; }
    @elseif(($theme_font ?? '') == 'poppins')
        body { font-family: 'Poppins', sans-serif !important; }
    @elseif(($theme_font ?? '') == 'lato')
        body { font-family: 'Lato', sans-serif !important; }
    @elseif(($theme_font ?? '') == 'outfit')
        body { font-family: 'Outfit', sans-serif !important; }
    @endif
</style>
