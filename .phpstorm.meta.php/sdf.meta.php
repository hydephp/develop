<?php

namespace PHPSTORM_META {

    override(\app(0), map([
        '' => '@',
        'navigation.main' => \Hyde\Framework\Features\Navigation\MainNavigationMenu::class,
        'navigation.sidebar' => \Hyde\Framework\Features\Navigation\DocumentationSidebar::class,
    ]));

    expectedArguments(\app(), 0, 'navigation.main', 'navigation.sidebar');
}
