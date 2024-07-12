<?php

declare(strict_types=1);

use Hyde\Facades\Author;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Config;

/**
 * High level test for the dynamic author pages feature.
 *
 * @covers \Hyde\Framework\Features\Blogging\DynamicBlogPostPageHelper
 * @covers \Hyde\Foundation\HydeCoreExtension
 */
class DynamicAuthorPagesTest extends TestCase
{
    public function testAuthorPagesAreGenerated()
    {
        $this->setUpTestEnvironment();

        $this->assertTrue(Hyde::pages()->contains('author/mr_hyde'));
        $this->assertTrue(Hyde::pages()->contains('author/jane_doe'));
        $this->assertFalse(Hyde::pages()->contains('author/user123'));
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

        // No pages for user123
    }

    protected function makePage(string $identifier, string $author, string $markdown): void
    {
        Hyde::pages()->addPage(new MarkdownPost(identifier: $identifier, matter: ['author' => $author], markdown: $markdown));
    }
}
