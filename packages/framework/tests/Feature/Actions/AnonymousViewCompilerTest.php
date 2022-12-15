<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Actions;

use Hyde\Framework\Actions\AnonymousViewCompiler;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

use InvalidArgumentException;

use function file_put_contents;

/**
 * @covers \Hyde\Framework\Actions\AnonymousViewCompiler
 */
class AnonymousViewCompilerTest extends TestCase
{
    public function testCanCompileBladeFile()
    {
        $this->file('foo.blade.php', "{{ 'Hello World' }}");

        $this->assertSame('Hello World', AnonymousViewCompiler::call('foo.blade.php'));
    }

    public function testCanCompileBladeFileWithData()
    {
        $this->file('foo.blade.php', '{{ $foo }}');

        $this->assertSame('bar', AnonymousViewCompiler::call('foo.blade.php', ['foo' => 'bar']));
    }

    public function testWithMissingView()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('View [foo.blade.php] not found.');

        AnonymousViewCompiler::call('foo.blade.php');
    }
}
