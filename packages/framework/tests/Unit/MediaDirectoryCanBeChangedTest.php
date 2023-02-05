<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * High level test of the feature that allows the media source (_media) directory,
 * and the media output directory (_site/media) to be changed.
 *
 * @see \Hyde\Framework\Testing\Unit\BuildOutputDirectoryCanBeChangedTest
 * @see \Hyde\Framework\Testing\Feature\ConfigurableSourceRootsFeatureTest
 */
class MediaDirectoryCanBeChangedTest extends TestCase
{
    public function test_media_output_directory_can_be_changed_for_site_builds()
    {
        $this->markTestIncomplete('Code tested in this test is not yet implemented.');

        Filesystem::deleteDirectory('_site');

        $this->directory('_assets');
        $this->file('_assets/app.css');

        Hyde::setMediaDirectory('_assets');

        $this->artisan('build');

        $this->assertDirectoryDoesNotExist(Hyde::path('_site/media'));
        $this->assertDirectoryExists(Hyde::path('_site/assets'));
        $this->assertFileExists(Hyde::path('_site/assets/app.css'));

        $this->resetSite();
    }
}
