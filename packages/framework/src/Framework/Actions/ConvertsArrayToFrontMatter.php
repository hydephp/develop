<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Symfony\Component\Yaml\Yaml;

/**
 * Convert an array into YAML Front Matter.
 *
 */
class ConvertsArrayToFrontMatter
{
    /**
     * Execute the action.
     *
     * @return string $yaml front matter
     */
    public function execute(array $array, int $flags = 0): string
    {
        if (empty($array)) {
            return '';
        }

        return "---\n".Yaml::dump($array, flags: $flags)."---\n";
    }
}
