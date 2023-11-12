<?php

use Hyde\Foundation\HydeKernel;
use Symfony\Component\Console\Output\BufferedOutput;
use Termwind\Repositories\Styles;

use function Termwind\{renderUsing};

uses(\Hyde\Testing\UnitTestCase::class);

beforeEach(function () {
    renderUsing($this->output = new BufferedOutput());

    $this::mockConfig();
});

afterEach(function () {
    renderUsing(null);

    Styles::flush();

    HydeKernel::setInstance(new HydeKernel());
});

test('printStartMessage method', function () {
    HydeKernel::setInstance(new class extends HydeKernel
    {
        public static function version(): string
        {
            return '1.2.3';
        }
    });

    $output = new \Hyde\RealtimeCompiler\ConsoleOutput();
    $output->printStartMessage('localhost', 8000);
    $this->assertSame(<<<'TXT'

     ╭─────────────────────────────────────╮
     │                                     │
     │ HydePHP Realtime Compiler v1.2.3    │
     │                                     │
     │ Listening on: http://localhost:8000 │
     │                                     │
     ╰─────────────────────────────────────╯


    TXT, str_replace(["\u{A0}", "\r"], [' ', ''], $this->output->fetch()));
});
