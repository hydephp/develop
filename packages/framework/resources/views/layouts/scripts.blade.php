{{-- The core HydeFront scripts --}}
@if(Hyde::scripts())
<script defer src="{{ Hyde::scripts() }}"></script>
@endif

{{-- The compiled Laravel Mix scripts --}}
@if(Asset::hasMediaFile('app.js'))
<script defer src="{{ Hyde::relativeLink('media/app.js', $currentPage) }}"></script>
@endif

{{-- Add any extra scripts to include before the closing <body> tag --}}
@stack('scripts')