# Changelog

All notable changes to this project will be documented in this file.

> Looking for older versions? See the [changelog archive (v3.3.8 — v4.1.9)](CHANGELOG_archive.md).

## [5.0.9] - 2026/7/21

### Added
- `\X\Util\AmazonSesClient` now supports cross-account SES access via STS AssumeRole — set `roleArn` (and optionally `roleSessionName`) to send through a role in another AWS account.
- AssumeRole integration test for `\X\Util\AmazonSesClient` (`@group assume-role`).
- `AWS_SES_ROLE_ARN` in `__tests__/.env.sample`.

### Fixed
- `\X\Util\AmazonSesClient` cached its SES client in a method-level `static`, so the client built by the first instance was reused by every other instance in the process — later instances silently ignored their own `region`, `credentials`, and `roleArn`. The client is now cached per instance.
- AssumeRole credentials are obtained through `AssumeRoleCredentialProvider` + `CredentialProvider::memoize`, so they refresh automatically instead of expiring after an hour in long-running processes.

## [5.0.8] - 2026/7/10

### Added
- Amazon SES client (`\X\Util\AmazonSesClient`) now supports IAM role authentication — omit `credentials` to use the AWS SDK default provider chain (e.g. EC2 instance profile).
- `debug` option for `\X\Util\AmazonSesClient` — logs options with `key`/`secret` masked.
- PHPUnit test suite for `\X\Util\AmazonSesClient` (`__tests__/AmazonSesClientTest.php`).
- IAM role integration tests for both Rekognition and SES clients (`@group iam-role`).
- Test commands in README for `--group` / `--exclude-group iam-role`.
- Docker local development environment for demo app — `docker compose up --build` starts nginx, PHP 7.3-fpm (Amazon Linux 2), and MariaDB 10.6.
- Sample user avatar images in `demo/public/upload/`.

### Changed
- PHP minimum version lowered from 8.0 to 7.3 (`composer.json`, `composer.json.dist`, `demo/composer.json`).
- `\X\Util\AmazonSesClient` `credentials` option is now optional (previously required). Existing credentials usage is unchanged.
- `\X\Util\AmazonSesClient` `configuration` option no longer sent to SES API when null.
- Removed CodeIgniter dependency (`get_instance()` / `form_validation`) from `\X\Util\AmazonSesClient::send()`.
- Regenerated `composer.lock` and `demo/composer.lock` for PHP 7.3 compatibility.
- Fixed `phpunit.xml` — corrected Rekognition test path and added SES test suite.
- Fixed test warnings — converted global constants to class constants in `ImageHelperTest` and `FileHelperTest`.
- Added `log_message()` stub in test bootstrap for non-CodeIgniter environments.
- Skip Imagick-dependent tests when the extension is not installed.
- Demo app `database.php` now reads DB credentials from environment variables with fallback defaults.
- Demo app `demo/README.md` rewritten with Docker-first setup instructions.
- Moved `demo/init.sql` to `demo/docker/init.sql` and added section comments to both `demo/docker/init.sql` and `skeleton/init.sql`.
- Fixed `demo/client/src/shared/Api.js` — removed hardcoded `Content-Type: application/json` that broke FormData requests.

## [5.0.7] - 2026/6/23

### Added
- Amazon Rekognition client (`\X\Rekognition\Client`) now supports IAM role authentication — omit `key`/`secret` to use the AWS SDK default provider chain (e.g. EC2 instance profile).
- Sandbox scripts for Rekognition face detection and comparison (`sandbox/rekognition-detect-faces.php`, `sandbox/rekognition-compare-faces.php`).

### Changed
- `\X\Rekognition\Client` `key`/`secret` options are now optional (previously required). Existing key/secret usage is unchanged.
- Mask `key`/`secret` in the Rekognition client debug log.
- Tidied `sandbox/` — consistent script names, flattened `rest/`, and added `sandbox/README.md`.

### Removed
- Japanese changelog files (`CHANGELOG_ja.md`, `CHANGELOG_ja_archive.md`).

## [5.0.6] - 2026/3/5

### Fixed
- Fixed broken links in README, CHANGELOG, and demo docs — migrated GitHub URLs from `takuya-motoshima` to `shumatsumonobu`.

## [5.0.5] - 2026/3/5

### Changed
- Improved PHPDoc across all `src/X/` classes — standardized `@param` array shapes, added missing `@throws`, and refined descriptions.

## [5.0.4] - 2026/1/7

### Added
- Interactive form validation test page in demo app (`/validation-test`) with real-time feedback.
- `SessionDatabaseDriver#updateTimestamp()` — PHP 7.0+ SessionHandlerInterface compliance, prevents "Failed to write session data" warnings.

### Changed
- Improved PHPDoc across all `src/X/` classes with better descriptions, usage examples, and standardized annotations.
- Revamped README.md and README_ja.md — badges, architecture overview, API reference, troubleshooting guide.

## [5.0.3] - 2025/11/25

### Added
- Form validation test script (`sandbox/form-validation-test.php`) covering checkbox array validation.

### Fixed
- Restore accidentally deleted `UserLogModel.php` in skeleton.

## [5.0.2] - 2025/11/8

### Changed
- Update copyright year to 2025 in LICENSE.
- Improve demo and skeleton structure (no core package changes).

## [5.0.1] - 2024/5/14

### Changed
- Fix installer — clean up `prototypes/`, `__tests__/`, `phpunit-printer.yml`, `phpunit.xml` after install.
- Add `client/package-lock.json` to skeleton.

## [5.0.0] - 2024/5/13

### Changed
- **PHP 8.0+ required.** To upgrade, extend the core classes in your application:

  | File | Class |
  |------|-------|
  | `AppController.php` | `extends \X\Controller\Controller` |
  | `AppInput.php` | `extends \X\Library\Input` |
  | `AppLoader.php` | `extends \X\Core\Loader` |
  | `AppModel.php` | `extends \X\Model\Model` |
  | `AppRouter.php` | `extends \X\Core\Router` |
  | `AppURI.php` | `extends \X\Core\URI` |

## [4.2.0] - 2024/5/13

### Changed
- Remove `$baseDir` argument from `Rekognition\Client#generateCollectionId()`.
- Remove deprecated snake_case methods from `EMail` class — use `messageFromTemplate`, `messageFromXml`, `setMailType`, `attachmentCid`.
- Rename methods for consistency:

  | Before | After |
  |--------|-------|
  | `ImageHelper::putBase64` | `ImageHelper::writeDataURLToFile` |
  | `ImageHelper::putBlob` | `ImageHelper::writeBlobToFile` |
  | `ImageHelper::readAsBase64` | `ImageHelper::readAsDataURL` |
  | `ImageHelper::isBase64` | `ImageHelper::isDataURL` |
  | `ImageHelper::convertBase64ToBlob` | `ImageHelper::dataURL2Blob` |
  | `ImageHelper::read` | `ImageHelper::readAsBlob` |
  | `VideoHelper::putBase64` | `VideoHelper::writeDataURLToFile` |
  | `VideoHelper::isBase64` | `VideoHelper::isDataURL` |
  | `VideoHelper::convertBase64ToBlob` | `VideoHelper::dataURL2Blob` |

[4.2.0]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.1.9...v4.2.0
[5.0.0]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.2.0...v5.0.0
[5.0.1]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v5.0.0...v5.0.1
[5.0.2]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v5.0.1...v5.0.2
[5.0.3]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v5.0.2...v5.0.3
[5.0.4]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v5.0.3...v5.0.4
[5.0.5]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v5.0.4...v5.0.5
[5.0.6]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v5.0.5...v5.0.6
[5.0.7]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v5.0.6...v5.0.7
[5.0.8]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v5.0.7...v5.0.8
[5.0.9]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v5.0.8...v5.0.9
