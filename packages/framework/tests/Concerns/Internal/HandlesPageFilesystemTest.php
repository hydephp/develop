<?php

namespace Hyde\Framework\Testing\Concerns\Internal;

use Hyde\Framework\Concerns\HydePage;
use Hyde\Testing\TestCase;

class HandlesPageFilesystemTestClass extends HydePage
{
    public static string $sourceDirectory = 'source';
    public static string $outputDirectory = 'output';
    public static string $fileExtension = '.md';
    public static string $template = 'template';

    public function compile(): string
    {
        return '';
    }
}
