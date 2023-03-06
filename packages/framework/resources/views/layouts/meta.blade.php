{{-- Render the dynamic page meta tags --}}
{{ $page->metadata() }}

{{-- Render the global and config defined meta tags --}}
{{ Site::metadata() }}

{{-- Add any extra tags to include in the <head> section --}}
@stack('meta')