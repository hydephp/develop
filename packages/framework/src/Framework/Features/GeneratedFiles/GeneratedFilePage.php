<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\GeneratedFiles;

use Hyde\Pages\InMemoryPage;

use function app;

/**
 * @internal Page adapter for a generated file registered by the framework.
 */
final class GeneratedFilePage extends InMemoryPage
{
    /** @var class-string<\Hyde\Framework\Features\GeneratedFiles\GeneratedFileGenerator> */
    protected readonly string $generator;

    /** @param class-string<\Hyde\Framework\Features\GeneratedFiles\GeneratedFileGenerator> $generator */
    public function __construct(string $outputPath, string $generator)
    {
        $this->generator = $generator;

        parent::__construct($outputPath, [
            'navigation' => ['hidden' => true],
        ], exactOutputPath: true);
    }

    public function compile(): string
    {
        return app($this->generator)->generateFile();
    }
}
