<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Facades\Filesystem;
use Hyde\Framework\Actions\CreatesNewMarkdownPostFile;
use Hyde\Framework\Actions\StaticPageBuilder;
use Hyde\Hyde;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\TestCase;

/**
 * @coversNothing High level test to ensure the internationalization features are working.
 */
class InternationalizationTest extends TestCase
{
    public function testCanCreateBlogPostFilesWithInternationalCharacterSets()
    {
        $creator = new CreatesNewMarkdownPostFile('你好世界', '简短描述', 'blog', 'default', '2024-12-22 10:45');
        $path = $creator->save();

        $this->assertSame('_posts/ni-hao-shi-jie.md', $path);
        $this->assertSame('ni-hao-shi-jie', $creator->getIdentifier());
        $this->assertSame($creator->getIdentifier(), Hyde::makeSlug('你好世界'));
        $this->assertFileExists($path);

        $contents = file_get_contents($path);
        $this->assertStringContainsString('title: 你好世界', $contents);
        $this->assertSame(<<<'EOF'
        ---
        title: 你好世界
        description: 简短描述
        category: blog
        author: default
        date: '2024-12-22 10:45'
        ---
        
        ## Write something awesome.
        
        EOF, $contents);

        Filesystem::unlink($path);
    }

    public function testCanCompileBlogPostFilesWithInternationalCharacterSets()
    {
        $page = new MarkdownPost('ni-hao-shi-jie', [
            'title' => '你好世界',
            'description' => '简短描述',
            'category' => 'blog',
            'author' => 'default',
            'date' => '2024-12-22 10:45',
        ]);

        $path = StaticPageBuilder::handle($page);

        $this->assertSame(Hyde::path('_site/posts/ni-hao-shi-jie.html'), $path);
        $this->assertFileExists($path);

        $contents = file_get_contents($path);

        $this->assertStringContainsString('<title>HydePHP - 你好世界</title>', $contents);
        $this->assertStringContainsString('<h1 itemprop="headline" class="mb-4">你好世界</h1>', $contents);
        $this->assertStringContainsString('<meta name="description" content="简短描述">', $contents);

        Filesystem::unlink($path);
    }
}
