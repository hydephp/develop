<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Concerns\BaseMarkdownPage;
use Hyde\Framework\Concerns\HydePage;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Markdown\Markdown;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Models\Support\Route;
use Hyde\Testing\TestCase;

/**
 * Test the HydePage class.
 *
 * Since the class is abstract, we can't test it directly,
 * so we will use the MarkdownPage class as a proxy,
 * since it's the simplest implementation.
 *
 * @covers \Hyde\Framework\Concerns\HydePage
 * @covers \Hyde\Framework\Concerns\BaseMarkdownPage
 * @covers \Hyde\Framework\Concerns\Internal\ConstructsPageSchemas
 * @covers \Hyde\Framework\Actions\Constructors\FindsTitleForPage
 * @covers \Hyde\Framework\Actions\Constructors\FindsNavigationDataForPage
 */
class HydePageTest extends TestCase
{
    // Section: Baseline tests

    public function testSourceDirectory()
    {
        $this->assertSame(
            'source',
            HandlesPageFilesystemTestClass::sourceDirectory()
        );
    }

    public function testOutputDirectory()
    {
        $this->assertSame(
            'output',
            HandlesPageFilesystemTestClass::outputDirectory()
        );
    }

    public function testFileExtension()
    {
        $this->assertSame(
            '.md',
            HandlesPageFilesystemTestClass::fileExtension()
        );
    }

    public function testSourcePath()
    {
        $this->assertSame(
            'source/hello-world.md',
            HandlesPageFilesystemTestClass::sourcePath('hello-world')
        );
    }

    public function testOutputPath()
    {
        $this->assertSame(
            'output/hello-world.html',
            HandlesPageFilesystemTestClass::outputPath('hello-world')
        );
    }

    public function testGetSourcePath()
    {
        $this->assertSame(
            'source/hello-world.md',
            (new HandlesPageFilesystemTestClass('hello-world'))->getSourcePath()
        );
    }

    public function testGetOutputPath()
    {
        $this->assertSame(
            'output/hello-world.html',
            (new HandlesPageFilesystemTestClass('hello-world'))->getOutputPath()
        );
    }

    public function testGetLink()
    {
        $this->assertSame(
            'output/hello-world.html',
            (new HandlesPageFilesystemTestClass('hello-world'))->getLink()
        );
    }

    public function testShowInNavigation()
    {
        $this->assertTrue((new BladePage('foo'))->showInNavigation());
        $this->assertTrue((new MarkdownPage())->showInNavigation());
        $this->assertTrue((new DocumentationPage())->showInNavigation());
        $this->assertFalse((new MarkdownPost())->showInNavigation());
    }

    public function testNavigationMenuPriority()
    {
        $this->assertSame(999, (new BladePage('foo'))->navigationMenuPriority());
        $this->assertSame(999, (new MarkdownPage())->navigationMenuPriority());
        $this->assertSame(999, (new DocumentationPage())->navigationMenuPriority());
        $this->assertSame(10, (new MarkdownPost())->navigationMenuPriority());
    }

    public function testNavigationMenuLabel()
    {
        $this->assertSame('Foo', (new BladePage('foo'))->navigationMenuLabel());
        $this->assertSame('Foo', (new MarkdownPage('foo'))->navigationMenuLabel());
        $this->assertSame('Foo', (new MarkdownPost('foo'))->navigationMenuLabel());
        $this->assertSame('Foo', (new DocumentationPage('foo'))->navigationMenuLabel());
    }

    public function testNavigationMenuGroup()
    {
        $this->assertNull((new BladePage('foo'))->navigationMenuGroup());
        $this->assertNull((new MarkdownPage())->navigationMenuGroup());
        $this->assertNull((new MarkdownPost())->navigationMenuGroup());
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
        MarkdownPage::make('foo', body: 'foo')->save();
        $this->assertEquals('foo',
            file_get_contents(Hyde::path('_pages/foo.md'))
        );
        unlink(Hyde::path('_pages/foo.md'));
    }

    public function test_save_method_writes_page_body_to_file_with_front_matter()
    {
        MarkdownPage::make('foo', matter: ['foo' => 'bar'], body: 'foo bar')->save();
        $this->assertEquals("---\nfoo: bar\n---\n\nfoo bar",
            file_get_contents(Hyde::path('_pages/foo.md'))
        );
        unlink(Hyde::path('_pages/foo.md'));
    }

    public function test_get_method_can_access_data_from_page()
    {
        $page = MarkdownPage::make('foo', ['foo' => 'bar']);
        $this->assertEquals('bar', $page->get('foo'));
    }

    public function test_get_method_can_access_nested_data_from_page()
    {
        $page = MarkdownPage::make('foo', ['foo' => ['bar' => 'baz']]);
        $this->assertEquals('baz', $page->get('foo')['bar']);
    }

    public function test_get_method_can_access_nested_data_from_page_with_dot_notation()
    {
        $page = MarkdownPage::make('foo', ['foo' => ['bar' => 'baz']]);
        $this->assertEquals('baz', $page->get('foo.bar'));
    }

    public function testGetLinkWithPrettyUrls()
    {
        config(['site.pretty_urls' => true]);
        $this->assertEquals('output/hello-world',
            (new HandlesPageFilesystemTestClass('hello-world'))->getLink()
        );
    }

    public function testGetLinkUsesHyperlinksHelper()
    {
        $this->assertSame(
            Hyde::formatLink((new HandlesPageFilesystemTestClass('hello-world'))->getOutputPath()),
            (new HandlesPageFilesystemTestClass('hello-world'))->getLink()
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

class HandlesPageFilesystemTestClass extends HydePage
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
