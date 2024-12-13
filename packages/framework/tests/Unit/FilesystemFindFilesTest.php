<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Hyde;
use Hyde\Testing\UnitTestCase;
use Hyde\Testing\CreatesTemporaryFiles;
use Hyde\Foundation\Kernel\Filesystem;
use Illuminate\Support\Collection;

/**
 * @covers \Hyde\Foundation\Kernel\Filesystem::findFiles
 *
 * @see \Hyde\Framework\Testing\Feature\FilesystemFacadeTest
 */
class FilesystemFindFilesTest extends UnitTestCase
{
    use CreatesTemporaryFiles;

    protected static bool $needsKernel = true;

    public function testFindFiles()
    {
        $this->files(['directory/apple.md', 'directory/banana.md', 'directory/cherry.md']);
        $this->assertSameArray(['apple.md', 'banana.md', 'cherry.md'], 'directory');
    }

    public function testFindFilesWithMixedExtensions()
    {
        $this->files(['directory/apple.md', 'directory/banana.txt', 'directory/cherry.blade.php']);
        $this->assertSameArray(['apple.md', 'banana.txt', 'cherry.blade.php'], 'directory');
    }

    public function testFindFilesWithExtension()
    {
        $this->files(['directory/apple.md', 'directory/banana.md', 'directory/cherry.md']);
        $this->assertSameArray(['apple.md', 'banana.md', 'cherry.md'], 'directory', 'md');
    }

    public function testFindFilesWithMixedExtensionsReturnsOnlySpecifiedExtension()
    {
        $this->files(['directory/apple.md', 'directory/banana.txt', 'directory/cherry.blade.php']);
        $this->assertSameArray(['apple.md'], 'directory', 'md');
    }

    public function testFindFilesWithRecursive()
    {
        $this->files(['directory/apple.md', 'directory/banana.md', 'directory/cherry.md', 'directory/nested/dates.md']);
        $this->assertSameArray(['apple.md', 'banana.md', 'cherry.md', 'nested/dates.md'], 'directory', false, true);
    }

    public function testFindFilesWithDeeplyRecursiveFiles()
    {
        $this->files(['directory/apple.md', 'directory/nested/banana.md', 'directory/nested/deeply/cherry.md']);
        $this->assertSameArray(['apple.md', 'nested/banana.md', 'nested/deeply/cherry.md'], 'directory', false, true);
    }

    public function testFindFilesWithVeryDeeplyRecursiveFiles()
    {
        $this->files(['directory/apple.md', 'directory/nested/banana.md', 'directory/nested/deeply/cherry.md', 'directory/nested/very/very/deeply/dates.md', 'directory/nested/very/very/excessively/deeply/elderberries.md']);
        $this->assertSameArray(['apple.md', 'nested/banana.md', 'nested/deeply/cherry.md', 'nested/very/very/deeply/dates.md', 'nested/very/very/excessively/deeply/elderberries.md'], 'directory', false, true);
    }

    public function testFindFilesIgnoresNestedFilesIfNotRecursive()
    {
        $this->files(['directory/apple.md', 'directory/nested/banana.md', 'directory/nested/deeply/cherry.md']);
        $this->assertSameArray(['apple.md'], 'directory');
    }

    public function testFindFilesReturnsCorrectFilesWhenUsingNestedSubdirectoriesOfDifferentExtensions()
    {
        $this->files(['directory/apple.md', 'directory/nested/banana.md', 'directory/nested/deeply/cherry.blade.php']);
        $this->assertSameArray(['apple.md', 'nested/banana.md'], 'directory', 'md', true);
    }


    protected function assertSameArray(array $expected, string $directory, string|false $matchExtension = false, bool $recursive = false): void
    {
        $files = (new Filesystem(Hyde::getInstance()))->findFiles($directory, $matchExtension, $recursive);

        // Compare sorted arrays because some filesystems may return files in a different order.
        $this->assertSame(collect($expected)->sort()->values()->all(), $files->sort()->values()->all());
    }

    protected function tearDown(): void
    {
        $this->cleanUpFilesystem();
    }
}
