<?php

declare(strict_types=1);

namespace Hyde\Support\Concerns;

use Illuminate\Support\Str;

/**
 * Map file extensions to mime types.
 *
 * @internal This class is currently experimental and should not be relied upon outside of Hyde as it may change at any time.
 *
 * @see \Hyde\Support\Concerns\File
 */
enum MimeType: string
{
    case txt = 'text/plain';
    case html = 'text/html';
    case css = 'text/css';
    case md = 'text/markdown';

    case png = 'image/png';
    case gif = 'image/gif';
    case jpg = 'image/jpeg';
    case svg = 'image/svg+xml';
    case ico = 'image/x-icon';

    case js = 'application/javascript';
    case json = 'application/json';
    case xml = 'application/xml';

    public function value(): string
    {
        return $this->value;
    }

    public static function get(string $extension): ?self
    {
        return collect(self::cases())->where('name', $extension)->first();
    }

    public static function has(string $extension): bool
    {
        return self::get($extension) !== null;
    }

    public static function match(string $pathOrExtension, ?string $default = 'text/plain'): ?string
    {
        return self::get(Str::after($pathOrExtension, '.'))?->value() ?? $default;
    }
}
