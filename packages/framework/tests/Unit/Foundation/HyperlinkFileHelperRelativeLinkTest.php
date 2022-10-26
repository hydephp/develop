<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Foundation;

use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Foundation\Hyperlinks::relativeLink
 */
class HyperlinkFileHelperRelativeLinkTest extends TestCase
{
    public function test_helper_returns_string_as_is_if_current_is_not_set()
    {
        $this->assertEquals('foo/bar.html', Hyde::relativeLink('foo/bar.html'));
    }

    public function test_helper_injects_proper_number_of_doubles_slash()
    {
        $this->mockCurrentPage('foo/bar.html');
        $this->assertEquals('../foo.html', Hyde::relativeLink('foo.html'));
    }

    public function test_helper_injects_proper_number_of_doubles_slash_for_deeply_nested_paths()
    {
        $this->mockCurrentPage('foo/bar/baz/qux.html');
        $this->assertEquals('../../../foo.html', Hyde::relativeLink('foo.html'));
    }

    public function test_helper_handles_destination_without_file_extension()
    {
        $this->mockCurrentPage('foo/bar.html');
        $this->assertEquals('../foo', Hyde::relativeLink('foo'));
    }

    public function test_helper_handles_current_without_file_extension()
    {
        $this->mockCurrentPage('foo/bar');
        $this->assertEquals('../foo.html', Hyde::relativeLink('foo.html'));
    }

    public function test_helper_handles_case_without_any_file_extensions()
    {
        $this->mockCurrentPage('foo/bar');
        $this->assertEquals('../foo', Hyde::relativeLink('foo'));
    }

    public function test_helper_handles_case_with_mixed_file_extensions()
    {
        $this->mockCurrentPage('foo/bar.md');
        $this->assertEquals('../foo.md', Hyde::relativeLink('foo.md'));
        $this->mockCurrentPage('foo/bar.txt');
        $this->assertEquals('../foo.txt', Hyde::relativeLink('foo.txt'));
    }

    public function test_helper_handles_different_file_extensions()
    {
        $this->mockCurrentPage('foo/bar');
        $this->assertEquals('../foo.png', Hyde::relativeLink('foo.png'));
        $this->assertEquals('../foo.css', Hyde::relativeLink('foo.css'));
        $this->assertEquals('../foo.js', Hyde::relativeLink('foo.js'));
    }

    public function test_helper_returns_pretty_url_if_enabled_and_destination_is_a_html_file()
    {
        config(['site.pretty_urls' => true]);
        $this->mockCurrentPage('foo/bar.html');
        $this->assertEquals('../foo', Hyde::relativeLink('foo.html'));
    }

    public function test_helper_method_does_not_require_current_path_to_be_html_to_use_pretty_urls()
    {
        config(['site.pretty_urls' => true]);
        $this->mockCurrentPage('foo/bar');
        $this->assertEquals('../foo', Hyde::relativeLink('foo.html'));
    }

    public function test_helper_returns_does_not_return_pretty_url_if_when_enabled_but_and_destination_is_not_a_html_file()
    {
        config(['site.pretty_urls' => true]);
        $this->mockCurrentPage('foo/bar.html');
        $this->assertEquals('../foo.png', Hyde::relativeLink('foo.png'));
    }

    public function test_helper_rewrites_index_when_using_pretty_urls()
    {
        config(['site.pretty_urls' => true]);
        $this->mockCurrentPage('foo.html');
        $this->assertEquals('/', Hyde::relativeLink('index.html'));
        $this->mockCurrentPage('foo/bar.html');
        $this->assertEquals('../', Hyde::relativeLink('index.html'));
        $this->mockCurrentPage('foo/bar/baz.html');
        $this->assertEquals('../../', Hyde::relativeLink('index.html'));
    }

    public function test_helper_does_not_rewrite_index_when_not_using_pretty_urls()
    {
        config(['site.pretty_urls' => false]);
        $this->mockCurrentPage('foo.html');
        $this->assertEquals('index.html', Hyde::relativeLink('index.html'));
        $this->mockCurrentPage('foo/bar.html');
        $this->assertEquals('../index.html', Hyde::relativeLink('index.html'));
        $this->mockCurrentPage('foo/bar/baz.html');
        $this->assertEquals('../../index.html', Hyde::relativeLink('index.html'));
    }

    public function test_helper_rewrites_documentation_page_index_when_using_pretty_urls()
    {
        config(['site.pretty_urls' => true]);
        $this->mockCurrentPage('foo.html');
        $this->assertEquals('docs/', Hyde::relativeLink('docs/index.html'));
        $this->mockCurrentPage('docs.html');
        $this->assertEquals('docs/', Hyde::relativeLink('docs/index.html'));
        $this->mockCurrentPage('foo/bar.html');
        $this->assertEquals('../docs/', Hyde::relativeLink('docs/index.html'));
        $this->mockCurrentPage('docs/foo.html');
        $this->assertEquals('../docs/', Hyde::relativeLink('docs/index.html'));
    }

    public function test_helper_does_not_rewrite_documentation_page_index_when_not_using_pretty_urls()
    {
        config(['site.pretty_urls' => false]);
        $this->mockCurrentPage('foo.html');
        $this->assertEquals('docs/index.html', Hyde::relativeLink('docs/index.html'));
        $this->mockCurrentPage('docs.html');
        $this->assertEquals('docs/index.html', Hyde::relativeLink('docs/index.html'));
        $this->mockCurrentPage('foo/bar.html');
        $this->assertEquals('../docs/index.html', Hyde::relativeLink('docs/index.html'));
        $this->mockCurrentPage('docs/foo.html');
        $this->assertEquals('../docs/index.html', Hyde::relativeLink('docs/index.html'));
    }

    public function test_helper_does_not_rewrite_already_processed_links()
    {
        $this->assertEquals('../foo', Hyde::relativeLink('../foo'));
    }
}
