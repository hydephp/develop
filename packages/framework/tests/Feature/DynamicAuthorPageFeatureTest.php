<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Facades\Author;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Config;
use Hyde\Facades\Filesystem;

/**
 * High level test for the dynamic author pages feature.
 *
 * @covers \Hyde\Framework\Features\Blogging\BlogPostAuthorPages
 * @covers \Hyde\Framework\Features\Blogging\DynamicPages\PostAuthorPage
 * @covers \Hyde\Framework\Features\Blogging\DynamicPages\PostAuthorsPage
 * @covers \Hyde\Foundation\HydeCoreExtension
 */
class DynamicAuthorPageFeatureTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->resetSite();
    }

    public function testAuthorPagesAreGenerated()
    {
        $this->setUpTestEnvironment();

        $this->assertSame([
            '_pages/404.blade.php',
            '_pages/index.blade.php',
            '_posts/coffee_lover_post.md',
            '_posts/guest_post_about_cats.md',
            '_posts/hyde_framework_intro.md',
            '_posts/laravel_tips.md',
            '_posts/php8_features.md',
            '_posts/ux_principles.md',
            '_posts/web_design_trends.md',
            'authors/index',
            'authors/john_doe',
            'authors/jane_smith',
            // 'authors/user123', // This user has no posts, so no author page should be created
            // TODO: 'authors/anonymous', // This will be supported as the author has made a post, and thus exists in the system, even if not in the config
            // TODO: 'authors/guest', // This post has no author, so no author page should be created (if the user wants one, they can define a config author named 'guest')
        ], array_keys(Hyde::pages()->all()));

        $this->assertSame([
            404,
            'index',
            'posts/coffee_lover_post',
            'posts/guest_post_about_cats',
            'posts/hyde_framework_intro',
            'posts/laravel_tips',
            'posts/php8_features',
            'posts/ux_principles',
            'posts/web_design_trends',
            'authors/index',
            'authors/john_doe',
            'authors/jane_smith',
            // 'authors/user123',
            // TODO: 'authors/anonymous',
            // TODO: 'authors/guest',
        ], array_keys(Hyde::routes()->all()));

        $this->artisan('build')
            ->expectsOutput('Creating Post Authors Pages...')
            ->expectsOutput('Creating Post Author Pages...')
            ->assertExitCode(0);

        // Check if the relevant pages were built
        $this->assertFileExists('_site/authors/index.html');
        $this->assertFileExists('_site/authors/john_doe.html');
        $this->assertFileExists('_site/authors/jane_smith.html');

        // Check if the built pages contain the expected content
        $authorsPage = Filesystem::get('_site/authors/index.html');
        $this->assertStringContainsString('John Doe', $authorsPage);
        $this->assertStringContainsString('Jane Smith', $authorsPage);

        $johnDoePage = Filesystem::get('_site/authors/john_doe.html');
        $this->assertStringContainsString('John Doe', $johnDoePage);
        $this->assertStringContainsString('Full-stack developer and HydePHP enthusiast', $johnDoePage);
        $this->assertStringContainsString('https://johndoe.dev', $johnDoePage);
        $this->assertStringContainsString('@john_doe_dev', $johnDoePage);
        $this->assertStringContainsString('johndoedev', $johnDoePage);
        $this->assertStringContainsString('Introduction to HydePHP Framework', $johnDoePage);
        $this->assertStringContainsString('Laravel Tips and Tricks', $johnDoePage);
        $this->assertStringContainsString('Exploring PHP 8 Features', $johnDoePage);

        $janeSmithPage = Filesystem::get('_site/authors/jane_smith.html');
        $this->assertStringContainsString('Jane Smith', $janeSmithPage);
        $this->assertStringContainsString('UX designer with a passion for creating intuitive interfaces', $janeSmithPage);
        $this->assertStringContainsString('Web Design Trends for 2024', $janeSmithPage);
        $this->assertStringContainsString('Essential UX Principles Every Designer Should Know', $janeSmithPage);
    }

    protected function setUpTestEnvironment(): void
    {
        Config::set('hyde.authors', [
            'john_doe' => Author::create(
                name: 'John Doe',
                website: 'https://johndoe.dev',
                bio: 'Full-stack developer and HydePHP enthusiast',
                avatar: 'https://ui-avatars.com/api/?name=John+Doe&background=0D8ABC&color=fff',
                socials: [
                    'twitter' => '@john_doe_dev',
                    'github' => 'johndoedev',
                ],
            ),
            'jane_smith' => Author::create(
                name: 'Jane Smith',
                website: 'https://janesmith.design',
                bio: 'UX designer with a passion for creating intuitive interfaces',
                socials: [
                    'dribbble' => 'jane_smith_design',
                    'linkedin' => 'janesmith-ux',
                ],
            ),
        ]);

        // Create three pages for John Doe
        $this->makePage('hyde_framework_intro', 'john_doe', 'Introduction to HydePHP Framework: A powerful static site generator');
        $this->makePage('laravel_tips', 'john_doe', 'Laravel Tips and Tricks: Boost your productivity with these handy techniques');
        $this->makePage('php8_features', 'john_doe', 'Exploring PHP 8 Features: What\'s new and exciting in the latest version');

        // Create two pages for Jane Smith
        $this->makePage('web_design_trends', 'jane_smith', 'Web Design Trends for 2024: Stay ahead of the curve with these emerging design patterns');
        $this->makePage('ux_principles', 'jane_smith', 'Essential UX Principles Every Designer Should Know: Creating user-centric experiences');

        // Add a post for an author that is not in the config
        $this->makePage('coffee_lover_post', 'coffee_enthusiast', 'The Art of Brewing: My Journey Through Different Coffee Preparation Methods');

        // Add a guest post with no author
        $this->makePage('guest_post_about_cats', '', '10 Reasons Why Cats Make the Purr-fect Companions');
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
