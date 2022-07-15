{{-- The core HydeFront stylesheet --}}
@unless(Asset::hasMediaFile('hyde.css'))
<link rel="stylesheet" href="{{ Asset::cdnLink('hyde.css') }}">
@else
<link rel="stylesheet" href="{{ Hyde::relativeLink('media/hyde.css') }}">
@endunless

{{-- The compiled Tailwind/App styles --}}
@if(Asset::hasMediaFile('app.css'))
<link rel="stylesheet" href="{{ Hyde::relativeLink('media/app.css') }}">
@endif

{{-- Add any extra styles to include after the others --}}
@stack('styles')