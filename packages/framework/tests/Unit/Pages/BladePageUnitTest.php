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

        /** @see BladePage::path */
        $this->expect('path')->toReturn(
            Hyde::path('_pages')
        );

        /** @see BladePage::getBladeView */
        $this->expect('getBladeView')->toReturn(
            //
        );

        /** @see BladePage::sourcePath */
        $this->expect('sourcePath')->toReturn(
            //
        );

        /** @see BladePage::files */
        $this->expect('files')->toReturn(
            //
        );

        /** @see BladePage::navigationMenuLabel */
        $this->expect('navigationMenuLabel')->toReturn(
            //
        );

        /** @see BladePage::getOutputPath */
        $this->expect('getOutputPath')->toReturn(
            //
        );

        /** @see BladePage::get */
        $this->expect('get')->toReturn(
            //
        );

        /** @see BladePage::outputDirectory */
        $this->expect('outputDirectory')->toReturn(
            //
        );

        /** @see BladePage::parse */
        $this->expect('parse')->toReturn(
            //
        );

        /** @see BladePage::navigationMenuGroup */
        $this->expect('navigationMenuGroup')->toReturn(
            //
        );

        /** @see BladePage::navigationMenuPriority */
        $this->expect('navigationMenuPriority')->toReturn(
            //
        );

        /** @see BladePage::getRouteKey */
        $this->expect('getRouteKey')->toReturn(
            //
        );

        /** @see BladePage::htmlTitle */
        $this->expect('htmlTitle')->toReturn(
            //
        );

        /** @see BladePage::all */
        $this->expect('all')->toReturn(
            //
        );

        /** @see BladePage::metadata */
        $this->expect('metadata')->toReturn(
            //
        );

        /** @see BladePage::__construct */
        $this->expect('__construct')->toReturn(
            //
        );

        /** @see BladePage::make */
        $this->expect('make')->toReturn(
            //
        );

        /** @see BladePage::getRoute */
        $this->expect('getRoute')->toReturn(
            //
        );

        /** @see BladePage::showInNavigation */
        $this->expect('showInNavigation')->toReturn(
            //
        );

        /** @see BladePage::getSourcePath */
        $this->expect('getSourcePath')->toReturn(
            //
        );

        /** @see BladePage::getLink */
        $this->expect('getLink')->toReturn(
            //
        );

        /** @see BladePage::getIdentifier */
        $this->expect('getIdentifier')->toReturn(
            //
        );

        /** @see BladePage::has */
        $this->expect('has')->toReturn(
            //
        );

        /** @see BladePage::toCoreDataObject */
        $this->expect('toCoreDataObject')->toReturn(
            //
        );

        /** @see BladePage::constructFactoryData */
        $this->expect('constructFactoryData')->toReturn(
            //
        );

        /** @see BladePage::fileExtension */
        $this->expect('fileExtension')->toReturn(
            //
        );

        /** @see BladePage::sourceDirectory */
        $this->expect('sourceDirectory')->toReturn(
            //
        );

        /** @see BladePage::compile */
        $this->expect('compile')->toReturn(
            //
        );

        /** @see BladePage::matter */
        $this->expect('matter')->toReturn(
            //
        );

        /** @see BladePage::outputPath */
        $this->expect('outputPath')->toReturn(
            //
        );
    }
}
