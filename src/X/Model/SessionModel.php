<?php
namespace X\Model;

/**
 * Abstract session management model.
 *
 * Provides a standardized interface for storing and retrieving user session data.
 * Extend this class and implement getUser() to define custom user data retrieval.
 *
 * Usage:
 * ```php
 * class UserSession extends \X\Model\SessionModel {
 *   protected static function getUser(string $id): array {
 *     return (new UserModel())->get_by_id((int)$id);
 *   }
 * }
 *
 * // Set user session
 * UserSession::set($userId);
 *
 * // Get session data
 * $user = UserSession::get();
 * ```
 */
abstract class SessionModel implements SessionModelInterface {
  /**
   * Key name of the session to store user information.
   * @var string
   */
  const SESSION_NAME = 'user';

  /**
   * Retrieve user data by ID for session storage.
   *
   * Subclasses must implement this to define how user data is fetched
   * (e.g., from a database model).
   *
   * @param string $id User ID.
   * @return array User data to store in session.
   */
  abstract protected static function getUser(string $id): array;

  /**
   * Set session data.
   *
   * With one argument, loads full user data into session via getUser().
   * With two arguments, updates a single session field.
   *
   * @param string $id User ID (1 arg) or field name (2 args).
   * @param mixed $value Field value when updating a single field.
   * @return string Called class name for method chaining.
   * @throws \RuntimeException If the specified field does not exist in session data.
   */
  public final static function set(string $id, $value=null): string {
    if (count(func_get_args()) === 1)
      $_SESSION[self::SESSION_NAME] = static::getUser($id);
    else {
      $field = $id;
      if (!array_key_exists($field, $_SESSION[self::SESSION_NAME]))
        throw new \RuntimeException($field . ' column does not exist');
      $_SESSION[self::SESSION_NAME][$field] = $value;
    }
    return get_called_class();
  }

  /**
   * Destroy the current user session.
   *
   * @return string Called class name for method chaining.
   */
  public final static function unset(): string {
    unset($_SESSION[self::SESSION_NAME]);
    return get_called_class();
  }

  /**
   * Check if a user session exists.
   *
   * @return bool True if session data is set.
   */
  public final static function isset(): bool {
    return isset($_SESSION[self::SESSION_NAME]);
  }

  /**
   * Get session data.
   *
   * Returns all session data as an object, or a single field value
   * if a field name is specified.
   *
   * @param string|null $field Field name to retrieve. Null returns all data.
   * @return \stdClass|mixed|null All session data, a single field value, or null if no session.
   */
  public final static function get(string $field=null) {
    if (!self::isset())
      return null;
    $user = json_decode(json_encode($_SESSION[self::SESSION_NAME]));
    return empty($field) ? $user : $user->$field;
  }
}
