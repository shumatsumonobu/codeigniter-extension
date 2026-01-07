<?php
require_once(dirname(__FILE__) . '/../core/AppException.php');

/**
 * Exception thrown when a user is not found.
 *
 * Used in user management operations when the target user
 * does not exist in the database.
 */
class UserNotFoundException extends AppException {
  /**
   * Create a new UserNotFoundException instance.
   */
  public function __construct() {
    parent::__construct('User not found');
  }
}