<?php

declare(strict_types=1);

namespace Tests\Benchmarks;

class ExampleBenchmark extends BenchCase
{
    public function testExample()
    {
        $this->benchmark(function () {
            return 'Hello World!';
        });
    }
}
