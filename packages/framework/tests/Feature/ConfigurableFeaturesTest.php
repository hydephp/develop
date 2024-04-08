<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Facades\Features;
use Hyde\Testing\UnitTestCase;
use Hyde\Foundation\HydeKernel;

/**
 * @covers \Hyde\Facades\Features
 */
class ConfigurableFeaturesTest extends UnitTestCase
{
    protected static array $defaultConfig;

    protected static bool $needsKernel = true;
    protected static bool $needsConfig = true;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$defaultConfig = require Hyde::path('config/hyde.php');
    }

    protected function setUp(): void
    {
        parent::setUp();

        self::mockConfig();

        HydeKernel::setInstance(new HydeKernel());
    }

    public function testHasHtmlPagesReturnsFalseWhenFeatureIsNotEnabled()
    {
        $this->expectMethodReturnsFalse('hasHtmlPages');
    }

    public function testHasBladePagesReturnsFalseWhenFeatureIsNotEnabled()
    {
        $this->expectMethodReturnsFalse('hasBladePages');
    }

    public function testHasMarkdownPagesReturnsFalseWhenFeatureIsNotEnabled()
    {
        $this->expectMethodReturnsFalse('hasMarkdownPages');
    }

    public function testHasMarkdownPostsReturnsFalseWhenFeatureIsNotEnabled()
    {
        $this->expectMethodReturnsFalse('hasMarkdownPosts');
    }

    public function testHasDocumentationPagesReturnsFalseWhenFeatureIsNotEnabled()
    {
        $this->expectMethodReturnsFalse('hasDocumentationPages');
    }

    public function testHasDocumentationSearchReturnsFalseWhenFeatureIsNotEnabled()
    {
        $this->expectMethodReturnsFalse('hasDocumentationSearch');
    }

    public function testHasDarkmodeReturnsFalseWhenFeatureIsNotEnabled()
    {
        $this->expectMethodReturnsFalse('hasDarkmode');
    }

    public function testHasTorchlightReturnsFalseWhenFeatureIsNotEnabled()
    {
        $this->expectMethodReturnsFalse('hasTorchlight');
    }

    public function testHasRssReturnsFalseWhenFeatureIsNotEnabled()
    {
        $this->expectMethodReturnsFalse('hasRss');
    }

    public function testHasHtmlPagesReturnsTrueWhenFeatureIsEnabled()
    {
        $this->expectMethodReturnsTrue('hasHtmlPages');
    }

    public function testHasBladePagesReturnsTrueWhenFeatureIsEnabled()
    {
        $this->expectMethodReturnsTrue('hasBladePages');
    }

    public function testHasMarkdownPagesReturnsTrueWhenFeatureIsEnabled()
    {
        $this->expectMethodReturnsTrue('hasMarkdownPages');
    }

    public function testHasMarkdownPostsReturnsTrueWhenFeatureIsEnabled()
    {
        $this->expectMethodReturnsTrue('hasMarkdownPosts');
    }

    public function testHasDocumentationPagesReturnsTrueWhenFeatureIsEnabled()
    {
        $this->expectMethodReturnsTrue('hasDocumentationPages');
    }

    public function testHasDarkmodeReturnsTrueWhenFeatureIsEnabled()
    {
        $this->expectMethodReturnsTrue('hasDarkmode');
    }

    public function testHasSitemapReturnsTrueWhenFeatureIsEnabled()
    {
        $this->expectMethodReturnsTrue('hasSitemap');
    }

    public function testCanGenerateSitemapHelperReturnsTrueIfHydeHasBaseUrl()
    {
        config(['hyde.url' => 'foo']);

        $this->assertTrue(Features::hasSitemap());
    }

    public function testCanGenerateSitemapHelperReturnsFalseIfHydeDoesNotHaveBaseUrl()
    {
        config(['hyde.url' => '']);
        $this->assertFalse(Features::hasSitemap());
    }

    public function testCanGenerateSitemapHelperReturnsFalseIfSitemapsAreDisabledInConfig()
    {
        config(['hyde.url' => 'foo']);
        config(['hyde.generate_sitemap' => false]);
        $this->assertFalse(Features::hasSitemap());
    }

    public function testToArrayMethodReturnsMethodArray()
    {
        $array = (new Features)->toArray();
        $this->assertIsArray($array);
        $this->assertNotEmpty($array);
        foreach ($array as $feature => $enabled) {
            $this->assertIsString($feature);
            $this->assertIsBool($enabled);
            $this->assertStringStartsNotWith('has', $feature);
        }
    }

    public function testToArrayMethodContainsAllSettings()
    {
        $array = (new Features)->toArray();

        $this->assertArrayHasKey('html-pages', $array);
        $this->assertArrayHasKey('markdown-posts', $array);
        $this->assertArrayHasKey('blade-pages', $array);
        $this->assertArrayHasKey('markdown-pages', $array);
        $this->assertArrayHasKey('documentation-pages', $array);
        $this->assertArrayHasKey('darkmode', $array);
        $this->assertArrayHasKey('documentation-search', $array);
        $this->assertArrayHasKey('torchlight', $array);

        $this->assertCount(8, $array);
    }

    public function testFeaturesCanBeMocked()
    {
        Features::mock('darkmode', true);
        $this->assertTrue(Features::hasDarkmode());

        Features::mock('darkmode', false);
        $this->assertFalse(Features::hasDarkmode());
    }

    public function testMultipleFeaturesCanBeMocked()
    {
        Features::mock([
            'blade-pages' => true,
            'darkmode' => true,
        ]);

        $this->assertTrue(Features::hasBladePages());
        $this->assertTrue(Features::hasDarkmode());

        Features::mock([
            'blade-pages' => false,
            'darkmode' => false,
        ]);

        $this->assertFalse(Features::hasBladePages());
        $this->assertFalse(Features::hasDarkmode());
    }

    public function testGetEnabledUsesDefaultOptionsByDefault()
    {
        $default = $this->defaultOptions();

        $this->assertSame($default, Features::getFeatures());
    }

    public function testGetEnabledUsesDefaultOptionsWhenConfigIsEmpty()
    {
        config(['hyde' => []]);

        $default = $this->defaultOptions();

        $this->assertSame($default, Features::getFeatures());
    }

    public function testGetEnabledUsesConfiguredOptions()
    {
        $config = [
            Features::htmlPages(),
            Features::markdownPosts(),
            Features::bladePages(),
        ];
        $expected = [
            Features::htmlPages() => true,
            Features::markdownPosts() => true,
            Features::bladePages() => true,
            'markdown-pages' => false,
            'documentation-pages' => false,
            'darkmode' => false,
            'documentation-search' => false,
            'torchlight' => false,
        ];

        config(['hyde.features' => $config]);

        $this->assertSame($expected, Features::getFeatures());
    }

    public function testCannotUseArbitraryValuesInEnabledOptions()
    {
        $config = [
            Features::htmlPages(),
            Features::markdownPosts(),
            Features::bladePages(),
            'foo',
        ];

        config(['hyde.features' => $config]);

        $this->assertSame([
            'html-pages',
            'markdown-posts',
            'blade-pages',
        ], Hyde::features()->enabled());
    }

    protected function defaultOptions(): array
    {
        return [
            'html-pages' => true,
            'markdown-posts' => true,
            'blade-pages' => true,
            'markdown-pages' => true,
            'documentation-pages' => true,
            'darkmode' => true,
            'documentation-search' => true,
            'torchlight' => true,
        ];
    }

    protected function expectMethodReturnsFalse(string $method): void
    {
        $this->assertFalse(Features::$method(), "Method '$method' should return false when feature is not enabled");
    }

    protected function expectMethodReturnsTrue(string $method): void
    {
        $this->mockDefaultConfig();

        $this->assertTrue(Features::$method(), "Method '$method' should return true when feature is enabled");
    }

    protected function mockDefaultConfig(): void
    {
        self::mockConfig(['hyde' => self::$defaultConfig]);
    }
}
