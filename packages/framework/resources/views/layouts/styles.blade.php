{{-- Prevent Alpine.js flashes --}}
<style>[x-cloak] {display: none!important}</style>

{{-- The compiled Tailwind/App styles --}}
@if(config('hyde.load_app_styles_from_cdn', false))
<link rel="stylesheet" href="{{ Asset::cdnLink('app.css') }}">
@elseif(Asset::hasMediaFile('app.css'))
<link rel="stylesheet" href="{{ Asset::mediaLink('app.css') }}">
@endif

@if(config('hyde.use_play_cdn', false))
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    <script>tailwind.config = { {!! Asset::injectTailwindConfig() !!} }</script>
@endif

{{-- Add any extra styles to include after the others --}}
@stack('styles')
