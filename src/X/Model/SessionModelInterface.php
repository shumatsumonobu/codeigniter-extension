<?php
namespace X\Model;

/**
 * Interface for session management models.
 *
 * Defines the contract for storing and retrieving user session data.
 */
interface SessionModelInterface {
  /**
   * Set session data.
   *
   * With one argument, loads full user data into session.
   * With two arguments, updates a single session field.
   *
   * @param string $id User ID (1 arg) or field name (2 args).
   * @param mixed $value Field value when updating a single field.
   * @return string Called class name for method chaining.
   */
  public static function set(string $id, $value=null): string;

  /**
   * Destroy the current user session.
   *
   * @return string Called class name for method chaining.
   */
  public static function unset(): string;

  /**
   * Check if a user session exists.
   *
   * @return bool True if session data is set.
   */
  public static function isset(): bool;

  /**
   * Get session data.
   *
   * @param string|null $field Field name to retrieve. Null returns all data.
   * @return \stdClass|mixed|null All session data, a single field value, or null if no session.
   */
  public static function get(string $field=null);
}