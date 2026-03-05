<?php
namespace X\Util;

/**
 * Data validation utility class.
 *
 * Provides validation methods for common data types including hostname,
 * IP address, CIDR notation, email, port number, Unix username, and file paths.
 */
final class Validation {
  /**
   * Validate hostname format.
   *
   * Accepts standard domain names and "localhost".
   *
   * @param string $value String to validate.
   * @return bool True if valid hostname.
   */
  public static function hostname(string $value): bool {
    return preg_match('/^(?!:\/\/)([a-zA-Z0-9-_]+\.)*[a-zA-Z0-9][a-zA-Z0-9-_]+\.[a-zA-Z]{2,11}?$/', $value) === 1
      || $value === 'localhost';
  }

  /**
   * Validate IPv4 address format.
   *
   * @param string $value String to validate.
   * @return bool True if valid IPv4 address.
   */
  public static function ipaddress(string $value): bool {
    return preg_match('/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/', $value) === 1;
  }

  /**
   * Validate IPv4 address or CIDR notation format.
   *
   * @param string $value String to validate (e.g., "192.168.1.0" or "192.168.1.0/24").
   * @return bool True if valid IPv4 address or CIDR notation.
   */
  public static function ipaddress_or_cidr(string $value): bool {
    return preg_match('/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])(\/(\d|[1-2]\d|3[0-2]))?$/', $value) === 1;
  }

  /**
   * Validate hostname or IPv4 address format.
   *
   * @param string $value String to validate.
   * @return bool True if valid hostname or IPv4 address.
   */
  public static function hostname_or_ipaddress(string $value): bool {
    return preg_match('/^((([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])|((?!:\/\/)([a-zA-Z0-9-_]+\.)*[a-zA-Z0-9][a-zA-Z0-9-_]+\.[a-zA-Z]{2,11}?))$/', $value) === 1
      || $value === 'localhost';
  }

  /**
   * Validate Unix username format.
   *
   * Must start with lowercase letter or underscore, followed by up to 31
   * lowercase alphanumeric chars, hyphens, or underscores.
   *
   * @param string $value String to validate.
   * @return bool True if valid Unix username.
   */
  public static function unix_username(string $value): bool {
    return preg_match('/^[a-z_]([a-z0-9_-]{0,31}|[a-z0-9_-]{0,30}\$)$/', $value) === 1;
  }

  /**
   * Validate port number (0-65535).
   *
   * @param string $value String to validate.
   * @return bool True if valid port number.
   */
  public static function port(string $value): bool {
    return preg_match('/^\d+$/', $value) && (int) $value >= 0 && (int) $value <= 65535;
  }

  /**
   * Validate email address format.
   *
   * Uses the FormValidation.io library's regular expression for validation.
   *
   * @param string $value String to validate.
   * @return bool True if valid email address.
   */
  public static function email(string $value): bool {
    // NOTE: Changed to the regular expression used for email address validation in the Form Validation JS library(https://formvalidation.io/guide/validators/email-address/).
    return preg_match('/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/', $value) === 1;
    // return preg_match("/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/", $value) === 1;
  }

  /**
   * Validate Unix file or directory path format.
   *
   * @param string $value String to validate.
   * @param bool $denyLeadingSlash Reject paths starting with "/". Default is false.
   * @return bool True if valid path.
   */
  public static function is_path(string $value, bool $denyLeadingSlash=false): bool {
    // UNIX path regular expression.
    // Based on the "/^(\/|(\/[\w\s@^!#$%&-]+)+(\.[a-z]+\/?)?)$/i" regular expression, the leading and trailing slashes have been improved to be optional.
    $re = "/^(\/|(\/?[\w\s@^!#$%&-\.]+)+\/?)$/";

    // Validate input values.
    $valid = preg_match($re, $value) === 1;

    // If leading slashes are allowed, return the result immediately.
    if (!$denyLeadingSlash)
      return $valid;

    // If leading slashes are not allowed, an error is returned if there is a leading slash.
    return $valid && preg_match('/^\//', $value) === 0;
  }
}