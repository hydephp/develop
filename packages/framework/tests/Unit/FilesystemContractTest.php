<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Support\Contracts\FilesystemContract;
use Hyde\Testing\TestCase;
use Illuminate\Filesystem\Filesystem;
use ReflectionClass;
use function array_diff;
use function get_class_methods;

/**
 * @covers \Hyde\Support\Contracts\FilesystemContract
 */
class FilesystemContractTest extends TestCase
{
    public function testAllBaseMethodsAreCoveredByInterface()
    {
        foreach ($this->getBaseMethods(new ReflectionClass(Filesystem::class)) as $method) {
            $this->assertContains($method->name, get_class_methods(FilesystemContract::class));
        }
    }

    protected function getBaseMethods(ReflectionClass $reflectionClass): array
    {
        $baseMethods = $reflectionClass->getMethods();
        foreach ($reflectionClass->getTraits() as $trait) {
            $baseMethods = array_diff($baseMethods, $trait->getMethods());
        }
        return $baseMethods;
    }
}
