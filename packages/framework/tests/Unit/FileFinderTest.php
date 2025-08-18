<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Hyde;
use Hyde\Testing\UnitTestCase;
use Hyde\Testing\CreatesTemporaryFiles;
use Hyde\Foundation\Kernel\Filesystem;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Framework\Actions\Internal\FileFinder::class)]
class FileFinderTest extends UnitTestCase
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

    public function testFindFilesWithLeadingDotInFileExtension()
    {
        $this->files(['directory/file.md', 'directory/another_file.md', 'directory/ignored.txt']);
        $this->assertSameArray(['file.md', 'another_file.md'], 'directory', 'md');
        $this->assertSameArray(['file.md', 'another_file.md'], 'directory', '.md');
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
        $this->assertSameArray([], 'nonexistent-directory');
    }

    public function testFindFilesWithMultipleExtensions()
    {
        $this->files(['directory/file1.md', 'directory/file2.txt', 'directory/file3.blade.php']);
        $this->assertSameArray(['file1.md', 'file2.txt'], 'directory', ['md', 'txt']);
    }

    public function testFindFilesWithMultipleExtensionsButOnlyOneMatches()
    {
        $this->files(['directory/file1.md', 'directory/file2.blade.php', 'directory/file3.blade.php']);
        $this->assertSameArray(['file1.md'], 'directory', ['md', 'txt']);
    }

    public function testFindFilesWithMultipleExtensionsCaseInsensitive()
    {
        $this->files(['directory/file1.MD', 'directory/file2.TXT', 'directory/file3.blade.PHP']);
        $this->assertSameArray(['file1.MD', 'file2.TXT'], 'directory', ['md', 'txt']);
    }

    public function testFindFilesWithEmptyArrayExtensions()
    {
        $this->files(['directory/file1.md', 'directory/file2.txt']);
        $this->assertSameArray([], 'directory', []);
    }

    public function testFindFilesWithMixedExtensionsAndRecursion()
    {
        $this->files(['directory/file1.md', 'directory/nested/file2.txt', 'directory/nested/deep/file3.blade.php']);
        $this->assertSameArray(['file1.md', 'nested/file2.txt'], 'directory', ['md', 'txt'], true);
    }

    public function testFindFilesWithMixedExtensionsNoRecursion()
    {
        $this->files(['directory/file1.md', 'directory/nested/file2.txt']);
        $this->assertSameArray(['file1.md'], 'directory', ['md', 'txt'], false);
    }

    public function testFindFilesWithNoFilesMatchingAnyExtension()
    {
        $this->files(['directory/file1.md', 'directory/file2.txt']);
        $this->assertSameArray([], 'directory', ['php', 'html']);
    }

    public function testFindFilesWithRecursiveAndNoFilesMatchingAnyExtension()
    {
        $this->files(['directory/file1.md', 'directory/nested/file2.txt']);
        $this->assertSameArray([], 'directory', ['php', 'html'], true);
    }

    public function testFindFilesWithRecursiveAndSomeMatchingExtensions()
    {
        $this->files(['directory/file1.md', 'directory/nested/file2.txt', 'directory/nested/deep/file3.html']);
        $this->assertSameArray(['file1.md', 'nested/file2.txt'], 'directory', ['md', 'txt'], true);
    }

    public function testFindFilesWithOnlyDotInExtensions()
    {
        $this->files(['directory/file.md', 'directory/file.txt']);
        $this->assertSameArray(['file.md'], 'directory', '.md');
        $this->assertSameArray(['file.txt'], 'directory', '.txt');
    }

    public function testFindFilesWithNoFilesWhenDirectoryContainsUnmatchedExtensions()
    {
        $this->files(['directory/file.md', 'directory/file.txt']);
        $this->assertSameArray([], 'directory', 'php');
        $this->assertSameArray([], 'directory', ['php']);
    }

    public function testFindFilesWithEmptyDirectoryAndMultipleExtensions()
    {
        $this->directory('directory');
        $this->assertSameArray([], 'directory', ['md', 'txt']);
    }

    public function testFindFilesWithInvalidExtensionsThrowsNoError()
    {
        $this->files(['directory/file.md', 'directory/file.txt']);
        $this->assertSameArray([], 'directory', '');
        $this->assertSameArray([], 'directory', ['']);
    }

    public function testFindFilesWithCsvStringExtensions()
    {
        $this->files(['directory/file1.md', 'directory/file2.txt', 'directory/file3.jpg']);
        $this->assertSameArray(['file1.md', 'file2.txt'], 'directory', 'md,txt');
    }

    public function testFindFilesWithCsvStringExtensionsAndSpaces()
    {
        $this->files(['directory/file1.md', 'directory/file2.txt', 'directory/file3.jpg']);
        $this->assertSameArray(['file1.md', 'file2.txt'], 'directory', 'md, txt');
    }

    public function testFindFilesWithCsvStringExtensionsMixedCase()
    {
        $this->files(['directory/file1.MD', 'directory/file2.TXT', 'directory/file3.jpg']);
        $this->assertSameArray(['file1.MD', 'file2.TXT'], 'directory', 'md,TXT');
    }

    public function testFindFilesWithCsvStringExtensionsInArray()
    {
        $this->files(['directory/file1.md', 'directory/file2.txt', 'directory/file3.jpg']);
        $this->assertSameArray(['file1.md', 'file2.txt'], 'directory', ['md,txt']);
    }

    public function testFindFilesWithCsvStringExtensionsInMixedArray()
    {
        $this->files(['directory/file1.md', 'directory/file2.txt', 'directory/file3.jpg']);
        $this->assertSameArray(['file1.md', 'file2.txt', 'file3.jpg'], 'directory', ['md,txt', 'jpg']);
    }

    protected function assertSameArray(array $expected, string $directory, string|array|false $matchExtensions = false, bool $recursive = false): void
    {
        $files = (new Filesystem(Hyde::getInstance()))->findFiles($directory, $matchExtensions, $recursive);

        // Compare sorted arrays because some filesystems may return files in a different order.
        $this->assertSame(collect($expected)->map(fn (string $file): string => $directory.'/'.$file)->sort()->values()->all(), $files->all());
    }

    protected function tearDown(): void
    {
        $this->cleanUpFilesystem();
    }
}
