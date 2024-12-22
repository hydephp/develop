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
    /**
     * @dataProvider internationalCharacterSetsProvider
     */
    public function testCanCreateBlogPostFilesWithInternationalCharacterSets(
        string $title,
        string $description,
        string $expectedSlug,
        string $expectedTitle
    ) {
        $creator = new CreatesNewMarkdownPostFile($title, $description, 'blog', 'default', '2024-12-22 10:45');
        $path = $creator->save();

        $this->assertSame("_posts/$expectedSlug.md", $path);
        $this->assertSame($expectedSlug, $creator->getIdentifier());
        $this->assertSame($creator->getIdentifier(), Hyde::makeSlug($title));
        $this->assertFileExists($path);

        $contents = file_get_contents($path);

        if (str_contains($title, ' ')) {
            $expectedTitle = "'$expectedTitle'";
        }

        if (str_contains($description, ' ')) {
            $description = "'$description'";
        }

        $this->assertStringContainsString("title: $expectedTitle", $contents);
        $this->assertSame(<<<EOF
        ---
        title: {$expectedTitle}
        description: {$description}
        category: blog
        author: default
        date: '2024-12-22 10:45'
        ---
        
        ## Write something awesome.
        
        EOF, $contents);

        Filesystem::unlink($path);
    }

    /**
     * @dataProvider internationalCharacterSetsProvider
     */
    public function testCanCompileBlogPostFilesWithInternationalCharacterSets(
        string $title,
        string $description,
        string $expectedSlug,
        string $expectedTitle
    ) {
        $page = new MarkdownPost($expectedSlug, [
            'title' => $title,
            'description' => $description,
            'category' => 'blog',
            'author' => 'default',
            'date' => '2024-12-22 10:45',
        ]);

        $path = StaticPageBuilder::handle($page);

        $this->assertSame(Hyde::path("_site/posts/$expectedSlug.html"), $path);
        $this->assertFileExists($path);

        $contents = file_get_contents($path);

        $this->assertStringContainsString("<title>HydePHP - $expectedTitle</title>", $contents);
        $this->assertStringContainsString("<h1 itemprop=\"headline\" class=\"mb-4\">$expectedTitle</h1>", $contents);
        $this->assertStringContainsString("<meta name=\"description\" content=\"$description\">", $contents);

        Filesystem::unlink($path);
    }

    public static function internationalCharacterSetsProvider(): array
    {
        return [
            'Chinese (Simplified)' => [
                '你好世界',
                '简短描述',
                'ni-hao-shi-jie',
                '你好世界',
            ],
            'Japanese' => [
                'こんにちは世界',
                '短い説明',
                'konnichihashi-jie',
                'こんにちは世界',
            ],
            'Korean' => [
                '안녕하세요 세계',
                '짧은 설명',
                'annyeonghaseyo-segye',
                '안녕하세요 세계',
            ],
        ];
    }
}
