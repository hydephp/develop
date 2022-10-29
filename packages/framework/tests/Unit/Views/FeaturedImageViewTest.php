<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Views;

use Hyde\Framework\Features\Blogging\Models\FeaturedImage;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\TestCase;
use function str_replace;
use function strip_newlines;
use function strip_tags;
use function trim;
use function view;

/**
 * @see resources/views/components/post/image.blade.php
 */
class FeaturedImageViewTest extends TestCase
{
    public function test_the_view()
    {
        $component = $this->renderComponent(FeaturedImage::make([
            'path' => 'foo',
            'description' => 'This is an image',
            'title' => 'FeaturedImage Title',
            'author' => 'John Doe',
            'license' => 'Creative Commons',
            'licenseUrl' => 'https://licence.example.com',
        ]));

        $this->assertStringContainsString('src="media/foo"', $component);
        $this->assertStringContainsString('alt="This is an image"', $component);
        $this->assertStringContainsString('title="FeaturedImage Title"', $component);
        $this->assertStringContainsString('Image by', $component);
        $this->assertStringContainsString('John Doe', $component);
        $this->assertStringContainsString('License', $component);
        $this->assertStringContainsString('Creative Commons', $component);
        $this->assertStringContainsString('href="https://licence.example.com" rel="license nofollow noopener"', $component);

        $this->assertEquals(
            $this->stripWhitespace('Image by John Doe. License Creative Commons.'),
            $this->stripWhitespace($this->stripHtml($component))
        );
    }

    public function test_image_author_attribution_string()
    {
        $image = new FeaturedImage([
            'author' => 'John Doe',
            'attributionUrl' => 'https://example.com/',
        ]);

        $string = $this->renderComponent($image);
        $this->assertStringContainsString('itemprop="creator"', $string);
        $this->assertStringContainsString('itemprop="url"', $string);
        $this->assertStringContainsString('itemtype="http://schema.org/Person"', $string);
        $this->assertStringContainsString('<span itemprop="name">John Doe</span>', $string);
        $this->assertStringContainsString('<a href="https://example.com/"', $string);

        $image = new FeaturedImage(['author' => 'John Doe']);
        $string = $this->renderComponent($image);
        $this->assertStringContainsString('itemprop="creator"', $string);
        $this->assertStringContainsString('itemtype="http://schema.org/Person"', $string);
        $this->assertStringContainsString('<span itemprop="name">John Doe</span>', $string);
    }

    public function test_copyright_string()
    {
        $image = new FeaturedImage(['copyright' => 'foo']);
        $this->assertEquals('<span itemprop="copyrightNotice">foo</span>', $image->getCopyrightString());

        $image = new FeaturedImage();
        $this->assertNull($image->getCopyrightString());
    }

    public function test_license_string()
    {
        $image = new FeaturedImage([
            'license' => 'foo',
            'licenseUrl' => 'https://example.com/bar.html',
        ]);
        $this->assertEquals('<a href="https://example.com/bar.html" rel="license nofollow noopener" '.
            'itemprop="license">foo</a>', $image->getLicenseString());

        $image = new FeaturedImage(['license' => 'foo']);
        $this->assertEquals('<span itemprop="license">foo</span>', $image->getLicenseString());

        $image = new FeaturedImage(['licenseUrl' => 'https://example.com/bar.html']);
        $this->assertNull($image->getLicenseString());

        $image = new FeaturedImage();
        $this->assertNull($image->getLicenseString());
    }

    public function test_fluent_attribution_logic_uses_rich_html_tags()
    {
        $image = new FeaturedImage([
            'author' => 'John Doe',
            'copyright' => 'foo',
            'license' => 'foo',
        ]);
        $string = $image->getFluentAttribution()->toHtml();

        $this->assertStringContainsString('Image by <span itemprop="creator" ', $string);
        $this->assertStringContainsString('<span itemprop="copyrightNotice">foo</span>', $string);
        $this->assertStringContainsString('License <span itemprop="license">foo</span>', $string);

        $image = new FeaturedImage(['author' => 'John Doe']);
        $string = $image->getFluentAttribution()->toHtml();

        $this->assertStringContainsString('Image by ', $string);
        $this->assertStringContainsString('John Doe', $string);

        $image = new FeaturedImage(['copyright' => 'foo']);
        $string = $image->getFluentAttribution()->toHtml();

        $this->assertStringContainsString('<span itemprop="copyrightNotice">foo</span>', $string);

        $image = new FeaturedImage(['license' => 'foo']);

        $string = $image->getFluentAttribution()->toHtml();
        $this->assertStringContainsString('License <span itemprop="license">foo</span>', $string);

        $image = new FeaturedImage();
        $this->assertEquals('', $image->getFluentAttribution()->toHtml());
    }

    public function test_fluent_attribution_logic_creates_fluent_messages()
    {
        $this->assertSame(
            'Image by John Doe. CC. License MIT.',
            $this->stripHtml((new FeaturedImage([
                'author' => 'John Doe',
                'copyright' => 'CC',
                'license' => 'MIT',
            ]))->getFluentAttribution()->toHtml())
        );

        $this->assertSame(
            'Image by John Doe. License MIT.',
            $this->stripHtml((new FeaturedImage([
                'author' => 'John Doe',
                'license' => 'MIT',
            ]))->getFluentAttribution()->toHtml())
        );

        $this->assertSame(
            'Image by John Doe. CC.',
            $this->stripHtml((new FeaturedImage([
                'author' => 'John Doe',
                'copyright' => 'CC',
            ]))->getFluentAttribution()->toHtml())
        );

        $this->assertSame(
            'All rights reserved.',
            $this->stripHtml((new FeaturedImage([
                'copyright' => 'All rights reserved',
            ]))->getFluentAttribution()->toHtml())
        );

        $this->assertSame(
            'Image by John Doe.',
            $this->stripHtml((new FeaturedImage([
                'author' => 'John Doe',
            ]))->getFluentAttribution()->toHtml())
        );

        $this->assertSame(
            'License MIT.',
            $this->stripHtml((new FeaturedImage([
                'license' => 'MIT',
            ]))->getFluentAttribution()->toHtml())
        );

        $this->assertSame('',
            $this->stripHtml((new FeaturedImage())->getFluentAttribution()->toHtml())
        );
    }

    protected function stripHtml(string $string): string
    {
        return trim(strip_newlines(strip_tags($string)), "\t ");
    }

    protected function stripWhitespace(string $string): string
    {
        return str_replace(' ', '', $string);
    }

    protected function renderComponent(FeaturedImage $image, bool $makeFile = true): string
    {
        $page = new MarkdownPost();

        $page->image = $image;


        if ($makeFile) {
            $image->path = $image->getSourcePath() ?? '_media/foo.jpg';
            $this->file($image->getSourcePath() ?? '_media/foo.jpg');
        }

        $this->mockPage($page);

        return view('hyde::components.post.image')->render();
    }
}
