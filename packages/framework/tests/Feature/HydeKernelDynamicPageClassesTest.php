<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Pages\Concerns\HydePage;
use Hyde\Testing\TestCase;

abstract class TestPageClass extends HydePage
{
    //
}

class TestPageClassWithSourceInformation extends HydePage
{
    public static string $sourceDirectory = 'foo';
    public static string $outputDirectory = 'foo';
    public static string $fileExtension = '.txt';

    public function compile(): string
    {
        return '';
    }
}
