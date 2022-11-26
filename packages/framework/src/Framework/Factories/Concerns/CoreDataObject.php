<?php

declare(strict_types=1);

namespace Hyde\Framework\Factories\Concerns;

use Hyde\Markdown\Models\FrontMatter;
use Hyde\Markdown\Models\Markdown;
use Hyde\Support\Concerns\Serializable;
use Hyde\Support\Contracts\SerializableContract;

/**
 * Experimental class to contain the core data for a page being constructed.
 *
 * It should contain immutable data known at the very start of construction.
 * In addition to the front matter and markdown, the data should contain
 * everything needed to identify the unique page being constructed.
 *
 * The benefit of using this class over passing around the page object when constructing a page is
 * that the latter's state is unpredictable due to the nature of the construction process continuously
 * mutating the page object. This class on the other hand is immutable, making it highly predictable.
 */
final class CoreDataObject implements SerializableContract
{
    use Serializable;

    public function __construct(
        public readonly FrontMatter $matter,
        public readonly Markdown|false $markdown,
        public readonly string $pageClass,
        public readonly string $identifier,
        public readonly string $sourcePath,
        public readonly string $outputPath,
        public readonly string $routeKey,
    ) {
        //
    }

    public function toArray(): array
    {
        return [
            'pageClass' => $this->pageClass,
            'identifier' => $this->identifier,
            'sourcePath' => $this->sourcePath,
            'outputPath' => $this->outputPath,
            'routeKey' => $this->routeKey,
        ];
    }
}
