<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\TextGenerators;

use Hyde\Hyde;
use Hyde\Facades\Config;
use Hyde\Facades\Features;

use function array_merge;
use function implode;

/**
 * Generates the contents for the robots.txt file.
 *
 * All crawlers are allowed by default, and each value in `hyde.robots.disallow`
 * is written verbatim as a Disallow rule, so wildcard patterns and the empty
 * rule are supported. A link to the sitemap is included when the sitemap
 * feature is enabled.
 */
class RobotsTxtGenerator
{
    public function generate(): string
    {
        return implode("\n", $this->getLines())."\n";
    }

    /** @return array<string> */
    protected function getLines(): array
    {
        $lines = array_merge(['User-agent: *'], $this->getRuleLines());

        if (Features::hasSitemap()) {
            $lines = array_merge($lines, ['', 'Sitemap: '.Hyde::url('sitemap.xml')]);
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

        foreach ($rules as $rule) {
            $lines[] = 'Disallow: '.(string) $rule;
        }

        return $lines;
    }
}
