<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Testing\TestCase;

/**
 * High level test of the sitemap generation feature.
 *
 * @see \Hyde\Framework\Testing\Feature\Services\SitemapServiceTest
 * @see \Hyde\Framework\Testing\Feature\Commands\BuildSitemapCommandTest
 *
 * @covers \Hyde\Framework\Features\XmlGenerators\SitemapGenerator
 * @covers \Hyde\Framework\Actions\PostBuildTasks\GenerateSitemap
 * @covers \Hyde\Console\Commands\BuildSitemapCommand
 */
class SitemapFeatureTest extends TestCase
{
    public function testTheSitemapFeature()
    {
        $this->setUpBroadSiteStructure();
        $this->withSiteUrl();
    }

    protected function setUpBroadSiteStructure(): void
    {
        $this->file('_pages/about.md', "# About\n\nThis is the about page.");
        $this->file('_pages/contact.html', '<h1>Contact</h1><p>This is the contact page.</p>');
        $this->file('_posts/hello-world.md', "# Hello, World!\n\nThis is the first post.");
        $this->file('_posts/second-post.md', "# Second Post\n\nThis is the second post.");
        $this->file('_docs/index.md', "# Documentation\n\nThis is the documentation index.");
        $this->file('_docs/installation.md', "# Installation\n\nThis is the installation guide.");
        $this->file('_docs/usage.md', "# Usage\n\nThis is the usage guide.");
        $this->file('_docs/404.md', "# 404\n\nThis is the 404 page.");
    }
}
