<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Enums\Feature;
use Hyde\Foundation\HydeCoreExtension;
use Hyde\Framework\Features\TextGenerators\LlmsTxtGenerator;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\UnitTestCase;
use ReflectionClass;

/**
 * @see \Hyde\Framework\Testing\Unit\HydeConfigFilesAreMatchingTest
 */
class ConfigFileTest extends UnitTestCase
{
    protected static bool $needsKernel = true;
    protected static bool $needsConfig = true;

    public function testDefaultOutputDirectoryValueMatchesDeclaredValue()
    {
        expect($this->getConfig('output_directory'))->toBe(Hyde::getOutputDirectory());
    }

    public function testDefaultMediaDirectoryValueMatchesDeclaredValue()
    {
        expect($this->getConfig('media_directory'))->toBe(Hyde::getMediaDirectory());
    }

    public function testDefaultSourceRootValueMatchesDeclaredValue()
    {
        expect($this->getConfig('source_root'))->toBe(Hyde::getSourceRoot());
    }

    public function testDefaultSourceDirectoriesValuesMatchDeclaredValues()
    {
        expect($this->getConfig('source_directories'))->toBe([
            HtmlPage::class => '_pages',
            BladePage::class => '_pages',
            MarkdownPage::class => '_pages',
            MarkdownPost::class => '_posts',
            DocumentationPage::class => '_docs',
        ]);
    }

    public function testDefaultSourceDirectoriesValuesCoverAllCoreExtensionClasses()
    {
        expect($this->getConfig('source_directories'))->toBe(collect(HydeCoreExtension::getPageClasses())
            ->mapWithKeys(fn ($pageClass) => [$pageClass => $pageClass::sourceDirectory()])
            ->toArray()
        );
    }

    public function testDefaultOutputDirectoriesValuesMatchDeclaredValues()
    {
        expect($this->getConfig('output_directories'))->toBe([
            HtmlPage::class => '',
            BladePage::class => '',
            MarkdownPage::class => '',
            MarkdownPost::class => 'posts',
            DocumentationPage::class => 'docs',
        ]);
    }

    public function testDefaultOutputDirectoriesValuesCoverAllCoreExtensionClasses()
    {
        expect($this->getConfig('output_directories'))->toBe(collect(HydeCoreExtension::getPageClasses())
            ->mapWithKeys(fn ($pageClass) => [$pageClass => $pageClass::outputDirectory()])
            ->toArray()
        );
    }

    public function testDefaultFeaturesArrayMatchesDefaultFeatures()
    {
        expect($this->getConfig('features'))
            ->toBe(Feature::cases());
    }

    public function testDefaultLlmsSectionsValuesMatchDeclaredValues()
    {
        expect($this->getConfig('llms')['sections'])->toBe([
            HtmlPage::class => 'Pages',
            BladePage::class => 'Pages',
            MarkdownPage::class => 'Pages',
            DocumentationPage::class => 'Documentation',
            MarkdownPost::class => 'Blog Posts',
        ]);
    }

    public function testDefaultLlmsSectionsMatchTheGeneratorFallbackUsedWhenTheOptionIsAbsent()
    {
        expect($this->getConfig('llms')['sections'])
            ->toBe((new ReflectionClass(LlmsTxtGenerator::class))->getConstant('DEFAULT_SECTIONS'));
    }

    public function testDefaultLlmsSectionsCoverAllCoreExtensionClasses()
    {
        expect(array_keys($this->getConfig('llms')['sections']))
            ->toEqualCanonicalizing(HydeCoreExtension::getPageClasses());
    }

    protected function getConfig(string $option): mixed
    {
        return (require Hyde::vendorPath('config/hyde.php'))[$option];
    }
}
