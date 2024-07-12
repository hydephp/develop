<?php

declare(strict_types=1);

use Hyde\Facades\Author;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Config;

/**
 * High level test for the dynamic author pages feature.
 *
 * @covers \Hyde\Framework\Features\Blogging\DynamicBlogPostPageHelper
 * @covers \Hyde\Framework\Features\Blogging\DynamicPages\PostAuthorPage
 * @covers \Hyde\Foundation\HydeCoreExtension
 */
class DynamicAuthorPagesTest extends TestCase
{
    public function testAuthorPagesAreGenerated()
    {
        $this->setUpTestEnvironment();

        $this->assertSame([
            '_pages/404.blade.php',
            '_pages/index.blade.php',
            '_posts/anonymous_post_1.md',
            '_posts/guest_post_1.md',
            '_posts/hyde_post_1.md',
            '_posts/hyde_post_2.md',
            '_posts/hyde_post_3.md',
            '_posts/jane_post_1.md',
            '_posts/jane_post_2.md',
            'author/mr_hyde',
            'author/jane_doe',
            // 'author/user123',
            // TODO: 'author/anonymous',
            // TODO: 'author/guest',
        ], array_keys(Hyde::pages()->all()));
    }

    protected function setUpTestEnvironment(): void
    {
        Config::set('hyde.authors', [
            'mr_hyde' => Author::create(
                name: 'Mr. Hyde',
                website: 'https://hydephp.com',
                bio: 'The mysterious author of HydePHP',
                avatar: 'avatar.png',
                socials: [
                    'twitter' => '@HydeFramework',
                    'github' => 'hydephp',
                ],
            ),
            'jane_doe' => Author::create(
                name: 'Jane Doe',
                bio: 'Slightly less evil. We think...',
            ),
            'user123' => Author::create(),
        ]);

        // Create three pages for Mr. Hyde
        $this->makePage('hyde_post_1', 'mr_hyde', 'Content for Hyde post 1');
        $this->makePage('hyde_post_2', 'mr_hyde', 'Content for Hyde post 2');
        $this->makePage('hyde_post_3', 'mr_hyde', 'Content for Hyde post 3');

        // Create two pages for Jane Doe
        $this->makePage('jane_post_1', 'jane_doe', 'Content for Jane post 1');
        $this->makePage('jane_post_2', 'jane_doe', 'Content for Jane post 2');

        // No pages for user123, but we will add one for an author that is not in the config
        $this->makePage('anonymous_post_1', 'anonymous', 'Content for anonymous post 1');

        // We will also add a guest post, where there is no author
        $this->makePage('guest_post_1', '', 'Content for guest post 1');
    }

    protected function makePage(string $identifier, string $author, string $markdown): void
    {
        if (filled($author)) {
            $markdown = <<<MARKDOWN
            ---
            author: $author
            ---
            
            $markdown
            MARKDOWN;
        }

        $this->file("_posts/$identifier.md", $markdown);
    }
}
