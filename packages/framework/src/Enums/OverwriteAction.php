<?php

declare(strict_types=1);

namespace Hyde\Enums;

enum OverwriteAction: string
{
    case Copy = 'copy';

    case Skip = 'skip';

    case Blocked = 'blocked';
}
