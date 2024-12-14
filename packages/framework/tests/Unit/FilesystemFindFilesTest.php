<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Hyde;
use Hyde\Testing\UnitTestCase;
use Hyde\Testing\CreatesTemporaryFiles;
use Hyde\Foundation\Kernel\Filesystem;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

/**
 * @covers \Hyde\Foundation\Kernel\Filesystem::findFiles
 * @covers \Hyde\Facades\Filesystem::findFiles
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

    public function testFindFilesWithFilesHavingNoExtensions()
    {
        $this->files(['directory/file', 'directory/another_file']);
        $this->assertSameArray(['file', 'another_file'], 'directory');
    }

    public function testFindFilesWithSpecialCharactersInNames()
    {
        $this->files(['directory/file-with-dash.md', 'directory/another_file.txt', 'directory/special@char!.blade.php']);
        $this->assertSameArray(['file-with-dash.md', 'another_file.txt', 'special@char!.blade.php'], 'directory');
    }

    public function testFindFilesWithSpecialPrefixes()
    {
        $this->files(['directory/_file.md', 'directory/-another_file.txt', 'directory/~special_file.blade.php']);
        $this->assertSameArray(['_file.md', '-another_file.txt', '~special_file.blade.php'], 'directory');
    }

    public function testFindFilesWithHiddenFiles()
    {
        $this->files(['directory/.hidden_file', 'directory/.another_hidden.md', 'directory/visible_file.md']);
        $this->assertSameArray(['visible_file.md'], 'directory');
    }

    public function testFindFilesWithRecursiveAndHiddenFiles()
    {
        $this->files(['directory/.hidden_file', 'directory/nested/.another_hidden.md', 'directory/nested/visible_file.md']);
        $this->assertSameArray(['nested/visible_file.md'], 'directory', false, true);
    }

    public function testFindFilesWithEmptyExtensionFilter()
    {
        $this->files(['directory/file.md', 'directory/another_file.txt']);
        $this->assertSameArray([], 'directory', '');
    }

    public function testFindFilesWithCaseInsensitiveExtensions()
    {
        $this->files(['directory/file.MD', 'directory/another_file.md', 'directory/ignored.TXT']);
        $this->assertSameArray(['file.MD', 'another_file.md'], 'directory', 'md');
    }

    public function testFindFilesWithCaseInsensitiveFilenames()
    {
        $this->files(['directory/file.md', 'directory/anotherFile.md', 'directory/ANOTHER_FILE.md']);
        $this->assertSameArray(['file.md', 'anotherFile.md', 'ANOTHER_FILE.md'], 'directory');
    }

    public function testFindFilesWithCaseInsensitiveExtensionFilter()
    {
        $this->files(['directory/file.MD', 'directory/another_file.md', 'directory/ignored.TXT']);
        $this->assertSameArray(['file.MD', 'another_file.md'], 'directory', 'MD');
    }

    public function testFindFilesHandlesLargeNumberOfFiles()
    {
        $this->files(array_map(fn ($i) => "directory/file$i.md", range(1, 100)));
        $this->assertSameArray(array_map(fn ($i) => "file$i.md", range(1, 100)), 'directory');
    }

    public function testFindFilesWithEmptyDirectory()
    {
        $this->directory('directory');
        $this->assertSameArray([], 'directory');
    }

    public function testFindFilesWithNonExistentDirectory()
    {
        $this->expectException(DirectoryNotFoundException::class);
        $this->assertSameArray([], 'nonexistent-directory');
    }

    public function testFindFilesFromFilesystemFacade()
    {
        $this->files(['directory/apple.md', 'directory/banana.md', 'directory/cherry.md']);
        $files = \Hyde\Facades\Filesystem::findFiles('directory');

        $this->assertSame(['apple.md', 'banana.md', 'cherry.md'], $files->sort()->values()->all());
    }

    public function testFindFilesFromFilesystemFacadeWithArguments()
    {
        $this->files(['directory/apple.md', 'directory/banana.txt', 'directory/cherry.blade.php', 'directory/nested/dates.md']);

        $files = \Hyde\Facades\Filesystem::findFiles('directory', 'md');
        $this->assertSame(['apple.md'], $files->all());

        $files = \Hyde\Facades\Filesystem::findFiles('directory', false, true);
        $this->assertSame(['apple.md', 'banana.txt', 'cherry.blade.php', 'nested/dates.md'], $files->sort()->values()->all());
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
