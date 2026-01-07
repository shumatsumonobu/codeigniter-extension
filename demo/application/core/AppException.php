<?php
/**
 * Application base exception.
 *
 * All custom application exceptions should extend this class.
 * Provides consistent exception handling and string representation.
 */
abstract class AppException extends Exception {
  /**
   * Create a new exception instance.
   *
   * @param string $message Exception message.
   * @param int $code Exception code.
   * @param Exception|null $previous Previous exception for chaining.
   */
  public function __construct(string $message, int $code = 0, Exception $previous = null) {
    parent::__construct($message, $code, $previous);
  }

  /**
   * Get string representation of exception.
   *
   * @return string Formatted exception string.
   */
  public function __toString(): string {
    return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
  }
}