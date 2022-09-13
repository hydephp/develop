{{-- Render the dynamic page meta tags --}}
{!! $page->renderPageMetadata() !!}

{{-- Render the global and config defined meta tags --}}
{!! Site::metadata()->render() !!}

{{-- Add any extra tags to include in the <head> section --}}
@stack('meta')
