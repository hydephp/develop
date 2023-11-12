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
