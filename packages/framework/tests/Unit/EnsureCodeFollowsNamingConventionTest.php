<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Illuminate\Filesystem\Filesystem;
use Hyde\Framework\Actions\BladeMatterParser;
use Hyde\Console\Commands\VendorPublishCommand;
use Hyde\Framework\Actions\CreatesNewMarkdownPostFile;
use Hyde\Framework\Actions\CreatesNewPageSourceFile;
use Hyde\Framework\Actions\MarkdownFileParser;
use Hyde\Framework\Actions\SourceFileParser;
use Hyde\Testing\UnitTestCase;
use ReflectionClass;

class EnsureCodeFollowsNamingConventionTest extends UnitTestCase
{
    public function testCommandsClassesFollowNamingConventions()
    {
        $files = glob('vendor/hyde/framework/src/Console/Commands/*.php');

        $this->assertNotEmpty($files, 'No commands found.');

        // Commands must not start with "Hyde" and must end with "Command"
        foreach ($files as $filepath) {
            $filename = basename($filepath, '.php');
            $this->assertStringStartsNotWith('Hyde', $filename);
            $this->assertStringEndsWith('Command', $filename);
        }
    }

    public function testCommandsDescriptionsFollowNamingConventions()
    {
        self::mockConfig();

        $files = glob('vendor/hyde/framework/src/Console/Commands/*.php');

        $this->assertNotEmpty($files, 'No commands found.');

        // Commands must have a string $description property
        foreach ($files as $filepath) {
            $class = 'Hyde\\Console\\Commands\\'.basename($filepath, '.php');
            $reflection = new ReflectionClass($class);

            $this->assertTrue($reflection->hasProperty('description') && $reflection->getProperty('description')->isProtected(),
                "Command class $class does not have a protected \$description property.\n ".realpath($filepath)
            );

            if ($class === VendorPublishCommand::class) {
                $params = [new Filesystem()];
            } else {
                $params = [];
            }

            $instance = new $class(...$params);
            $description = $instance->getDescription();

            $this->assertIsString($description);
            $this->assertNotEmpty($description);

            $this->assertSame(strtoupper($description[0]),
                $description[0],
                "Command class $class description does not start with an uppercase letter.\n ".realpath($filepath)
            );

            $this->assertTrue($description[strlen($description) - 1] !== '.' && $description[strlen($description) - 1] !== '!' && $description[strlen($description) - 1] !== '?',
                "Command class $class description ends with a period or another punctuation mark.\n ".realpath($filepath)
            );

            $this->assertSame($description, trim($description),
                'Command class '.$class.' description has leading or trailing whitespace.'
            );

            $this->assertStringNotContainsString('  ', $description,
                'Command class '.$class.' description has multiple consecutive spaces.'
            );

            $this->assertStringNotContainsString("\n", $description,
                'Command class '.$class.' description has a newline character.'
            );
        }
    }

    public function testActionEntryPointsFollowNamingConventions()
    {
        $files = glob('vendor/hyde/framework/src/Framework/Actions/*.php');

        $this->assertNotEmpty($files, 'No action classes found.');

        $exclude = [
            CreatesNewMarkdownPostFile::class,
            CreatesNewPageSourceFile::class,
            MarkdownFileParser::class,
            BladeMatterParser::class,
            SourceFileParser::class,
        ];

        // Actions must have either a public static handle() method or a public non-static execute() method
        foreach ($files as $filepath) {
            $class = 'Hyde\\Framework\\Actions\\'.basename($filepath, '.php');

            if (in_array($class, $exclude)) {
                continue;
            }

            $reflection = new ReflectionClass($class);

            $hasHandleMethod = $reflection->hasMethod('handle') && $reflection->getMethod('handle')->isPublic() && $reflection->getMethod('handle')->isStatic();
            $hasExecuteMethod = $reflection->hasMethod('execute') && $reflection->getMethod('execute')->isPublic() && ! $reflection->getMethod('execute')->isStatic();

            $this->assertTrue($hasHandleMethod || $hasExecuteMethod,
                "Action class $class does not have a public static handle() method or a public non-static execute() method.\n ".realpath($filepath)
            );
        }
    }
}
