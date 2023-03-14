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
        
        \033[34m     __ __        __   \033[33m ___  __ _____
        \033[34m    / // /_ _____/ /__ \033[33m/ _ \/ // / _ \
        \033[34m   / _  / // / _  / -_)\033[33m ___/ _  / ___/
        \033[34m  /_//_/\_, /\_,_/\__/\033[33m_/  /_//_/_/
        \033[34m       /___/
            
        \033[0m
        ASCII, ConsoleServiceProviderTestClass::logo());
    }
}

class ConsoleServiceProviderTestClass extends ConsoleServiceProvider
{
    public static function logo(): string
    {
        return parent::logo();
    }
}
