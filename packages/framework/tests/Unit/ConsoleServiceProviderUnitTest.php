<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Console\ConsoleServiceProvider;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Console\ConsoleServiceProvider
 */
class ConsoleServiceProviderUnitTest extends UnitTestCase
{
    public function testProviderRegistersLogo()
    {
        $this->assertSame(<<<ASCII
        
        \033[94m     __ __        __   \033[91m ___  __ _____
        \033[94m    / // /_ _____/ /__ \033[91m/ _ \/ // / _ \
        \033[94m   / _  / // / _  / -_)\033[91m ___/ _  / ___/
        \033[94m  /_//_/\_, /\_,_/\__/\033[91m_/  /_//_/_/
        \033[94m       /___/
            
        \033[0m
        ASCII, ConsoleServiceProviderTestClass::logo());
    }

    public function testProviderRegistersNoAnsiLogo()
    {
        $serverBackup = $_SERVER;

        $_SERVER['argv'] = ['--no-ansi'];

        $this->assertSame('HydePHP', ConsoleServiceProviderTestClass::logo());

        $_SERVER = $serverBackup;
    }
}

class ConsoleServiceProviderTestClass extends ConsoleServiceProvider
{
    public static function logo(): string
    {
        return parent::logo();
    }
}
