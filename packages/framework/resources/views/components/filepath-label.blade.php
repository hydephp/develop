@props(['path', 'highlightedByTorchlight' => false])
<small @class([
    'relative float-right opacity-50 hover:opacity-100 transition-opacity duration-250 not-prose hidden md:block',
    '-top-1 right-1' => $highlightedByTorchlight,
    'top-0 right-0' => ! $highlightedByTorchlight,
])><span class="sr-only">Filepath: </span>{{ $path }}</small>