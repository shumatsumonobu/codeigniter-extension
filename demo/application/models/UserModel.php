<?php
use \X\Util\Logger;
use \X\Util\Cipher;
use \X\Util\ImageHelper;
use \X\Util\FileHelper;
use \X\Util\GoogleAuthenticator;

/**
 * User model for authentication and user management.
 *
 * Handles user CRUD operations, authentication, and profile management.
 */
class UserModel extends \AppModel {
  const TABLE = 'user';

  /**
   * Authenticate user and create session.
   *
   * @param string $email User email address.
   * @param string $password Plain text password.
   * @return bool True if authentication successful, false otherwise.
   */
  public function login(string $email, string $password): bool {
    $user = $this
      ->where('email', $email)
      ->where('password', Cipher::encode_sha256($password))
      ->get()
      ->row_array();
    if (empty($user))
      return false;
    unset($user['password']);
    $_SESSION[SESSION_NAME] = $user;
    return true;
  }

  /**
   * Destroy user session.
   *
   * @return bool Always returns true.
   */
  public function logout(): bool {
    unset($_SESSION[SESSION_NAME]);
    return true;
  }

  /**
   * Get paginated user list for DataTables.
   *
   * @param int $offset Starting record offset.
   * @param int $limit Number of records to return.
   * @param string $order Column name to sort by.
   * @param string $direction Sort direction ('asc' or 'desc').
   * @param array|null $search Search filters (keyword for email/name search).
   * @param int $loginUserId Current user ID to exclude from results.
   * @return array DataTables response with recordsTotal, recordsFiltered, and data.
   */
  public function paginate(int $offset, int $limit, string $order, string $direction, ?array $search, int $loginUserId): array {
    function setWhere(CI_Model $model, ?array $search, int $loginUserId) {
      $model->where('id !=', $loginUserId);
      if (!empty($search['keyword']))
        $model
          ->group_start()
          ->or_like('email', $search['keyword'])
          ->or_like('name', $search['keyword'])
          ->group_end();
    }
    setWhere($this, $search, $loginUserId);
    $rows = $this
      ->select('id, role, email, name, modified')
      ->order_by($order, $direction)
      ->limit($limit, $offset)
      ->get()
      ->result_array();
    setWhere($this, $search, $loginUserId);
    $recordsFiltered = $this->count_all_results();
    $recordsTotal = $this
      ->where('id !=', $loginUserId)
      ->count_all_results();
    return ['recordsTotal' => $recordsTotal, 'recordsFiltered' => $recordsFiltered, 'data' => $rows];
  }

  /**
   * Create a new user with icon image.
   *
   * @param array $set User data (role, email, name, password, icon as data URL).
   * @return void
   * @throws \Throwable On database or file operation failure.
   */
  public function createUser(array $set) {
    try {
      parent::trans_begin();
      $userId = $this
        ->set('role', $set['role'])
        ->set('email', $set['email'])
        ->set('name', $set['name'])
        ->set('password', Cipher::encode_sha256($set['password']))
        ->insert();
      $this->writeUserIconImage($userId, $set['icon']);
      parent::trans_commit();
    } catch (\Throwable $e) {
      parent::trans_rollback();
      throw $e;
    }
  }

  /**
   * Check if email address is already registered.
   *
   * @param string $email Email address to check.
   * @param int|null $excludeUserId User ID to exclude from check (for update validation).
   * @return bool True if email exists, false otherwise.
   */
  public function emailExists(string $email, int $excludeUserId = null): bool {
    if (!empty($excludeUserId))
      $this->where('id !=', $excludeUserId);
    return $this
      ->where('email', $email)
      ->count_all_results() > 0;
  }

  /**
   * Get user by ID.
   *
   * @param int $userId User ID.
   * @return array|null User data or null if not found.
   */
  public function getUserById(int $userId): ?array {
    return $this
      ->select('id, role, email, name, created, modified')
      ->where('id', $userId)
      ->get()
      ->row_array();
  }

  /**
   * Update user information and icon image.
   *
   * @param int $userId User ID to update.
   * @param array $set Updated user data (email, name, role, password, icon, changePassword flag).
   * @return void
   * @throws UserNotFoundException If user does not exist.
   * @throws \Throwable On database or file operation failure.
   */
  public function updateUser(int $userId, array $set) {
    try {
      parent::trans_begin();
      if (!$this->userIdExists($userId))
        throw new UserNotFoundException();
      if (!empty($set['changePassword'])) {
        $this->set('password', Cipher::encode_sha256($set['password']));
        Logger::debug("Change the password whose user ID is {$userId}");
      }
      if (!empty($set['role']))
        $this->set('role', $set['role']);
      $this
        ->set('email', $set['email'])
        ->set('name', $set['name'])
        // Explicitly update modification date when only image changes
        ->set('modified', 'NOW()', FALSE)
        ->where('id', $userId)
        ->update();
      $this->writeUserIconImage($userId, $set['icon']);
      parent::trans_commit();
    } catch (\Throwable $e) {
      parent::trans_rollback();
      throw $e;
    }
  }

  /**
   * Delete user and associated icon image.
   *
   * @param int $userId User ID to delete.
   * @return void
   * @throws UserNotFoundException If user does not exist.
   * @throws \Throwable On database or file operation failure.
   */
  public function deleteUser(int $userId) {
    try {
      if (!$this->userIdExists($userId))
        throw new UserNotFoundException();
      parent::trans_begin();
      $this
        ->where('id', $userId)
        ->delete();
      $this->deleteUserIconImage($userId);
      parent::trans_commit();
    } catch (\Throwable $e) {
      parent::trans_rollback();
      throw $e;
    }
  }

  /**
   * Validate password security requirements.
   *
   * Checks that new password is different from current password,
   * not too similar to current password, and not the same as email.
   *
   * @param int $userId User ID.
   * @param string $newPassword New password to validate.
   * @return bool True if password meets security requirements, false otherwise.
   */
  public function passwordSecurityCheck(int $userId, string $newPassword) {
    $user = $this
      ->select('password, email')
      ->where('id', $userId)
      ->get()
      ->row_array();

    // Reject if same as current password
    if ($user['password'] === Cipher::encode_sha256($newPassword))
      return false;

    // Check password similarity
    $CI =& get_instance();
    $CI->load->library('password_security');
    if (!$CI->password_security->checkPasswordSimilarity($user['password'], $newPassword))
      return false;

    // Reject if same as email
    if ($user['email'] === $newPassword)
      return false;
    return true;
  }

  // =========================================
  // MFA (Multi-Factor Authentication) Methods
  // =========================================

  /**
   * Check if user has MFA enabled.
   *
   * @param int $userId User ID.
   * @return bool True if MFA is enabled.
   */
  public function isMfaEnabled(int $userId): bool {
    $user = $this
      ->select('mfa_enabled, mfa_secret')
      ->where('id', $userId)
      ->get()
      ->row_array();
    return GoogleAuthenticator::isMfaEnforced($user['mfa_secret'] ?? null, (bool)($user['mfa_enabled'] ?? false));
  }

  /**
   * Get user's MFA status and info.
   *
   * @param int $userId User ID.
   * @return array MFA status info.
   */
  public function getMfaStatus(int $userId): array {
    $user = $this
      ->select('mfa_enabled, mfa_secret, backup_codes')
      ->where('id', $userId)
      ->get()
      ->row_array();

    $backupCodesCount = 0;
    if (!empty($user['backup_codes'])) {
      $backupCodesCount = GoogleAuthenticator::getRemainingBackupCodesCount($user['backup_codes']);
    }

    return [
      'enabled' => (bool)($user['mfa_enabled'] ?? false),
      'has_secret' => !empty($user['mfa_secret']),
      'backup_codes_remaining' => $backupCodesCount
    ];
  }

  /**
   * Initialize MFA setup for a user.
   *
   * @param int $userId User ID.
   * @param string $email User email for QR code.
   * @return array MFA setup data with secret and QR code URL.
   */
  public function initMfaSetup(int $userId, string $email): array {
    $setup = GoogleAuthenticator::createMfaSetup($email, 'CodeIgniter Demo', 10);

    // Store secret temporarily (not enabled yet until verification)
    $this
      ->set('mfa_secret', $setup['secret'])
      ->where('id', $userId)
      ->update();

    return [
      'secret' => $setup['secret'],
      'qr_code_url' => $setup['qr_code_url'],
      'otpauth_url' => $setup['otpauth_url'],
      'backup_codes' => $setup['backup_codes'],
      'backup_hashes' => $setup['backup_hashes']
    ];
  }

  /**
   * Complete MFA setup after verification.
   *
   * @param int $userId User ID.
   * @param array $backupHashes Backup code hashes to store.
   * @return bool True on success.
   */
  public function completeMfaSetup(int $userId, array $backupHashes): bool {
    $this
      ->set('mfa_enabled', 1)
      ->set('backup_codes', GoogleAuthenticator::serializeBackupHashes($backupHashes))
      ->where('id', $userId)
      ->update();
    return true;
  }

  /**
   * Verify MFA code (TOTP or backup code).
   *
   * @param int $userId User ID.
   * @param string $code Code to verify.
   * @return array Result with 'valid', 'type', and updated backup codes if applicable.
   */
  public function verifyMfaCode(int $userId, string $code): array {
    $user = $this
      ->select('mfa_secret, backup_codes')
      ->where('id', $userId)
      ->get()
      ->row_array();

    if (empty($user['mfa_secret'])) {
      return ['valid' => false, 'type' => null, 'error' => 'MFA not configured'];
    }

    $backupHashes = [];
    if (!empty($user['backup_codes'])) {
      $backupHashes = GoogleAuthenticator::deserializeBackupHashes($user['backup_codes']);
    }

    $result = GoogleAuthenticator::verifyTotpOrBackup($user['mfa_secret'], $code, $backupHashes);

    // If backup code was used, remove it
    if ($result['valid'] && $result['type'] === 'backup') {
      $backupHashes = GoogleAuthenticator::removeUsedBackupCode($backupHashes, $result['backup_index']);
      $this
        ->set('backup_codes', GoogleAuthenticator::serializeBackupHashes($backupHashes))
        ->where('id', $userId)
        ->update();
      $result['backup_codes_remaining'] = count($backupHashes);
    }

    return $result;
  }

  /**
   * Disable MFA for a user.
   *
   * @param int $userId User ID.
   * @return bool True on success.
   */
  public function disableMfa(int $userId): bool {
    $this
      ->set('mfa_enabled', 0)
      ->set('mfa_secret', null)
      ->set('backup_codes', null)
      ->set('recovery_hash', null)
      ->set('recovery_expires', null)
      ->where('id', $userId)
      ->update();
    return true;
  }

  /**
   * Generate new backup codes for a user.
   *
   * @param int $userId User ID.
   * @return array New backup codes (formatted).
   */
  public function regenerateBackupCodes(int $userId): array {
    $result = GoogleAuthenticator::generateBackupCodes(10);

    $this
      ->set('backup_codes', GoogleAuthenticator::serializeBackupHashes($result['hashes']))
      ->where('id', $userId)
      ->update();

    return GoogleAuthenticator::formatBackupCodes($result['codes']);
  }

  /**
   * Create a recovery token for MFA reset.
   *
   * @param int $userId User ID.
   * @return string Recovery token to send via email.
   */
  public function createRecoveryToken(int $userId): string {
    $recovery = GoogleAuthenticator::generateRecoveryToken(3600); // 1 hour expiry

    $this
      ->set('recovery_hash', $recovery['hash'])
      ->set('recovery_expires', $recovery['expires_at'])
      ->where('id', $userId)
      ->update();

    return $recovery['token'];
  }

  /**
   * Verify a recovery token and disable MFA.
   *
   * @param int $userId User ID.
   * @param string $token Recovery token.
   * @return bool True if token valid and MFA disabled.
   */
  public function verifyRecoveryToken(int $userId, string $token): bool {
    $user = $this
      ->select('recovery_hash, recovery_expires')
      ->where('id', $userId)
      ->get()
      ->row_array();

    if (empty($user['recovery_hash']) || empty($user['recovery_expires'])) {
      return false;
    }

    if (GoogleAuthenticator::verifyRecoveryToken($token, $user['recovery_hash'], (int)$user['recovery_expires'])) {
      $this->disableMfa($userId);
      return true;
    }

    return false;
  }

  /**
   * Get user's MFA secret for verification.
   *
   * @param int $userId User ID.
   * @return string|null MFA secret or null.
   */
  public function getMfaSecret(int $userId): ?string {
    $user = $this
      ->select('mfa_secret')
      ->where('id', $userId)
      ->get()
      ->row_array();
    return $user['mfa_secret'] ?? null;
  }

  /**
   * Write user icon image to upload directory.
   *
   * @param int $userId User ID.
   * @param string $dataUrl Base64 encoded data URL of the image.
   * @return void
   */
  private function writeUserIconImage(int $userId, string $dataUrl) {
    $filePath = FCPATH . "upload/{$userId}.png";
    ImageHelper::writeDataURLToFile($dataUrl, $filePath);
    Logger::debug("Write {$filePath}");
  }

  /**
   * Check if user ID exists.
   *
   * @param int $userId User ID to check.
   * @return bool True if user exists, false otherwise.
   */
  private function userIdExists(int $userId): bool {
    return $this
      ->where('id', $userId)
      ->count_all_results() > 0;
  }

  /**
   * Delete user icon image from upload directory.
   *
   * @param int $userId User ID.
   * @return void
   */
  private function deleteUserIconImage(int $userId) {
    $filePath = FCPATH . "upload/{$userId}.png";
    FileHelper::delete($dataUrl, $filePath);
    Logger::debug("Delete {$filePath}");
  }
}
