<?php

use Hyde\Framework\Hyde;
use Illuminate\Support\Facades\File;

if (! function_exists('unlinkIfExists')) {
    function unlinkIfExists(string $filepath)
    {
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }
}

if (! function_exists('backupDirectory')) {
    /** @deprecated v0.60.x - You should not run tests in a production environment. */
    function backupDirectory(string $directory)
    {
        if (file_exists($directory)) {
            File::copyDirectory($directory, $directory.'-bak', true);
        }
    }
}

if (! function_exists('restoreDirectory')) {
    /** @deprecated v0.60.x - You should not run tests in a production environment. */
    function restoreDirectory(string $directory)
    {
        if (file_exists($directory.'-bak')) {
            File::moveDirectory($directory.'-bak', $directory, true);
            File::deleteDirectory($directory.'-bak');
        }
    }
}

if (! function_exists('deleteDirectory')) {
    function deleteDirectory(string $directory)
    {
        if (file_exists($directory)) {
            File::deleteDirectory($directory);
        }
    }
}

if (! function_exists('createTestPost')) {
    /** @deprecated - You usually don't need an actual post file anymore. Use touch() instead. */
    function createTestPost(?string $path = null): string
    {
        $path = Hyde::path($path ?? '_posts/test-post.md');
        file_put_contents($path, '---
title: My New Post
category: blog
author: Mr. Hyde
---

# My New Post

This is a post stub used in the automated tests
');

        return $path;
    }
}

if (! function_exists('unlinkUnlessDefault')) {
    function unlinkUnlessDefault(string $filepath)
    {
        $protected = [
            'app.css',
            'index.blade.php',
            '404.blade.php',
            '.gitkeep',
        ];

        if (! in_array(basename($filepath), $protected)) {
            unlink($filepath);
        }
    }
}
