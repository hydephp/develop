<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Views;

use Hyde\Framework\Features\Blogging\Models\FeaturedImage;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\TestCase;
use function str_replace;
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
        $string = $this->renderComponent(new FeaturedImage(['author' => 'John Doe']));
        $this->assertStringContainsString('itemprop="creator"', $string);
        $this->assertStringContainsString('itemtype="http://schema.org/Person"', $string);
        $this->assertStringContainsString('<span itemprop="name">John Doe</span>', $string);
    }

    public function test_image_author_attribution_string_with_url()
    {
        $string = $this->renderComponent(new FeaturedImage([
            'author' => 'John Doe',
            'attributionUrl' => 'https://example.com/',
        ]));
        $this->assertStringContainsString('itemprop="creator"', $string);
        $this->assertStringContainsString('itemprop="url"', $string);
        $this->assertStringContainsString('itemtype="http://schema.org/Person"', $string);
        $this->assertStringContainsString('<span itemprop="name">John Doe</span>', $string);
        $this->assertStringContainsString('<a href="https://example.com/"', $string);
    }

    public function test_copyright_string()
    {
        $string = $this->renderComponent(new FeaturedImage(['copyright' => 'foo copy']));
        $this->assertStringContainsString('<span itemprop="copyrightNotice">', $string);
        $this->assertStringContainsString('foo copy', $string);
    }

    public function test_copyright_string_inverse()
    {
        $string = $this->renderComponent(new FeaturedImage());
        $this->assertStringNotContainsString('<span itemprop="copyrightNotice">', $string);
    }

    public function test_license_string()
    {
        $string = $this->renderComponent(new FeaturedImage(['license' => 'foo']));

        $this->assertStringContainsString('<span itemprop="license">foo</span>', $string);
    }

    public function test_license_string_with_url()
    {
        $image = new FeaturedImage([
            'license' => 'foo',
            'licenseUrl' => 'https://example.com/bar.html',
        ]);
        $string = $this->renderComponent($image);

        $this->assertStringContainsString('<a href="https://example.com/bar.html" rel="license nofollow noopener" itemprop="license">foo</a>', $string);
    }

    public function test_license_string_inverse()
    {
        $string = $this->renderComponent(new FeaturedImage());
        $this->assertStringNotContainsString('<span itemprop="license">', $string);
        $this->assertStringNotContainsString('license', $string);
    }

    public function test_license_string_inverse_with_url()
    {
        $string = $this->renderComponent(new FeaturedImage(['licenseUrl' => 'https://example.com/bar.html']));
        $this->assertStringNotContainsString('<span itemprop="license">', $string);
        $this->assertStringNotContainsString('license', $string);
    }

    public function test_fluent_attribution_logic_uses_rich_html_tags()
    {
        $image = new FeaturedImage([
            'author' => 'John Doe',
            'copyright' => 'foo',
            'license' => 'foo',
        ]);
        $string = $this->renderComponent($image);

        $this->assertStringContainsString('Image by', $string);
        $this->assertStringContainsString('License', $string);
        $this->assertStringContainsString('<span itemprop="creator" ', $string);
        $this->assertStringContainsString('<span itemprop="copyrightNotice">foo</span>', $string);
        $this->assertStringContainsString('<span itemprop="license">foo</span>', $string);

        $this->assertStringContainsString('Image by', $string);
        $this->assertStringContainsString('John Doe', $string);
    }

    public function test_fluent_attribution_logic_uses_rich_html_tags_1()
    {
        $image = new FeaturedImage(['author' => 'John Doe']);
        $string = $this->renderComponent($image);
        $this->assertStringContainsString('Image by', $string);
        $this->assertStringContainsString('John Doe', $string);
    }

    public function test_fluent_attribution_logic_uses_rich_html_tags_2()
    {
        $image = new FeaturedImage(['copyright' => 'foo']);
        $string = $this->renderComponent($image);

        $this->assertStringContainsString('<span itemprop="copyrightNotice">foo</span>', $string);
    }

    public function test_fluent_attribution_logic_uses_rich_html_tags_3()
    {
        $image = new FeaturedImage(['license' => 'foo']);

        $string = $this->renderComponent($image);
        $this->assertStringContainsString('<span itemprop="license">foo</span>', $string);
    }

    public function test_fluent_attribution_logic_uses_rich_html_tags_4()
    {
        $image = new FeaturedImage();
        $string = $this->renderComponent($image);
        $this->assertStringNotContainsString('Image by', $string);
        $this->assertStringNotContainsString('License', $string);
    }

    public function test_fluent_attribution_logic_creates_fluent_messages1()
    {
        $image = new FeaturedImage([
            'author' => 'John Doe',
            'copyright' => 'CC',
            'license' => 'MIT',
        ]);

        $this->assertSame(
            $this->stripWhitespace('Image by John Doe. CC. License MIT.'),
            $this->stripHtml(($this->renderComponent($image)))
        );
    }

    public function test_fluent_attribution_logic_creates_fluent_messages2()
    {
        $image = new FeaturedImage([
            'author' => 'John Doe',
            'license' => 'MIT',
        ]);
        $expect = 'Image by John Doe. License MIT.';
        $this->assertSame(
            $this->stripWhitespace($expect),
            $this->stripHtml(($this->renderComponent($image)))
        );
    }

    public function test_fluent_attribution_logic_creates_fluent_messages3()
    {
        $expect = 'Image by John Doe. CC.';
        $image = new FeaturedImage([
            'author' => 'John Doe',
            'copyright' => 'CC',
        ]);

        $this->assertSame(
            $this->stripWhitespace($expect),
            $this->stripHtml(($this->renderComponent($image)))
        );
    }

    public function test_fluent_attribution_logic_creates_fluent_messages4()
    {
        $expect = 'All rights reserved.';
        $image = new FeaturedImage([
            'copyright' => 'All rights reserved',
        ]);

        $this->assertSame(
            $this->stripWhitespace($expect),
            $this->stripHtml(($this->renderComponent($image)))
        );
    }

    public function test_fluent_attribution_logic_creates_fluent_messages5()
    {
        $expect = 'Image by John Doe.';
        $image = new FeaturedImage([
            'author' => 'John Doe',
        ]);

        $this->assertSame(
            $this->stripWhitespace($expect),
            $this->stripHtml(($this->renderComponent($image)))
        );
    }

    public function test_fluent_attribution_logic_creates_fluent_messages6()
    {
        $expect = 'License MIT.';
        $image = new FeaturedImage([
            'license' => 'MIT',
        ]);

        $this->assertSame(
            $this->stripWhitespace($expect),
            $this->stripHtml(($this->renderComponent($image)))
        );
    }

    public function test_fluent_attribution_logic_creates_fluent_messages7()
    {
        $expect = '';
        $image = new FeaturedImage([]);

        $this->assertSame(
            $this->stripWhitespace($expect),
            $this->stripHtml(($this->renderComponent($image)))
        );
    }

    protected function stripHtml(string $string): string
    {
        return trim(($this->stripWhitespace(strip_tags($string))), "\t ");
    }

    protected function stripWhitespace(string $string): string
    {
        return str_replace([' ', "\r", "\n"], '', $string);
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
