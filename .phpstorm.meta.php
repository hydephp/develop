<?php

namespace PHPSTORM_META {
    override(\app(0), map([
        'navigation.main' => \Hyde\Framework\Features\Navigation\MainNavigationMenu::class,
        'navigation.sidebar' => \Hyde\Framework\Features\Navigation\DocumentationSidebar::class,
    ]));
    override((new \Illuminate\Contracts\Container\Container())->make(0), map([
        \Hyde\Foundation\HydeKernel::class => \Hyde\Foundation\HydeKernel::class,
    ]));
}
