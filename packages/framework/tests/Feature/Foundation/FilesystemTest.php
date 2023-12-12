<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Foundation;

use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\Kernel\Filesystem;
use Hyde\Foundation\PharSupport;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\UnitTestCase;

use function Hyde\normalize_slashes;

/**
 * @covers \Hyde\Foundation\HydeKernel
 * @covers \Hyde\Foundation\Kernel\Filesystem
 */
class FilesystemTest extends UnitTestCase
{
    protected string $originalBasePath;

    protected Filesystem $filesystem;

    public static function setUpBeforeClass(): void
    {
        self::needsKernel();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalBasePath = Hyde::getBasePath();
        $this->filesystem = new Filesystem(Hyde::getInstance());
    }

    protected function tearDown(): void
    {
        Hyde::getInstance()->setBasePath($this->originalBasePath);

        parent::tearDown();
    }

    public function testGetBasePathReturnsKernelsBasePath()
    {
        Hyde::getInstance()->setBasePath('/foo');
        $this->assertSame('/foo', $this->filesystem->getBasePath());
    }

    public function testPathMethodExists()
    {
        $this->assertTrue(method_exists(Filesystem::class, 'path'));
    }

    public function testPathMethodReturnsString()
    {
        $this->assertIsString($this->filesystem->path());
    }

    public function testPathMethodReturnsBasePathWhenNotSuppliedWithArgument()
    {
        Hyde::getInstance()->setBasePath('/foo');
        $this->assertSame('/foo', $this->filesystem->path());
    }

    public function testPathMethodReturnsPathRelativeToBasePathWhenSuppliedWithArgument()
    {
        Hyde::getInstance()->setBasePath('/foo');
        $this->assertSame('/foo/foo/bar.php', $this->filesystem->path('foo/bar.php'));
    }

    public function testPathMethodReturnsQualifiedFilePathWhenSuppliedWithArgument()
    {
        Hyde::getInstance()->setBasePath('/foo');
        $this->assertSame('/foo/file.php', $this->filesystem->path('file.php'));
    }

    public function testPathMethodReturnsExpectedValueForNestedPathArguments()
    {
        Hyde::getInstance()->setBasePath('/foo');
        $this->assertSame('/foo/directory/file.php', $this->filesystem->path('directory/file.php'));
    }

    public function testPathMethodStripsTrailingDirectorySeparatorsFromArgument()
    {
        Hyde::getInstance()->setBasePath('/foo');
        $this->assertSame('/foo/file.php', $this->filesystem->path('\\/file.php/'));
    }

    public function testPathMethodReturnsExpectedValueRegardlessOfTrailingDirectorySeparatorsInArgument()
    {
        Hyde::getInstance()->setBasePath('/foo');
        $this->assertSame('/foo/bar/file.php', $this->filesystem->path('\\/bar/file.php/'));
    }

    public function testPathMethodResolvesAlreadyAbsolutePaths()
    {
        Hyde::getInstance()->setBasePath('/foo');
        $this->assertSame('/foo/bar', $this->filesystem->path('/foo/bar'));
    }

    public function testPathMethodResolvesAlreadyAbsolutePathsUsingHelper()
    {
        $this->assertSame($this->filesystem->path('foo'), $this->filesystem->path($this->filesystem->path('foo')));
    }

    public function testPathMethodResolvesAlreadyAbsolutePathsUsingHelperWithTrailingSlash()
    {
        $this->assertSame($this->filesystem->path('foo'), $this->filesystem->path($this->filesystem->path('foo/')));
    }

    public function testPathMethodDoesNotModifyPharPaths()
    {
        $this->assertSame('phar://foo', $this->filesystem->path('phar://foo'));
    }

    public function testHydePathMethodExists()
    {
        $this->assertTrue(method_exists(HydeKernel::class, 'path'));
    }

    public function testHydePathStringIsReturned()
    {
        $this->assertIsString(Hyde::path());
    }

    public function testHydePathReturnedDirectoryContainsContentExpectedToBeInTheProjectDirectory()
    {
        $this->assertFileExists(Hyde::path('hyde'));
    }

    public function testVendorPathMethodExists()
    {
        $this->assertTrue(method_exists(Filesystem::class, 'vendorPath'));
    }

    public function testVendorPathMethodReturnsString()
    {
        $this->assertIsString($this->filesystem->vendorPath());
    }

    public function testVendorPathMethodReturnsTheVendorPath()
    {
        $this->assertSame(Hyde::path('vendor/hyde/framework'), $this->filesystem->vendorPath());
    }

    public function testVendorPathMethodReturnsQualifiedFilePathWhenSuppliedWithArgument()
    {
        $this->assertSame($this->filesystem->vendorPath('file.php'), $this->filesystem->vendorPath().'/file.php');
    }

    public function testVendorPathMethodReturnsExpectedValueRegardlessOfTrailingDirectorySeparatorsInArgument()
    {
        Hyde::getInstance()->setBasePath('/foo');
        $this->assertSame('/foo/vendor/hyde/framework/file.php', $this->filesystem->vendorPath('\\//file.php/'));
    }

    public function testVendorPathCanSpecifyWhichHydePackageToUse()
    {
        $this->assertDirectoryExists(Hyde::vendorPath(package: 'realtime-compiler'));
        $this->assertFileExists(Hyde::vendorPath('composer.json', 'realtime-compiler'));
    }

    public function testVendorPathCanRunInPhar()
    {
        PharSupport::mock('running', true);
        PharSupport::mock('hasVendorDirectory', false);

        $this->assertContains($this->filesystem->vendorPath(), [
            // Monorepo support for symlinked packages directory
            str_replace('/', DIRECTORY_SEPARATOR, Hyde::path('packages/framework')),
            str_replace('/', DIRECTORY_SEPARATOR, Hyde::path('vendor/hyde/framework')),
        ]);

        PharSupport::clearMocks();
    }

    public function testTouchHelperCreatesFileAtGivenPath()
    {
        $this->assertTrue($this->filesystem->touch('foo'));
        $this->assertFileExists(Hyde::path('foo'));
        $this->filesystem->unlink('foo');
    }

    public function testTouchHelperCreatesMultipleFilesAtGivenPaths()
    {
        $this->assertTrue($this->filesystem->touch(['foo', 'bar']));
        $this->assertFileExists(Hyde::path('foo'));
        $this->assertFileExists(Hyde::path('bar'));
        $this->filesystem->unlink('foo');
        $this->filesystem->unlink('bar');
    }

    public function testUnlinkHelperDeletesFileAtGivenPath()
    {
        touch(Hyde::path('foo'));
        $this->assertTrue($this->filesystem->unlink('foo'));
        $this->assertFileDoesNotExist(Hyde::path('foo'));
    }

    public function testUnlinkHelperDeletesMultipleFilesAtGivenPaths()
    {
        touch(Hyde::path('foo'));
        touch(Hyde::path('bar'));
        $this->assertTrue($this->filesystem->unlink(['foo', 'bar']));
        $this->assertFileDoesNotExist(Hyde::path('foo'));
        $this->assertFileDoesNotExist(Hyde::path('bar'));
    }

    public function testUnlinkIfExistsHelperDeletesFileAtGivenPath()
    {
        touch(Hyde::path('foo'));
        $this->assertTrue($this->filesystem->unlinkIfExists('foo'));
        $this->assertFileDoesNotExist(Hyde::path('foo'));
    }

    public function testUnlinkIfExistsHandlesNonExistentFilesGracefully()
    {
        $this->assertFalse($this->filesystem->unlinkIfExists('foo'));
    }

    public function testGetModelSourcePathMethodReturnsPathForModelClasses()
    {
        $this->assertSame(Hyde::path('_pages'), HtmlPage::path());
        $this->assertSame(Hyde::path('_pages'), BladePage::path());
        $this->assertSame(Hyde::path('_pages'), MarkdownPage::path());
        $this->assertSame(Hyde::path('_posts'), MarkdownPost::path());
        $this->assertSame(Hyde::path('_docs'), DocumentationPage::path());
    }

    public function testGetModelSourcePathMethodReturnsPathToFileForModelClasses()
    {
        $this->assertSame(Hyde::path('_pages/foo.md'), HtmlPage::path('foo.md'));
        $this->assertSame(Hyde::path('_pages/foo.md'), BladePage::path('foo.md'));
        $this->assertSame(Hyde::path('_pages/foo.md'), MarkdownPage::path('foo.md'));
        $this->assertSame(Hyde::path('_posts/foo.md'), MarkdownPost::path('foo.md'));
        $this->assertSame(Hyde::path('_docs/foo.md'), DocumentationPage::path('foo.md'));
    }

    public function testHelperForBladePages()
    {
        $this->assertSame(Hyde::path('_pages'), BladePage::path());
    }

    public function testHelperForMarkdownPages()
    {
        $this->assertSame(Hyde::path('_pages'), MarkdownPage::path());
    }

    public function testHelperForMarkdownPosts()
    {
        $this->assertSame(Hyde::path('_posts'), MarkdownPost::path());
    }

    public function testHelperForDocumentationPages()
    {
        $this->assertSame(Hyde::path('_docs'), DocumentationPage::path());
    }

    public function testHelperForMediaPath()
    {
        $this->assertSame(Hyde::path('_media'), Hyde::mediaPath());
    }

    public function testHelperForMediaPathReturnsPathToFileWithinTheDirectory()
    {
        $this->assertSame(Hyde::path('_media/foo.css'), Hyde::mediaPath('foo.css'));
    }

    public function testGetMediaPathReturnsAbsolutePath()
    {
        $this->assertSame(Hyde::path('_media'), Hyde::mediaPath());
    }

    public function testHelperForMediaOutputPath()
    {
        $this->assertSame(Hyde::path('_site/media'), Hyde::siteMediaPath());
    }

    public function testHelperForMediaOutputPathReturnsPathToFileWithinTheDirectory()
    {
        $this->assertSame(Hyde::path('_site/media/foo.css'), Hyde::siteMediaPath('foo.css'));
    }

    public function testGetMediaOutputPathReturnsAbsolutePath()
    {
        $this->assertSame(Hyde::path('_site/media'), Hyde::siteMediaPath());
    }

    public function testHelperForSiteOutputPath()
    {
        $this->assertSame(Hyde::path('_site'), Hyde::sitePath());
    }

    public function testHelperForSiteOutputPathReturnsPathToFileWithinTheDirectory()
    {
        $this->assertSame(Hyde::path('_site/foo.html'), Hyde::sitePath('foo.html'));
    }

    public function testGetSiteOutputPathReturnsAbsolutePath()
    {
        $this->assertSame(Hyde::path('_site'), Hyde::sitePath());
    }

    public function testSiteOutputPathHelperIgnoresTrailingSlashes()
    {
        $this->assertSame(Hyde::path('_site/foo.html'), Hyde::sitePath('/foo.html/'));
    }

    public function testPathToAbsolute()
    {
        $this->assertSame(Hyde::path('foo'), Hyde::pathToAbsolute('foo'));
    }

    public function testPathToAbsoluteHelperIsAliasForPathHelper()
    {
        $this->assertSame(Hyde::path('foo'), $this->filesystem->pathToAbsolute('foo'));
    }

    public function testPathToAbsoluteCanConvertArrayOfPaths()
    {
        $this->assertSame(
            [Hyde::path('foo'), Hyde::path('bar')],
            $this->filesystem->pathToAbsolute(['foo', 'bar'])
        );
    }

    public function testPathToRelativeHelperDecodesHydePathIntoRelative()
    {
        $this->assertSame('foo', Hyde::pathToRelative(Hyde::path('foo')));
        $this->assertSame('foo', Hyde::pathToRelative(Hyde::path('/foo/')));
        $this->assertSame('foo.md', Hyde::pathToRelative(Hyde::path('foo.md')));
        $this->assertSame('foo/bar', Hyde::pathToRelative(Hyde::path('foo/bar')));
        $this->assertSame('foo/bar.md', Hyde::pathToRelative(Hyde::path('foo/bar.md')));
    }

    public function testPathToRelativeHelperDoesNotModifyAlreadyRelativePaths()
    {
        $this->assertSame('foo', Hyde::pathToRelative('foo'));
        $this->assertSame('foo/', Hyde::pathToRelative('foo/'));
        $this->assertSame('../foo', Hyde::pathToRelative('../foo'));
        $this->assertSame('../foo/', Hyde::pathToRelative('../foo/'));
        $this->assertSame('foo.md', Hyde::pathToRelative('foo.md'));
        $this->assertSame('foo/bar', Hyde::pathToRelative('foo/bar'));
        $this->assertSame('foo/bar.md', Hyde::pathToRelative('foo/bar.md'));
    }

    public function testPathToRelativeHelperDoesNotModifyNonProjectPaths()
    {
        $testStrings = [
            'C:\Documents\Newsletters\Summer2018.pdf',
            '\Program Files\Custom Utilities\StringFinder.exe',
            '2018\January.xlsx',
            '..\Publications\TravelBrochure.pdf',
            'C:\Projects\library\library.sln',
            'C:Projects\library\library.sln',
            '/home/seth/Pictures/penguin.jpg',
            '~/Pictures/penguin.jpg',
        ];

        foreach ($testStrings as $testString) {
            $this->assertSame(normalize_slashes($testString), Hyde::pathToRelative($testString));
        }
    }
}
