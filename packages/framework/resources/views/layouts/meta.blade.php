{{-- Render the dynamic page meta tags --}}
{!! $page->renderPageMetadata() !!}

{{-- Render the global and config defined meta tags --}}
{!! \Hyde\Framework\Models\Site::metadata()->render() !!}

{{-- Add any extra tags to include in the <head> section --}}
@stack('meta')
