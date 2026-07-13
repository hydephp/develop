<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\TextGenerators;

use Hyde\Hyde;
use Hyde\Facades\Config;
use Hyde\Facades\Features;
use Hyde\Framework\Features\XmlGenerators\SitemapPage;

use function array_map;
use function array_merge;
use function implode;

/**
 * Generates the contents for the robots.txt file.
 *
 * All crawlers are allowed by default, and paths configured in `hyde.robots.disallow`
 * are written verbatim as Disallow rules. A link to the sitemap is included when
 * the sitemap feature is enabled.
 *
 * @see \Hyde\Framework\Features\TextGenerators\RobotsTxtPage
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
            $lines = array_merge($lines, ['', 'Sitemap: '.Hyde::url(SitemapPage::routeKey())]);
        }

        return $lines;
    }

    /** @return array<string> */
    protected function getRuleLines(): array
    {
        $disallow = Config::getArray('hyde.robots.disallow', []);

        if ($disallow === []) {
            return ['Allow: /'];
        }

        return array_map(fn (string $path): string => "Disallow: $path", $disallow);
    }
}
