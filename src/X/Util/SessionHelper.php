<?php
namespace X\Util;
use \X\Util\Logger;

/**
 * Session data utility class.
 *
 * Provides methods for unserializing PHP session data with support for
 * different serialize handlers (php, php_binary).
 */
final class SessionHelper {
  /**
   * Unserialize session data using regex-based splitting.
   *
   * @param string $session Serialized session string.
   * @return array Associative array of session key-value pairs.
   */
  public static function unserialize(string $session) {
    $unserialized = [];
    $params = preg_split('/([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\|/', $session, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    for($i=0; $params[$i]; $i++)
      $unserialized[$params[$i++]] = unserialize($params[$i]);
    return $unserialized;
  }

  /**
   * Unserialize session data for the "php" serialize handler.
   *
   * Parses the pipe-delimited format used when session.serialize_handler = "php".
   *
   * @param string $session Serialized session string.
   * @return array Associative array of session key-value pairs.
   * @throws \RuntimeException If session data is malformed.
   */
  public static function unserializePhp(string $session): array {
    $unserialized = [];
    $offset = 0;
    while ($offset < strlen($session)) {
      if (!strstr(substr($session, $offset), '|'))
        throw new \RuntimeException('invalid data, remaining: ' . substr($session, $offset));
      $pos = strpos($session, '|', $offset);
      $num = $pos - $offset;
      $varName = substr($session, $offset, $num);
      $offset += $num + 1;
      $data = unserialize(substr($session, $offset));
      $unserialized[$varName] = $data;
      $offset += strlen(serialize($data));
    }
    return $unserialized;
  }

  /**
   * Unserialize session data for the "php_binary" serialize handler.
   *
   * Parses the binary length-prefixed format used when
   * session.serialize_handler = "php_binary".
   *
   * @param string $session Serialized session string.
   * @return array Associative array of session key-value pairs.
   */
  public static function unserializePhpBinary(string $session): array {
    $unserialized = [];
    $offset = 0;
    while ($offset < strlen($session)) {
      $num = ord($session[$offset]);
      $offset += 1;
      $varName = substr($session, $offset, $num);
      $offset += $num;
      $data = unserialize(substr($session, $offset));
      $unserialized[$varName] = $data;
      $offset += strlen(serialize($data));
    }
    return $unserialized;
  }
}