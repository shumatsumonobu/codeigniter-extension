<?php
use PHPUnit\Framework\TestCase;
use \X\Util\GoogleAuthenticator;

final class GoogleAuthenticatorTest extends TestCase {
  /**
   * Test that generateSecret returns a valid Base32 string.
   */
  public function testGenerateSecretReturnsValidBase32(): void {
    $secret = GoogleAuthenticator::generateSecret();
    $this->assertTrue(GoogleAuthenticator::isValidSecret($secret));
    $this->assertRegExp('/^[A-Z2-7]+$/', $secret);
  }

  /**
   * Test that generateSecret with default length produces expected output length.
   */
  public function testGenerateSecretDefaultLength(): void {
    $secret = GoogleAuthenticator::generateSecret();
    // 20 bytes = 160 bits, Base32 encodes 5 bits per char = 32 chars
    $this->assertEquals(32, strlen($secret));
  }

  /**
   * Test that generateSecret respects custom length.
   */
  public function testGenerateSecretCustomLength(): void {
    $secret = GoogleAuthenticator::generateSecret(32);
    // 32 bytes = 256 bits, Base32 encodes 5 bits per char = ~52 chars
    $this->assertGreaterThanOrEqual(48, strlen($secret));
  }

  /**
   * Test that generateSecret throws exception for length less than 16.
   */
  public function testGenerateSecretThrowsExceptionForShortLength(): void {
    $this->expectException(\InvalidArgumentException::class);
    GoogleAuthenticator::generateSecret(15);
  }

  /**
   * Test that generateSecret produces unique secrets.
   */
  public function testGenerateSecretUniqueness(): void {
    $secrets = [];
    for ($i = 0; $i < 100; $i++) {
      $secrets[] = GoogleAuthenticator::generateSecret();
    }
    $uniqueSecrets = array_unique($secrets);
    $this->assertCount(100, $uniqueSecrets);
  }

  /**
   * Test getCode returns a 6-digit numeric string.
   */
  public function testGetCodeFormat(): void {
    $secret = GoogleAuthenticator::generateSecret();
    $code = GoogleAuthenticator::getCode($secret);

    $this->assertEquals(6, strlen($code));
    $this->assertTrue(ctype_digit($code));
  }

  /**
   * Test getCode returns consistent results for same time slice.
   */
  public function testGetCodeConsistency(): void {
    $secret = GoogleAuthenticator::generateSecret();
    $timeSlice = (int) floor(time() / 30);

    $code1 = GoogleAuthenticator::getCode($secret, $timeSlice);
    $code2 = GoogleAuthenticator::getCode($secret, $timeSlice);

    $this->assertEquals($code1, $code2);
  }

  /**
   * Test getCode returns different codes for different time slices.
   */
  public function testGetCodeDifferentTimeSlices(): void {
    $secret = GoogleAuthenticator::generateSecret();
    $timeSlice = (int) floor(time() / 30);

    $code1 = GoogleAuthenticator::getCode($secret, $timeSlice);
    $code2 = GoogleAuthenticator::getCode($secret, $timeSlice + 1);

    // Codes should be different (with very high probability)
    $this->assertNotEquals($code1, $code2);
  }

  /**
   * Test verifyCode with correct code.
   */
  public function testVerifyCodeCorrect(): void {
    $secret = GoogleAuthenticator::generateSecret();
    $code = GoogleAuthenticator::getCode($secret);

    $this->assertTrue(GoogleAuthenticator::verifyCode($secret, $code));
  }

  /**
   * Test verifyCode with incorrect code.
   */
  public function testVerifyCodeIncorrect(): void {
    $secret = GoogleAuthenticator::generateSecret();

    $this->assertFalse(GoogleAuthenticator::verifyCode($secret, '000000'));
    $this->assertFalse(GoogleAuthenticator::verifyCode($secret, '999999'));
  }

  /**
   * Test verifyCode rejects non-numeric codes.
   */
  public function testVerifyCodeRejectsNonNumeric(): void {
    $secret = GoogleAuthenticator::generateSecret();

    $this->assertFalse(GoogleAuthenticator::verifyCode($secret, 'abcdef'));
    $this->assertFalse(GoogleAuthenticator::verifyCode($secret, '12345a'));
  }

  /**
   * Test verifyCode rejects wrong length codes.
   */
  public function testVerifyCodeRejectsWrongLength(): void {
    $secret = GoogleAuthenticator::generateSecret();

    $this->assertFalse(GoogleAuthenticator::verifyCode($secret, '12345'));
    $this->assertFalse(GoogleAuthenticator::verifyCode($secret, '1234567'));
    $this->assertFalse(GoogleAuthenticator::verifyCode($secret, ''));
  }

  /**
   * Test verifyCode with discrepancy allows time drift.
   */
  public function testVerifyCodeWithDiscrepancy(): void {
    $secret = GoogleAuthenticator::generateSecret();
    $timeSlice = (int) floor(time() / 30);

    // Get code for previous time slice
    $previousCode = GoogleAuthenticator::getCode($secret, $timeSlice - 1);

    // Should be valid with default discrepancy of 1
    $this->assertTrue(GoogleAuthenticator::verifyCode($secret, $previousCode, 1, time()));

    // Should be invalid with discrepancy of 0
    $currentCode = GoogleAuthenticator::getCode($secret, $timeSlice);
    if ($previousCode !== $currentCode) {
      $this->assertFalse(GoogleAuthenticator::verifyCode($secret, $previousCode, 0, time()));
    }
  }

  /**
   * Test known TOTP vector (RFC 6238 test vector).
   */
  public function testKnownTotpVector(): void {
    // RFC 6238 test vector: secret = "12345678901234567890" (ASCII)
    // This translates to Base32: GEZDGNBVGY3TQOJQ
    $secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';

    // Test at timestamp 59, time slice = 1
    $code = GoogleAuthenticator::getCode($secret, 1);
    $this->assertEquals(6, strlen($code));
    $this->assertTrue(ctype_digit($code));
  }

  /**
   * Test getOtpauthUrl format.
   */
  public function testGetOtpauthUrlFormat(): void {
    $secret = 'JBSWY3DPEHPK3PXP';
    $url = GoogleAuthenticator::getOtpauthUrl('user@example.com', $secret, 'MyApp');

    $this->assertStringStartsWith('otpauth://totp/', $url);
    $this->assertStringContainsString('secret=' . $secret, $url);
    $this->assertStringContainsString('issuer=MyApp', $url);
    $this->assertStringContainsString('user%40example.com', $url);
    $this->assertStringContainsString('algorithm=SHA1', $url);
    $this->assertStringContainsString('digits=6', $url);
    $this->assertStringContainsString('period=30', $url);
  }

  /**
   * Test getQrCodeUrl returns valid URL.
   */
  public function testGetQrCodeUrlFormat(): void {
    $secret = 'JBSWY3DPEHPK3PXP';
    $url = GoogleAuthenticator::getQrCodeUrl('user@example.com', $secret, 'MyApp');

    $this->assertStringStartsWith('https://chart.googleapis.com/chart?', $url);
    $this->assertStringContainsString('cht=qr', $url);
    $this->assertStringContainsString('chs=200x200', $url);
    $this->assertStringContainsString('otpauth', $url);
  }

  /**
   * Test getQrCodeUrl with custom size.
   */
  public function testGetQrCodeUrlCustomSize(): void {
    $secret = 'JBSWY3DPEHPK3PXP';
    $url = GoogleAuthenticator::getQrCodeUrl('user@example.com', $secret, 'MyApp', 300);

    $this->assertStringContainsString('chs=300x300', $url);
  }

  /**
   * Test generateBackupCodes returns correct count.
   */
  public function testGenerateBackupCodesCount(): void {
    $result = GoogleAuthenticator::generateBackupCodes();

    $this->assertCount(10, $result['codes']);
    $this->assertCount(10, $result['hashes']);
  }

  /**
   * Test generateBackupCodes with custom count.
   */
  public function testGenerateBackupCodesCustomCount(): void {
    $result = GoogleAuthenticator::generateBackupCodes(5);

    $this->assertCount(5, $result['codes']);
    $this->assertCount(5, $result['hashes']);
  }

  /**
   * Test generateBackupCodes returns numeric codes of correct length.
   */
  public function testGenerateBackupCodesFormat(): void {
    $result = GoogleAuthenticator::generateBackupCodes(10, 8);

    foreach ($result['codes'] as $code) {
      $this->assertEquals(8, strlen($code));
      $this->assertTrue(ctype_digit($code));
    }
  }

  /**
   * Test generateBackupCodes returns unique codes.
   */
  public function testGenerateBackupCodesUniqueness(): void {
    $result = GoogleAuthenticator::generateBackupCodes(100);

    $uniqueCodes = array_unique($result['codes']);
    $this->assertCount(100, $uniqueCodes);
  }

  /**
   * Test generateBackupCodes hashes are valid bcrypt hashes.
   */
  public function testGenerateBackupCodesHashFormat(): void {
    $result = GoogleAuthenticator::generateBackupCodes();

    foreach ($result['hashes'] as $hash) {
      $this->assertStringStartsWith('$2y$', $hash);
    }
  }

  /**
   * Test verifyBackupCode with valid code.
   */
  public function testVerifyBackupCodeValid(): void {
    $result = GoogleAuthenticator::generateBackupCodes(5);

    // Verify each code matches its hash
    foreach ($result['codes'] as $index => $code) {
      $matchedIndex = GoogleAuthenticator::verifyBackupCode($code, $result['hashes']);
      $this->assertEquals($index, $matchedIndex);
    }
  }

  /**
   * Test verifyBackupCode with invalid code.
   */
  public function testVerifyBackupCodeInvalid(): void {
    $result = GoogleAuthenticator::generateBackupCodes(5);

    $invalidResult = GoogleAuthenticator::verifyBackupCode('00000000', $result['hashes']);
    $this->assertFalse($invalidResult);
  }

  /**
   * Test verifyBackupCode with empty hashes array.
   */
  public function testVerifyBackupCodeEmptyHashes(): void {
    $result = GoogleAuthenticator::verifyBackupCode('12345678', []);
    $this->assertFalse($result);
  }

  /**
   * Test getTimeRemaining returns value between 0 and 29.
   */
  public function testGetTimeRemainingRange(): void {
    $remaining = GoogleAuthenticator::getTimeRemaining();

    $this->assertGreaterThanOrEqual(1, $remaining);
    $this->assertLessThanOrEqual(30, $remaining);
  }

  /**
   * Test getTimeRemaining with specific timestamp.
   */
  public function testGetTimeRemainingWithTimestamp(): void {
    // At timestamp 0, remaining should be 30
    $this->assertEquals(30, GoogleAuthenticator::getTimeRemaining(0));

    // At timestamp 15, remaining should be 15
    $this->assertEquals(15, GoogleAuthenticator::getTimeRemaining(15));

    // At timestamp 29, remaining should be 1
    $this->assertEquals(1, GoogleAuthenticator::getTimeRemaining(29));

    // At timestamp 30, remaining should be 30
    $this->assertEquals(30, GoogleAuthenticator::getTimeRemaining(30));
  }

  /**
   * @dataProvider validSecretProvider
   */
  public function testIsValidSecretValid(string $secret): void {
    $this->assertTrue(GoogleAuthenticator::isValidSecret($secret));
  }

  public function validSecretProvider(): array {
    return [
      ['JBSWY3DPEHPK3PXP'],
      ['GEZDGNBVGY3TQOJQ'],
      ['MFRGGZDFMY2TQNZZ'],
      ['ABCDEFGHIJKLMNOP'],
      ['2345672345672345'],
      ['AAAAAAAAAAAAAAAA'], // Minimum 16 chars
      ['jbswy3dpehpk3pxp'], // Lowercase should be valid
    ];
  }

  /**
   * @dataProvider invalidSecretProvider
   */
  public function testIsValidSecretInvalid(string $secret): void {
    $this->assertFalse(GoogleAuthenticator::isValidSecret($secret));
  }

  public function invalidSecretProvider(): array {
    return [
      ['SHORT'],           // Too short
      ['ABCDEFGHIJKLMNO'], // 15 chars, too short
      ['JBSWY3DPEHPK3PX1'], // Contains invalid char '1'
      ['JBSWY3DPEHPK3PX8'], // Contains invalid char '8'
      ['JBSWY3DPEHPK3PX9'], // Contains invalid char '9'
      ['JBSWY3DPEHPK3PX0'], // Contains invalid char '0'
      ['JBSWY3DPEHPK-PXP'], // Contains invalid char '-'
      [''],                 // Empty string
    ];
  }

  /**
   * Test full MFA flow simulation.
   */
  public function testFullMfaFlow(): void {
    // 1. Generate secret for user
    $secret = GoogleAuthenticator::generateSecret();
    $this->assertTrue(GoogleAuthenticator::isValidSecret($secret));

    // 2. Generate QR code URL (user would scan this)
    $qrUrl = GoogleAuthenticator::getQrCodeUrl('user@example.com', $secret, 'TestApp');
    $this->assertNotEmpty($qrUrl);

    // 3. Get current code (simulating what the app would generate)
    $code = GoogleAuthenticator::getCode($secret);

    // 4. Verify the code
    $this->assertTrue(GoogleAuthenticator::verifyCode($secret, $code));

    // 5. Generate backup codes
    $backupResult = GoogleAuthenticator::generateBackupCodes(5);
    $this->assertCount(5, $backupResult['codes']);

    // 6. Verify a backup code
    $backupCode = $backupResult['codes'][0];
    $index = GoogleAuthenticator::verifyBackupCode($backupCode, $backupResult['hashes']);
    $this->assertEquals(0, $index);

    // 7. After using backup code, remove it from valid hashes
    unset($backupResult['hashes'][$index]);

    // 8. Same backup code should no longer work
    $this->assertFalse(GoogleAuthenticator::verifyBackupCode($backupCode, $backupResult['hashes']));
  }

  // ===========================================
  // Recovery Flow Tests
  // ===========================================

  /**
   * Test generateRecoveryToken returns correct structure.
   */
  public function testGenerateRecoveryTokenStructure(): void {
    $result = GoogleAuthenticator::generateRecoveryToken();

    $this->assertArrayHasKey('token', $result);
    $this->assertArrayHasKey('hash', $result);
    $this->assertArrayHasKey('expires_at', $result);

    // Token should be hex string (64 chars for 32 bytes)
    $this->assertEquals(64, strlen($result['token']));
    $this->assertTrue(ctype_xdigit($result['token']));

    // Hash should be bcrypt
    $this->assertStringStartsWith('$2y$', $result['hash']);

    // Expires should be in the future
    $this->assertGreaterThan(time(), $result['expires_at']);
  }

  /**
   * Test generateRecoveryToken with custom expiry.
   */
  public function testGenerateRecoveryTokenCustomExpiry(): void {
    $expiry = 7200; // 2 hours
    $result = GoogleAuthenticator::generateRecoveryToken($expiry);

    $expectedExpiry = time() + $expiry;
    // Allow 2 second tolerance for test execution time
    $this->assertEqualsWithDelta($expectedExpiry, $result['expires_at'], 2);
  }

  /**
   * Test verifyRecoveryToken with valid token.
   */
  public function testVerifyRecoveryTokenValid(): void {
    $result = GoogleAuthenticator::generateRecoveryToken();

    $isValid = GoogleAuthenticator::verifyRecoveryToken(
      $result['token'],
      $result['hash'],
      $result['expires_at']
    );

    $this->assertTrue($isValid);
  }

  /**
   * Test verifyRecoveryToken with invalid token.
   */
  public function testVerifyRecoveryTokenInvalid(): void {
    $result = GoogleAuthenticator::generateRecoveryToken();

    $isValid = GoogleAuthenticator::verifyRecoveryToken(
      'invalid_token',
      $result['hash'],
      $result['expires_at']
    );

    $this->assertFalse($isValid);
  }

  /**
   * Test verifyRecoveryToken with expired token.
   */
  public function testVerifyRecoveryTokenExpired(): void {
    $result = GoogleAuthenticator::generateRecoveryToken();

    // Set expiry to past
    $expiredTime = time() - 3600;

    $isValid = GoogleAuthenticator::verifyRecoveryToken(
      $result['token'],
      $result['hash'],
      $expiredTime
    );

    $this->assertFalse($isValid);
  }

  // ===========================================
  // Backup Code Formatting Tests
  // ===========================================

  /**
   * Test formatBackupCodes default formatting.
   */
  public function testFormatBackupCodesDefault(): void {
    $codes = ['12345678', '87654321'];
    $formatted = GoogleAuthenticator::formatBackupCodes($codes);

    $this->assertEquals(['1234-5678', '8765-4321'], $formatted);
  }

  /**
   * Test formatBackupCodes with custom group size.
   */
  public function testFormatBackupCodesCustomGroupSize(): void {
    $codes = ['123456789012'];
    $formatted = GoogleAuthenticator::formatBackupCodes($codes, 3, '.');

    $this->assertEquals(['123.456.789.012'], $formatted);
  }

  /**
   * Test normalizeBackupCode removes formatting.
   */
  public function testNormalizeBackupCode(): void {
    $this->assertEquals('12345678', GoogleAuthenticator::normalizeBackupCode('1234-5678'));
    $this->assertEquals('12345678', GoogleAuthenticator::normalizeBackupCode('1234 5678'));
    $this->assertEquals('12345678', GoogleAuthenticator::normalizeBackupCode('12.34.56.78'));
    $this->assertEquals('12345678', GoogleAuthenticator::normalizeBackupCode('  1234-5678  '));
  }

  // ===========================================
  // Serialization Tests
  // ===========================================

  /**
   * Test serializeBackupHashes produces valid JSON.
   */
  public function testSerializeBackupHashes(): void {
    $result = GoogleAuthenticator::generateBackupCodes(3);
    $serialized = GoogleAuthenticator::serializeBackupHashes($result['hashes']);

    $this->assertJson($serialized);

    $decoded = json_decode($serialized, true);
    $this->assertCount(3, $decoded);
  }

  /**
   * Test deserializeBackupHashes restores array.
   */
  public function testDeserializeBackupHashes(): void {
    $result = GoogleAuthenticator::generateBackupCodes(3);
    $serialized = GoogleAuthenticator::serializeBackupHashes($result['hashes']);
    $deserialized = GoogleAuthenticator::deserializeBackupHashes($serialized);

    $this->assertEquals(array_values($result['hashes']), $deserialized);
  }

  /**
   * Test deserializeBackupHashes handles invalid JSON.
   */
  public function testDeserializeBackupHashesInvalidJson(): void {
    $result = GoogleAuthenticator::deserializeBackupHashes('invalid json');
    $this->assertEquals([], $result);
  }

  // ===========================================
  // Backup Code Management Tests
  // ===========================================

  /**
   * Test removeUsedBackupCode removes correct index.
   */
  public function testRemoveUsedBackupCode(): void {
    $hashes = ['hash0', 'hash1', 'hash2', 'hash3'];
    $updated = GoogleAuthenticator::removeUsedBackupCode($hashes, 1);

    $this->assertCount(3, $updated);
    $this->assertEquals(['hash0', 'hash2', 'hash3'], $updated);
  }

  /**
   * Test getRemainingBackupCodesCount with array.
   */
  public function testGetRemainingBackupCodesCountArray(): void {
    $result = GoogleAuthenticator::generateBackupCodes(5);
    $count = GoogleAuthenticator::getRemainingBackupCodesCount($result['hashes']);

    $this->assertEquals(5, $count);
  }

  /**
   * Test getRemainingBackupCodesCount with serialized string.
   */
  public function testGetRemainingBackupCodesCountSerialized(): void {
    $result = GoogleAuthenticator::generateBackupCodes(7);
    $serialized = GoogleAuthenticator::serializeBackupHashes($result['hashes']);
    $count = GoogleAuthenticator::getRemainingBackupCodesCount($serialized);

    $this->assertEquals(7, $count);
  }

  // ===========================================
  // Helper Method Tests
  // ===========================================

  /**
   * Test isMfaEnforced returns true when enabled with valid secret.
   */
  public function testIsMfaEnforcedTrue(): void {
    $secret = GoogleAuthenticator::generateSecret();
    $this->assertTrue(GoogleAuthenticator::isMfaEnforced($secret, true));
  }

  /**
   * Test isMfaEnforced returns false when disabled.
   */
  public function testIsMfaEnforcedDisabled(): void {
    $secret = GoogleAuthenticator::generateSecret();
    $this->assertFalse(GoogleAuthenticator::isMfaEnforced($secret, false));
  }

  /**
   * Test isMfaEnforced returns false with null secret.
   */
  public function testIsMfaEnforcedNullSecret(): void {
    $this->assertFalse(GoogleAuthenticator::isMfaEnforced(null, true));
  }

  /**
   * Test isMfaEnforced returns false with invalid secret.
   */
  public function testIsMfaEnforcedInvalidSecret(): void {
    $this->assertFalse(GoogleAuthenticator::isMfaEnforced('short', true));
  }

  // ===========================================
  // MFA Setup Bundle Tests
  // ===========================================

  /**
   * Test createMfaSetup returns complete bundle.
   */
  public function testCreateMfaSetupStructure(): void {
    $setup = GoogleAuthenticator::createMfaSetup('user@test.com', 'TestApp');

    $this->assertArrayHasKey('secret', $setup);
    $this->assertArrayHasKey('qr_code_url', $setup);
    $this->assertArrayHasKey('otpauth_url', $setup);
    $this->assertArrayHasKey('backup_codes', $setup);
    $this->assertArrayHasKey('backup_hashes', $setup);

    // Verify secret is valid
    $this->assertTrue(GoogleAuthenticator::isValidSecret($setup['secret']));

    // Verify QR code URL
    $this->assertStringContainsString('chart.googleapis.com', $setup['qr_code_url']);

    // Verify otpauth URL
    $this->assertStringStartsWith('otpauth://totp/', $setup['otpauth_url']);

    // Verify backup codes are formatted
    $this->assertCount(10, $setup['backup_codes']);
    $this->assertStringContainsString('-', $setup['backup_codes'][0]);

    // Verify backup hashes
    $this->assertCount(10, $setup['backup_hashes']);
  }

  /**
   * Test createMfaSetup with custom backup code count.
   */
  public function testCreateMfaSetupCustomBackupCount(): void {
    $setup = GoogleAuthenticator::createMfaSetup('user@test.com', 'TestApp', 5);

    $this->assertCount(5, $setup['backup_codes']);
    $this->assertCount(5, $setup['backup_hashes']);
  }

  // ===========================================
  // Verify Any Code Tests
  // ===========================================

  /**
   * Test verifyTotpOrBackup with valid TOTP code.
   */
  public function testVerifyAnyCodeTotp(): void {
    $secret = GoogleAuthenticator::generateSecret();
    $code = GoogleAuthenticator::getCode($secret);
    $backupResult = GoogleAuthenticator::generateBackupCodes(3);

    $result = GoogleAuthenticator::verifyTotpOrBackup($secret, $code, $backupResult['hashes']);

    $this->assertTrue($result['valid']);
    $this->assertEquals('totp', $result['type']);
    $this->assertNull($result['backup_index']);
  }

  /**
   * Test verifyTotpOrBackup with valid backup code.
   */
  public function testVerifyAnyCodeBackup(): void {
    $secret = GoogleAuthenticator::generateSecret();
    $backupResult = GoogleAuthenticator::generateBackupCodes(3);
    $backupCode = $backupResult['codes'][1];

    $result = GoogleAuthenticator::verifyTotpOrBackup($secret, $backupCode, $backupResult['hashes']);

    $this->assertTrue($result['valid']);
    $this->assertEquals('backup', $result['type']);
    $this->assertEquals(1, $result['backup_index']);
  }

  /**
   * Test verifyTotpOrBackup with formatted backup code.
   */
  public function testVerifyAnyCodeFormattedBackup(): void {
    $secret = GoogleAuthenticator::generateSecret();
    $backupResult = GoogleAuthenticator::generateBackupCodes(3);
    $formattedCode = GoogleAuthenticator::formatBackupCodes([$backupResult['codes'][0]])[0];

    $result = GoogleAuthenticator::verifyTotpOrBackup($secret, $formattedCode, $backupResult['hashes']);

    $this->assertTrue($result['valid']);
    $this->assertEquals('backup', $result['type']);
  }

  /**
   * Test verifyTotpOrBackup with invalid code.
   */
  public function testVerifyAnyCodeInvalid(): void {
    $secret = GoogleAuthenticator::generateSecret();
    $backupResult = GoogleAuthenticator::generateBackupCodes(3);

    $result = GoogleAuthenticator::verifyTotpOrBackup($secret, '000000', $backupResult['hashes']);

    $this->assertFalse($result['valid']);
    $this->assertNull($result['type']);
    $this->assertNull($result['backup_index']);
  }

  // ===========================================
  // E2E Recovery Flow Test
  // ===========================================

  /**
   * Test complete recovery flow E2E.
   */
  public function testCompleteRecoveryFlowE2E(): void {
    // 1. User sets up MFA
    $setup = GoogleAuthenticator::createMfaSetup('user@example.com', 'TestApp', 5);

    // Store these in "database"
    $dbSecret = $setup['secret'];
    $dbBackupHashes = $setup['backup_hashes'];
    $dbMfaEnabled = true;

    // Show backup codes to user once (formatted)
    $this->assertCount(5, $setup['backup_codes']);

    // 2. User loses phone, needs to use backup code
    // Simulate user entering a formatted backup code
    $userInput = $setup['backup_codes'][2]; // e.g., "1234-5678"

    // 3. Verify the backup code
    $result = GoogleAuthenticator::verifyTotpOrBackup($dbSecret, $userInput, $dbBackupHashes);
    $this->assertTrue($result['valid']);
    $this->assertEquals('backup', $result['type']);
    $this->assertEquals(2, $result['backup_index']);

    // 4. Remove used backup code
    $dbBackupHashes = GoogleAuthenticator::removeUsedBackupCode($dbBackupHashes, $result['backup_index']);
    $this->assertEquals(4, GoogleAuthenticator::getRemainingBackupCodesCount($dbBackupHashes));

    // 5. Same backup code should no longer work
    $result2 = GoogleAuthenticator::verifyTotpOrBackup($dbSecret, $userInput, $dbBackupHashes);
    $this->assertFalse($result2['valid']);

    // 6. User requests email recovery (lost all backup codes scenario)
    $recovery = GoogleAuthenticator::generateRecoveryToken(3600);

    // Store hash and expiry in "database"
    $dbRecoveryHash = $recovery['hash'];
    $dbRecoveryExpiry = $recovery['expires_at'];

    // Send token to user's email (recovery['token'])

    // 7. User clicks recovery link with token
    $isValidRecovery = GoogleAuthenticator::verifyRecoveryToken(
      $recovery['token'],
      $dbRecoveryHash,
      $dbRecoveryExpiry
    );
    $this->assertTrue($isValidRecovery);

    // 8. Disable MFA after successful recovery
    $dbMfaEnabled = false;
    $this->assertFalse(GoogleAuthenticator::isMfaEnforced($dbSecret, $dbMfaEnabled));

    // 9. User re-enables MFA with new setup
    $newSetup = GoogleAuthenticator::createMfaSetup('user@example.com', 'TestApp');
    $dbSecret = $newSetup['secret'];
    $dbBackupHashes = $newSetup['backup_hashes'];
    $dbMfaEnabled = true;

    // 10. Verify new TOTP works
    $newCode = GoogleAuthenticator::getCode($dbSecret);
    $this->assertTrue(GoogleAuthenticator::verifyCode($dbSecret, $newCode));
  }

  /**
   * Test database storage and retrieval simulation.
   */
  public function testDatabaseStorageSimulation(): void {
    // Setup MFA
    $setup = GoogleAuthenticator::createMfaSetup('user@example.com', 'MyApp');

    // Simulate storing in database
    $dbRecord = [
      'email' => 'user@example.com',
      'mfa_secret' => $setup['secret'],
      'mfa_enabled' => true,
      'backup_codes' => GoogleAuthenticator::serializeBackupHashes($setup['backup_hashes']),
      'created_at' => date('Y-m-d H:i:s')
    ];

    // Simulate retrieving from database
    $user = $dbRecord;

    // Restore backup hashes from storage
    $backupHashes = GoogleAuthenticator::deserializeBackupHashes($user['backup_codes']);

    // Check MFA is enforced
    $this->assertTrue(GoogleAuthenticator::isMfaEnforced($user['mfa_secret'], $user['mfa_enabled']));

    // Verify TOTP code
    $code = GoogleAuthenticator::getCode($user['mfa_secret']);
    $result = GoogleAuthenticator::verifyTotpOrBackup($user['mfa_secret'], $code, $backupHashes);
    $this->assertTrue($result['valid']);

    // Use a backup code
    $normalizedBackup = GoogleAuthenticator::normalizeBackupCode($setup['backup_codes'][0]);
    $backupResult = GoogleAuthenticator::verifyBackupCode($normalizedBackup, $backupHashes);
    $this->assertNotFalse($backupResult);

    // Update database with used code removed
    $backupHashes = GoogleAuthenticator::removeUsedBackupCode($backupHashes, $backupResult);
    $dbRecord['backup_codes'] = GoogleAuthenticator::serializeBackupHashes($backupHashes);

    // Verify remaining count
    $remaining = GoogleAuthenticator::getRemainingBackupCodesCount($dbRecord['backup_codes']);
    $this->assertEquals(9, $remaining);
  }
}
