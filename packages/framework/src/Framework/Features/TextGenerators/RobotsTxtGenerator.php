<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\TextGenerators;

use Hyde\Hyde;
use Hyde\Facades\Config;
use Hyde\Facades\Features;
use Hyde\Framework\Exceptions\InvalidConfigurationException;
use Hyde\Framework\Features\GeneratedFiles\GeneratedFileGenerator;
use Hyde\Framework\Features\GeneratedFiles\GeneratedFilePaths;

use function array_merge;
use function get_debug_type;
use function implode;
use function is_string;
use function sprintf;

/**
 * Generates the contents for the robots.txt file.
 *
 * All crawlers are allowed by default, and each value in `hyde.robots.disallow`
 * is written verbatim as a Disallow rule, so wildcard patterns and the empty
 * rule are supported. A link to the sitemap is included when the sitemap
 * feature is enabled.
 *
 * @see \Hyde\Framework\Features\GeneratedFiles\GeneratedFilePage
 */
class RobotsTxtGenerator implements GeneratedFileGenerator
{
    public function generate(): string
    {
        return implode("\n", $this->getLines())."\n";
    }

    public function generateFile(): string
    {
        return $this->generate();
    }

    /** @return array<string> */
    protected function getLines(): array
    {
        $lines = array_merge(['User-agent: *'], $this->getRuleLines());

        if (Features::hasSitemap()) {
            $lines = array_merge($lines, ['', 'Sitemap: '.Hyde::url(GeneratedFilePaths::SITEMAP)]);
        }

        return $lines;
    }

    /** @return array<string> */
    protected function getRuleLines(): array
    {
        $rules = Config::getArray('hyde.robots.disallow', []);

        if ($rules === []) {
            return ['Allow: /'];
        }

        $lines = [];

        foreach ($rules as $index => $rule) {
            if (! is_string($rule)) {
                throw new InvalidConfigurationException(sprintf(
                    'Invalid `hyde.robots.disallow` entry at index [%s]: each Disallow rule must be a string, %s given.',
                    $index, get_debug_type($rule)
                ), 'hyde', 'disallow');
            }

            $lines[] = "Disallow: $rule";
        }

        return $lines;
    }
}
