<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Features\Templates\PublishableView;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Features\Templates\PublishableView
 */
class PublishableViewTest extends TestCase
{
    public function testPublishableView(): void
    {
        $this->assertSame('Test View', PublishableTestView::getTitle());
        $this->assertSame('A test view', PublishableTestView::getDescription());
        $this->assertSame(Hyde::path('_pages' . DIRECTORY_SEPARATOR . 'output.md'), PublishableTestView::getOutputPath());
    }

    public function testPublishableViewWithNoOutputPath(): void
    {
        $this->assertSame('Test View', PublishableTestViewWithNoOutputPath::getTitle());
        $this->assertSame('A test view', PublishableTestViewWithNoOutputPath::getDescription());
        $this->assertSame(Hyde::path('_pages' . DIRECTORY_SEPARATOR . 'input.md'), PublishableTestViewWithNoOutputPath::getOutputPath());
    }

    public function testPublishableViewPublish(): void
    {
        Hyde::touch('input.md');
        $this->assertTrue(RelativePublishableTestView::publish());
        $this->assertTrue(file_exists(RelativePublishableTestView::getOutputPath()));

        Hyde::unlink('input.md');
        unlink(RelativePublishableTestView::getOutputPath());
    }

    public function testPublishableViewPublishForce(): void
    {
        Hyde::touch('input.md');
        touch(RelativePublishableTestView::getOutputPath());
        $this->assertFalse(RelativePublishableTestView::publish());

        $this->assertTrue(RelativePublishableTestView::publish(true));
        $this->assertTrue(file_exists(RelativePublishableTestView::getOutputPath()));

        Hyde::unlink('input.md');
        unlink(RelativePublishableTestView::getOutputPath());
    }
}

class PublishableTestView extends PublishableView
{
    protected static string $title = 'Test View';
    protected static string $desc = 'A test view';
    protected static string $path = 'input.md';
    protected static ?string $outputPath = 'output.md';
}

class PublishableTestViewWithNoOutputPath extends PublishableView
{
    protected static string $title = 'Test View';
    protected static string $desc = 'A test view';
    protected static string $path = 'input.md';
}

class RelativePublishableTestView extends PublishableTestView
{
    protected static function getSourcePath(): string
    {
        // Don't mess with vendor paths here
        return Hyde::path('input.md');
    }
}