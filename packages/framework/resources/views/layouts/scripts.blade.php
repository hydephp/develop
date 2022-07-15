{{-- The core HydeFront scripts --}}
@unless(Asset::hasMediaFile('hyde.js'))
<script defer src="{{ Asset::cdnLink('hyde.js') }}"></script>
@else
<script defer src="{{ Hyde::relativeLink('media/hyde.js') }}"></script>
@endunless

{{-- The compiled Laravel Mix scripts --}}
@if(Asset::hasMediaFile('app.js'))
<script defer src="{{ Hyde::relativeLink('media/app.js') }}"></script>
@endif

{{-- Add any extra scripts to include before the closing <body> tag --}}
@stack('scripts')