<?php

declare(strict_types=1);

namespace Hyde\Testing\Common;

/**
 * Providers helpers and a contract for unit testing for the specified page class.
 *
 * These unit tests ensure all inherited methods are callable, and that they return the expected value.
 *
 * @see \Hyde\Testing\Common\BaseHydePageUnitTest
 *
 * @coversNothing
 */
abstract class BaseMarkdownPageUnitTest extends BaseHydePageUnitTest
{
    abstract public function testMarkdown();

    abstract public function testSave();
}
