<p>
    @if(is_bool($sidebar->getFooter())))
        <a href="{{ Hyde::relativeLink('index.html') }}">Back to home page</a>
    @else
        {{ Hyde::markdown($sidebar->getFooter()) }}
    @endif
</p>