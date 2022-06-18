<?php

// Script to use in the Monorepo split action to update the root Composer.json
// creating one that works for the hyde/hyde project.

// We do two main things:
// 1. Replace dev-master versions with the configured versions.
// 2. Remove the package entries from the repositories configuration.

// Usage:
// This script is intended to be downloaded and run in split-monorepo.yml
// where it follows roughly the flow below:
// 1. Checkout ref readonly-hyde-mirror
// 2. Download the script to the root
// 3. Run the script, then delete it

// @todo check if we need mockery and pest dependencies

// Configuration settings
const frameworkVersion = '^0.38';
const rcVersion = '^2.1';
$time_start = microtime(true);
echo "Transforming composer.json\n";

$json = json_decode(file_get_contents('composer.json'), true);

$json['require']['hyde/framework'] = frameworkVersion;
$json['require-dev']['hyde/realtime-compiler'] = rcVersion;

unset($json['repositories']);

file_put_contents('composer.json', json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo 'Done. Finished in '.number_format((microtime(true) - $time_start) * 1000, 2)."ms\n";
exit(0);
