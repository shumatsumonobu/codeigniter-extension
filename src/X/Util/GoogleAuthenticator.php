<?php
namespace X\Util;

/**
 * Google Authenticator TOTP utility class.
 *
 * Provides Time-based One-Time Password (TOTP) authentication methods
 * compatible with Google Authenticator and other TOTP apps.
 * Implements RFC 6238 (TOTP) and RFC 4226 (HOTP).
 *
 * @example
 * ```php
 * use \X\Util\GoogleAuthenticator;
 *
 * // Generate a new secret for user
 * $secret = GoogleAuthenticator::generateSecret();
 *
 * // Get QR code URL for scanning with Google Authenticator app
 * $qrCodeUrl = GoogleAuthenticator::getQrCodeUrl('user@example.com', $secret, 'MyApp');
 *
 * // Verify the code entered by the user
 * $isValid = GoogleAuthenticator::verifyCode($secret, '123456');
 *
 * // Generate backup codes for account recovery
 * $backupCodes = GoogleAuthenticator::generateBackupCodes();
 * ```
 */
final class GoogleAuthenticator {
  /**
   * Base32 alphabet for encoding/decoding secrets.
   */
  private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

  /**
   * Default code length (6 digits).
   */
  private const CODE_LENGTH = 6;

  /**
   * Default time step in seconds (30 seconds).
   */
  private const TIME_STEP = 30;

  /**
   * Default number of backup codes to generate.
   */
  private const BACKUP_CODES_COUNT = 10;

  /**
   * Default backup code length.
   */
  private const BACKUP_CODE_LENGTH = 8;

  /**
   * Recovery token expiry time in seconds (1 hour).
   */
  private const RECOVERY_TOKEN_EXPIRY = 3600;

  /**
   * Recovery token length in bytes.
   */
  private const RECOVERY_TOKEN_LENGTH = 32;

  /**
   * Generate a new Base32-encoded secret key.
   *
   * @param int $length (optional) Secret length in bytes before Base32 encoding. Default is 20 (160 bits, recommended by RFC 4226).
   * @return string Base32-encoded secret key.
   * @throws \InvalidArgumentException If length is less than 16.
   *
   * @example
   * ```php
   * $secret = GoogleAuthenticator::generateSecret();
   * // Returns something like: "JBSWY3DPEHPK3PXP"
   * ```
   */
  public static function generateSecret(int $length = 20): string {
    if ($length < 16) {
      throw new \InvalidArgumentException('Secret length must be at least 16 bytes for security');
    }
    $randomBytes = random_bytes($length);
    return self::base32Encode($randomBytes);
  }

  /**
   * Verify a TOTP code against a secret.
   *
   * @param string $secret Base32-encoded secret key.
   * @param string $code The 6-digit code to verify.
   * @param int $discrepancy (optional) Number of time windows to check before and after current time. Default is 1 (allows 30 seconds drift).
   * @param int|null $timestamp (optional) Unix timestamp to verify against. Default is current time.
   * @return bool True if the code is valid, false otherwise.
   *
   * @example
   * ```php
   * $secret = 'JBSWY3DPEHPK3PXP';
   * $code = '123456';
   * if (GoogleAuthenticator::verifyCode($secret, $code)) {
   *     echo 'Code is valid!';
   * }
   * ```
   */
  public static function verifyCode(string $secret, string $code, int $discrepancy = 1, ?int $timestamp = null): bool {
    if (strlen($code) !== self::CODE_LENGTH) {
      return false;
    }
    if (!ctype_digit($code)) {
      return false;
    }

    $timestamp = $timestamp ?? time();
    $timeSlice = (int) floor($timestamp / self::TIME_STEP);

    for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
      $calculatedCode = self::getCode($secret, $timeSlice + $i);
      if (hash_equals($calculatedCode, $code)) {
        return true;
      }
    }

    return false;
  }

  /**
   * Get the current TOTP code for a secret.
   *
   * @param string $secret Base32-encoded secret key.
   * @param int|null $timeSlice (optional) Time slice to generate code for. Default is current time slice.
   * @return string The 6-digit TOTP code.
   *
   * @example
   * ```php
   * $secret = 'JBSWY3DPEHPK3PXP';
   * $currentCode = GoogleAuthenticator::getCode($secret);
   * echo "Current code: $currentCode";
   * ```
   */
  public static function getCode(string $secret, ?int $timeSlice = null): string {
    if ($timeSlice === null) {
      $timeSlice = (int) floor(time() / self::TIME_STEP);
    }

    $secretKey = self::base32Decode($secret);

    // Pack the time slice as a 64-bit big-endian integer
    $time = pack('N*', 0, $timeSlice);

    // Generate HMAC-SHA1 hash
    $hash = hash_hmac('sha1', $time, $secretKey, true);

    // Extract dynamic offset (last 4 bits of the hash)
    $offset = ord($hash[strlen($hash) - 1]) & 0x0f;

    // Extract 4 bytes starting at offset, mask the MSB
    $code = (
      ((ord($hash[$offset]) & 0x7f) << 24) |
      ((ord($hash[$offset + 1]) & 0xff) << 16) |
      ((ord($hash[$offset + 2]) & 0xff) << 8) |
      (ord($hash[$offset + 3]) & 0xff)
    ) % pow(10, self::CODE_LENGTH);

    return str_pad((string) $code, self::CODE_LENGTH, '0', STR_PAD_LEFT);
  }

  /**
   * Generate a QR code URL for Google Authenticator.
   *
   * Uses Google Charts API to generate a QR code image URL.
   *
   * @param string $accountName User's account name or email (displayed in the app).
   * @param string $secret Base32-encoded secret key.
   * @param string $issuer Application/service name (displayed in the app).
   * @param int $size (optional) QR code image size in pixels. Default is 200.
   * @return string URL to the QR code image.
   *
   * @example
   * ```php
   * $qrUrl = GoogleAuthenticator::getQrCodeUrl('user@example.com', $secret, 'MyApp');
   * echo "<img src='$qrUrl' alt='Scan with Google Authenticator'>";
   * ```
   */
  public static function getQrCodeUrl(string $accountName, string $secret, string $issuer, int $size = 200): string {
    $otpauthUrl = self::getOtpauthUrl($accountName, $secret, $issuer);
    return 'https://chart.googleapis.com/chart?chs=' . $size . 'x' . $size .
           '&chld=M|0&cht=qr&chl=' . urlencode($otpauthUrl);
  }

  /**
   * Get the otpauth URL for manual entry or QR code generation.
   *
   * @param string $accountName User's account name or email.
   * @param string $secret Base32-encoded secret key.
   * @param string $issuer Application/service name.
   * @return string The otpauth:// URL.
   *
   * @example
   * ```php
   * $url = GoogleAuthenticator::getOtpauthUrl('user@example.com', $secret, 'MyApp');
   * // Returns: otpauth://totp/MyApp:user@example.com?secret=XXXX&issuer=MyApp
   * ```
   */
  public static function getOtpauthUrl(string $accountName, string $secret, string $issuer): string {
    return 'otpauth://totp/' . rawurlencode($issuer) . ':' . rawurlencode($accountName) .
           '?secret=' . $secret .
           '&issuer=' . rawurlencode($issuer) .
           '&algorithm=SHA1' .
           '&digits=' . self::CODE_LENGTH .
           '&period=' . self::TIME_STEP;
  }

  /**
   * Generate backup codes for account recovery.
   *
   * Backup codes are single-use codes that can be used when the user
   * doesn't have access to their authenticator app.
   *
   * @param int $count (optional) Number of backup codes to generate. Default is 10.
   * @param int $length (optional) Length of each backup code. Default is 8.
   * @return array Array of backup codes (plaintext) and their hashes.
   *
   * @example
   * ```php
   * $result = GoogleAuthenticator::generateBackupCodes();
   * // Store $result['hashes'] in database
   * // Show $result['codes'] to user once
   *
   * // Returns:
   * // [
   * //   'codes' => ['12345678', '87654321', ...],
   * //   'hashes' => ['$2y$10$...', '$2y$10$...', ...]
   * // ]
   * ```
   */
  public static function generateBackupCodes(int $count = self::BACKUP_CODES_COUNT, int $length = self::BACKUP_CODE_LENGTH): array {
    $codes = [];
    $hashes = [];

    for ($i = 0; $i < $count; $i++) {
      $code = self::generateNumericCode($length);
      $codes[] = $code;
      $hashes[] = password_hash($code, PASSWORD_BCRYPT);
    }

    return [
      'codes' => $codes,
      'hashes' => $hashes
    ];
  }

  /**
   * Verify a backup code against stored hashes.
   *
   * @param string $code The backup code to verify.
   * @param array $hashes Array of bcrypt hashes of valid backup codes.
   * @return int|false Returns the index of the matched hash (for removal) or false if not valid.
   *
   * @example
   * ```php
   * $storedHashes = [...]; // Retrieved from database
   * $result = GoogleAuthenticator::verifyBackupCode('12345678', $storedHashes);
   * if ($result !== false) {
   *     // Remove the used hash from database
   *     unset($storedHashes[$result]);
   *     echo 'Backup code accepted!';
   * }
   * ```
   */
  public static function verifyBackupCode(string $code, array $hashes): int|false {
    foreach ($hashes as $index => $hash) {
      if (password_verify($code, $hash)) {
        return $index;
      }
    }
    return false;
  }

  /**
   * Get the remaining seconds until the current code expires.
   *
   * @param int|null $timestamp (optional) Unix timestamp. Default is current time.
   * @return int Seconds remaining (0-29).
   *
   * @example
   * ```php
   * $remaining = GoogleAuthenticator::getTimeRemaining();
   * echo "Code expires in $remaining seconds";
   * ```
   */
  public static function getTimeRemaining(?int $timestamp = null): int {
    $timestamp = $timestamp ?? time();
    return self::TIME_STEP - ($timestamp % self::TIME_STEP);
  }

  /**
   * Check if a secret is valid Base32 format.
   *
   * @param string $secret The secret to validate.
   * @return bool True if valid Base32, false otherwise.
   *
   * @example
   * ```php
   * if (GoogleAuthenticator::isValidSecret($userInput)) {
   *     // Process the secret
   * }
   * ```
   */
  public static function isValidSecret(string $secret): bool {
    // Remove padding and check if only valid Base32 characters
    $secret = rtrim(strtoupper($secret), '=');
    return strlen($secret) >= 16 && preg_match('/^[A-Z2-7]+$/', $secret) === 1;
  }

  /**
   * Encode binary data to Base32.
   *
   * @param string $data Binary data to encode.
   * @return string Base32-encoded string.
   */
  private static function base32Encode(string $data): string {
    $binary = '';
    foreach (str_split($data) as $char) {
      $binary .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
    }

    $encoded = '';
    $chunks = str_split($binary, 5);
    foreach ($chunks as $chunk) {
      if (strlen($chunk) < 5) {
        $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
      }
      $encoded .= self::BASE32_ALPHABET[bindec($chunk)];
    }

    return $encoded;
  }

  /**
   * Decode Base32-encoded string to binary data.
   *
   * @param string $data Base32-encoded string.
   * @return string Binary data.
   * @throws \InvalidArgumentException If the input contains invalid characters.
   */
  private static function base32Decode(string $data): string {
    $data = strtoupper($data);
    $data = rtrim($data, '=');

    $binary = '';
    foreach (str_split($data) as $char) {
      $pos = strpos(self::BASE32_ALPHABET, $char);
      if ($pos === false) {
        throw new \InvalidArgumentException("Invalid Base32 character: $char");
      }
      $binary .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
    }

    $result = '';
    $chunks = str_split($binary, 8);
    foreach ($chunks as $chunk) {
      if (strlen($chunk) === 8) {
        $result .= chr(bindec($chunk));
      }
    }

    return $result;
  }

  /**
   * Generate a recovery token for email-based account recovery.
   *
   * This creates a secure token that can be sent via email to allow
   * users to disable MFA when they've lost access to their authenticator.
   *
   * @param int $expiry (optional) Token expiry time in seconds. Default is 3600 (1 hour).
   * @return array Contains 'token' (to send via email), 'hash' (to store), and 'expires_at' (Unix timestamp).
   *
   * @example
   * ```php
   * $recovery = GoogleAuthenticator::generateRecoveryToken();
   * // Store $recovery['hash'] and $recovery['expires_at'] in database
   * // Send $recovery['token'] to user's email
   *
   * // Returns:
   * // [
   * //   'token' => 'a1b2c3d4...',
   * //   'hash' => '$2y$10$...',
   * //   'expires_at' => 1699999999
   * // ]
   * ```
   */
  public static function generateRecoveryToken(int $expiry = self::RECOVERY_TOKEN_EXPIRY): array {
    $token = bin2hex(random_bytes(self::RECOVERY_TOKEN_LENGTH));
    return [
      'token' => $token,
      'hash' => password_hash($token, PASSWORD_BCRYPT),
      'expires_at' => time() + $expiry
    ];
  }

  /**
   * Verify a recovery token.
   *
   * @param string $token The recovery token to verify.
   * @param string $hash The stored hash of the token.
   * @param int $expiresAt The Unix timestamp when the token expires.
   * @return bool True if the token is valid and not expired, false otherwise.
   *
   * @example
   * ```php
   * $isValid = GoogleAuthenticator::verifyRecoveryToken($userToken, $storedHash, $storedExpiry);
   * if ($isValid) {
   *     // Disable MFA for the user
   *     // Delete the recovery token from database
   * }
   * ```
   */
  public static function verifyRecoveryToken(string $token, string $hash, int $expiresAt): bool {
    if (time() > $expiresAt) {
      return false;
    }
    return password_verify($token, $hash);
  }

  /**
   * Format backup codes for display to user.
   *
   * Formats codes in groups for easier reading (e.g., "1234-5678").
   *
   * @param array $codes Array of backup codes.
   * @param int $groupSize (optional) Number of digits per group. Default is 4.
   * @param string $separator (optional) Separator between groups. Default is "-".
   * @return array Formatted backup codes.
   *
   * @example
   * ```php
   * $codes = ['12345678', '87654321'];
   * $formatted = GoogleAuthenticator::formatBackupCodes($codes);
   * // Returns: ['1234-5678', '8765-4321']
   * ```
   */
  public static function formatBackupCodes(array $codes, int $groupSize = 4, string $separator = '-'): array {
    return array_map(function($code) use ($groupSize, $separator) {
      return implode($separator, str_split($code, $groupSize));
    }, $codes);
  }

  /**
   * Normalize a backup code input (remove formatting).
   *
   * Strips separators and whitespace from user-entered backup codes.
   *
   * @param string $code The user-entered backup code.
   * @return string Normalized code containing only digits.
   *
   * @example
   * ```php
   * $input = '1234-5678';
   * $normalized = GoogleAuthenticator::normalizeBackupCode($input);
   * // Returns: '12345678'
   * ```
   */
  public static function normalizeBackupCode(string $code): string {
    return preg_replace('/[^0-9]/', '', $code);
  }

  /**
   * Serialize backup code hashes for database storage.
   *
   * @param array $hashes Array of bcrypt hashes.
   * @return string JSON-encoded string for database storage.
   *
   * @example
   * ```php
   * $result = GoogleAuthenticator::generateBackupCodes();
   * $serialized = GoogleAuthenticator::serializeBackupHashes($result['hashes']);
   * // Store $serialized in a TEXT/VARCHAR database column
   * ```
   */
  public static function serializeBackupHashes(array $hashes): string {
    return json_encode(array_values($hashes));
  }

  /**
   * Deserialize backup code hashes from database storage.
   *
   * @param string $serialized JSON-encoded string from database.
   * @return array Array of bcrypt hashes.
   *
   * @example
   * ```php
   * $hashes = GoogleAuthenticator::deserializeBackupHashes($dbValue);
   * $index = GoogleAuthenticator::verifyBackupCode($userInput, $hashes);
   * ```
   */
  public static function deserializeBackupHashes(string $serialized): array {
    $hashes = json_decode($serialized, true);
    return is_array($hashes) ? $hashes : [];
  }

  /**
   * Remove a used backup code from the hashes array.
   *
   * @param array $hashes Array of bcrypt hashes.
   * @param int $usedIndex Index of the used code (returned by verifyBackupCode).
   * @return array Updated hashes array with the used code removed.
   *
   * @example
   * ```php
   * $index = GoogleAuthenticator::verifyBackupCode($code, $hashes);
   * if ($index !== false) {
   *     $hashes = GoogleAuthenticator::removeUsedBackupCode($hashes, $index);
   *     // Save updated $hashes to database
   * }
   * ```
   */
  public static function removeUsedBackupCode(array $hashes, int $usedIndex): array {
    unset($hashes[$usedIndex]);
    return array_values($hashes);
  }

  /**
   * Get the count of remaining backup codes.
   *
   * @param array|string $hashes Array of hashes or serialized JSON string.
   * @return int Number of remaining backup codes.
   *
   * @example
   * ```php
   * $remaining = GoogleAuthenticator::getRemainingBackupCodesCount($hashes);
   * if ($remaining < 3) {
   *     // Warn user to generate new backup codes
   * }
   * ```
   */
  public static function getRemainingBackupCodesCount(array|string $hashes): int {
    if (is_string($hashes)) {
      $hashes = self::deserializeBackupHashes($hashes);
    }
    return count($hashes);
  }

  /**
   * Check if MFA should be enforced (has valid secret and is enabled).
   *
   * Helper method to check MFA status from stored user data.
   *
   * @param string|null $secret User's stored MFA secret.
   * @param bool $mfaEnabled User's MFA enabled flag.
   * @return bool True if MFA should be enforced.
   *
   * @example
   * ```php
   * if (GoogleAuthenticator::isMfaEnforced($user['mfa_secret'], $user['mfa_enabled'])) {
   *     // Require MFA verification
   * }
   * ```
   */
  public static function isMfaEnforced(?string $secret, bool $mfaEnabled): bool {
    return $mfaEnabled && !empty($secret) && self::isValidSecret($secret);
  }

  /**
   * Create a complete MFA setup bundle for a new user.
   *
   * Generates everything needed to enable MFA for a user.
   *
   * @param string $accountName User's email or account name.
   * @param string $issuer Application name.
   * @param int $backupCodesCount (optional) Number of backup codes. Default is 10.
   * @return array Complete setup bundle with secret, QR code URL, otpauth URL, and backup codes.
   *
   * @example
   * ```php
   * $setup = GoogleAuthenticator::createMfaSetup('user@example.com', 'MyApp');
   * // Returns:
   * // [
   * //   'secret' => 'JBSWY3DPEHPK3PXP...',
   * //   'qr_code_url' => 'https://chart.googleapis.com/...',
   * //   'otpauth_url' => 'otpauth://totp/...',
   * //   'backup_codes' => ['1234-5678', '8765-4321', ...],
   * //   'backup_hashes' => ['$2y$10$...', ...]
   * // ]
   * ```
   */
  public static function createMfaSetup(string $accountName, string $issuer, int $backupCodesCount = 10): array {
    $secret = self::generateSecret();
    $backupResult = self::generateBackupCodes($backupCodesCount);

    return [
      'secret' => $secret,
      'qr_code_url' => self::getQrCodeUrl($accountName, $secret, $issuer),
      'otpauth_url' => self::getOtpauthUrl($accountName, $secret, $issuer),
      'backup_codes' => self::formatBackupCodes($backupResult['codes']),
      'backup_hashes' => $backupResult['hashes']
    ];
  }

  /**
   * Verify either a TOTP code or a backup code.
   *
   * Convenience method that tries TOTP verification first, then backup codes.
   *
   * @param string $secret User's MFA secret.
   * @param string $code The code entered by the user.
   * @param array $backupHashes Array of backup code hashes.
   * @return array Result with 'valid' (bool), 'type' ('totp'|'backup'|null), and 'backup_index' (int|null).
   *
   * @example
   * ```php
   * $result = GoogleAuthenticator::verifyTotpOrBackup($secret, $userInput, $backupHashes);
   * if ($result['valid']) {
   *     if ($result['type'] === 'backup') {
   *         // Remove used backup code
   *         $hashes = GoogleAuthenticator::removeUsedBackupCode($backupHashes, $result['backup_index']);
   *     }
   *     // Grant access
   * }
   * ```
   */
  public static function verifyTotpOrBackup(string $secret, string $code, array $backupHashes = [], int $discrepancy = 2): array {
    // Try TOTP verification first (discrepancy=2 allows ±60 seconds for time sync issues)
    if (self::verifyCode($secret, $code, $discrepancy)) {
      return [
        'valid' => true,
        'type' => 'totp',
        'backup_index' => null
      ];
    }

    // Try backup code verification
    $normalizedCode = self::normalizeBackupCode($code);
    $backupIndex = self::verifyBackupCode($normalizedCode, $backupHashes);
    if ($backupIndex !== false) {
      return [
        'valid' => true,
        'type' => 'backup',
        'backup_index' => $backupIndex
      ];
    }

    return [
      'valid' => false,
      'type' => null,
      'backup_index' => null
    ];
  }

  /**
   * Generate a random numeric code.
   *
   * @param int $length Code length.
   * @return string Numeric code.
   */
  private static function generateNumericCode(int $length): string {
    $code = '';
    for ($i = 0; $i < $length; $i++) {
      $code .= random_int(0, 9);
    }
    return $code;
  }
}
