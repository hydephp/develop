<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Exceptions\FileNotFoundException;
use Hyde\Hyde;
use Hyde\Markdown\Models\Markdown;
use Hyde\Pages\BladePage;
use Hyde\Pages\Concerns\BaseMarkdownPage;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Support\Models\Route;
use Hyde\Testing\TestCase;

/**
 * Test the HydePage class.
 *
 * Since the class is abstract, we can't test it directly,
 * so we will use the MarkdownPage class as a proxy,
 * since it's the simplest implementation.
 *
 * @covers \Hyde\Pages\Concerns\HydePage
 * @covers \Hyde\Pages\Concerns\BaseMarkdownPage
 * @covers \Hyde\Framework\Factories\Concerns\HasFactory
 * @covers \Hyde\Framework\Factories\NavigationDataFactory
 * @covers \Hyde\Framework\Factories\FeaturedImageFactory
 * @covers \Hyde\Framework\Factories\HydePageDataFactory
 * @covers \Hyde\Framework\Factories\BlogPostDataFactory
 * @covers \Hyde\Framework\Concerns\InteractsWithFrontMatter
 */
class HydePageTest extends TestCase
{
    // Section: Baseline tests

    public function testSourceDirectory()
    {
        $this->assertSame(
            'source',
            TestPage::sourceDirectory()
        );
    }

    public function testOutputDirectory()
    {
        $this->assertSame(
            'output',
            TestPage::outputDirectory()
        );
    }

    public function testFileExtension()
    {
        $this->assertSame(
            '.md',
            TestPage::fileExtension()
        );
    }

    public function testSourcePath()
    {
        $this->assertSame(
            'source/hello-world.md',
            TestPage::sourcePath('hello-world')
        );
    }

    public function testOutputPath()
    {
        $this->assertSame(
            'output/hello-world.html',
            TestPage::outputPath('hello-world')
        );
    }

    public function testPath()
    {
        $this->assertSame(
            Hyde::path('source/hello-world'),
            TestPage::path('hello-world')
        );
    }

    public function testGetSourcePath()
    {
        $this->assertSame(
            'source/hello-world.md',
            (new TestPage('hello-world'))->getSourcePath()
        );
    }

    public function testGetOutputPath()
    {
        $this->assertSame(
            'output/hello-world.html',
            (new TestPage('hello-world'))->getOutputPath()
        );
    }

    public function testGetLink()
    {
        $this->assertSame(
            'output/hello-world.html',
            (new TestPage('hello-world'))->getLink()
        );
    }

    public function testMake()
    {
        $this->assertEquals(TestPage::make(), new TestPage());
    }

    public function testMakeWithData()
    {
        $this->assertEquals(
            TestPage::make('foo', ['foo' => 'bar']),
            new TestPage('foo', ['foo' => 'bar'])
        );
    }

    public function testShowInNavigation()
    {
        $this->assertTrue((new BladePage('foo'))->showInNavigation());
        $this->assertTrue((new MarkdownPage())->showInNavigation());
        $this->assertTrue((new DocumentationPage())->showInNavigation());
        $this->assertFalse((new MarkdownPost())->showInNavigation());
        $this->assertTrue((new HtmlPage())->showInNavigation());
    }

    public function testNavigationMenuPriority()
    {
        $this->assertSame(999, (new BladePage('foo'))->navigationMenuPriority());
        $this->assertSame(999, (new MarkdownPage())->navigationMenuPriority());
        $this->assertSame(999, (new DocumentationPage())->navigationMenuPriority());
        $this->assertSame(10, (new MarkdownPost())->navigationMenuPriority());
        $this->assertSame(999, (new HtmlPage())->navigationMenuPriority());
    }

    public function testNavigationMenuLabel()
    {
        $this->assertSame('Foo', (new BladePage('foo'))->navigationMenuLabel());
        $this->assertSame('Foo', (new MarkdownPage('foo'))->navigationMenuLabel());
        $this->assertSame('Foo', (new MarkdownPost('foo'))->navigationMenuLabel());
        $this->assertSame('Foo', (new DocumentationPage('foo'))->navigationMenuLabel());
        $this->assertSame('Foo', (new HtmlPage('foo'))->navigationMenuLabel());
    }

    public function testNavigationMenuGroup()
    {
        $this->assertNull((new BladePage('foo'))->navigationMenuGroup());
        $this->assertNull((new MarkdownPage())->navigationMenuGroup());
        $this->assertNull((new MarkdownPost())->navigationMenuGroup());
        $this->assertNull((new HtmlPage())->navigationMenuGroup());
        $this->assertSame('other', (new DocumentationPage())->navigationMenuGroup());
        $this->assertSame('foo', DocumentationPage::make(matter: ['navigation' => ['group' => 'foo']])->navigationMenuGroup());
    }

    // Section: In-depth tests

    public function test_get_source_directory_returns_static_property()
    {
        MarkdownPage::$sourceDirectory = 'foo';
        $this->assertEquals('foo', MarkdownPage::sourceDirectory());
        $this->resetDirectoryConfiguration();
    }

    public function test_get_source_directory_trims_trailing_slashes()
    {
        MarkdownPage::$sourceDirectory = '/foo/\\';
        $this->assertEquals('foo', MarkdownPage::sourceDirectory());
        $this->resetDirectoryConfiguration();
    }

    public function test_get_output_directory_returns_static_property()
    {
        MarkdownPage::$outputDirectory = 'foo';
        $this->assertEquals('foo', MarkdownPage::outputDirectory());
        $this->resetDirectoryConfiguration();
    }

    public function test_get_output_directory_trims_trailing_slashes()
    {
        MarkdownPage::$outputDirectory = '/foo/\\';
        $this->assertEquals('foo', MarkdownPage::outputDirectory());
        $this->resetDirectoryConfiguration();
    }

    public function test_get_file_extension_returns_static_property()
    {
        MarkdownPage::$fileExtension = '.foo';
        $this->assertEquals('.foo', MarkdownPage::fileExtension());
        $this->resetDirectoryConfiguration();
    }

    public function test_get_file_extension_forces_leading_period()
    {
        MarkdownPage::$fileExtension = 'foo';
        $this->assertEquals('.foo', MarkdownPage::fileExtension());
        $this->resetDirectoryConfiguration();
    }

    public function test_get_identifier_returns_identifier_property()
    {
        $page = new MarkdownPage('foo');
        $this->assertEquals('foo', $page->getIdentifier());
    }

    public function test_static_get_method_returns_discovered_page()
    {
        $this->assertEquals(BladePage::parse('index'), BladePage::get('index'));
    }

    public function test_static_get_method_throws_exception_if_page_not_found()
    {
        $this->expectException(FileNotFoundException::class);
        BladePage::get('foo');
    }

    public function test_parse_parses_supplied_slug_into_a_page_model()
    {
        Hyde::touch(('_pages/foo.md'));

        $this->assertInstanceOf(MarkdownPage::class, $page = MarkdownPage::parse('foo'));
        $this->assertEquals('foo', $page->identifier);

        unlink(Hyde::path('_pages/foo.md'));
    }

    public function test_files_returns_array_of_source_files()
    {
        Hyde::touch(('_pages/foo.md'));
        $this->assertEquals(['foo'], MarkdownPage::files());
        unlink(Hyde::path('_pages/foo.md'));
    }

    public function test_all_returns_collection_of_all_parsed_source_files_from_page_index()
    {
        Hyde::touch(('_pages/foo.md'));
        $this->assertEquals(
            Hyde::pages()->getPages(MarkdownPage::class),
            MarkdownPage::all()
        );
        $this->assertEquals(
            ['_pages/foo.md' => tap(new MarkdownPage('foo'), function ($page) {
                $page->title = 'Foo';
            })],
            MarkdownPage::all()->toArray()
        );
        unlink(Hyde::path('_pages/foo.md'));
    }

    public function test_qualify_basename_properly_expands_basename_for_the_model()
    {
        $this->assertEquals('_pages/foo.md', MarkdownPage::sourcePath('foo'));
    }

    public function test_qualify_basename_trims_slashes_from_input()
    {
        $this->assertEquals('_pages/foo.md', MarkdownPage::sourcePath('/foo/\\'));
    }

    public function test_qualify_basename_uses_the_static_properties()
    {
        MarkdownPage::$sourceDirectory = 'foo';
        MarkdownPage::$fileExtension = 'txt';
        $this->assertEquals('foo/bar.txt', MarkdownPage::sourcePath('bar'));
        $this->resetDirectoryConfiguration();
    }

    public function test_path_returns_absolute_path_to_source_directory_when_no_parameter_is_supplied()
    {
        $this->assertSame(
            Hyde::path('source'), TestPage::path()
        );
    }

    public function test_path_returns_absolute_path_to_file_in_source_directory_when_parameter_is_supplied()
    {
        $this->assertSame(
            Hyde::path('source/foo.md'), TestPage::path('foo.md')
        );
    }

    public function test_path_method_removes_trailing_slashes()
    {
        $this->assertSame(
            Hyde::path('source/foo.md'), TestPage::path('/foo.md/')
        );
    }

    public function test_get_output_location_returns_the_file_output_path_for_the_supplied_basename()
    {
        $this->assertEquals('foo.html', MarkdownPage::outputPath('foo'));
    }

    public function test_get_output_location_returns_the_configured_location()
    {
        MarkdownPage::$outputDirectory = 'foo';
        $this->assertEquals('foo/bar.html', MarkdownPage::outputPath('bar'));
        $this->resetDirectoryConfiguration();
    }

    public function test_get_output_location_trims_trailing_slashes_from_directory_setting()
    {
        MarkdownPage::$outputDirectory = '/foo/\\';
        $this->assertEquals('foo/bar.html', MarkdownPage::outputPath('bar'));
        $this->resetDirectoryConfiguration();
    }

    public function test_get_output_location_trims_trailing_slashes_from_basename()
    {
        $this->assertEquals('foo.html', MarkdownPage::outputPath('/foo/\\'));
    }

    public function test_get_current_page_path_returns_output_directory_and_basename()
    {
        $page = new MarkdownPage('foo');
        $this->assertEquals('foo', $page->getRouteKey());
    }

    public function test_get_current_page_path_returns_output_directory_and_basename_for_configured_directory()
    {
        MarkdownPage::$outputDirectory = 'foo';
        $page = new MarkdownPage('bar');
        $this->assertEquals('foo/bar', $page->getRouteKey());
        $this->resetDirectoryConfiguration();
    }

    public function test_get_current_page_path_trims_trailing_slashes_from_directory_setting()
    {
        MarkdownPage::$outputDirectory = '/foo/\\';
        $page = new MarkdownPage('bar');
        $this->assertEquals('foo/bar', $page->getRouteKey());
        $this->resetDirectoryConfiguration();
    }

    public function test_get_output_path_returns_current_page_path_with_html_extension_appended()
    {
        $page = new MarkdownPage('foo');
        $this->assertEquals('foo.html', $page->getOutputPath());
    }

    public function test_get_source_path_returns_qualified_basename()
    {
        $this->assertEquals(
            MarkdownPage::sourcePath('foo'),
            (new MarkdownPage('foo'))->getSourcePath()
        );
    }

    public function test_markdown_page_implements_page_contract()
    {
        $this->assertInstanceOf(HydePage::class, new MarkdownPage());
    }

    public function test_all_page_models_extend_abstract_page()
    {
        $pages = [
            MarkdownPage::class,
            MarkdownPost::class,
            DocumentationPage::class,
        ];

        foreach ($pages as $page) {
            $this->assertInstanceOf(HydePage::class, new $page());
        }

        $this->assertInstanceOf(HydePage::class, new BladePage('foo'));
    }

    public function test_all_page_models_have_configured_source_directory()
    {
        $pages = [
            BladePage::class => '_pages',
            MarkdownPage::class => '_pages',
            MarkdownPost::class => '_posts',
            DocumentationPage::class => '_docs',
        ];

        foreach ($pages as $page => $expected) {
            $this->assertEquals($expected, $page::$sourceDirectory);
        }
    }

    public function test_all_page_models_have_configured_output_directory()
    {
        $pages = [
            BladePage::class => '',
            MarkdownPage::class => '',
            MarkdownPost::class => 'posts',
            DocumentationPage::class => 'docs',
        ];

        foreach ($pages as $page => $expected) {
            $this->assertEquals($expected, $page::$outputDirectory);
        }
    }

    public function test_all_page_models_have_configured_file_extension()
    {
        $pages = [
            BladePage::class => '.blade.php',
            MarkdownPage::class => '.md',
            MarkdownPost::class => '.md',
            DocumentationPage::class => '.md',
        ];

        foreach ($pages as $page => $expected) {
            $this->assertEquals($expected, $page::$fileExtension);
        }
    }

    public function test_abstract_markdown_page_extends_abstract_page()
    {
        $this->assertInstanceOf(HydePage::class, $this->mock(BaseMarkdownPage::class));
    }

    public function test_abstract_markdown_page_implements_page_contract()
    {
        $this->assertInstanceOf(HydePage::class, $this->mock(BaseMarkdownPage::class));
    }

    public function test_abstract_markdown_page_has_markdown_document_property()
    {
        $this->assertClassHasAttribute('markdown', BaseMarkdownPage::class);
    }

    public function test_abstract_markdown_page_has_file_extension_property()
    {
        $this->assertClassHasAttribute('fileExtension', BaseMarkdownPage::class);
    }

    public function test_abstract_markdown_page_file_extension_property_is_set_to_md()
    {
        $this->assertEquals('.md', BaseMarkdownPage::$fileExtension);
    }

    public function test_abstract_markdown_page_constructor_arguments_are_optional()
    {
        $page = $this->mock(BaseMarkdownPage::class);
        $this->assertInstanceOf(BaseMarkdownPage::class, $page);
    }

    public function test_abstract_markdown_page_constructor_assigns_markdown_document_property_if_set()
    {
        $markdown = new Markdown();
        $page = new MarkdownPage(markdown: $markdown);
        $this->assertSame($markdown, $page->markdown);
    }

    public function test_abstract_markdown_page_constructor_creates_new_markdown_document_if_no_markdown_document_is_set()
    {
        $page = new MarkdownPage();
        $this->assertInstanceOf(Markdown::class, $page->markdown);
    }

    public function test_abstract_markdown_page_markdown_helper_returns_the_markdown_document_instance()
    {
        $page = new MarkdownPage();
        $this->assertSame($page->markdown, $page->markdown());
    }

    public function test_abstract_markdown_page_markdown_helper_returns_the_configured_markdown_document_instance()
    {
        $markdown = new Markdown();
        $page = new MarkdownPage(markdown: $markdown);
        $this->assertSame($markdown, $page->markdown());
    }

    public function test_abstract_markdown_page_make_helper_constructs_dynamic_title_automatically()
    {
        $page = MarkdownPage::make('', ['title' => 'Foo']);
        $this->assertEquals('Foo', $page->title);
    }

    public function test_markdown_based_pages_extend_abstract_markdown_page()
    {
        $pages = [
            MarkdownPage::class,
            MarkdownPost::class,
            DocumentationPage::class,
        ];

        foreach ($pages as $page) {
            $this->assertInstanceOf(BaseMarkdownPage::class, new $page());
        }
    }

    public function test_blade_pages_do_not_extend_abstract_markdown_page()
    {
        $this->assertNotInstanceOf(BaseMarkdownPage::class, new BladePage('foo'));
    }

    public function test_get_route_returns_page_route()
    {
        $page = new MarkdownPage();
        $this->assertEquals(new Route($page), $page->getRoute());
    }

    public function test_get_route_returns_the_route_object_from_the_router_index()
    {
        $this->file('_pages/foo.md');
        $page = MarkdownPage::parse('foo');
        $this->assertSame(\Hyde\Facades\Route::get('foo'), $page->getRoute());
    }

    public function test_html_title_returns_site_name_plus_page_title()
    {
        $this->assertEquals('HydePHP - Foo', MarkdownPage::make('', ['title' => 'Foo'])->htmlTitle());
    }

    public function test_html_title_uses_configured_site_name()
    {
        config(['site.name' => 'Foo Bar']);
        $this->assertEquals('Foo Bar - Foo', (new MarkdownPage('Foo'))->htmlTitle());
    }

    public function test_body_helper_returns_markdown_document_body_in_markdown_pages()
    {
        $page = new MarkdownPage(markdown: new Markdown(body: '# Foo'));
        $this->assertEquals('# Foo', $page->markdown->body());
    }

    public function test_show_in_navigation_returns_false_for_markdown_post()
    {
        $page = MarkdownPost::make();

        $this->assertFalse($page->showInNavigation());
    }

    public function test_show_in_navigation_returns_true_for_documentation_page_if_slug_is_index()
    {
        $page = DocumentationPage::make('index');

        $this->assertTrue($page->showInNavigation());
    }

    public function test_show_in_navigation_returns_true_for_documentation_page_if_slug_is_not_index()
    {
        $page = DocumentationPage::make('not-index');

        $this->assertTrue($page->showInNavigation());
    }

    public function test_show_in_navigation_returns_false_for_abstract_markdown_page_if_matter_navigation_hidden_is_true()
    {
        $page = MarkdownPage::make('foo', ['navigation.hidden' => true]);

        $this->assertFalse($page->showInNavigation());
    }

    public function test_show_in_navigation_returns_true_for_abstract_markdown_page_if_matter_navigation_visible_is_true()
    {
        $page = MarkdownPage::make('foo', ['navigation.visible' => true]);

        $this->assertTrue($page->showInNavigation());
    }

    public function test_show_in_navigation_returns_true_for_abstract_markdown_page_if_matter_navigation_hidden_is_false()
    {
        $page = MarkdownPage::make('foo', ['navigation.hidden' => false]);

        $this->assertTrue($page->showInNavigation());
    }

    public function test_show_in_navigation_returns_true_for_abstract_markdown_page_if_matter_navigation_hidden_is_not_set()
    {
        $page = MarkdownPage::make('foo', ['navigation.hidden' => null]);

        $this->assertTrue($page->showInNavigation());
    }

    public function test_show_in_navigation_returns_false_if_slug_is_present_in_config_hyde_navigation_exclude()
    {
        $page = MarkdownPage::make('foo');
        $this->assertTrue($page->showInNavigation());

        config(['hyde.navigation.exclude' => ['foo']]);
        $page = MarkdownPage::make('foo');
        $this->assertFalse($page->showInNavigation());
    }

    public function test_show_in_navigation_returns_false_if_slug_is_404()
    {
        $page = MarkdownPage::make('404');
        $this->assertFalse($page->showInNavigation());
    }

    public function test_show_in_navigation_defaults_to_true_if_all_checks_pass()
    {
        $page = MarkdownPage::make('foo');
        $this->assertTrue($page->showInNavigation());
    }

    public function test_navigation_menu_priority_returns_front_matter_value_of_navigation_priority_if_abstract_markdown_page_and_not_null()
    {
        $page = MarkdownPage::make('foo', ['navigation.priority' => 1]);
        $this->assertEquals(1, $page->navigationMenuPriority());
    }

    public function test_navigation_menu_priority_can_be_set_using_order_property()
    {
        $page = MarkdownPage::make('foo', ['navigation.order' => 1]);
        $this->assertEquals(1, $page->navigationMenuPriority());
    }

    public function test_navigation_menu_priority_returns_specified_config_value_if_slug_exists_in_config_hyde_navigation_order()
    {
        $page = MarkdownPage::make('foo');
        $this->assertEquals(999, $page->navigationMenuPriority());

        config(['hyde.navigation.order' => ['foo' => 1]]);
        $page = MarkdownPage::make('foo');
        $this->assertEquals(1, $page->navigationMenuPriority());
    }

    public function test_navigation_menu_priority_gives_precedence_to_front_matter_over_config_hyde_navigation_order()
    {
        $page = MarkdownPage::make('foo', ['navigation.priority' => 1]);

        $this->assertEquals(1, $page->navigationMenuPriority());

        config(['hyde.navigation.order' => ['foo' => 2]]);
        $this->assertEquals(1, $page->navigationMenuPriority());
    }

    public function test_navigation_menu_priority_returns_999_for_documentation_page()
    {
        $page = DocumentationPage::make('index');
        $this->assertEquals(999, $page->navigationMenuPriority());
    }

    public function test_navigation_menu_priority_returns_0_if_slug_is_index()
    {
        $page = MarkdownPage::make('index');
        $this->assertEquals(0, $page->navigationMenuPriority());
    }

    public function test_navigation_menu_priority_returns_10_if_slug_is_posts()
    {
        $page = MarkdownPage::make('posts');
        $this->assertEquals(10, $page->navigationMenuPriority());
    }

    public function test_navigation_menu_priority_defaults_to_999_if_no_other_conditions_are_met()
    {
        $page = MarkdownPage::make('foo');
        $this->assertEquals(999, $page->navigationMenuPriority());
    }

    public function test_navigation_menu_title_returns_navigation_title_matter_if_set()
    {
        $page = MarkdownPage::make('foo', ['navigation.label' => 'foo']);
        $this->assertEquals('foo', $page->navigationMenuLabel());
    }

    public function test_navigation_menu_title_returns_title_matter_if_set()
    {
        $page = MarkdownPage::make('foo', ['title' => 'foo']);
        $this->assertEquals('foo', $page->navigationMenuLabel());
    }

    public function test_navigation_menu_title_navigation_title_has_precedence_over_title()
    {
        $page = MarkdownPage::make('foo', ['title' => 'foo', 'navigation.label' => 'bar']);
        $this->assertEquals('bar', $page->navigationMenuLabel());
    }

    public function test_navigation_menu_title_returns_docs_if_slug_is_index_and_model_is_documentation_page()
    {
        $page = DocumentationPage::make('index');
        $this->assertEquals('Docs', $page->navigationMenuLabel());
    }

    public function test_navigation_menu_title_returns_home_if_slug_is_index_and_model_is_not_documentation_page()
    {
        $page = MarkdownPage::make('index');
        $this->assertEquals('Home', $page->navigationMenuLabel());
    }

    public function test_navigation_menu_title_returns_title_if_title_is_set_and_not_empty()
    {
        $page = MarkdownPage::make('bar', ['title' => 'foo']);
        $this->assertEquals('foo', $page->navigationMenuLabel());
    }

    public function test_navigation_menu_title_falls_back_to_hyde_make_title_from_slug()
    {
        $page = MarkdownPage::make('foo');
        $this->assertEquals('Foo', $page->navigationMenuLabel());
    }

    public function test_navigation_menu_title_can_be_set_in_configuration()
    {
        config(['hyde.navigation.labels' => ['foo' => 'bar']]);
        $page = MarkdownPage::make('foo');
        $this->assertEquals('bar', $page->navigationMenuLabel());
    }

    public function test_documentation_page_can_be_hidden_from_navigation_using_config()
    {
        config(['hyde.navigation.exclude' => ['docs/index']]);
        $page = DocumentationPage::make('index');
        $this->assertFalse($page->showInNavigation());
    }

    public function test_get_canonical_url_returns_url_for_top_level_page()
    {
        config(['site.url' => 'https://example.com']);
        $page = new MarkdownPage('foo');

        $this->assertEquals('https://example.com/foo.html', $page->canonicalUrl);
    }

    public function test_get_canonical_url_returns_pretty_url_for_top_level_page()
    {
        config(['site.url' => 'https://example.com']);
        config(['site.pretty_urls' => true]);
        $page = new MarkdownPage('foo');

        $this->assertEquals('https://example.com/foo', $page->canonicalUrl);
    }

    public function test_get_canonical_url_returns_url_for_nested_page()
    {
        config(['site.url' => 'https://example.com']);
        $page = new MarkdownPage('foo/bar');

        $this->assertEquals('https://example.com/foo/bar.html', $page->canonicalUrl);
    }

    public function test_get_canonical_url_returns_url_for_deeply_nested_page()
    {
        config(['site.url' => 'https://example.com']);
        $page = new MarkdownPage('foo/bar/baz');

        $this->assertEquals('https://example.com/foo/bar/baz.html', $page->canonicalUrl);
    }

    public function test_canonical_url_is_not_set_when_identifier_is_null()
    {
        config(['site.url' => 'https://example.com']);
        $page = new MarkdownPage();
        $this->assertNull($page->canonicalUrl);
        $this->assertStringNotContainsString(
            '<link rel="canonical"',
            $page->metadata()->render()
        );
    }

    public function test_canonical_url_is_not_set_when_site_url_is_null()
    {
        config(['site.url' => null]);
        $page = new MarkdownPage('foo');
        $this->assertNull($page->canonicalUrl);
        $this->assertStringNotContainsString(
            '<link rel="canonical"',
            $page->metadata()->render()
        );
    }

    public function test_custom_canonical_link_can_be_set_in_front_matter()
    {
        config(['site.url' => 'https://example.com']);
        $page = MarkdownPage::make(matter: ['canonicalUrl' => 'foo/bar']);
        $this->assertEquals('foo/bar', $page->canonicalUrl);
        $this->assertStringContainsString(
            '<link rel="canonical" href="foo/bar">',
            $page->metadata()->render()
        );
    }

    public function test_render_page_metadata_returns_string()
    {
        $page = new MarkdownPage('foo');
        $this->assertIsString($page->metadata()->render());
    }

    public function test_has_method_returns_true_if_page_has_standard_property()
    {
        $page = new MarkdownPage('foo');
        $this->assertTrue($page->has('identifier'));
    }

    public function test_has_method_returns_false_if_page_does_not_have_standard_property()
    {
        $page = new MarkdownPage();
        $this->assertFalse($page->has('foo'));
    }

    public function test_has_method_returns_true_if_page_has_dynamic_property()
    {
        $page = new MarkdownPage();
        $page->foo = 'bar';
        $this->assertTrue($page->has('foo'));
    }

    public function test_has_method_returns_false_if_page_does_not_have_dynamic_property()
    {
        $page = new MarkdownPage();
        $this->assertFalse($page->has('foo'));
    }

    public function test_has_method_returns_true_if_page_has_property_set_in_front_matter()
    {
        $page = MarkdownPage::make(matter: ['foo' => 'bar']);
        $this->assertTrue($page->has('foo'));
    }

    public function test_has_method_returns_false_if_page_does_not_have_property_set_in_front_matter()
    {
        $page = MarkdownPage::make();
        $this->assertFalse($page->has('foo'));
    }

    public function test_has_method_returns_false_if_property_exists_but_is_blank()
    {
        $page = MarkdownPage::make();
        $page->foo = null;
        $this->assertFalse($page->has('foo'));

        $page = MarkdownPage::make();
        $page->foo = '';
        $this->assertFalse($page->has('foo'));
    }

    public function test_has_method_returns_true_if_page_has_blank_property_set_in_front_matter()
    {
        $this->assertFalse(MarkdownPage::make(matter: ['foo' => null])->has('foo'));
        $this->assertFalse(MarkdownPage::make(matter: ['foo' => ''])->has('foo'));
    }

    public function test_markdown_pages_can_be_saved_to_disk()
    {
        $page = new MarkdownPage('foo');
        $page->save();
        $this->assertFileExists(Hyde::path('_pages/foo.md'));
        unlink(Hyde::path('_pages/foo.md'));
    }

    public function test_save_method_converts_front_matter_array_to_yaml_block()
    {
        MarkdownPage::make('foo', matter: ['foo' => 'bar'])->save();
        $this->assertEquals("---\nfoo: bar\n---\n\n",
            file_get_contents(Hyde::path('_pages/foo.md'))
        );
        unlink(Hyde::path('_pages/foo.md'));
    }

    public function test_save_method_writes_page_body_to_file()
    {
        MarkdownPage::make('foo', markdown: 'foo')->save();
        $this->assertEquals('foo',
            file_get_contents(Hyde::path('_pages/foo.md'))
        );
        unlink(Hyde::path('_pages/foo.md'));
    }

    public function test_save_method_writes_page_body_to_file_with_front_matter()
    {
        MarkdownPage::make('foo', matter: ['foo' => 'bar'], markdown: 'foo bar')->save();
        $this->assertEquals("---\nfoo: bar\n---\n\nfoo bar",
            file_get_contents(Hyde::path('_pages/foo.md'))
        );
        unlink(Hyde::path('_pages/foo.md'));
    }

    public function test_new_markdown_pages_can_be_saved()
    {
        $page = new MarkdownPage('foo');
        $page->save();

        $this->assertFileExists(Hyde::path('_pages/foo.md'));
        $this->assertSame('', file_get_contents(Hyde::path('_pages/foo.md')));

        unlink(Hyde::path('_pages/foo.md'));
    }

    public function test_existing_parsed_markdown_pages_can_be_saved()
    {
        $page = new MarkdownPage('foo', markdown: 'bar');
        $page->save();

        $this->assertSame('bar', file_get_contents(Hyde::path('_pages/foo.md')));

        /** @var BaseMarkdownPage $parsed */
        $parsed = MarkdownPage::all()->getPage('_pages/foo.md');
        $this->assertSame('bar', $parsed->markdown->body());

        $parsed->markdown = new Markdown('baz');
        $parsed->save();

        $this->assertSame('baz', file_get_contents(Hyde::path('_pages/foo.md')));

        unlink(Hyde::path('_pages/foo.md'));
    }

    public function test_markdown_posts_can_be_saved()
    {
        $post = new MarkdownPost('foo');
        $post->save();
        $this->assertFileExists(Hyde::path('_posts/foo.md'));
        unlink(Hyde::path('_posts/foo.md'));
    }

    public function test_documentation_pages_can_be_saved()
    {
        $page = new DocumentationPage('foo');
        $page->save();
        $this->assertFileExists(Hyde::path('_docs/foo.md'));
        unlink(Hyde::path('_docs/foo.md'));
    }

    public function test_get_method_can_access_data_from_page()
    {
        $page = MarkdownPage::make('foo', ['foo' => 'bar']);
        $this->assertEquals('bar', $page->data('foo'));
    }

    public function test_get_method_can_access_nested_data_from_page()
    {
        $page = MarkdownPage::make('foo', ['foo' => ['bar' => 'baz']]);
        $this->assertEquals('baz', $page->data('foo')['bar']);
    }

    public function test_get_method_can_access_nested_data_from_page_with_dot_notation()
    {
        $page = MarkdownPage::make('foo', ['foo' => ['bar' => 'baz']]);
        $this->assertEquals('baz', $page->data('foo.bar'));
    }

    public function testGetLinkWithPrettyUrls()
    {
        config(['site.pretty_urls' => true]);
        $this->assertEquals('output/hello-world',
            (new TestPage('hello-world'))->getLink()
        );
    }

    public function testGetLinkUsesHyperlinksHelper()
    {
        $this->assertSame(
            Hyde::formatLink((new TestPage('hello-world'))->getOutputPath()),
            (new TestPage('hello-world'))->getLink()
        );
    }

    public function test_path_helpers_return_same_result_as_fluent_filesystem_helpers()
    {
        $this->assertSameIgnoringDirSeparatorType(BladePage::path('foo'), Hyde::getBladePagePath('foo'));
        $this->assertSameIgnoringDirSeparatorType(MarkdownPage::path('foo'), Hyde::getMarkdownPagePath('foo'));
        $this->assertSameIgnoringDirSeparatorType(MarkdownPost::path('foo'), Hyde::getMarkdownPostPath('foo'));
        $this->assertSameIgnoringDirSeparatorType(DocumentationPage::path('foo'), Hyde::getDocumentationPagePath('foo'));
    }

    public function test_all_pages_are_routable()
    {
        $pages = [
            BladePage::class,
            MarkdownPage::class,
            MarkdownPost::class,
            DocumentationPage::class,
            HtmlPage::class,
        ];

        /** @var HydePage $page */
        foreach ($pages as $page) {
            $page = new $page('foo');

            $this->assertInstanceOf(Route::class, $page->getRoute());
            $this->assertEquals(new Route($page), $page->getRoute());
            $this->assertSame($page->getRoute()->getLink(), $page->getLink());

            Hyde::touch($page::sourcePath('foo'));
            Hyde::boot();

            $this->assertArrayHasKey($page->getSourcePath(), Hyde::pages());
            $this->assertArrayHasKey($page->getRouteKey(), Hyde::routes());

            unlink($page::sourcePath('foo'));
            Hyde::boot();
        }
    }

    public function test_navigation_data_factory_hides_page_from_navigation_when_in_a_subdirectory()
    {
        $page = MarkdownPage::make('foo/bar');
        $this->assertFalse($page->showInNavigation());
        $this->assertNull($page->navigationMenuGroup());
    }

    public function test_navigation_data_factory_hides_page_from_navigation_when_in_a_and_config_is_set_to_hidden()
    {
        config(['hyde.navigation.subdirectories' => 'hidden']);
        $page = MarkdownPage::make('foo/bar');
        $this->assertFalse($page->showInNavigation());
        $this->assertNull($page->navigationMenuGroup());
    }

    public function test_navigation_data_factory_does_not_hide_page_from_navigation_when_in_a_subdirectory_and_allowed_in_configuration()
    {
        config(['hyde.navigation.subdirectories' => 'flat']);
        $page = MarkdownPage::make('foo/bar');
        $this->assertTrue($page->showInNavigation());
        $this->assertNull($page->navigationMenuGroup());
    }

    public function test_navigation_data_factory_allows_show_in_navigation_and_sets_group_when_dropdown_is_selected_in_config()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);
        $page = MarkdownPage::make('foo/bar');
        $this->assertTrue($page->showInNavigation());
        $this->assertEquals('foo', $page->navigationMenuGroup());
    }

    protected function assertSameIgnoringDirSeparatorType(string $expected, string $actual): void
    {
        $this->assertSame(
            str_replace('\\', '/', $expected),
            str_replace('\\', '/', $actual)
        );
    }

    protected function resetDirectoryConfiguration(): void
    {
        BladePage::$sourceDirectory = '_pages';
        MarkdownPage::$sourceDirectory = '_pages';
        MarkdownPost::$sourceDirectory = '_posts';
        DocumentationPage::$sourceDirectory = '_docs';
        MarkdownPage::$fileExtension = '.md';
    }
}

class TestPage extends HydePage
{
    public static string $sourceDirectory = 'source';
    public static string $outputDirectory = 'output';
    public static string $fileExtension = '.md';
    public static string $template = 'template';

    public function compile(): string
    {
        return '';
    }
}
