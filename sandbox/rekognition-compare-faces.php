<?php
/**
 * Amazon Rekognition face comparison test.
 *
 * Credentials are optional. Omit them to authenticate via the AWS SDK default
 * provider chain (e.g. EC2 instance profile / IAM role):
 *
 * ```sh
 * # IAM role (EC2 instance profile) — no credentials
 * php sandbox/rekognition-compare-faces.php
 *
 * # Explicit IAM user access key
 * php sandbox/rekognition-compare-faces.php <ACCESS_KEY> <SECRET_KEY>
 * ```
 */
require __DIR__ . '/../vendor/autoload.php';
use \X\Rekognition\Client;

// Credentials from CLI args (optional). When omitted the SDK resolves them
// from the default provider chain (instance profile / IAM role).
$options = [];
if (!empty($argv[1]) && !empty($argv[2])) {
  $options['key'] = $argv[1];
  $options['secret'] = $argv[2];
}
$client = new Client($options);

// Read as binary blobs so it works regardless of OS (the library's file-path
// detection only recognizes Unix-style absolute paths).
$personA    = file_get_contents(__DIR__ . '/input/person_a.png');
$personAAlt = file_get_contents(__DIR__ . '/input/person_a_alt.png');
$personB    = file_get_contents(__DIR__ . '/input/person_b.png');

// Same person → high similarity is expected.
$same = $client->compareFaces($personA, $personAAlt);
echo "Same person (A vs A-alt):  {$same}%" . PHP_EOL;

// Different people → low similarity is expected.
$diff = $client->compareFaces($personA, $personB);
echo "Different people (A vs B):  {$diff}%" . PHP_EOL;
