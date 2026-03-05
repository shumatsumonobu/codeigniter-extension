# Changelog

All notable changes to this project will be documented in this file.

> Looking for older versions? See the [changelog archive (v3.3.8 — v4.1.9)](CHANGELOG_archive.md).

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

[4.2.0]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.1.9...v4.2.0
[5.0.0]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.2.0...v5.0.0
[5.0.1]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v5.0.0...v5.0.1
[5.0.2]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v5.0.1...v5.0.2
[5.0.3]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v5.0.2...v5.0.3
[5.0.4]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v5.0.3...v5.0.4
[5.0.5]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v5.0.4...v5.0.5
