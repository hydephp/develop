{{-- Render the dynamic page meta tags --}}
{{ $page->metadata() }}

{{-- Render the global and config defined meta tags --}}
{{ Site::metadata() }}

{{-- If the user has defined any custom head tags, render them here --}}
{!! config('hyde.hooks.head') !!}

{{-- Add any extra tags to include in the <head> section --}}
@stack('meta')