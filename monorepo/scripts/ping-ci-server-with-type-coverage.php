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

$data = [
    'commit' => $commit,
    'report' => file_get_contents('type-coverage.json') ?? exit(404),
    'branch' => $branch,
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

if (curl_getinfo($curl, CURLINFO_HTTP_CODE) !== 200) {
    echo 'Type coverage report failed to send';
    exit(curl_getinfo($curl, CURLINFO_HTTP_CODE));
}
