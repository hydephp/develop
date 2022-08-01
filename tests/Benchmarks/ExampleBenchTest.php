<?php

namespace Tests\Benchmarks;

class ExampleBenchTest extends BenchCase
{
    public function testExampleBench()
    {
         $this->benchmark(function () {
             return 'Hello World!';
         });
    }
}
