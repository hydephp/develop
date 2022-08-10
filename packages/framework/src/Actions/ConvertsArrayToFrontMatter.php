<?php

namespace Hyde\Framework\Actions;

/**
 * Convert an array into YAML Front Matter.
 * Currently, does not support nested arrays.
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

        // Initialize the array
        $yaml = [];

        // Set the first line to the opening starting block
        $yaml[] = '---';

        // For each line, add the key-value pair as YAML
        foreach ($array as $key => $value) {
            if ($this->valueIsNotEmpty($value)) {
                $yaml[] = sprintf("%s: %s", $key, json_encode($value));
            }
        }

        // Set the closing block
        $yaml[] = '---';

        // Add an extra line
        $yaml[] = '';

        // Return the array imploded into a string with newline characters
        return implode("\n", $yaml);
    }

    protected function valueIsNotEmpty(mixed $value): bool
    {
        return trim($value) !== '' && $value !== null;
    }
}
