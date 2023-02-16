<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Facades\Features;
use Hyde\Facades\Site;
use Hyde\Foundation\HydeCoreExtension;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\TestCase;
use ReflectionClass;

/**
 * @see \Hyde\Framework\Testing\Unit\HydeConfigFilesAreMatchingTest
 */
class ConfigFileTest extends TestCase
{
    public function default_output_directory_value_matches_declared_value()
    {
        expect($this->getConfig('output_directory'))->toBe(Site::getOutputDirectory());
    }

    public function default_media_directory_value_matches_declared_value()
    {
        expect($this->getConfig('media_directory'))->toBe(Hyde::getMediaDirectory());
    }

    public function default_source_root_value_matches_declared_value()
    {
        expect($this->getConfig('source_root'))->toBe(Hyde::getSourceRoot());
    }

    public function default_source_directories_values_match_declared_values()
    {
        expect($this->getConfig('source_directories'))->toBe([
            HtmlPage::class => '_pages',
            BladePage::class => '_pages',
            MarkdownPage::class => '_pages',
            MarkdownPost::class => '_posts',
            DocumentationPage::class => '_docs',
        ]);
    }

    public function default_source_directories_values_cover_all_core_extension_classes()
    {
        expect($this->getConfig('source_directories'))->toBe(collect(HydeCoreExtension::getPageClasses())
            ->mapWithKeys(fn ($pageClass) => [$pageClass => $pageClass::$sourceDirectory])
            ->toArray()
        );
    }

    public function default_output_directories_values_match_declared_values()
    {
        expect($this->getConfig('output_directories'))->toBe([
            HtmlPage::class => '',
            BladePage::class => '',
            MarkdownPage::class => '',
            MarkdownPost::class => 'posts',
            DocumentationPage::class => 'docs',
        ]);
    }

    public function default_output_directories_values_cover_all_core_extension_classes()
    {
        expect($this->getConfig('output_directories'))->toBe(collect(HydeCoreExtension::getPageClasses())
            ->mapWithKeys(fn ($pageClass) => [$pageClass => $pageClass::$outputDirectory])
            ->toArray()
        );
    }

    public function default_features_array_matches_default_features()
    {
        expect($this->getConfig('features'))
            ->toBe(new ReflectionClass(Features::class))->getMethod('getDefaultOptions')->invoke(null);
    }

    protected function getConfig(string $option): mixed
    {
        return (require Hyde::vendorPath('config/hyde.php'))[$option];
    }
}
