<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\helpers;

require_once __DIR__.'/BaseHydePageUnitTest.php';

/**
 * Providers helpers and a contract for unit testing for the specified page class.
 *
 * These unit tests ensure all inherited methods are callable, and that they return the expected value.
 *
 * @see \Hyde\Framework\Testing\helpers\BaseHydePageUnitTest
 *
 * @coversNothing
 */
abstract class BaseMarkdownPageUnitTest extends BaseHydePageUnitTest
{
    abstract public function testMarkdown();

    abstract public function testSave();
}
