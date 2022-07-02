<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Actions\GeneratesNavigationMenu;
use Hyde\Framework\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;

class GeneratesNavigationMenuTest extends TestCase
{
    public function test_get_method_returns_array()
    {
        $array = GeneratesNavigationMenu::getNavigationLinks();
        $this->assertIsArray($array);
    }

    public function test_generated_links_include_documentation_pages()
    {
        touch(Hyde::path('_docs/index.md'));

        $generator = new GeneratesNavigationMenu('index');
        $this->assertIsArray($generator->links);

        $this->assertContains('docs/index.html', Arr::flatten($generator->links));
    }

    public function test_get_links_from_config_method()
    {
        $generator = new GeneratesNavigationMenu(currentPage: 'foo/bar');

        Config::set('hyde.navigation_menu_links', [
            [
                'title' => 'GNMTestExt',
                'destination' => 'https://example.org/test',
                'priority' => 800,
            ],
            [
                'title' => 'GNMTestInt',
                'slug' => 'foo/bar',
            ],
        ]);

        $result = $generator->getLinksFromConfig();

        $this->assertCount(2, $result);

        $this->assertEquals($result[0], [
            'title' => 'GNMTestExt',
            'route' => 'https://example.org/test',
            'current' => false,
            'priority' => 800,
        ]);

        $this->assertEquals([
            'title' => 'GNMTestInt',
            'route' => '../foo/bar.html',
            'current' => true,
            'priority' => 999,
        ], $result[1]);
    }

    public function test_files_starting_with_underscores_are_ignored()
    {
        touch(Hyde::path('_pages/_foo.md'));
        touch(Hyde::path('_pages/_foo.blade.php'));

        $array = GeneratesNavigationMenu::getNavigationLinks();
        $this->assertIsArray($array);
        $this->assertCount(1, $array);

        unlink(Hyde::path('_pages/_foo.md'));
        unlink(Hyde::path('_pages/_foo.blade.php'));
    }
}
