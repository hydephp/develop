<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Views;

use function array_merge;
use Hyde\Framework\Factories\FeaturedImageFactory;
use Hyde\Framework\Features\Blogging\Models\FeaturedImage;
use Hyde\Markdown\Models\FrontMatter;
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
        $component = $this->renderComponent([
            'image.path' => 'foo.jpg',
            'image.description' => 'This is an image',
            'image.title' => 'FeaturedImage Title',
            'image.author' => 'John Doe',
            'image.license' => 'Creative Commons',
            'image.licenseUrl' => 'https://licence.example.com',
        ]);

        $this->assertStringContainsString('src="media/foo.jpg"', $component);
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
        $string = $this->renderComponent(['image.author' => 'John Doe']);
        $this->assertStringContainsString('itemprop="creator"', $string);
        $this->assertStringContainsString('itemtype="https://schema.org/Person"', $string);
        $this->assertStringContainsString('<span itemprop="name">John Doe</span>', $string);
    }

    public function test_image_author_attribution_string_with_url()
    {
        $string = $this->renderComponent([
            'image.author' => 'John Doe',
            'image.attributionUrl' => 'https://example.com/',
        ]);
        $this->assertStringContainsString('itemprop="creator"', $string);
        $this->assertStringContainsString('itemprop="url"', $string);
        $this->assertStringContainsString('itemtype="https://schema.org/Person"', $string);
        $this->assertStringContainsString('<span itemprop="name">John Doe</span>', $string);
        $this->assertStringContainsString('<a href="https://example.com/"', $string);
    }

    public function test_copyright_string()
    {
        $string = $this->renderComponent(['image.copyright' => 'foo copy']);
        $this->assertStringContainsString('<span itemprop="copyrightNotice">', $string);
        $this->assertStringContainsString('foo copy', $string);
    }

    public function test_copyright_string_inverse()
    {
        $string = $this->renderComponent([]);
        $this->assertStringNotContainsString('<span itemprop="copyrightNotice">', $string);
    }

    public function test_license_string()
    {
        $string = $this->renderComponent(['image.license' => 'foo']);

        $this->assertStringContainsString('<span itemprop="license">foo</span>', $string);
    }

    public function test_license_string_with_url()
    {
        $image = $this->make([
            'image.license' => 'foo',
            'image.licenseUrl' => 'https://example.com/bar.html',
        ]);
        $string = $this->renderComponent($image);

        $this->assertStringContainsString('<a href="https://example.com/bar.html" rel="license nofollow noopener" itemprop="license">foo</a>', $string);
    }

    public function test_license_string_inverse()
    {
        $string = $this->renderComponent([]);
        $this->assertStringNotContainsString('<span itemprop="license">', $string);
        $this->assertStringNotContainsString('license', $string);
    }

    public function test_license_string_inverse_with_url()
    {
        $string = $this->renderComponent(['image.licenseUrl' => 'https://example.com/bar.html']);
        $this->assertStringNotContainsString('<span itemprop="license">', $string);
        $this->assertStringNotContainsString('license', $string);
    }

    public function test_fluent_attribution_logic_uses_rich_html_tags()
    {
        $image = $this->make([
            'image.author' => 'John Doe',
            'image.copyright' => 'foo',
            'image.license' => 'foo',
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
        $image = $this->make(['image.author' => 'John Doe']);
        $string = $this->renderComponent($image);
        $this->assertStringContainsString('Image by', $string);
        $this->assertStringContainsString('John Doe', $string);
    }

    public function test_fluent_attribution_logic_uses_rich_html_tags_2()
    {
        $image = $this->make(['image.copyright' => 'foo']);
        $string = $this->renderComponent($image);

        $this->assertStringContainsString('<span itemprop="copyrightNotice">foo</span>', $string);
    }

    public function test_fluent_attribution_logic_uses_rich_html_tags_3()
    {
        $image = $this->make(['image.license' => 'foo']);

        $string = $this->renderComponent($image);
        $this->assertStringContainsString('<span itemprop="license">foo</span>', $string);
    }

    public function test_fluent_attribution_logic_uses_rich_html_tags_4()
    {
        $image = $this->make();
        $string = $this->renderComponent($image);
        $this->assertStringNotContainsString('Image by', $string);
        $this->assertStringNotContainsString('License', $string);
    }

    public function test_fluent_attribution_logic_creates_fluent_messages1()
    {
        $image = $this->make([
            'image.author' => 'John Doe',
            'image.copyright' => 'CC',
            'image.license' => 'MIT',
        ]);

        $this->assertSame(
            $this->stripWhitespace('Image by John Doe. CC. License MIT.'),
            $this->stripHtml($this->renderComponent($image))
        );
    }

    public function test_fluent_attribution_logic_creates_fluent_messages2()
    {
        $image = $this->make([
            'image.author' => 'John Doe',
            'image.license' => 'MIT',
        ]);
        $expect = 'Image by John Doe. License MIT.';
        $this->assertSame(
            $this->stripWhitespace($expect),
            $this->stripHtml($this->renderComponent($image))
        );
    }

    public function test_fluent_attribution_logic_creates_fluent_messages3()
    {
        $expect = 'Image by John Doe. CC.';
        $image = $this->make([
            'image.author' => 'John Doe',
            'image.copyright' => 'CC',
        ]);

        $this->assertSame(
            $this->stripWhitespace($expect),
            $this->stripHtml($this->renderComponent($image))
        );
    }

    public function test_fluent_attribution_logic_creates_fluent_messages4()
    {
        $expect = 'All rights reserved.';
        $image = $this->make([
            'image.copyright' => 'All rights reserved',
        ]);

        $this->assertSame(
            $this->stripWhitespace($expect),
            $this->stripHtml($this->renderComponent($image))
        );
    }

    public function test_fluent_attribution_logic_creates_fluent_messages5()
    {
        $expect = 'Image by John Doe.';
        $image = $this->make([
            'image.author' => 'John Doe',
        ]);

        $this->assertSame(
            $this->stripWhitespace($expect),
            $this->stripHtml($this->renderComponent($image))
        );
    }

    public function test_fluent_attribution_logic_creates_fluent_messages6()
    {
        $expect = 'License MIT.';
        $image = $this->make([
            'image.license' => 'MIT',
        ]);

        $this->assertSame(
            $this->stripWhitespace($expect),
            $this->stripHtml($this->renderComponent($image))
        );
    }

    public function test_fluent_attribution_logic_creates_fluent_messages7()
    {
        $expect = '';
        $image = $this->make([]);

        $this->assertSame(
            $this->stripWhitespace($expect),
            $this->stripHtml($this->renderComponent($image))
        );
    }

    protected function stripHtml(string $string): string
    {
        return trim($this->stripWhitespace(strip_tags($string)), "\t ");
    }

    protected function stripWhitespace(string $string): string
    {
        return str_replace([' ', "\r", "\n"], '', $string);
    }

    protected function renderComponent(FeaturedImage|array $data = ['image.path'=>'foo']): string
    {
        $image = $data instanceof FeaturedImage ? $data : $this->make($data);

        $page = new MarkdownPost();
        $page->image = $image;
        $this->mockPage($page);

        return view('hyde::components.post.image')->render();
    }

    protected function make(array $data = [], string $path = 'foo.png'): FeaturedImage
    {
        $this->file("_media/$path");

        return FeaturedImageFactory::make(FrontMatter::fromArray(array_merge(
            ['image.path' => $path],
            $data,
        )));
    }
}
