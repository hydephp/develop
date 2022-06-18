<?php

namespace Hyde\Testing\Framework\Unit;

use Hyde\Framework\Hyde;
use Hyde\Testing\TestCase;

/**
 * Class FileHelperRelativeLinkTest.
 *
 * @covers \Hyde\Framework\Concerns\Internal\FileHelpers
 */
class FileHelperRelativeLinkTest extends TestCase
{
    // Test helper returns string as is when $current is not set
    public function test_helper_returns_string_as_is_if_current_is_not_set()
    {
        $this->assertEquals('foo/bar.html', Hyde::relativeLink('foo/bar.html'));
    }

    // Test helper injects the proper number of `../`
    public function test_helper_injects_proper_number_of_doubles_slash()
    {
        $this->assertEquals('../foo.html', Hyde::relativeLink('foo.html', 'foo/bar.html'));
    }

    // Test helper injects the proper number of `../` for deeply nested $current paths
    public function test_helper_injects_proper_number_of_doubles_slash_for_deeply_nested_paths()
    {
        $this->assertEquals('../../../foo.html', Hyde::relativeLink('foo.html', 'foo/bar/baz/qux.html'));
    }

    // Test helper handles destination without file extension
    public function test_helper_handles_destination_without_file_extension()
    {
        $this->assertEquals('../foo', Hyde::relativeLink('foo', 'foo/bar.html'));
    }

    // Test helper handles $current without file extension
    public function test_helper_handles_current_without_file_extension()
    {
        $this->assertEquals('../foo.html', Hyde::relativeLink('foo.html', 'foo/bar'));
    }

    // Test helper handles case without any file extensions
    public function test_helper_handles_case_without_any_file_extensions()
    {
        $this->assertEquals('../foo', Hyde::relativeLink('foo', 'foo/bar'));
    }

    // Test helper handles case with mixed file extensions
    public function test_helper_handles_case_with_mixed_file_extensions()
    {
        $this->assertEquals('../foo.md', Hyde::relativeLink('foo.md', 'foo/bar.md'));
        $this->assertEquals('../foo.txt', Hyde::relativeLink('foo.txt', 'foo/bar.txt'));
    }

    // Test helper handles different file extensions
    public function test_helper_handles_different_file_extensions()
    {
        $this->assertEquals('../foo.png', Hyde::relativeLink('foo.png', 'foo/bar'));
        $this->assertEquals('../foo.css', Hyde::relativeLink('foo.css', 'foo/bar'));
        $this->assertEquals('../foo.js', Hyde::relativeLink('foo.js', 'foo/bar'));
    }

    // Test helper returns pretty URL if enabled and destination is a HTML file
    public function test_helper_returns_pretty_url_if_enabled_and_destination_is_a_html_file()
    {
        config(['hyde.pretty_urls' => true]);
        $this->assertEquals('../foo', Hyde::relativeLink('foo.html', 'foo/bar.html'));
    }

    // Test helper method does not require current path to be HTML to use pretty URLs
    public function test_helper_method_does_not_require_current_path_to_be_html_to_use_pretty_urls()
    {
        config(['hyde.pretty_urls' => true]);
        $this->assertEquals('../foo', Hyde::relativeLink('foo.html', 'foo/bar'));
    }

    // Test helper returns does not return pretty URL if when enabled but and destination is not a HTML file
    public function test_helper_returns_does_not_return_pretty_url_if_when_enabled_but_and_destination_is_not_a_html_file()
    {
        config(['hyde.pretty_urls' => true]);
        $this->assertEquals('../foo.png', Hyde::relativeLink('foo.png', 'foo/bar.html'));
    }

    public function test_helper_rewrites_index_when_using_pretty_urls()
    {
        config(['hyde.pretty_urls' => true]);
        $this->assertEquals('/', Hyde::relativeLink('index.html', 'foo.html'));
        $this->assertEquals('../', Hyde::relativeLink('index.html', 'foo/bar.html'));
        $this->assertEquals('../../', Hyde::relativeLink('index.html', 'foo/bar/baz.html'));
    }

    public function test_helper_does_not_rewrite_index_when_not_using_pretty_urls()
    {
        config(['hyde.pretty_urls' => false]);
        $this->assertEquals('index.html', Hyde::relativeLink('index.html', 'foo.html'));
        $this->assertEquals('../index.html', Hyde::relativeLink('index.html', 'foo/bar.html'));
        $this->assertEquals('../../index.html', Hyde::relativeLink('index.html', 'foo/bar/baz.html'));
    }

    public function test_helper_rewrites_documentation_page_index_when_using_pretty_urls()
    {
        config(['hyde.pretty_urls' => true]);
        $this->assertEquals('docs/', Hyde::relativeLink('docs/index.html', 'foo.html'));
        $this->assertEquals('docs/', Hyde::relativeLink('docs/index.html', 'docs.html'));
        $this->assertEquals('../docs/', Hyde::relativeLink('docs/index.html', 'foo/bar.html'));
        $this->assertEquals('../docs/', Hyde::relativeLink('docs/index.html', 'docs/foo.html'));
    }
}
