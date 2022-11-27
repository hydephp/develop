<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\Concerns\HydePage;

require_once __DIR__ . '/BaseHydePageUnitTest.php';

/**
 * @covers \Hyde\Pages\BladePage
 */
class BladePageUnitTest extends BaseHydePageUnitTest
{
    protected static string|HydePage $page = BladePage::class;

    protected function setUp(): void
    {
        parent::setUp();

        $this->expect('path')->toReturn(
            Hyde::path('_pages')
        );

        $this->expect('getBladeView')->toReturn(
            //
        );

        $this->expect('sourcePath')->toReturn(
            //
        );

        $this->expect('files')->toReturn(
            //
        );

        $this->expect('navigationMenuLabel')->toReturn(
            //
        );

        $this->expect('getOutputPath')->toReturn(
            //
        );

        $this->expect('get')->toReturn(
            //
        );

        $this->expect('outputDirectory')->toReturn(
            //
        );

        $this->expect('parse')->toReturn(
            //
        );

        $this->expect('navigationMenuGroup')->toReturn(
            //
        );

        $this->expect('navigationMenuPriority')->toReturn(
            //
        );

        $this->expect('getRouteKey')->toReturn(
            //
        );

        $this->expect('htmlTitle')->toReturn(
            //
        );

        $this->expect('all')->toReturn(
            //
        );

        $this->expect('metadata')->toReturn(
            //
        );

        $this->expect('__construct')->toReturn(
            //
        );

        $this->expect('make')->toReturn(
            //
        );

        $this->expect('getRoute')->toReturn(
            //
        );

        $this->expect('showInNavigation')->toReturn(
            //
        );

        $this->expect('getSourcePath')->toReturn(
            //
        );

        $this->expect('getLink')->toReturn(
            //
        );

        $this->expect('getIdentifier')->toReturn(
            //
        );

        $this->expect('has')->toReturn(
            //
        );

        $this->expect('toCoreDataObject')->toReturn(
            //
        );

        $this->expect('constructFactoryData')->toReturn(
            //
        );

        $this->expect('fileExtension')->toReturn(
            //
        );

        $this->expect('sourceDirectory')->toReturn(
            //
        );

        $this->expect('compile')->toReturn(
            //
        );

        $this->expect('matter')->toReturn(
            //
        );

        $this->expect('outputPath')->toReturn(
            //
        );
    }
}
