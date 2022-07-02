<?php

namespace Hyde\Framework\Testing\Feature\Services;

use Hyde\Framework\Concerns\Markdown\HasConfigurableMarkdownFeatures;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Config;

/**
 * @covers \Hyde\Framework\Concerns\Markdown\HasConfigurableMarkdownFeatures
 */
class HasConfigurableMarkdownFeaturesTest extends TestCase
{
    use HasConfigurableMarkdownFeatures;

    private string $sourceModel;

    public function test_has_features_array()
    {
        $this->assertIsArray($this->features);
    }

    public function test_the_features_array_is_empty_by_default()
    {
        $this->assertEmpty($this->features);
    }

    public function test_features_can_be_added_to_the_array()
    {
        $this->addFeature('test');
        $this->assertContains('test', $this->features);
    }

    public function test_features_can_be_removed_from_the_array()
    {
        $this->addFeature('test');
        $this->removeFeature('test');
        $this->assertNotContains('test', $this->features);
    }

    public function test_method_chaining_can_be_used_to_programmatically_add_features_to_the_array()
    {
        $this->addFeature('test')->addFeature('test2');
        $this->assertContains('test', $this->features);
        $this->assertContains('test2', $this->features);
    }

    public function test_method_chaining_can_be_used_to_programmatically_remove_features_from_the_array()
    {
        $this->addFeature('test')->addFeature('test2')->removeFeature('test');
        $this->assertNotContains('test', $this->features);
        $this->assertContains('test2', $this->features);
    }

    public function test_method_with_table_of_contents_method_chain_adds_the_table_of_contents_feature()
    {
        $this->withTableOfContents();
        $this->assertContains('table-of-contents', $this->features);
    }

    public function test_method_with_permalinks_method_chain_adds_the_permalinks_feature()
    {
        $this->withPermalinks();
        $this->assertContains('permalinks', $this->features);
    }

    public function test_has_feature_returns_true_if_the_feature_is_in_the_array()
    {
        $this->addFeature('test');
        $this->assertTrue($this->hasFeature('test'));
    }

    public function test_has_feature_returns_false_if_the_feature_is_not_in_the_array()
    {
        $this->assertFalse($this->hasFeature('test'));
    }

    public function test_method_can_enable_permalinks_returns_true_if_the_permalinks_feature_is_in_the_array()
    {
        $this->addFeature('permalinks');
        $this->assertTrue($this->canEnablePermalinks());
    }

    public function test_method_can_enable_permalinks_is_automatically_for_documentation_pages()
    {
        Config::set('docs.table_of_contents.enabled', true);
        $this->sourceModel = DocumentationPage::class;

        $this->assertTrue($this->canEnablePermalinks());
    }

    public function test_method_can_enable_permalinks_returns_false_if_the_permalinks_feature_is_not_in_the_array()
    {
        $this->assertFalse($this->canEnablePermalinks());
    }

    public function test_method_can_enable_torchlight_returns_true_if_the_torchlight_feature_is_in_the_array()
    {
        $this->addFeature('torchlight');
        $this->assertTrue($this->canEnableTorchlight());
    }

    public function test_method_can_enable_torchlight_returns_false_if_the_torchlight_feature_is_not_in_the_array()
    {
        $this->assertFalse($this->canEnableTorchlight());
    }
}
