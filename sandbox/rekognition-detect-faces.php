<?php
/**
 * Amazon Rekognition face detection test.
 *
 * Credentials are optional. Omit them to authenticate via the AWS SDK default
 * provider chain (e.g. EC2 instance profile / IAM role):
 *
 * ```sh
 * # IAM role (EC2 instance profile) — no credentials
 * php sandbox/rekognition-detect-faces.php
 *
 * # Explicit IAM user access key
 * php sandbox/rekognition-detect-faces.php <ACCESS_KEY> <SECRET_KEY>
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

// Read as a binary blob so it works regardless of OS (the library's file-path
// detection only recognizes Unix-style absolute paths).
$image = file_get_contents(__DIR__ . '/input/person_a.png');

$faces = $client->detectionFaces($image);
echo 'Detected faces: ' . count($faces) . PHP_EOL;
echo print_r($faces, true);
