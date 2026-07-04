<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Hyde;
use Hyde\Testing\UnitTestCase;
use Hyde\Enums\OverwriteAction;
use Hyde\Framework\Services\OverwritePolicy;

use function file_put_contents;
use function unlink;
use function uniqid;
use function is_file;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Framework\Services\OverwritePolicy::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Enums\OverwriteAction::class)]
class OverwritePolicyTest extends UnitTestCase
{
    protected static bool $needsKernel = true;

    protected string $source;
    protected string $destination;

    protected function setUp(): void
    {
        // The Filesystem facade resolves paths relative to the project root, so the fixtures live there.
        $this->source = 'overwrite-policy-source-'.uniqid().'.tmp';
        $this->destination = 'overwrite-policy-destination-'.uniqid().'.tmp';
    }

    protected function tearDown(): void
    {
        foreach ([$this->source, $this->destination] as $path) {
            if (is_file(Hyde::path($path))) {
                unlink(Hyde::path($path));
            }
        }
    }

    public function testDecidesToCopyWhenDestinationIsMissing()
    {
        $this->putSource('Hello world');

        $this->assertSame(OverwriteAction::Copy, OverwritePolicy::decide($this->source, $this->destination));
    }

    public function testDecidesToSkipWhenDestinationIsIdenticalToSource()
    {
        $this->putSource('Hello world');
        $this->putDestination('Hello world');

        $this->assertSame(OverwriteAction::Skip, OverwritePolicy::decide($this->source, $this->destination));
    }

    public function testDecidesToBlockWhenDestinationDiffersFromSource()
    {
        $this->putSource('Hello world');
        $this->putDestination('Hello world, but modified by the user');

        $this->assertSame(OverwriteAction::Blocked, OverwritePolicy::decide($this->source, $this->destination));
    }

    public function testDecidesToSkipWhenFilesDifferOnlyByLineEndings()
    {
        $this->putSource("Hello\nworld\n");
        $this->putDestination("Hello\r\nworld\r\n");

        $this->assertSame(OverwriteAction::Skip, OverwritePolicy::decide($this->source, $this->destination));
    }

    protected function putSource(string $contents): void
    {
        file_put_contents(Hyde::path($this->source), $contents);
    }

    protected function putDestination(string $contents): void
    {
        file_put_contents(Hyde::path($this->destination), $contents);
    }
}
