<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use ReflectionClass;
use Hyde\Testing\UnitTestCase;
use Hyde\Console\Helpers\PublishablePage;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Console\Helpers\PublishablePage::class)]
class PublishablePageTest extends UnitTestCase
{
    public function testConstructsWithAllProperties()
    {
        $page = new PublishablePage(
            key: 'posts',
            label: 'Posts feed',
            description: 'A feed of your latest posts.',
            source: 'resources/views/homepages/post-feed.blade.php',
            defaultTarget: '_pages/posts.blade.php',
            alternativeTargets: ['_pages/index.blade.php' => 'Use as your site homepage'],
            allowCustomTarget: false,
        );

        $this->assertSame('posts', $page->key);
        $this->assertSame('Posts feed', $page->label);
        $this->assertSame('A feed of your latest posts.', $page->description);
        $this->assertSame('resources/views/homepages/post-feed.blade.php', $page->source);
        $this->assertSame('_pages/posts.blade.php', $page->defaultTarget);
        $this->assertSame(['_pages/index.blade.php' => 'Use as your site homepage'], $page->alternativeTargets);
        $this->assertFalse($page->allowCustomTarget);
    }

    public function testOptionalPropertiesDefaultToNoAlternativesAndCustomTargetAllowed()
    {
        $page = new PublishablePage(
            key: 'welcome',
            label: 'Welcome page',
            description: 'The default welcome page.',
            source: 'resources/views/homepages/welcome.blade.php',
            defaultTarget: '_pages/index.blade.php',
        );

        $this->assertSame([], $page->alternativeTargets);
        $this->assertTrue($page->allowCustomTarget);
    }

    public function testValueObjectIsImmutable()
    {
        $reflection = new ReflectionClass(PublishablePage::class);

        $this->assertTrue($reflection->isFinal());

        foreach ($reflection->getProperties() as $property) {
            $this->assertTrue($property->isReadOnly(), "Property {$property->getName()} should be readonly.");
        }
    }
}
