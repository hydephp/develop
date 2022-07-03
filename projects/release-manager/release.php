<?php

// Internal script I bodged together, don't use in production, verify before committing, etc.

// Run with `php .\projects\release-manager\release.php`

echo "Preparing a new syndicated HydePHP release!\n";

echo "Using NPM for versioning...\n";

$version = trim(shell_exec('npm version minor --no-git-tag-version')).'-beta';

echo "Version: $version\n";

echo "Updating Hyde composer.json...\n";

// get just the minor number
$shortVersion = substr($version, 3);
// trim everything after the first dot
$shortVersion = substr($shortVersion, 0, strpos($shortVersion, '.'));

echo "Short version: $shortVersion\n";
$composerJson = json_decode(file_get_contents(__DIR__.'./../../packages/hyde/composer.json'), true);
$composerJson['require']['hyde/framework'] = "^0.$shortVersion";
file_put_contents(__DIR__.'./../../packages/hyde/composer.json', json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "Transforming upcoming release notes... \n";

$notes = file_get_contents(__DIR__.'/../../RELEASE_NOTES.md');

$notes = str_replace("\r", '', $notes);

// remove default release notes
$defaults = [
    '- for new features.',
    '- for changes in existing functionality.',
    '- for soon-to-be removed features.',
    '- for now removed features.',
    '- for any bug fixes.',
    '- in case of vulnerabilities.',
];

foreach ($defaults as $default) {
    $notes = str_replace($default, '', $notes);
}

$notes = str_replace('Keep an Unreleased section at the top to track upcoming changes.

This serves two purposes:

1. People can see what changes they might expect in upcoming releases
2. At release time, you can move the Unreleased section changes into a new release version section.', '', $notes);

$notes = trim($notes);

$notes = str_replace('## [Unreleased]', "## [$version](https://github.com/hydephp/develop/releases/tag/$version)", $notes);
$notes = str_replace('YYYY-MM-DD', date('Y-m-d'), $notes);
$notes = $notes."\n";

echo "Done. \n";

echo 'Resetting upcoming release notes stub';
copy(__DIR__.'/release-notes.stub', __DIR__.'/../../RELEASE_NOTES.md');

echo 'Updating changelog with the upcoming release notes... ';

$changelog = file_get_contents(__DIR__.'/../../CHANGELOG.md');

$needle = '<!-- CHANGELOG_START -->';

$changelog = substr_replace($changelog, $needle."\n\n".$notes, strpos($changelog, $needle), strlen($needle));
file_put_contents(__DIR__.'/../../CHANGELOG.md', $changelog);

echo "Done. \n";

$title = "$version - ".date('Y-m-d');
$body = ltrim(substr($notes, strpos($notes, "\n") + 2));
$companionBody = sprintf('Please see the release notes in the development monorepo https://github.com/hydephp/develop/releases/tag/%s', $version);

echo "\nAll done!\nNext, verify the changes, then you can commit the release with the following message: \n";
echo "$title\n";
echo "And here is a link to publish the release: \n";
echo "https://github.com/hydephp/develop/releases/new?tag=$version&title=".urlencode($title).'&body='.urlencode($body)."\n";

echo "\n\nThen you can use the following to to create the companion releases: \n";
echo "https://github.com/hydephp/framework/releases/new?tag=$version&title=".urlencode($title).'&body='.urlencode($companionBody)."\n";
echo "https://github.com/hydephp/hyde/releases/new?tag=$version&title=".urlencode($title).'&body='.urlencode($companionBody)."\n";
