<?php

namespace Hyde\Framework\Contracts;

interface PageParserContract
{
    /**
     * Handle the parsing job.
     *
     * @return void
     */
    public function execute(): void;

    /**
     * Get the parsed page object.
     *
     * @return AbstractPage
     */
    public function get(): AbstractPage;
}
