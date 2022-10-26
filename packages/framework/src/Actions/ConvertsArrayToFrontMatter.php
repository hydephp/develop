<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Symfony\Component\Yaml\Yaml;

/**
 * Convert an array into YAML Front Matter.
 *
 * @see \Hyde\Framework\Testing\Feature\ConvertsArrayToFrontMatterTest
 */
class ConvertsArrayToFrontMatter
{
    /**
     * Execute the action.
     *
     * @param  array  $array
     * @return string $yaml front matter
     */
    public function execute(array $array): string
    {
        if (empty($array)) {
            return '';
        }

        return "---\n".Yaml::dump($array)."---\n";
    }
}
