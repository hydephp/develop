<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use PHPUnit\Framework\Constraint\IsEqual;

/**
 * Test that the framework configuration files are matching the published ones.
 */
class HydeConfigFilesAreMatchingTest extends TestCase
{
    public function test_hyde_config_files_are_matching()
    {
        $this->assertFileEqualsIgnoringNewlineType(
            Hyde::path('config/hyde.php'),
            Hyde::vendorPath('config/hyde.php')
        );
    }

    public function test_site_config_files_are_matching()
    {
        $this->assertFileEqualsIgnoringNewlineType(
            Hyde::path('config/site.php'),
            Hyde::vendorPath('config/site.php')
        );
    }

    public function test_docs_config_files_are_matching()
    {
        $this->assertFileEqualsIgnoringNewlineType(
            Hyde::path('config/docs.php'),
            Hyde::vendorPath('config/docs.php')
        );
    }

    public function test_markdown_config_files_are_matching()
    {
        $this->assertFileEqualsIgnoringNewlineType(
            Hyde::path('config/markdown.php'),
            Hyde::vendorPath('config/markdown.php')
        );
    }

    protected function assertFileEqualsIgnoringNewlineType(string $expected, string $actual, string $message = ''): void
    {
        static::assertFileExists($expected, $message);
        static::assertFileExists($actual, $message);

        $constraint = new IsEqual(str_replace("\r", '', file_get_contents($expected)));

        static::assertThat(str_replace("\r", '', file_get_contents($actual)), $constraint, $message);
    }
}
