<?php

declare(strict_types=1);

namespace Hyde\Foundation\Kernel;

use Hyde\Facades\Filesystem;
use Hyde\Foundation\Concerns\BaseFoundationCollection;
use Hyde\Framework\Exceptions\FileNotFoundException;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Support\Filesystem\SourceFile;
use RuntimeException;

use function basename;
use function method_exists;
use function property_exists;
use function str_starts_with;

/**
 * The FileCollection contains all the discovered source files.
 *
 * @template T of \Hyde\Support\Filesystem\SourceFile
 *
 * @extends \Hyde\Foundation\Concerns\BaseFoundationCollection<string, T>
 *
 * @property array<string, SourceFile> $items The files in the collection.
 *
 * @method SourceFile|null get(string $key, SourceFile $default = null)
 *
 * This class is stored as a singleton in the HydeKernel.
 * You would commonly access it via the facade or Hyde helper:
 *
 * @see \Hyde\Foundation\Facades\Files
 * @see \Hyde\Hyde::files()
 */
final class FileCollection extends BaseFoundationCollection
{
    public function addFile(SourceFile $file): void
    {
        $this->put($file->getPath(), $file);
    }

    protected function runDiscovery(): void
    {
        /** @var class-string<\Hyde\Pages\Concerns\HydePage> $pageClass */
        foreach ($this->kernel->getRegisteredPageClasses() as $pageClass) {
            self::guardAgainstLegacyFileExtensionApi($pageClass);

            if ($pageClass::isDiscoverable()) {
                $this->discoverFilesFor($pageClass);
            }
        }
    }

    /**
     * Fail fast for page classes still using the file extension API renamed in HydePHP v3.
     * Without this guard such classes are simply not discoverable, so a build would
     * succeed while silently omitting the entire page type. Temporary upgrade
     * aid that can be removed in a future release.
     *
     * @param  class-string<HydePage>  $pageClass
     */
    protected static function guardAgainstLegacyFileExtensionApi(string $pageClass): void
    {
        if (property_exists($pageClass, 'fileExtension') || method_exists($pageClass, 'fileExtension') || method_exists($pageClass, 'setFileExtension')) {
            throw new RuntimeException("The page class [$pageClass] uses the \$fileExtension API which was renamed in HydePHP v3. Rename \$fileExtension, fileExtension(), and setFileExtension() to \$sourceExtension, sourceExtension(), and setSourceExtension().");
        }
    }

    protected function runExtensionHandlers(): void
    {
        /** @var class-string<\Hyde\Foundation\Concerns\HydeExtension> $extension */
        foreach ($this->kernel->getExtensions() as $extension) {
            $extension->discoverFiles($this);
        }
    }

    /** @param class-string<HydePage> $pageClass */
    protected function discoverFilesFor(string $pageClass): void
    {
        // Scan the source directory, and directories therein, for files that match the model's file extension.
        foreach (Filesystem::findFiles($pageClass::sourceDirectory(), $pageClass::sourceExtension(), true) as $path) {
            if (! str_starts_with(basename((string) $path), '_')) {
                $this->addFile(SourceFile::make($path, $pageClass));
            }
        }
    }

    public function getFile(string $path): SourceFile
    {
        return $this->get($path) ?? throw new FileNotFoundException($path);
    }

    /** @param  class-string<\Hyde\Pages\Concerns\HydePage>|null  $pageClass */
    public function getFiles(?string $pageClass = null): FileCollection
    {
        return $pageClass ? $this->filter(function (SourceFile $file) use ($pageClass): bool {
            return $file->pageClass === $pageClass;
        }) : $this;
    }
}
