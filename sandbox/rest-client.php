<?php
/**
 * REST client test. Start the server first, then run this client:
 *
 * ```sh
 * # Terminal 1 — start the server
 * cd sandbox && php -S localhost:9000
 *
 * # Terminal 2 — run the client
 * php sandbox/rest-client.php
 * ```
 */
require __DIR__ . '/../src/X/Util/RestClient.php';
require __DIR__ . '/../src/X/Util/Logger.php';
use \X\Util\RestClient;

// REST Client.
$client = new RestClient([
  'base_url' => 'http://localhost:9000/rest-server.php',
  'debug' => true,
  'headers' => [
    'X-My-Key' => 'foo',
  ],
]);

// Send request with custom headers.
$res = $client->get('/');

// Output response.
echo 'HTTP Status: ' . $res->status . PHP_EOL;
echo 'HTTP Body: ' . print_r($res->response, true) . PHP_EOL;