<small @class([
    'relative float-right opacity-50 hover:opacity-100 transition-opacity duration-250 not-prose hidden md:block',
    '-top-1 right-1' => $torchlight,
    'top-0 right-0' => ! $torchlight,
])><span class="sr-only">Filepath: </span>{{ $path }}</small>