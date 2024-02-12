<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Facades\Filesystem;
use Hyde\Framework\Actions\CreatesNewMarkdownPostFile;
use Hyde\Framework\Services\BuildService;
use Illuminate\Console\OutputStyle;
use Mockery;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Helper\ProgressBar;

use function config;

use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;

class RelativeLinksAcrossPagesRetainsIntegrityTest extends TestCase
{
    use InteractsWithDirectories;

    protected function setUp(): void
    {
        parent::setUp();

        config(['hyde.enable_cache_busting' => false]);
        config(['hyde.navigation.subdirectories' => 'flat']);

        $this->needsDirectory('_pages/nested');
        $this->file('_pages/root.md');
        $this->file('_pages/root1.md');
        Filesystem::touch('_pages/nested/level1.md');
        Filesystem::touch('_pages/nested/level1b.md');

        $this->file('_docs/index.md');
        $this->file('_docs/docs.md');

        (new CreatesNewMarkdownPostFile('My New Post', null, null, null))->save();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(Hyde::path('_pages/nested'));
        Filesystem::unlink('_site/root.html');
        Filesystem::unlink('_site/root1.html');
        $this->resetSite();
        $this->resetPosts();

        parent::tearDown();
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

    public function testRelativeLinksAcrossPagesRetainsIntegrity()
    {
        $service = new BuildService(Mockery::mock(OutputStyle::class, [
            'getFormatter' => Mockery::mock(OutputFormatterInterface::class, [
                'hasStyle' => false,
                'setStyle' => null,
            ]),
            'createProgressBar' => new ProgressBar(Mockery::mock(OutputStyle::class, [
                'isDecorated' => false,
                'getVerbosity' => 0,
                'write' => null,
                'getFormatter' => Mockery::mock(OutputFormatterInterface::class, [
                    'hasStyle' => false,
                    'setStyle' => null,
                    'isDecorated' => false,
                    'setDecorated' => null,
                    'format' => null,
                ]),
            ])),
            'writeln' => null,
            'newLine' => null,
            'write' => null,
        ]));

        $service->transferMediaAssets();
        $service->compileStaticPages();

        $this->assertSee('root', [
            '<link rel="stylesheet" href="media/app.css">',
            '<a href="index.html"',
            '<a href="docs/index.html"',
            '<a href="root.html" aria-current="page"',
            '<a href="root1.html"',
            '<a href="nested/level1.html"',
            '<a href="nested/level1b.html"',
        ]);

        $this->assertSee('nested/level1', [
            '<link rel="stylesheet" href="../media/app.css">',
            '<a href="../index.html"',
            '<a href="../docs/index.html"',
            '<a href="../root.html"',
            '<a href="../root1.html"',
            '<a href="../nested/level1.html" aria-current="page"',
            '<a href="../nested/level1b.html"',
        ]);

        $this->assertSee('docs/index', [
            '<link rel="stylesheet" href="../media/app.css">',
            '<a href="../docs/index.html">',
            '<a href="../docs/docs.html"',
            '<a href="../index.html">Back to home page</a>',
        ]);
    }
}
