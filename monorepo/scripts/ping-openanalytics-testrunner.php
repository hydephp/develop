<?php

/**
 * @internal This script is used to ping the OpenAnalytics server with the test results.
 *
 * @example php ping.php 'Monorepo Smoke Tests' ${{ secrets.OPENANALYTICS_TOKEN }}
 *
 * @uses vendor/bin/pest --stop-on-failure --testdox-text testdox.txt
 */
echo "Pinging statistics server\n";

$runner = $argv[1] ?? exit(400);
$token = $argv[2] ?? exit(400);

$url = 'https://analytics.hydephp.se/api/test_runs';

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

$data = [
    'runner' => json_encode($runner),
    'tests' => substr_count(file_get_contents('testdox.txt') ?: exit(404), '[x]'),
];

curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

$resp = curl_exec($curl);
curl_close($curl);
var_dump($resp);
