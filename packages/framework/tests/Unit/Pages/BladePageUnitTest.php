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
            '// TODO'
        );

        /** @see BladePage::sourcePath */
        $this->expect('sourcePath')->toReturn(
            '// TODO'
        );

        /** @see BladePage::files */
        $this->expect('files')->toReturn(
            '// TODO'
        );

        /** @see BladePage::navigationMenuLabel */
        $this->expect('navigationMenuLabel')->toReturn(
            '// TODO'
        );

        /** @see BladePage::getOutputPath */
        $this->expect('getOutputPath')->toReturn(
            '// TODO'
        );

        /** @see BladePage::get */
        $this->expect('get')->toReturn(
            '// TODO'
        );

        /** @see BladePage::outputDirectory */
        $this->expect('outputDirectory')->toReturn(
            '// TODO'
        );

        /** @see BladePage::parse */
        $this->expect('parse')->toReturn(
            '// TODO'
        );

        /** @see BladePage::navigationMenuGroup */
        $this->expect('navigationMenuGroup')->toReturn(
            '// TODO'
        );

        /** @see BladePage::navigationMenuPriority */
        $this->expect('navigationMenuPriority')->toReturn(
            '// TODO'
        );

        /** @see BladePage::getRouteKey */
        $this->expect('getRouteKey')->toReturn(
            '// TODO'
        );

        /** @see BladePage::htmlTitle */
        $this->expect('htmlTitle')->toReturn(
            '// TODO'
        );

        /** @see BladePage::all */
        $this->expect('all')->toReturn(
            '// TODO'
        );

        /** @see BladePage::metadata */
        $this->expect('metadata')->toReturn(
            '// TODO'
        );

        /** @see BladePage::__construct */
        $this->expect('__construct')->toReturn(
            '// TODO'
        );

        /** @see BladePage::make */
        $this->expect('make')->toReturn(
            '// TODO'
        );

        /** @see BladePage::getRoute */
        $this->expect('getRoute')->toReturn(
            '// TODO'
        );

        /** @see BladePage::showInNavigation */
        $this->expect('showInNavigation')->toReturn(
            '// TODO'
        );

        /** @see BladePage::getSourcePath */
        $this->expect('getSourcePath')->toReturn(
            '// TODO'
        );

        /** @see BladePage::getLink */
        $this->expect('getLink')->toReturn(
            '// TODO'
        );

        /** @see BladePage::getIdentifier */
        $this->expect('getIdentifier')->toReturn(
            '// TODO'
        );

        /** @see BladePage::has */
        $this->expect('has')->toReturn(
            '// TODO'
        );

        /** @see BladePage::toCoreDataObject */
        $this->expect('toCoreDataObject')->toReturn(
            '// TODO'
        );

        /** @see BladePage::constructFactoryData */
        $this->expect('constructFactoryData')->toReturn(
            '// TODO'
        );

        /** @see BladePage::fileExtension */
        $this->expect('fileExtension')->toReturn(
            '// TODO'
        );

        /** @see BladePage::sourceDirectory */
        $this->expect('sourceDirectory')->toReturn(
            '// TODO'
        );

        /** @see BladePage::compile */
        $this->expect('compile')->toReturn(
            '// TODO'
        );

        /** @see BladePage::matter */
        $this->expect('matter')->toReturn(
            '// TODO'
        );

        /** @see BladePage::outputPath */
        $this->expect('outputPath')->toReturn(
            '// TODO'
        );
    }
}
