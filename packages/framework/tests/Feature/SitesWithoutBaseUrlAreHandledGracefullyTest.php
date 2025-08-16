<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Pages\DocumentationPage;

/**
 * High level test to ensure that sites without a base URL are handled gracefully.
 *
 * For example: In case a user forgot to set a base URL, we don't want their site
 * to have localhost links in the compiled HTML output since that would break
 * things when deployed to production. So we fall back to relative links.
 *
 * Some things like sitemaps and RSS feeds cannot be generated without a base URL,
 * as their schemas generally do not allow relative URLs. In those cases, we
 * don't generate files at all, and we don't add any links to them either.
 *
 * This test is not testing a specific class, but a general feature of the framework.
 */
#[\PHPUnit\Framework\Attributes\CoversNothing]
class SitesWithoutBaseUrlAreHandledGracefullyTest extends TestCase
{
    public static function pageClassProvider(): \Iterator
    {
        yield [MarkdownPage::class];
        yield [MarkdownPost::class];
        yield [DocumentationPage::class];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('pageClassProvider')]
    public function testLocalhostLinksAreNotAddedToCompiledHtmlWhenBaseUrlIsNull(string $class)
    {
        $this->withoutSiteUrl();

        $this->assertStringNotContainsString('http://localhost', $this->getHtml($class));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('pageClassProvider')]
    public function testLocalhostLinksAreNotAddedToCompiledHtmlWhenBaseUrlIsNotSet(string $class)
    {
        $this->withoutSiteUrl();

        $this->assertStringNotContainsString('http://localhost', $this->getHtml($class));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('pageClassProvider')]
    public function testLocalhostLinksAreNotAddedToCompiledHtmlWhenBaseUrlIsSetToLocalhost(string $class)
    {
        config(['hyde.url' => 'http://localhost']);

        $this->assertStringNotContainsString('http://localhost', $this->getHtml($class));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('pageClassProvider')]
    public function testSiteUrlLinksAreAddedToCompiledHtmlWhenBaseUrlIsSetToValidUrl(string $class)
    {
        $this->withSiteUrl();

        $this->assertStringNotContainsString('http://localhost', $this->getHtml($class));
    }

    protected function getHtml(string $class): string
    {
        $page = new $class('foo');

        Hyde::shareViewData($page);

        return $page->compile();
    }
}
