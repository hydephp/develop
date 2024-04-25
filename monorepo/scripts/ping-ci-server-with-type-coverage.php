<?php

define('TIME_START', microtime(true));

/**
 * @internal This script is used to ping the CI server with the type coverage results.
 *
 * @example php __FILE__ ${{ secrets.CI_SERVER_TOKEN }} ${{ github.event.pull_request.head.sha }} ${{ github.head_ref }}
 *
 * @uses vendor/bin/psalm > psalmout.txt
 */
echo "Pinging CI server\n";

$token = $argv[1] ?? exit(400);
$commit = $argv[2] ?? exit(400);
$branch = $argv[3] ?? 'master';
$runId = $argv[4] ?? null;

if (file_exists('psalmout.txt')) {
    // Count the number of errors in the output
    $psalmErrors = substr_count(file_get_contents('psalmout.txt'), '[0;31mERROR[0m: ');
}

$data = [
    'commit' => $commit,
    'report' => file_get_contents('type-coverage.json') ?? exit(404),
    'branch' => $branch,
    'psalmErrors' => $psalmErrors ?? null,
    'runId' => $runId,
];

$url = 'https://ci.hydephp.com/api/github/actions/type-coverage';

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

curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

$resp = curl_exec($curl);
curl_close($curl);
var_dump($resp);

// if curl has 401 it's probably as it was run in a fork so that's fine, but if it's another error 400 or above we should fail the build
$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
if ($code >= 400 && $code !== 401) {
    echo "::warning:: Failed to send type report to statistics server with code $code\n";
    echo 'Type coverage report failed to send';
    // exit($code);
}
