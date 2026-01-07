<?php
namespace X\Exception;

/**
 * Exception thrown when REST API request fails.
 *
 * Thrown by RestClient when HTTP requests encounter errors
 * (e.g., connection timeout, HTTP error status codes, invalid responses).
 *
 * Usage:
 * ```php
 * try {
 *   $response = RestClient::get('https://api.example.com/users');
 * } catch (RestClientException $e) {
 *   Logger::error('API request failed: ' . $e->getMessage());
 * }
 * ```
 */
class RestClientException extends \Exception {}