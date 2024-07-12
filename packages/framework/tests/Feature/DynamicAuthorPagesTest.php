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
        Hyde::pages()->addPage(new MarkdownPost(identifier: 'hyde_post_1', matter: ['author' => 'mr_hyde'], markdown: 'Content for Hyde post 1'));
        Hyde::pages()->addPage(new MarkdownPost(identifier: 'hyde_post_2', matter: ['author' => 'mr_hyde'], markdown: 'Content for Hyde post 2'));
        Hyde::pages()->addPage(new MarkdownPost(identifier: 'hyde_post_3', matter: ['author' => 'mr_hyde'], markdown: 'Content for Hyde post 3'));

        // Create two pages for Jane Doe
        Hyde::pages()->addPage(new MarkdownPost(identifier: 'jane_post_1', matter: ['author' => 'jane_doe'], markdown: 'Content for Jane post 1'));
        Hyde::pages()->addPage(new MarkdownPost(identifier: 'jane_post_2', matter: ['author' => 'jane_doe'], markdown: 'Content for Jane post 2'));

        // No pages for user123
    }
}
