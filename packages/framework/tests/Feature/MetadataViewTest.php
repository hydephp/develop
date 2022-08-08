<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Models\Metadata\Metadata
 */
class MetadataViewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['site.url' => 'http://localhost']);
    }

    protected function build(?string $page = null): void
    {
        if ($page) {
            $this->artisan("rebuild $page");
        } else {
            $this->artisan('build');
        }
    }

    protected function assertSee(string $page, string|array $text): void
    {
        if (is_array($text)) {
            foreach ($text as $string) {
                $this->assertSee($page, $string);
            }

            return;
        }

        $this->assertStringContainsString($text,
            file_get_contents(Hyde::path("_site/$page.html")),
            "Failed asserting that the page '$page' contains the text '$text'");
    }
}
