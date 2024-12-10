<?php

declare(strict_types=1);

namespace Hyde\Console\Helpers;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use function Laravel\Prompts\multiselect;

/**
 * @internal This class offloads logic from the PublishViewsCommand class and should not be used elsewhere.
 */
class InteractivePublishCommandHelper
{
    public function promptForFiles(Collection $files, string $baseDir): array
    {
        $choices = $files->mapWithKeys(/** @return array<string, string> */ function (string $source) use ($baseDir): array {
            return [$source => Str::after($source, $baseDir.'/')];
        });

        return multiselect('Select the files you want to publish (CTRL+A to toggle all)', $choices, [], 10, 'required', hint: 'Navigate with arrow keys, space to select, enter to confirm.');
    }
}
