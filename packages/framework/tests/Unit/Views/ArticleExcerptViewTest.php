<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Views;

use Hyde\Hyde;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Blade;

/**
 * @see resources/views/components/article-excerpt.blade.php
 */
class ArticleExcerptViewTest extends TestCase
{
    protected function renderTestView(MarkdownPost $post): string
    {
        return Blade::render(file_get_contents(
            Hyde::vendorPath('resources/views/components/article-excerpt.blade.php')
        ), ['post' => $post]);
    }

    public function testComponentCanBeRendered()
    {
        $view = $this->renderTestView(MarkdownPost::make());
        $this->assertStringContainsString('https://schema.org/Article', $view);
    }

    public function testComponentRendersPostData()
    {
        $view = $this->renderTestView(MarkdownPost::make(matter: [
            'title' => 'Test Post',
            'date' => '2022-01-01 12:00:00',
            'author' => 'John Doe',
            'description' => 'This is a test post.',
        ]));

        $this->assertStringContainsString('Test Post', $view);
        $this->assertStringContainsString('Jan 1st, 2022', $view);
        $this->assertStringContainsString('John Doe', $view);
        $this->assertStringContainsString('This is a test post.', $view);
        $this->assertStringContainsString('Read post', $view);
    }

    public function testComponentRendersPostWithAuthorObject()
    {
        $view = $this->renderTestView(MarkdownPost::make(matter: [
            'author' => [
                'name' => 'John Doe',
                'url' => '#',
            ],
        ]));

        $this->assertStringContainsString('John Doe', $view);
    }

    public function testThereIsNoCommaAfterDateStringWhenThereIsNoAuthor()
    {
        $view = $this->renderTestView(MarkdownPost::make(matter: [
            'date' => '2022-01-01',
        ]));

        $this->assertStringContainsString('Jan 1st, 2022</span>', $view);
        $this->assertStringNotContainsString('Jan 1st, 2022</span>,', $view);
    }

    public function testThereIsACommaAfterDateStringWhenThereIsAAuthor()
    {
        $view = $this->renderTestView(MarkdownPost::make(matter: [
            'date' => '2022-01-01',
            'author' => 'John Doe',
        ]));

        $this->assertStringContainsString('Jan 1st, 2022</span>,', $view);
    }
}
