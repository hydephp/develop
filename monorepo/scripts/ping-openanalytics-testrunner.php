<?php

/**
 * @internal This script is used to ping the OpenAnalytics server with the test results.
 *
 * @example php ping.php 'Monorepo Smoke Tests' ${{ secrets.OPENANALYTICS_TOKEN }}
 *
 * @uses vendor/bin/pest --stop-on-failure --log-junit report.xml
 */
echo "Pinging statistics server\n";

$runner = $argv[1] ?? exit(400);
$token = $argv[2] ?? null;
if ($token === null) {
    // Probably running in a fork
    echo "::warning:: No token provided, skipping ping\n";
    exit(0);
}

if (! file_exists('report.xml')) {
    exit(404);
}

$url = 'https://analytics.hydephp.se/api/test_runs';
$data = [
    'runner' => json_encode($runner),
];

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$headers = [
    'Accept: application/json',
    "Authorization: Bearer $token",
    'Content-Type: application/json',
];
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

//    Supported fields:
//    'timestamp' => 'date:nullable',
//    'runner' => 'string:nullable',
//    'tests' => 'integer',
//    'assertions' => 'integer:nullable',
//    // Since 2023-02-28
//    'time' => 'integer:nullable',
//    'commit' => 'string:nullable',
//    'branch' => 'string:nullable',
//    'runner_os' => 'string:nullable',

$junit = str_replace("''", '""', str_replace('"', '', str_replace('""', "''",
    substr(substr(explode("\n", file_get_contents('report.xml'))[2], 13), 0, -3)))
);
foreach (explode(' ', $junit) as $pair) {
    $data[explode('=', $pair)[0]] = explode('=', $pair)[1];
}

$data['commit'] = shell_exec('git rev-parse HEAD');
$data['branch'] = shell_exec('git branch --show-current');
$data['runner_os'] = php_uname('s');

curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

$resp = curl_exec($curl);
curl_close($curl);
var_dump($resp);

// if curl has 401 it's probably as it was run in a fork so that's fine, but if it's another error 400 or above we should fail the build
$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
if ($code >= 400 && $code !== 401) {
    // exit($code);
    echo "::warning:: Failed to ping statistics server with code $code\n";
}
