{{-- The core HydeFront stylesheet --}}
@if(Asset::hasMediaFile('hyde.css'))
<link rel="stylesheet" href="{{ Hyde::relativeLink('media/hyde.css') }}">
@else
<link rel="stylesheet" href="{{ Asset::cdnLink('hyde.css') }}">
@endif

{{-- The compiled Tailwind/App styles --}}
@if(config('hyde.load_app_styles_from_cdn', false))
<link rel="stylesheet" href="{{ Asset::cdnLink('app.css') }}">
@else
    @if(Asset::hasMediaFile('app.css'))
    <link rel="stylesheet" href="{{ Hyde::relativeLink('media/app.css') }}">
    @endif
@endif

{{-- Add any extra styles to include after the others --}}
@stack('styles')