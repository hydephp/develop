<?php

function renameTestMethods($directory): void
{
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    $pattern = '/public function test_([a-zA-Z0-9_]+)\(/';

    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            echo "Renaming test methods in $file\n";

            $contents = file_get_contents($file);

            $contents = preg_replace_callback($pattern, function ($matches) {
                return 'public function test'.str_replace('_', '', ucwords($matches[1], '_')).'(';
            }, $contents);

            file_put_contents($file, $contents);
        }
    }
}

renameTestMethods('tests');
renameTestMethods('packages/hyde/tests');
renameTestMethods('packages/framework/tests');
renameTestMethods('packages/publications/tests');
renameTestMethods('packages/realtime-compiler/tests');

echo "Test methods renamed successfully.\n";
