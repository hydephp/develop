<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="refresh" content="0;url='{{ $destination }}'" />
        <style>@media (prefers-color-scheme:dark){html{background-color:#111827;color:white;}a{color:#9e9eff}}</style>

        <title>Redirecting to {{ $destination }}</title>
    </head>
    <body>
        @if($showText)
            Redirecting to <a href="{{ $destination }}">{{ $destination }}</a>.
        @endif
    </body>
</html>
