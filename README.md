# CodeIgniter Extension

[![PHP Version](https://img.shields.io/packagist/php-v/takuya-motoshima/codeigniter-extension)](https://packagist.org/packages/takuya-motoshima/codeigniter-extension)
[![License](https://img.shields.io/packagist/l/takuya-motoshima/codeigniter-extension)](LICENSE)
[![Packagist Downloads](https://img.shields.io/packagist/dt/takuya-motoshima/codeigniter-extension)](https://packagist.org/packages/takuya-motoshima/codeigniter-extension)
[![Latest Version](https://img.shields.io/packagist/v/takuya-motoshima/codeigniter-extension)](https://packagist.org/packages/takuya-motoshima/codeigniter-extension)

[日本語](README_ja.md) | [Changelog](CHANGELOG.md) | [変更履歴](CHANGELOG_ja.md)

An enhanced CodeIgniter 3 package providing extended core classes (controllers, models, views) and utility classes.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
- [Architecture](#architecture)
- [Usage Examples](#usage-examples)
- [API Reference](#api-reference)
- [Troubleshooting](#troubleshooting)
- [Testing](#testing)
- [Documentation](#documentation)
- [License](#license)

## Features

### Core Extensions
- **Enhanced Controllers** - JSON response, template rendering, access control
- **Advanced Models** - Query caching, batch operations, helper methods
- **Enhanced Router** - Annotation-based access control

### Utility Classes
- **Image Processing** - Resize, crop, format conversion, GIF frame extraction, PDF to image
- **Video Processing** - Video file manipulation and conversion
- **File Operations** - Advanced file and directory operations with locking
- **CSV Handling** - Import/export utilities
- **Email** - Template-based emails, Amazon SES integration
- **REST Client** - HTTP client for API integrations
- **Security** - Encryption/decryption, IP validation
- **Validation** - Custom rules (hostname, IP, CIDR, datetime, paths)
- **Session Management** - Database-backed sessions with custom columns, PHP 7.0+ SessionHandlerInterface compliance
- **Logging** - Enhanced logging with context
- **Template Engine** - Twig integration with session variables
- **Google Authenticator MFA** - TOTP-based two-factor authentication with backup codes and recovery flow

### AWS Integration
- **Amazon Rekognition** - Face detection, comparison, and analysis
- **Amazon SES** - Reliable email delivery service

## Requirements

- **PHP** 7.3.0 or later
- **Composer**
- **PHP Extensions:**
  - php-gd
  - php-mbstring
  - php-xml
  - php-imagick (optional, for GIF operations)

### Optional: ImageMagick Installation

Required for `extractFirstFrameOfGif` method in `\X\Util\ImageHelper`.

**Amazon Linux 2:**
```sh
sudo yum -y install ImageMagick php-imagick
```

**Amazon Linux 2023:**
```sh
# Install ImageMagick and PECL
sudo dnf -y install ImageMagick ImageMagick-devel php-pear.noarch

# Install imagick extension
sudo pecl install imagick
echo "extension=imagick.so" | sudo tee -a /etc/php.ini

# Restart services
sudo systemctl restart nginx php-fpm
```

## Installation

Create a new project using Composer:

```sh
composer create-project takuya-motoshima/codeigniter-extension myapp
cd myapp
```

## Quick Start

### 1. Set Permissions

```sh
sudo chmod -R 755 public/upload application/{logs,cache,session}
sudo chown -R nginx:nginx public/upload application/{logs,cache,session}
```

### 2. Configure Web Server

Copy the Nginx configuration:

```sh
sudo cp nginx.sample.conf /etc/nginx/conf.d/myapp.conf
sudo systemctl restart nginx
```

### 3. Set Up Database

Import the database schema:

```sh
mysql -u root -p your_database < skeleton/init.sql
```

### 4. Build Frontend Assets

```sh
cd client
npm install
npm run build
```

### 5. Access Application

Open `http://{your-server-ip}:3000/` in your browser.

**Default Credentials:**
- Email: `robin@example.com`
- Password: `password`

### Screenshots

<p align="left">
  <img alt="Sign In" src="https://raw.githubusercontent.com/takuya-motoshima/codeigniter-extension/master/screencaps/sign-in.png" width="45%">
  <img alt="User List" src="https://raw.githubusercontent.com/takuya-motoshima/codeigniter-extension/master/screencaps/list-of-users.png" width="45%">
</p>

#### MFA Screenshots

<p align="left">
  <img alt="Login Page" src="https://raw.githubusercontent.com/takuya-motoshima/codeigniter-extension/master/screencaps/01-login-page.png" width="45%">
  <img alt="MFA Settings (Disabled)" src="https://raw.githubusercontent.com/takuya-motoshima/codeigniter-extension/master/screencaps/02-mfa-settings-disabled.png" width="45%">
</p>
<p align="left">
  <img alt="MFA Settings (Enabled)" src="https://raw.githubusercontent.com/takuya-motoshima/codeigniter-extension/master/screencaps/03-mfa-settings-enabled.png" width="45%">
  <img alt="MFA Setup Screen" src="https://raw.githubusercontent.com/takuya-motoshima/codeigniter-extension/master/screencaps/04-mfa-settings-setup-screen.png" width="45%">
</p>
<p align="left">
  <img alt="MFA Backup Codes" src="https://raw.githubusercontent.com/takuya-motoshima/codeigniter-extension/master/screencaps/05-mfa-settings-setup-recover-code.png" width="45%">
  <img alt="MFA Verify" src="https://raw.githubusercontent.com/takuya-motoshima/codeigniter-extension/master/screencaps/07-mfa-verify.png" width="45%">
</p>
<p align="left">
  <img alt="MFA Recovery" src="https://raw.githubusercontent.com/takuya-motoshima/codeigniter-extension/master/screencaps/08-mfa-recovery.png" width="45%">
</p>

## Configuration

### Basic Config (`application/config/config.php`)

<table>
  <thead>
    <tr>
      <th>Setting</th>
      <th>Default</th>
      <th>Recommended</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>base_url</td>
      <td><em>empty</em></td>
      <td>if (!empty($_SERVER['HTTP_HOST'])) $config['base_url'] = '//' . $_SERVER['HTTP_HOST'] . str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);</td>
    </tr>
    <tr>
      <td>enable_hooks</td>
      <td>FALSE</td>
      <td>TRUE</td>
    </tr>
    <tr>
      <td>permitted_uri_chars</td>
      <td>a-z 0-9~%.:_\-</td>
      <td>a-z 0-9~%.:_\-,</td>
    </tr>
    <tr>
      <td>sess_save_path</td>
      <td>NULL</td>
      <td>APPPATH . 'session';</td>
    </tr>
    <tr>
      <td>cookie_httponly</td>
      <td>FALSE</td>
      <td>TRUE</td>
    </tr>
    <tr>
      <td>composer_autoload</td>
      <td>FALSE</td>
      <td>realpath(APPPATH . '../vendor/autoload.php');</td>
    </tr>
    <tr>
      <td>index_page</td>
      <td>index.php</td>
      <td><em>empty</em></td>
    </tr>
  </tbody>
</table>

### Access Control Setup

#### 1. Define Default Route

In `application/config/routes.php`:

```php
$route['default_controller'] = 'users/login';
```

#### 2. Set Session Constant

In `application/config/constants.php`:

```php
const SESSION_NAME = 'session';
```

#### 3. Configure Hooks

In `application/config/hooks.php`:

```php
use \X\Annotation\AnnotationReader;
use \X\Util\Logger;

$hook['post_controller_constructor'] = function() {
  if (is_cli()) return;

  $CI =& get_instance();
  $meta = AnnotationReader::getAccessibility($CI->router->class, $CI->router->method);
  $loggedin = !empty($_SESSION[SESSION_NAME]);

  if (!$meta->allow_http)
    throw new \RuntimeException('HTTP access is not allowed');
  else if ($loggedin && !$meta->allow_login)
    redirect('/users/index');
  else if (!$loggedin && !$meta->allow_logoff)
    redirect('/users/login');
};

$hook['pre_system'] = function () {
  $dotenv = Dotenv\Dotenv::createImmutable(ENV_DIR);
  $dotenv->load();
  set_exception_handler(function ($e) {
    Logger::error($e);
    show_error($e->getMessage(), 500);
  });
};
```

### Session Management

The package extends CodeIgniter's database session driver with PHP 7.0+ SessionHandlerInterface compatibility:

**Features:**
- **Custom Session Columns** - Store additional data (e.g., email, user_id) directly in session table
- **updateTimestamp Implementation** - Complies with PHP 7.0+ SessionHandlerInterface requirements
- **No Warning Logs** - Prevents "Failed to write session data" warnings in PHP 7.0+

**Configuration** (`application/config/config.php`):

```php
$config['sess_driver'] = 'database';
$config['sess_save_path'] = 'session';
$config['sess_table_additional_columns'] = ['email'];
```

**Technical Details:**

The `SessionDatabaseDriver` class implements the `updateTimestamp()` method required by PHP 7.0+'s SessionHandlerInterface. This prevents PHP from falling back to the default file handler, which causes warnings like:

```
Warning: session_write_close(): Failed to write session data using user defined save handler.
```

For more information, see the [PHP SessionHandlerInterface documentation](https://www.php.net/manual/en/class.sessionhandlerinterface.php).

## Architecture

### Directory Structure

```
src/X/
├── Annotation/          # Access control annotations
│   ├── Access.php           # @Access annotation definition
│   └── AnnotationReader.php # Annotation parser
├── Composer/            # Composer installer
│   └── Installer.php        # Post create-project handler
├── Constant/            # Constants
│   ├── Environment.php      # Environment constants (DEVELOPMENT, TESTING, PRODUCTION)
│   └── HttpStatus.php       # HTTP status code constants
├── Controller/          # Controller extensions
│   └── Controller.php       # Base controller with response helpers
├── Core/                # CodeIgniter core extensions
│   ├── Loader.php           # Extended loader
│   ├── Router.php           # Extended router
│   └── URI.php              # Extended URI
├── Database/            # Database extensions
│   ├── DB.php               # DB factory
│   ├── Driver.php           # Base driver
│   ├── QueryBuilder.php     # Extended query builder
│   └── Result.php           # Extended result set
├── Exception/           # Custom exceptions
│   ├── AccessDeniedException.php
│   └── RestClientException.php
├── Hook/                # Hooks
│   └── Authenticate.php     # Authentication hook
├── Library/             # Library extensions
│   ├── FormValidation.php   # Extended form validation
│   ├── Input.php            # Extended input library
│   ├── Router.php           # Router library
│   └── SessionDatabaseDriver.php # Database session driver (PHP 7.0+)
├── Model/               # Model extensions
│   ├── Model.php            # Base model with query builder
│   ├── AddressModel.php     # Address model
│   ├── SessionModel.php     # Session model
│   └── SessionModelInterface.php
├── Rekognition/         # AWS Rekognition
│   └── Client.php           # Face detection/comparison client
└── Util/                # Utility classes (22 classes)
    ├── AmazonSesClient.php  # Amazon SES email
    ├── ArrayHelper.php      # Array operations
    ├── Cipher.php           # Encryption (AES-256-CTR)
    ├── CsvHelper.php        # CSV import/export
    ├── DateHelper.php       # Date operations
    ├── EMail.php            # Email with templates
    ├── FileHelper.php       # File/directory operations
    ├── GoogleAuthenticator.php # TOTP MFA with backup codes
    ├── HtmlHelper.php       # HTML utilities
    ├── HttpInput.php        # HTTP input processing
    ├── HttpResponse.php     # HTTP response builder
    ├── ImageHelper.php      # Image processing
    ├── IpUtils.php          # IP address utilities
    ├── Iterator.php         # Combinatorics
    ├── Loader.php           # Resource loader
    ├── Logger.php           # Logging
    ├── RestClient.php       # REST API client
    ├── SessionHelper.php    # Session utilities
    ├── StringHelper.php     # String operations
    ├── Template.php         # Twig integration
    ├── UrlHelper.php        # URL utilities
    ├── Validation.php       # Data validation
    └── VideoHelper.php      # Video processing
```

### Application Structure (skeleton/)

Projects created with this package follow this structure:

```
application/
├── core/
│   ├── AppController.php    # Extends \X\Controller\Controller
│   └── AppModel.php         # Extends \X\Model\Model
├── config/
│   ├── hooks.php            # Access control via AnnotationReader
│   └── constants.php        # SESSION_NAME, ENV_DIR constants
├── controllers/             # Application controllers
├── models/                  # Application models
└── views/                   # Twig templates
```

## Usage Examples

### Controllers

```php
use \X\Annotation\Access;

class Users extends AppController {
  /**
   * @Access(allow_login=true, allow_logoff=false, allow_role="admin")
   */
  public function index() {
    $users = $this->UserModel->get()->result_array();
    parent::set('users', $users)->view('users/index');
  }

  /**
   * @Access(allow_http=true)
   */
  public function api() {
    $data = ['message' => 'Success'];
    parent::set($data)->json();
  }
}
```

### Models

```php
class UserModel extends AppModel {
  const TABLE = 'user';

  public function getActiveUsers() {
    return $this
      ->where('active', 1)
      ->order_by('name', 'ASC')
      ->get()
      ->result_array();
  }
}
```

### Twig Templates

Session variables are automatically available:

```php
// PHP
$_SESSION['user'] = ['name' => 'John Smith', 'role' => 'admin'];
```

```twig
{# Template #}
{% if session.user is defined %}
  <p>Welcome, {{ session.user.name }}!</p>
  {% if session.user.role == 'admin' %}
    <a href="/admin">Admin Panel</a>
  {% endif %}
{% endif %}
```

### Using Utilities

```php
// Image processing
use \X\Util\ImageHelper;
ImageHelper::resize('/path/to/image.jpg', '/path/to/output.jpg', 800, 600);

// File operations
use \X\Util\FileHelper;
FileHelper::makeDirectory('/path/to/dir', 0755);

// Encryption
use \X\Util\Cipher;
$encrypted = Cipher::encrypt('secret data', 'encryption-key');

// REST client
use \X\Util\RestClient;
$client = new RestClient(['base_url' => 'https://api.example.com']);
$response = $client->get('/users');
```

### Google Authenticator MFA

Complete two-factor authentication implementation with TOTP codes, backup codes, and account recovery.

#### Setup MFA for a User

```php
use \X\Util\GoogleAuthenticator;

// Generate complete MFA setup bundle
$setup = GoogleAuthenticator::createMfaSetup('user@example.com', 'MyApp');

// Store in database
$user['mfa_secret'] = $setup['secret'];
$user['mfa_enabled'] = true;
$user['backup_codes'] = GoogleAuthenticator::serializeBackupHashes($setup['backup_hashes']);

// Show to user (once only!)
echo "Scan this QR code: " . $setup['qr_code_url'];
echo "Or enter manually: " . $setup['secret'];
echo "Backup codes: " . implode(', ', $setup['backup_codes']);
```

#### Verify TOTP Code at Login

```php
use \X\Util\GoogleAuthenticator;

// Check if MFA is required
if (GoogleAuthenticator::isMfaEnforced($user['mfa_secret'], $user['mfa_enabled'])) {
    $code = $_POST['mfa_code'];
    $backupHashes = GoogleAuthenticator::deserializeBackupHashes($user['backup_codes']);

    // Verify TOTP or backup code
    $result = GoogleAuthenticator::verifyTotpOrBackup($user['mfa_secret'], $code, $backupHashes);

    if ($result['valid']) {
        if ($result['type'] === 'backup') {
            // Remove used backup code
            $backupHashes = GoogleAuthenticator::removeUsedBackupCode($backupHashes, $result['backup_index']);
            $user['backup_codes'] = GoogleAuthenticator::serializeBackupHashes($backupHashes);
            // Update database
        }
        // Grant access
        $_SESSION['mfa_verified'] = true;
    } else {
        // Invalid code
        throw new \Exception('Invalid MFA code');
    }
}
```

#### Account Recovery Flow

```php
use \X\Util\GoogleAuthenticator;

// Generate recovery token (send via email)
$recovery = GoogleAuthenticator::generateRecoveryToken(3600); // 1 hour expiry

// Store hash and expiry in database
$user['recovery_hash'] = $recovery['hash'];
$user['recovery_expires'] = $recovery['expires_at'];

// Send $recovery['token'] to user's email

// When user clicks recovery link
$token = $_GET['token'];
if (GoogleAuthenticator::verifyRecoveryToken($token, $user['recovery_hash'], $user['recovery_expires'])) {
    // Disable MFA
    $user['mfa_enabled'] = false;
    // Clear recovery token
    $user['recovery_hash'] = null;
}
```

#### Database Schema

```sql
ALTER TABLE `user` ADD COLUMN `mfa_secret` VARCHAR(64) NULL;
ALTER TABLE `user` ADD COLUMN `mfa_enabled` TINYINT(1) DEFAULT 0;
ALTER TABLE `user` ADD COLUMN `backup_codes` TEXT NULL;
ALTER TABLE `user` ADD COLUMN `recovery_hash` VARCHAR(255) NULL;
ALTER TABLE `user` ADD COLUMN `recovery_expires` INT NULL;
```

## API Reference

### Controller Methods

| Method | Description |
|--------|-------------|
| `json()` | Send JSON response |
| `view($template)` | Render Twig template |
| `html($html)` | Send HTML response |
| `text($text)` | Send plain text response |
| `image($path)` | Send image response |
| `download($filename, $content)` | Force file download |
| `set($key, $value)` | Set response data |
| `setCorsHeader($origin)` | Set CORS headers |

### Model Methods

| Method | Description |
|--------|-------------|
| `get_all()` | Get all records |
| `get_by_id($id)` | Get record by ID |
| `count_by_id($id)` | Count records by ID |
| `exists_by_id($id)` | Check if record exists |
| `insert_on_duplicate_update()` | INSERT ... ON DUPLICATE KEY UPDATE |
| `insert_on_duplicate_update_batch()` | Batch upsert |

### Utility Classes

| Class | Key Methods |
|-------|-------------|
| `ImageHelper` | `resize()`, `crop()`, `writeDataURLToFile()`, `pdf2Image()` |
| `FileHelper` | `makeDirectory()`, `delete()`, `copyFile()`, `move()` |
| `Cipher` | `encrypt()`, `decrypt()`, `encode_sha256()` |
| `RestClient` | `get()`, `post()`, `put()`, `delete()` |
| `Logger` | `debug()`, `info()`, `error()`, `display()` |
| `Validation` | `hostname()`, `ipaddress()`, `email()`, `is_path()` |
| `IpUtils` | `isIPv4()`, `isIPv6()`, `inRange()` |
| `Template` | `load($template, $params)` |
| `GoogleAuthenticator` | `generateSecret()`, `verifyCode()`, `getQrCodeUrl()`, `createMfaSetup()` |

### GoogleAuthenticator Methods

| Method | Description |
|--------|-------------|
| `generateSecret($length)` | Generate Base32-encoded TOTP secret |
| `getCode($secret, $timeSlice)` | Get current 6-digit TOTP code |
| `verifyCode($secret, $code, $discrepancy)` | Verify a TOTP code with time drift tolerance |
| `getQrCodeUrl($account, $secret, $issuer)` | Generate QR code URL for authenticator apps |
| `getOtpauthUrl($account, $secret, $issuer)` | Generate otpauth:// URL for manual entry |
| `generateBackupCodes($count, $length)` | Generate single-use backup codes with hashes |
| `verifyBackupCode($code, $hashes)` | Verify a backup code, returns index for removal |
| `formatBackupCodes($codes, $groupSize)` | Format codes for display (e.g., "1234-5678") |
| `normalizeBackupCode($code)` | Remove formatting from user input |
| `serializeBackupHashes($hashes)` | JSON encode hashes for database storage |
| `deserializeBackupHashes($json)` | Restore hashes from database |
| `removeUsedBackupCode($hashes, $index)` | Remove used code from hash array |
| `getRemainingBackupCodesCount($hashes)` | Count remaining backup codes |
| `generateRecoveryToken($expiry)` | Generate email recovery token with hash |
| `verifyRecoveryToken($token, $hash, $expires)` | Verify recovery token |
| `isMfaEnforced($secret, $enabled)` | Check if MFA should be required |
| `createMfaSetup($account, $issuer, $backupCount)` | Generate complete MFA setup bundle |
| `verifyTotpOrBackup($secret, $code, $backupHashes)` | Verify TOTP or backup code |
| `getTimeRemaining($timestamp)` | Seconds until current code expires |
| `isValidSecret($secret)` | Validate Base32 secret format |

## Troubleshooting

### Common Issues

#### "Failed to write session data" Warning

**Problem:** PHP 7.0+ shows session write warnings.

**Solution:** This package includes `SessionDatabaseDriver` which implements `updateTimestamp()` for PHP 7.0+ compatibility. Ensure you're using:

```php
$config['sess_driver'] = 'database';
```

#### Imagick Extension Not Found

**Problem:** `extractFirstFrameOfGif()` throws error.

**Solution:** Install ImageMagick and php-imagick:

```sh
# Amazon Linux 2023
sudo dnf -y install ImageMagick ImageMagick-devel php-pear.noarch
sudo pecl install imagick
echo "extension=imagick.so" | sudo tee -a /etc/php.ini
sudo systemctl restart php-fpm
```

#### Access Annotation Not Working

**Problem:** `@Access` annotations are ignored.

**Solution:**
1. Enable hooks in `config.php`: `$config['enable_hooks'] = TRUE;`
2. Configure `hooks.php` with `AnnotationReader::getAccessibility()`

#### Template Cache Issues

**Problem:** Twig templates not updating.

**Solution:** Clear the cache directory:

```sh
rm -rf application/cache/templates/*
```

## Testing

Run unit tests:

```sh
composer test
```

Test files are located in:
- `__tests__/*.php` - Test cases
- `phpunit.xml` - Configuration
- `phpunit-printer.yml` - Output format

## Documentation

- **[API Documentation](https://takuya-motoshima.github.io/codeigniter-extension/)** - Complete API reference
- **[Demo Application](demo/)** - Full working example
- **[Changelog](CHANGELOG.md)** - Version history and changes
- **[CodeIgniter 3 Guide](https://codeigniter.com/userguide3/)** - Official framework documentation

### Generate PHPDoc

```sh
# Download phpDocumentor (one-time)
wget https://phpdoc.org/phpDocumentor.phar
chmod +x phpDocumentor.phar

# Generate docs
php phpDocumentor.phar run -d src/ --ignore vendor --ignore src/X/Database/Driver/ -t docs/
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Author

**Takuya Motoshima**
- GitHub: [@takuya-motoshima](https://github.com/takuya-motoshima)
- Twitter: [@TakuyaMotoshima](https://x.com/takuya_motech)
- Facebook: [takuya.motoshima.7](https://www.facebook.com/takuya.motoshima.7)

## License

[MIT License](LICENSE)
