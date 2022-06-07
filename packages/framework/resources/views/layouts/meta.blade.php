{{-- Render the config defined and dynamic page meta tags --}}
{!! $page->renderPageMetadata() !!}

{{-- Add any extra tags to include in the <head> section --}}
@stack('meta')
