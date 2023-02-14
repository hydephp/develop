<?php

define('TIME_START', microtime(true));

/**
 * @internal This script is used to ping the CI server with the type coverage results.
 * @example php __FILE__ ${{ secrets.CI_SERVER_TOKEN }} ${{ github.event.pull_request.head.sha }}
 * @uses vendor/bin/psalm > psalmout.txt
 */

echo "Pinging CI server\n";

$token = $argv[1] ?? exit(400);
$commit = $argv[2] ?? exit(400);

// Very inefficient, but it works fine for our purposes.
function getCoverage(string $contents): string
{
    $lines = explode(PHP_EOL, $contents);
    foreach ($lines as $line) {
        if (str_starts_with($line, 'Psalm was able to infer types for ') && str_ends_with($line, '% of the codebase')) {
            return substr($line, 34, -16);
        }
    }
    throw new \Exception('Could not find coverage in Psalm output');
}

$data = [
    'commit' => $commit,
    'coverage' => getCoverage(file_get_contents('psalmout.txt')),
    'time' => microtime(true) - TIME_START,
];

$url = 'https://ci.hydephp.se/api/github/actions/type-coverage';

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$headers = array(
    'Accept: application/json',
    "Authorization: Bearer $token",
    'Content-Type: application/json',
);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

$resp = curl_exec($curl);
curl_close($curl);
var_dump($resp);
