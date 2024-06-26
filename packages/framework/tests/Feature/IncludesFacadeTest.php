<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Facades\Filesystem;
use Hyde\Support\Includes;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Support\Includes
 *
 * @see \Hyde\Framework\Testing\Unit\IncludesFacadeUnitTest
 */
class IncludesFacadeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->directory('resources/includes');
    }

    public function testPathReturnsTheIncludesDirectory()
    {
        $this->assertSame(
            Hyde::path('resources/includes'),
            Includes::path()
        );
    }

    public function testPathReturnsAPartialWithinTheIncludesDirectory()
    {
        $this->assertSame(
            Hyde::path('resources/includes/partial.html'),
            Includes::path('partial.html')
        );
    }

    public function testGetReturnsPartial()
    {
        $expected = 'foo bar';
        file_put_contents(Hyde::path('resources/includes/foo.txt'), $expected);
        $this->assertSame($expected, Includes::get('foo.txt'));
        Filesystem::unlink('resources/includes/foo.txt');
    }

    public function testGetReturnsDefaultValueWhenNotFound()
    {
        $this->assertNull(Includes::get('foo.txt'));
        $this->assertSame('default', Includes::get('foo.txt', 'default'));
    }

    public function testHtmlReturnsRenderedPartial()
    {
        $expected = '<h1>foo bar</h1>';
        file_put_contents(Hyde::path('resources/includes/foo.html'), '<h1>foo bar</h1>');
        $this->assertSame($expected, Includes::html('foo.html'));
        Filesystem::unlink('resources/includes/foo.html');
    }

    public function testHtmlReturnsEfaultValueWhenNotFound()
    {
        $this->assertNull(Includes::html('foo.html'));
        $this->assertSame('<h1>default</h1>', Includes::html('foo.html', '<h1>default</h1>'));
    }

    public function testHtmlWithAndWithoutExtension()
    {
        file_put_contents(Hyde::path('resources/includes/foo.html'), '# foo bar');
        $this->assertSame(Includes::html('foo.html'), Includes::html('foo'));
        Filesystem::unlink('resources/includes/foo.html');
    }

    public function testMarkdownReturnsRenderedPartial()
    {
        $expected = "<h1>foo bar</h1>\n";
        file_put_contents(Hyde::path('resources/includes/foo.md'), '# foo bar');
        $this->assertSame($expected, Includes::markdown('foo.md'));
        Filesystem::unlink('resources/includes/foo.md');
    }

    public function testMarkdownReturnsRenderedDefaultValueWhenNotFound()
    {
        $this->assertNull(Includes::markdown('foo.md'));
        $this->assertSame("<h1>default</h1>\n", Includes::markdown('foo.md', '# default'));
    }

    public function testMarkdownWithAndWithoutExtension()
    {
        file_put_contents(Hyde::path('resources/includes/foo.md'), '# foo bar');
        $this->assertSame(Includes::markdown('foo.md'), Includes::markdown('foo'));
        Filesystem::unlink('resources/includes/foo.md');
    }

    public function testBladeReturnsRenderedPartial()
    {
        $expected = 'foo bar';
        file_put_contents(Hyde::path('resources/includes/foo.blade.php'), '{{ "foo bar" }}');
        $this->assertSame($expected, Includes::blade('foo.blade.php'));
        Filesystem::unlink('resources/includes/foo.blade.php');
    }

    public function testBladeWithAndWithoutExtension()
    {
        file_put_contents(Hyde::path('resources/includes/foo.blade.php'), '# foo bar');
        $this->assertSame(Includes::blade('foo.blade.php'), Includes::blade('foo'));
        Filesystem::unlink('resources/includes/foo.blade.php');
    }

    public function testBladeReturnsRenderedDefaultValueWhenNotFound()
    {
        $this->assertNull(Includes::blade('foo.blade.php'));
        $this->assertSame('default', Includes::blade('foo.blade.php', '{{ "default" }}'));
    }

    public function testAdvancedMarkdownDocumentIsCompiledToHtml()
    {
        $markdown = <<<'MARKDOWN'
        # Heading
        
        This is a paragraph. It has some **bold** and *italic* text.
        
        >info Info Blockquote
        
        ```php
        // filepath: hello.php
        echo 'Hello, World!';
        ```
        
        ## Subheading
        
        
        - [x] Checked task list
        - [ ] Unchecked task list
        
        ### Table
        
        | Syntax | Description |
        | ----------- | ----------- |
        | Header | Title |
        | Paragraph | Text |
        
        MARKDOWN;

        $expected = <<<'HTML'
        <h1>Heading</h1>
        <p>This is a paragraph. It has some <strong>bold</strong> and <em>italic</em> text.</p>
        <blockquote class="info"><p>Info Blockquote</p></blockquote>
        <pre><code class="language-php"><small class="filepath not-prose"><span class="sr-only">Filepath: </span>hello.php</small>echo 'Hello, World!';
        </code></pre>
        <h2>Subheading</h2>
        <ul>
        <li><input checked="" disabled="" type="checkbox"> Checked task list</li>
        <li><input disabled="" type="checkbox"> Unchecked task list</li>
        </ul>
        <h3>Table</h3>
        <table>
        <thead>
        <tr>
        <th>Syntax</th>
        <th>Description</th>
        </tr>
        </thead>
        <tbody>
        <tr>
        <td>Header</td>
        <td>Title</td>
        </tr>
        <tr>
        <td>Paragraph</td>
        <td>Text</td>
        </tr>
        </tbody>
        </table>

        HTML;

        $this->file('resources/includes/advanced.md', $markdown);
        $this->assertSame($expected, Includes::markdown('advanced.md'));
    }

    public function testTorchlightAttributionIsNotInjectedToMarkdownPartials()
    {
        $this->file('resources/includes/without-torchlight.md', 'Syntax highlighted by torchlight.dev');

        $this->assertSame(
            '<p>Syntax highlighted by torchlight.dev</p>
',
            Includes::markdown('without-torchlight.md')
        );
    }
}
