<?php

use Symfony\Component\Console\Output\BufferedOutput;
use Termwind\Repositories\Styles;

use function Termwind\{renderUsing};

beforeEach(function () {
    renderUsing($this->output = new BufferedOutput());
});

afterEach(function () {
    renderUsing(null);

    Styles::flush();
});

test('printStartMessage method', function () {
    // Todo handle version dynamically

    $output = new \Hyde\RealtimeCompiler\ConsoleOutput();
    $output->printStartMessage('localhost', 8000);
    $this->assertSame(<<<'TXT'

     ╭────────────────────────────────────╮
     │                                    │
     │ HydePHP Realtime Compiler v1.3.3   │
     │                                    │
     │ Listening on http://localhost:8000 │
     │                                    │
     ╰────────────────────────────────────╯


    TXT, str_replace(["\u{A0}", "\r"], [' ', ''], $this->output->fetch()));
});
