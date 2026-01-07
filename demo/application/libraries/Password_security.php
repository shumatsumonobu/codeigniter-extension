<?php
use \X\Util\Logger;

/**
 * Password security validation library.
 *
 * Provides methods to validate password strength and similarity.
 * Prevents users from choosing passwords too similar to their previous ones.
 */
class Password_security {
  /**
   * Check if new password is sufficiently different from old password.
   *
   * Rejects passwords that share:
   * - First 3 characters with old password
   * - Characters 2-4 with old password
   * - Last 3 characters with old password
   *
   * @param string $newPassword New password to validate.
   * @param string $oldPassowrd Old password to compare against.
   * @return bool True if passwords are sufficiently different, false if too similar.
   */
  public function checkPasswordSimilarity(string $newPassword, string $oldPassowrd) {
    $newPassword = strtolower($newPassword);
    $oldPassowrd = strtolower($oldPassowrd);

    // Reject if first 3 characters match
    if (substr($newPassword, 0, 3) === substr($oldPassowrd, 0, 3))
      return false;

    // Reject if characters 2-4 match
    if (substr($newPassword, 1, 3) === substr($oldPassowrd, 1, 3))
      return false;

    // Reject if last 3 characters match
    if (substr($newPassword, -3) === substr($oldPassowrd, -3))
      return false;
    return true;
  }
}
