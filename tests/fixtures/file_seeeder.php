<?php

declare(strict_types=1);

error_reporting(E_ALL);
$time = microtime(true);

// Generate some file fixtures for quick visual testing.

use Hyde\Framework\Actions\CreatesNewPageSourceFile;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;

$autoloader = require __DIR__.'/../../vendor/autoload.php';
$app = require_once __DIR__.'/../../app/bootstrap.php';

shell_exec('php hyde publish:homepage posts -n');

foreach (['Index', 'Page 1', 'Page 2', 'Page 3'] as $page) {
    file_put_contents((new CreatesNewPageSourceFile($page, DocumentationPage::class, true))->getOutputPath(),
        file_get_contents(__DIR__.'/markdown.md'),
        FILE_APPEND
    );
}

file_put_contents((new CreatesNewPageSourceFile('Markdown Page', MarkdownPage::class, true))->getOutputPath(),
    file_get_contents(__DIR__.'/markdown.md'),
    FILE_APPEND
);

file_put_contents(hyde()->path('_pages/html.html'), '<h1>HTML Page</h1>');

(new CreatesNewPageSourceFile('Blade Page', BladePage::class, true));

echo 'Finished in '.round((microtime(true) - $time) * 1000, 2).'ms';
