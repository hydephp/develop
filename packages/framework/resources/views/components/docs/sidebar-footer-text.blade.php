<p>
    @if(config('docs.sidebar.footer_text', true))
        {{ Hyde::markdown(config('docs.sidebar.footer_text')) }}
    @else
        <a href="{{ Hyde::relativeLink('index.html') }}">Back to home page</a>
    @endif
</p>