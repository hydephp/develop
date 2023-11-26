<p>
    @if(is_bool(config('docs.sidebar.footer', true)))
        <a href="{{ Hyde::relativeLink('index.html') }}">Back to home page</a>
    @else
        {{ Hyde::markdown(config('docs.sidebar.footer')) }}
    @endif
</p>