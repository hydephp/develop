<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Actions;

use Hyde\Framework\Actions\AnonymousViewCompiler;
use Hyde\Framework\Exceptions\FileNotFoundException;
use Hyde\Testing\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Framework\Actions\AnonymousViewCompiler::class)]
class AnonymousViewCompilerTest extends TestCase
{
    public function testCanCompileBladeFile()
    {
        $this->file('foo.blade.php', "{{ 'Hello World' }}");

        $this->assertSame('Hello World', AnonymousViewCompiler::handle('foo.blade.php'));
    }

    public function testCanCompileBladeFileWithData()
    {
        $this->file('foo.blade.php', '{{ $foo }}');

        $this->assertSame('bar', AnonymousViewCompiler::handle('foo.blade.php', ['foo' => 'bar']));
    }

    public function testWithMissingView()
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('File [foo.blade.php] not found.');

        AnonymousViewCompiler::handle('foo.blade.php');
    }
}
