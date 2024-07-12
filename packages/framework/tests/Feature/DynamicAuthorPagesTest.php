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
    }
}
