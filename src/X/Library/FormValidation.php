<?php
namespace X\Library;
use \X\Util\Validation;

/**
 * Extended form validation class.
 *
 * Provides additional validation rules beyond CodeIgniter's built-in form validation.
 * Includes validation for datetime, hostname, IP address, email, file paths, etc.
 *
 * Usage:
 * ```php
 * $this->form_validation
 *   ->set_data(['email' => 'user@example.com', 'port' => '8080'])
 *   ->set_rules('email', 'Email', 'required|email')
 *   ->set_rules('port', 'Port', 'required|port');
 * ```
 */
abstract class FormValidation extends \CI_Form_validation {
  /**
   * Initialize form validation.
   *
   * @param array $rules Initial validation rules. Default is empty array.
   */
  function __construct($rules=[]) {
    parent::__construct($rules);
  }

  /**
   * Validate datetime format.
   *
   * @example
   * ```php
   * $this->form_validation
   *   ->set_data(['datetime' => '2021-02-03 17:46:00'])
   *   ->set_rules('datetime', 'datetime', 'required|datetime[Y-m-d H:i:s]');
   * ```
   *
   * @param string $value Input value to validate.
   * @param string $format Expected date format (PHP date format string).
   * @return bool True if valid, false otherwise.
   */
  public function datetime(string $value, string $format): bool {
    $value = str_replace(['-', '/'], '-', $value);
    if (date($format, strtotime($value)) == $value)
      return true;
    $this->set_message('datetime', "The {field} field must have format $format.");
    return false;
  }

  /**
   * Validate hostname format.
   *
   * @param string $value Input value to validate.
   * @return bool True if valid hostname, false otherwise.
   */
  public function hostname(string $value): bool {
    if (Validation::hostname($value))
      return true;
    $this->set_message('hostname', 'The {field} field must contain a valid host name.');
    return false;
  }

  /**
   * Validate IP address format.
   *
   * @param string $value Input value to validate.
   * @return bool True if valid IP address, false otherwise.
   */
  public function ipaddress(string $value): bool {
    if (Validation::ipaddress($value))
      return true;
    $this->set_message('ipaddress', 'The {field} field must contain a valid ip address.');
    return false;
  }

  /**
   * Validate IP address or CIDR notation format.
   *
   * @param string $value Input value to validate.
   * @return bool True if valid IP address or CIDR, false otherwise.
   */
  public function ipaddress_or_cidr(string $value): bool {
    if (Validation::ipaddress_or_cidr($value))
      return true;
    $this->set_message('ipaddress_or_cidr', 'The {field} field must contain a valid ip address or CIDR.');
    return false;
  }

  /**
   * Validate hostname or IP address format.
   *
   * @param string $value Input value to validate.
   * @return bool True if valid hostname or IP address, false otherwise.
   */
  public function hostname_or_ipaddress(string $value): bool {
    if (Validation::hostname_or_ipaddress($value))
      return true;
    $this->set_message('hostname_or_ipaddress', 'The {field} field must contain a valid host name or ip address.');
    return false;
  }

  /**
   * Validate UNIX username format.
   *
   * @param string $value Input value to validate.
   * @return bool True if valid UNIX username, false otherwise.
   */
  public function unix_username(string $value): bool {
    if (Validation::unix_username($value))
      return true;
    $this->set_message('unix_username', 'The {field} field must contain a valid UNIX username.');
    return false;
  }

  /**
   * Validate port number (1-65535).
   *
   * @param string $value Input value to validate.
   * @return bool True if valid port number, false otherwise.
   */
  public function port(string $value): bool {
    if (Validation::port($value))
      return true;
    $this->set_message('port', 'The {field} field must contain a valid port number.');
    return false;
  }

  /**
   * Validate email address format.
   *
   * Uses the regular expression from the HTML5 specification for validation.
   *
   * @see https://html.spec.whatwg.org/multipage/input.html#valid-e-mail-address
   * @param string $value Input value to validate.
   * @return bool True if valid email address, false otherwise.
   */
  public function email(string $value): bool {
    if (Validation::email($value))
      return true;
    $this->set_message('email', 'The {field} field must contain a valid email address.');
    return false;
  }

  /**
   * Validate file or directory path format.
   *
   * @param string $value Input value to validate.
   * @param mixed $denyLeadingSlash Deny leading slashes if true. Default is false.
   * @return bool True if valid path, false otherwise.
   */
  public function is_path(string $value, $denyLeadingSlash=false): bool {
    if (Validation::is_path($value, filter_var($denyLeadingSlash, FILTER_VALIDATE_BOOLEAN)))
      return true;
    $this->set_message('is_path', 'The {field} field must contain a valid directory path.');
    return false;
  }
}