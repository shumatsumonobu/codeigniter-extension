<?php
namespace X\Util;
use \X\Util\Loader;

/**
 * Cryptographic utility class.
 *
 * Provides encryption and hashing methods including SHA-256, AES-256,
 * IV generation, and secure random token generation.
 */
final class Cipher {
  /**
   * Generate SHA-256 hash with a secret key.
   *
   * @param string $plaintext String to hash.
   * @param string|null $key Secret key appended before hashing. Default is `encryption_key` from config.
   * @return string 64-character hexadecimal hash string.
   * @throws \RuntimeException If no key is provided and encryption_key is not configured.
   */
  public static function encode_sha256(string $plaintext, string $key=null): string {
    if (empty($key))
      $key = Loader::config('config', 'encryption_key');
    if (empty($key))
      throw new \RuntimeException('Cant find encryption_key in application/config/config.php file');
    return hash('sha256', $plaintext . $key);
  }

  /**
   * Generate a cryptographic initialization vector (IV).
   *
   * @param string $algorithm Cipher algorithm. Default is "AES-256-CTR".
   * @return string Binary IV string of the appropriate length for the algorithm.
   */
  public static function generateInitialVector(string $algorithm='AES-256-CTR'): string {
    $len = openssl_cipher_iv_length($algorithm);
    return openssl_random_pseudo_bytes($len);
  }

  /**
   * Encrypt a string using symmetric encryption.
   *
   * ```php
   * use \X\Util\Cipher;
   *
   * $iv = Cipher::generateInitialVector();
   * $plaintext = 'Hello, World.';
   * $key = 'key';
   * $encrypted = Cipher::encrypt($plaintext, $key, $iv);// UHLY5PckT7Da02e42g==
   * $decrypted = Cipher::decrypt($encrypted, $key, $iv);// Hello, World.
   * ```
   * @param string $plaintext String to be encrypted.
   * @param string $key Encryption key.
   * @param string $iv IV.
   * @param string $algorithm (optional) Cryptographic Algorithm. Default is "AES-256-CTR".
   * @return string Encrypted string.
   */
  public static function encrypt(string $plaintext, string $key, string $iv, string $algorithm='AES-256-CTR'): string {
    $options = 0;
    return openssl_encrypt($plaintext, $algorithm, $key, $options, $iv);
  }

  /**
   * Decrypt a string encrypted with symmetric encryption.
   *
   * ```php
   * use \X\Util\Cipher;
   *
   * $iv = Cipher::generateInitialVector();
   * $plaintext = 'Hello, World.';
   * $key = 'key';
   * $encrypted = Cipher::encrypt($plaintext, $key, $iv);// UHLY5PckT7Da02e42g==
   * $decrypted = Cipher::decrypt($encrypted, $key, $iv);// Hello, World.
   * ```
   * @param string $encrypted Encrypted string.
   * @param string $key Decryption key.
   * @param string $iv IV.
   * @param string $algorithm (optional) Cryptographic Algorithm. Default is "AES-256-CTR".
   * @return string Decrypted string.
   */
  public static function decrypt(string $encrypted, string $key, string $iv, string $algorithm='AES-256-CTR'): string {
    $options = 0;
    return openssl_decrypt($encrypted, $algorithm, $key, $options, $iv);
  }

  /**
   * Generate a random base64-encoded key.
   *
   * @param int $len Number of random bytes to generate. Default is 32.
   * @return string Base64-encoded random key.
   * @throws \RuntimeException If length is less than 1.
   */
  public static function generateKey(int $len=32): string {
    if ($len < 1)
      throw new RuntimeException('Key length must be 1 or more');
    return base64_encode(random_bytes($len));
  }

  /**
   * Generate OpenSSL Key Pair
   * ```php
   * use \X\Util\Cipher;
   * 
   *  // Generate 4096bit long RSA key pair.
   *  Cipher::generateKeyPair($privateKey, $publicKey, [
   *    'digest_alg' => 'sha512',
   *    'private_key_bits' => 4096,
   *    'private_key_type' => OPENSSL_KEYTYPE_RSA
   *  ]);
   * 
   *  // Debug private key.
   *  // Output: -----BEGIN PRIVATE KEY-----
   *  //         MIIJQgIBADANBgkqhkiG9w0BAQEFAASCCSwwggkoAgEAAoICAQCpvdXUNEfrA4T+
   *  //         ...
   *  //         -----END PRIVATE KEY-----
   *  echo 'Private key:'. PHP_EOL . $privateKey;
   * 
   *  // Debug public key.
   *  // Output: -----BEGIN PUBLIC KEY-----
   *  //         MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAqb3V1DRH6wOE/oVhJWEo
   *  //         ...
   *  //         -----END PUBLIC KEY-----
   *  echo 'Public key:' . PHP_EOL. $publicKey;
   *  
   *  // OpenSSH encode the public key.
   *  // Output: ssh-rsa AAAAB3NzaC...
   *  $publicKey = Cipher::encodeOpenSshPublicKey($privateKey);
   * 
   *  // Debug OpenSSH-encoded public key.
   *  echo 'OpenSSH-encoded public key:' . PHP_EOL . $publicKey;
   * ```
   * @param string &$privateKey Receives the generated PEM-encoded private key.
   * @param string &$publicKey Receives the generated PEM-encoded public key.
   * @param array{
   *   digest_alg?: string,
   *   x509_extensions?: string,
   *   req_extensions?: string,
   *   private_key_bits?: int,
   *   private_key_type?: int,
   *   encrypt_key?: bool,
   *   encrypt_key_cipher?: int,
   *   curve_name?: string,
   *   config?: string
   * } $options OpenSSL key generation options:
   *   - `digest_alg`: Digest method (see openssl_get_md_methods()). Default is "sha512".
   *   - `private_key_bits`: Key length in bits. Default is 4096.
   *   - `private_key_type`: Key type constant (OPENSSL_KEYTYPE_RSA, etc.). Default is OPENSSL_KEYTYPE_RSA.
   *   - `config`: Path to alternative openssl.conf file.
   * @return void
   */
  public static function generateKeyPair(&$privateKey, &$publicKey, array $options=[]): void {
    $options = array_merge([
      'digest_alg' => 'sha512',
      'private_key_bits' => 4096,
      'private_key_type' => OPENSSL_KEYTYPE_RSA
    ], $options);
    $privateKeyResource = openssl_pkey_new($options);
    openssl_pkey_export($privateKeyResource, $privateKey);
    $publicKey = openssl_pkey_get_details($privateKeyResource)['key'];
  }

  /**
   * Convert a PEM private key to OpenSSH public key format.
   *
   * @param string $privateKey PEM-encoded private key.
   * @return string OpenSSH public key string (e.g., "ssh-rsa AAAA...").
   */
  public static function encodeOpenSshPublicKey(string $privateKey): string {
    $privateKeyResource = openssl_pkey_get_private($privateKey);
    $keyInfo = openssl_pkey_get_details($privateKeyResource);
    $buffer  = pack('N', 7) . 'ssh-rsa' . self::encodeOpenSshBuffer($keyInfo['rsa']['e']) . self::encodeOpenSshBuffer($keyInfo['rsa']['n']);
    return 'ssh-rsa ' . base64_encode($buffer);
  }

  /**
   * Encode a binary buffer in OpenSSH wire format.
   *
   * @param string $buffer Binary data to encode.
   * @return string Length-prefixed encoded buffer.
   */
  private static function encodeOpenSshBuffer(string $buffer): string {
    $len = strlen($buffer);
    if (ord($buffer[0]) & 0x80) {
      $len++;
      $buffer = "\x00" . $buffer;
    }
    return pack('Na*', $len, $buffer);
  }

  /**
   * Generate a cryptographically secure random string.
   *
   * @param int $len String length. Default is 64.
   * @param string $chars Character set to use. Default is alphanumeric (a-z, A-Z, 0-9).
   * @return string Random string of the specified length.
   * @throws \RangeException If length is less than 1.
   */
  public static function randStr(int $len=64, string $chars='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'): string {
    if ($len < 1)
      throw new \RangeException('Length must be a positive integer');
    $res = '';
    for ($i=0; $i<$len; $i++)
      $res .= $chars[random_int(0, strlen($chars) - 1)];
    return $res;
  }

  /**
   * Generate a random token68-compliant string.
   *
   * Token68 format is defined in RFC 7235, allowing alphanumeric chars
   * plus `-._~+/` and optionally trailing `=`.
   *
   * @param int $len String length. Default is 64.
   * @return string Random token68 string.
   */
  public static function randToken68(int $len=64): string {
    $equal = $len > 1 && random_int(0, 1) === 1 ? '=' : '';
    return self::randStr($len - strlen($equal), '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-._~+/') . $equal;
  }
}