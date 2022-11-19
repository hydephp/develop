<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Console\Commands\Interfaces\CommandHandleInterface;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Framework\Features\Publications\PublicationHelper;
use Hyde\Framework\Services\BuildService;
use Hyde\Pages\MarkdownPage;
use LaravelZero\Framework\Commands\Command;
use Rgasch\Collection\Collection;
