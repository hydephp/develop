<?php

declare(strict_types=1);

namespace Hyde\Foundation\Concerns;

use Hyde\Foundation\HydeKernel;

use function ltrim;
use function rtrim;

/**
 * @internal Single-use trait for the HydeKernel class.
 *
 * @see \Hyde\Foundation\HydeKernel
 */
trait ManagesHydeKernel
{
    public static function getInstance(): HydeKernel
    {
        return static::$instance;
    }

    public static function setInstance(HydeKernel $instance): void
    {
        static::$instance = $instance;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function setBasePath(string $basePath): void
    {
        $this->basePath = rtrim($basePath, '/\\');
    }

    public function getSourceRoot(): string
    {
        return $this->sourceRoot;
    }

    public function setSourceRoot(string $sourceRoot): void
    {
        $this->sourceRoot = $this->normalizeSourcePath($sourceRoot);
    }

    public function getOutputDirectory(): string
    {
        return $this->outputDirectory;
    }

    public function setOutputDirectory(string $outputDirectory): void
    {
        $this->outputDirectory = $this->normalizeSourcePath($outputDirectory);
    }

    public function getMediaDirectory(): string
    {
        return $this->mediaDirectory;
    }

    public function setMediaDirectory(string $mediaDirectory): void
    {
        $this->mediaDirectory = $this->normalizeSourcePath($mediaDirectory);
    }

    public function getMediaOutputDirectory(): string
    {
        return ltrim($this->getMediaDirectory(), '_');
    }

    protected function normalizeSourcePath(string $outputDirectory): string
    {
        return $this->pathToRelative(rtrim($outputDirectory, '/\\'));
    }
}
