<?php

declare(strict_types=1);

namespace Hyde\Support\Concerns;

enum MimeType
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
}
