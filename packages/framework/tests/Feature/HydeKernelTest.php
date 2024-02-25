<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Throwable;
use Composer\InstalledVersions;
use Hyde\Facades\Features;
use Hyde\Foundation\Facades\Pages;
use Hyde\Foundation\Facades\Routes;
use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\Kernel\Filesystem;
use Hyde\Framework\HydeServiceProvider;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\InMemoryPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Support\Facades\Render;
use Hyde\Support\Models\Route;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\HtmlString;

/**
 * This test class runs high-level tests on the HydeKernel class,
 * as most of the logic actually resides in linked service classes.
 *
 * @covers \Hyde\Foundation\HydeKernel
 * @covers \Hyde\Hyde
 *
 * @see \Hyde\Framework\Testing\Unit\HydeHelperFacadeMakeTitleTest
 * @see \Hyde\Framework\Testing\Feature\HydeExtensionFeatureTest
 */
class HydeKernelTest extends TestCase
{
    public function testKernelSingletonCanBeAccessedByServiceContainer()
    {
        $this->assertSame(app(HydeKernel::class), app(HydeKernel::class));
    }

    public function testKernelSingletonCanBeAccessedByKernelStaticMethod()
    {
        $this->assertSame(app(HydeKernel::class), HydeKernel::getInstance());
    }

    public function testKernelSingletonCanBeAccessedByHydeFacadeMethod()
    {
        $this->assertSame(app(HydeKernel::class), Hyde::getInstance());
    }

    public function testKernelSingletonCanBeAccessedByHelperFunction()
    {
        $this->assertSame(app(HydeKernel::class), hyde());
    }

    public function testHydeFacadeVersionMethodReturnsKernelVersion()
    {
        $this->assertSame(HydeKernel::version(), Hyde::version());
    }

    public function testHydeFacadeGetFacadeRootMethodReturnsKernelSingleton()
    {
        $this->assertSame(app(HydeKernel::class), Hyde::getFacadeRoot());
        $this->assertSame(HydeKernel::getInstance(), Hyde::getFacadeRoot());
        $this->assertSame(Hyde::getInstance(), Hyde::getFacadeRoot());
    }

    public function testFeaturesHelperReturnsNewFeaturesInstance()
    {
        $this->assertInstanceOf(Features::class, Hyde::features());
    }

    public function testHasFeatureHelperCallsMethodOnFeaturesClass()
    {
        $this->assertSame(Features::enabled('foo'), Hyde::hasFeature('foo'));
    }

    public function testCurrentPageHelperReturnsCurrentPageName()
    {
        Render::share('routeKey', 'foo');
        $this->assertSame('foo', Hyde::currentRouteKey());
    }

    public function testCurrentRouteHelperReturnsCurrentRouteObject()
    {
        $expected = new Route(new MarkdownPage());
        Render::share('route', $expected);
        $this->assertInstanceOf(Route::class, Hyde::currentRoute());
        $this->assertSame($expected, Hyde::currentRoute());
    }

    public function testCurrentPageHelperReturnsCurrentPageObject()
    {
        $expected = new MarkdownPage();
        Render::share('page', $expected);
        $this->assertInstanceOf(HydePage::class, Hyde::currentPage());
        $this->assertSame($expected, Hyde::currentPage());
    }

    public function testMakeTitleHelperReturnsTitleFromPageSlug()
    {
        $this->assertSame('Foo Bar', Hyde::makeTitle('foo-bar'));
    }

    public function testNormalizeNewlinesReplacesCarriageReturnsWithUnixEndings()
    {
        $this->assertSame("foo\nbar\nbaz", Hyde::normalizeNewlines("foo\nbar\r\nbaz"));
    }

    public function testStripNewlinesHelperRemovesAllNewlines()
    {
        $this->assertSame('foo bar baz', Hyde::stripNewlines("foo\n bar\r\n baz"));
    }

    public function testTrimSlashesFunctionTrimsTrailingSlashes()
    {
        $tests = ['foo',  '/foo',  'foo/',  '/foo/',  '\foo\\',  '\\/foo/\\'];

        foreach ($tests as $test) {
            $this->assertSame('foo', Hyde::trimSlashes($test));
        }

        $tests = ['',  '/',  '\\',  '/\\'];

        foreach ($tests as $test) {
            $this->assertSame('', Hyde::trimSlashes($test));
        }

        $tests = ['foo/bar',  'foo/bar/',  'foo/bar\\',  '\\/foo/bar/\\'];

        foreach ($tests as $test) {
            $this->assertSame('foo/bar', Hyde::trimSlashes($test));
        }
    }

    public function testMarkdownHelperConvertsMarkdownToHtml()
    {
        $this->assertEquals(new HtmlString("<p>foo</p>\n"), Hyde::markdown('foo'));
    }

    public function testMarkdownHelperConvertsIndentedMarkdownToHtml()
    {
        $this->assertEquals(new HtmlString("<p>foo</p>\n"), Hyde::markdown('    foo', true));
    }

    public function testFormatHtmlPathHelperFormatsPathAccordingToConfigRules()
    {
        Config::set('hyde.pretty_urls', false);
        $this->assertSame('foo.html', Hyde::formatLink('foo.html'));
        $this->assertSame('index.html', Hyde::formatLink('index.html'));

        Config::set('hyde.pretty_urls', true);
        $this->assertSame('foo', Hyde::formatLink('foo.html'));
        $this->assertSame('/', Hyde::formatLink('index.html'));
    }

    public function testRelativeLinkHelperReturnsRelativeLinkToDestination()
    {
        Render::share('routeKey', 'bar');
        $this->assertSame('foo', Hyde::relativeLink('foo'));

        Render::share('routeKey', 'foo/bar');
        $this->assertSame('../foo', Hyde::relativeLink('foo'));
    }

    public function testMediaLinkHelperReturnsRelativeLinkToDestination()
    {
        Render::share('routeKey', 'bar');
        $this->assertSame('media/foo', Hyde::mediaLink('foo'));

        Render::share('routeKey', 'foo/bar');
        $this->assertSame('../media/foo', Hyde::mediaLink('foo'));
    }

    public function testImageHelperReturnsImagePathForGivenName()
    {
        Render::share('routeKey', 'foo');
        $this->assertSame('media/foo.jpg', Hyde::asset('foo.jpg'));
        $this->assertSame('https://example.com/foo.jpg', Hyde::asset('https://example.com/foo.jpg'));

        Render::share('routeKey', 'foo/bar');
        $this->assertSame('../media/foo.jpg', Hyde::asset('foo.jpg'));
        $this->assertSame('https://example.com/foo.jpg', Hyde::asset('https://example.com/foo.jpg'));
    }

    public function testImageHelperTrimsMediaPrefix()
    {
        $this->assertSame('media/foo.jpg', Hyde::asset('media/foo.jpg'));
    }

    public function testImageHelperSupportsCustomMediaDirectories()
    {
        Hyde::setMediaDirectory('_assets');
        $this->assertSame('assets/foo.jpg', Hyde::asset('foo.jpg'));
    }

    public function testRouteHelper()
    {
        $this->assertNotNull(Hyde::route('index'));
        $this->assertSame(Routes::get('index'), Hyde::route('index'));
    }

    public function testRouteHelperWithInvalidRoute()
    {
        $this->assertNull(Hyde::route('foo'));
    }

    public function testHasSiteUrlHelperReturnsBooleanValueForWhenConfigSettingIsSet()
    {
        Config::set('hyde.url', 'https://example.com');
        $this->assertTrue(Hyde::hasSiteUrl());

        Config::set('hyde.url', null);
        $this->assertFalse(Hyde::hasSiteUrl());
    }

    public function testUrlReturnsQualifiedUrlPaths()
    {
        Config::set('hyde.url', 'https://example.com');
        $this->assertSame('https://example.com', Hyde::url());
        $this->assertSame('https://example.com/foo', Hyde::url('foo'));

        Config::set('hyde.pretty_urls', false);
        $this->assertSame('https://example.com/foo.html', Hyde::url('foo.html'));
        $this->assertSame('https://example.com/index.html', Hyde::url('index.html'));

        Config::set('hyde.pretty_urls', true);
        $this->assertSame('https://example.com/foo', Hyde::url('foo.html'));
        $this->assertSame('https://example.com', Hyde::url('index.html'));
    }

    public function testFilesystemHelperReturnsTheKernelFilesystemInstance()
    {
        $this->assertInstanceOf(Filesystem::class, Hyde::filesystem());
    }

    public function testPathReturnsQualifiedPathForGivenPath()
    {
        $this->assertSame(Hyde::getBasePath(), Hyde::path());
        $this->assertSame(Hyde::getBasePath().'/foo', Hyde::path('foo'));
    }

    public function testVendorPathReturnsQualifiedPathForGivenPath()
    {
        $this->assertSame(Hyde::getBasePath().'/vendor/hyde/framework', Hyde::vendorPath());
        $this->assertSame(Hyde::getBasePath().'/vendor/hyde/framework/foo', Hyde::vendorPath('foo'));
    }

    public function testFluentModelSourcePathHelpers()
    {
        $this->assertSame(Hyde::path('_pages'), BladePage::path());
        $this->assertSame(Hyde::path('_posts'), MarkdownPost::path());
        $this->assertSame(Hyde::path('_pages'), MarkdownPage::path());
        $this->assertSame(Hyde::path('_docs'), DocumentationPage::path());

        $this->assertSame(Hyde::path('_media'), Hyde::mediaPath());
        $this->assertSame(Hyde::path('_pages'), BladePage::path());
        $this->assertSame(Hyde::path('_pages'), MarkdownPage::path());
        $this->assertSame(Hyde::path('_posts'), MarkdownPost::path());
        $this->assertSame(Hyde::path('_docs'), DocumentationPage::path());
        $this->assertSame(Hyde::path('_site'), Hyde::sitePath());
        $this->assertSame(Hyde::path('_site/media'), Hyde::siteMediaPath());
    }

    public function testPathToRelativeHelperReturnsRelativePathForGivenPath()
    {
        $this->assertSame('foo', Hyde::pathToRelative(Hyde::path('foo')));
    }

    public function testToArrayMethod()
    {
        // AssertSame cannot be used as features is reinstantiated on each call
        $this->assertEquals([
            'basePath' => Hyde::getBasePath(),
            'sourceRoot' => Hyde::getSourceRoot(),
            'outputDirectory' => Hyde::getOutputDirectory(),
            'mediaDirectory' => Hyde::getMediaDirectory(),
            'extensions' => Hyde::getRegisteredExtensions(),
            'features' => Hyde::features(),
            'files' => Hyde::files(),
            'pages' => Hyde::pages(),
            'routes' => Hyde::routes(),
        ], Hyde::toArray());
    }

    public function testJsonSerializeMethod()
    {
        $this->assertEquals(Hyde::kernel()->jsonSerialize(), collect(Hyde::toArray())->toArray());
    }

    public function testToJsonMethod()
    {
        $this->assertEquals(Hyde::kernel()->toJson(), json_encode(Hyde::toArray()));
    }

    public function testVersionConstantIsAValidSemverString()
    {
        // https://semver.org/#is-there-a-suggested-regular-expression-regex-to-check-a-semver-stringd
        $this->assertMatchesRegularExpression(
            '/^(?P<major>0|[1-9]\d*)\.(?P<minor>0|[1-9]\d*)\.(?P<patch>0|[1-9]\d*)(?:-(?P<prerelease>(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+(?P<buildmetadata>[0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?$/',
            HydeKernel::VERSION
        );
    }

    public function testVersionConstantIsUpToDateWithComposer()
    {
        $version = InstalledVersions::getPrettyVersion('hyde/framework');

        if (str_starts_with($version, 'dev-')) {
            $this->markTestSkipped('Installed version is for development');
        }

        $this->assertSame(HydeKernel::VERSION, $version);
    }

    public function testVersionConstantIsUpToDateWithGit()
    {
        try {
            $version = trim(shell_exec('git describe --abbrev=0 --tags'));
        } catch (Throwable) {
            $this->markTestSkipped('Could not get version from Git');
        }

        if ('v'.HydeKernel::VERSION === $version) {
            $this->assertSame('v'.HydeKernel::VERSION, $version);
        } else {
            $this->markTestSkipped('Version constant does not match Git version!');
        }
    }

    public function testVersionMethodReturnsVersionConstant()
    {
        $this->assertSame(HydeKernel::VERSION, Hyde::version());
    }

    public function testCanGetSourceRoot()
    {
        $this->assertSame('', Hyde::getSourceRoot());
    }

    public function testCanSetSourceRoot()
    {
        Hyde::setSourceRoot('foo');
        $this->assertSame('foo', Hyde::getSourceRoot());
    }

    public function testCanSetSourceRootToAbsoluteProjectPath()
    {
        Hyde::setSourceRoot(Hyde::path('foo'));
        $this->assertSame('foo', Hyde::getSourceRoot());
    }

    public function testSetSourceRootTrimsTrailingSlashes()
    {
        Hyde::setSourceRoot('/foo/');
        $this->assertSame('/foo', Hyde::getSourceRoot());
    }

    public function testCanGetOutputDirectory()
    {
        $this->assertSame('_site', Hyde::getOutputDirectory());
    }

    public function testCanSetOutputDirectory()
    {
        Hyde::setOutputDirectory('foo');
        $this->assertSame('foo', Hyde::getOutputDirectory());
        $this->assertSame(Hyde::path('foo'), Hyde::sitePath());
    }

    public function testCanSetOutputDirectoryToAbsoluteProjectPath()
    {
        Hyde::setOutputDirectory(Hyde::path('foo'));
        $this->assertSame('foo', Hyde::getOutputDirectory());
        $this->assertSame(Hyde::path('foo'), Hyde::sitePath());
    }

    public function testSetOutputDirectoryTrimsTrailingSlashes()
    {
        Hyde::setOutputDirectory('/foo/');
        $this->assertSame('/foo', Hyde::getOutputDirectory());
    }

    public function testCanGetMediaDirectory()
    {
        $this->assertSame('_media', Hyde::getMediaDirectory());
    }

    public function testCanSetMediaDirectory()
    {
        Hyde::setMediaDirectory('foo');
        $this->assertSame('foo', Hyde::getMediaDirectory());
    }

    public function testSetMediaDirectoryTrimsTrailingSlashes()
    {
        Hyde::setMediaDirectory('/foo/');
        $this->assertSame('/foo', Hyde::getMediaDirectory());
    }

    public function testCanGetMediaOutputDirectoryName()
    {
        $this->assertSame('media', Hyde::getMediaOutputDirectory());
    }

    public function testGetMediaOutputDirectoryNameUsesTrimmedVersionOfMediaSourceDirectory()
    {
        Hyde::setMediaDirectory('_foo');
        $this->assertSame('foo', Hyde::getMediaOutputDirectory());
    }

    public function testCanGetSiteMediaOutputDirectory()
    {
        $this->assertSame(Hyde::path('_site/media'), Hyde::siteMediaPath());
    }

    public function testGetSiteMediaOutputDirectoryUsesTrimmedVersionOfMediaSourceDirectory()
    {
        Hyde::setMediaDirectory('_foo');
        $this->assertSame(Hyde::path('_site/foo'), Hyde::siteMediaPath());
    }

    public function testGetSiteMediaOutputDirectoryUsesConfiguredSiteOutputDirectory()
    {
        Hyde::setOutputDirectory(Hyde::path('foo'));
        Hyde::setMediaDirectory('bar');
        $this->assertSame(Hyde::path('foo/bar'), Hyde::siteMediaPath());
    }

    public function testMediaOutputDirectoryCanBeChangedInConfiguration()
    {
        $this->assertEquals('_media', Hyde::getMediaDirectory());

        config(['hyde.media_directory' => '_assets']);
        (new HydeServiceProvider($this->app))->register();

        $this->assertEquals('_assets', Hyde::getMediaDirectory());
    }

    public function testCanAccessKernelFluentlyUsingTheFacade()
    {
        $this->assertInstanceOf(HydeKernel::class, Hyde::kernel());
        $this->assertSame(HydeKernel::getInstance(), Hyde::kernel());
        $this->assertSame(HydeKernel::VERSION, Hyde::kernel()->version());
    }

    public function testCanRegisterBootingCallbackClosure()
    {
        $kernel = new HydeKernel();

        $kernel->booting(function () {
            $this->assertTrue(true);
        });

        $kernel->boot();
    }

    public function testCanRegisterBootedCallbackClosure()
    {
        $kernel = new HydeKernel();

        $kernel->booted(function () {
            $this->assertTrue(true);
        });

        $kernel->boot();
    }

    public function testCanRegisterBootingCallbackCallable()
    {
        $kernel = new HydeKernel();

        $kernel->booting(new CallableClass($this));

        $kernel->boot();
    }

    public function testCanRegisterBootedCallbackCallable()
    {
        $kernel = new HydeKernel();

        $kernel->booted(new CallableClass($this));

        $kernel->boot();
    }

    public function testBootingCallbackReceivesKernelInstance()
    {
        $kernel = new HydeKernel();

        $kernel->booting(function ($_kernel) use ($kernel) {
            $this->assertSame($kernel, $_kernel);
        });

        $kernel->boot();
    }

    public function testBootedCallbackReceivesKernelInstance()
    {
        $kernel = new HydeKernel();

        $kernel->booted(function ($_kernel) use ($kernel) {
            $this->assertSame($kernel, $_kernel);
        });

        $kernel->boot();
    }

    public function testCanUseBootingCallbacksToInjectCustomPages()
    {
        $kernel = HydeKernel::getInstance();

        $page = new InMemoryPage('foo');
        $kernel->booting(function (HydeKernel $kernel) use ($page): void {
            $kernel->pages()->addPage($page);
        });

        $this->assertSame($page, Pages::getPage('foo'));
        $this->assertEquals($page->getRoute(), Routes::getRoute('foo'));
    }

    public function testIsBootedReturnsFalseWhenNotBooted()
    {
        $kernel = new HydeKernel();
        $this->assertFalse($kernel->isBooted());
    }

    public function testIsBootedReturnsTrueWhenBooted()
    {
        $kernel = new HydeKernel();
        $kernel->boot();
        $this->assertTrue($kernel->isBooted());
    }

    public function testIsBootedMethodOnTheFacade()
    {
        $this->assertFalse(Hyde::isBooted());
        Hyde::kernel()->boot();
        $this->assertTrue(Hyde::isBooted());
    }
}

class CallableClass
{
    private TestCase $test;

    public function __construct($test)
    {
        $this->test = $test;
    }

    public function __invoke(): void
    {
        $this->test->assertTrue(true);
    }
}
