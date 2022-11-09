<?php

declare(strict_types=1);

namespace Hyde\Support\Concerns;

use Illuminate\Support\Str;

/**
 * Map file extensions to mime types.
 *
 * @see \Hyde\Support\Concerns\File
 */
enum MimeType: string
{
    case css  = 'text/css';
    case gif  = 'image/gif';
    case html = 'text/html';
    case ico  = 'image/x-icon';
    case jpg  = 'image/jpeg';
    case js   = 'application/javascript';
    case json = 'application/json';
    case md   = 'text/markdown';
    case png  = 'image/png';
    case svg  = 'image/svg+xml';
    case txt  = 'text/plain';
    case xml  = 'application/xml';

    public static function match(string $path, ?string $default = 'text/plain'): ?string
    {
        return (collect(self::cases())->where('name', Str::after($path, '.'))->first()?->value) ?? $default;
    }
}
