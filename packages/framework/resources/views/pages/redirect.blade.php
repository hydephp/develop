<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="refresh" content="0;url='{{ $destination }}'" />

        <title>Redirecting to {{ $destination }}</title>
    </head>
    <body>
        Redirecting to <a href="{{ $destination }}">{{ $destination }}</a>.
    </body>
</html>
