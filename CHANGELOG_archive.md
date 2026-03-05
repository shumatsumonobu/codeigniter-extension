# Changelog Archive (v3.3.8 — v4.1.9)

> For recent changes, see [CHANGELOG.md](CHANGELOG.md).

---

## [4.1.9] - 2023/9/15

### Changed
- Add leading-slash rejection option to path validation (`\X\Util\Validation#is_path`). Defaults to allow.

## [4.1.8] - 2023/9/15

### Changed
- Rename path validation function from `directory_path` to `is_path`.

## [4.1.7] - 2023/8/29

### Changed
- `FileHelper::makeDirectory()` now returns `true` on success, `false` on failure. Error log level changed from `error` to `info`.

## [4.1.6] - 2023/8/9

### Changed
- `Rekognition\Client#compareFaces()` returns `0` similarity instead of throwing `RuntimeException` when no faces are found.
- `FileHelper#delete()` clears the stat cache (`clearstatcache`) before removing its own directory.

## [4.1.5] - 2023/5/25

### Added
- PDF-to-image conversion via `ImageHelper::pdf2Image()`.

## [4.1.4] - 2023/5/11

### Changed
- Add unit tests for `Rekognition\Client`.
- Rename `RestClient` member variables: `$option` → `$options`, `$response_source` → `$responseRaw`, `$headers` → `$responseHeaders`.
- Move test directory from `tests/` to `__tests__/`.

## [4.1.3] - 2023/2/28

### Added
- `ImageHelper`: extract first GIF frame, get GIF frame count.

### Changed
- Add unit tests for `ImageHelper`.

## [4.1.2] - 2023/2/10

### Added
- `ArrayHelper#filteringElements()` — create associative arrays or pluck specific keys from a list.

### Fixed
- `RestClient` referenced a deleted logger method.

## [4.1.1] - 2023/1/20

### Added
- `HttpInput` utility class for reading request data.

## [4.1.0] - 2023/1/20

### Changed
- Upgrade CodeIgniter dependency from 3.1.11 to 3.1.13.

## [4.0.25] - 2022/12/26

### Fixed
- Reset validation rules before SES send validation (`AmazonSesClient`).

## [4.0.24] - 2022/12/26

### Changed
- Add XSS/RFD mitigation headers to JSON responses: `X-Content-Type-Options: nosniff` and `Content-Disposition: attachment`.

## [4.0.23] - 2022/12/26

### Changed
- Internal redirect now sets the correct response content type.

## [4.0.22] - 2022/12/13

### Changed
- Add `$forceJsonResponse` option to controller error responses.

## [4.0.21] - 2022/12/9

### Changed
- Tighten email validation rules — reject leading/trailing dots and consecutive dots; accept quoted local parts and Unicode characters.

## [4.0.20] - 2022/9/26

### Fixed
- Fix PUT data loading warning in `\X\Library\Input`.

## [4.0.19] - 2022/9/25

### Changed
- Remove PID from logger output.
- Rename `Logger::print()` → `Logger::display()`, remove deprecated `printWithoutPath` / `printHidepath`.
- Change default `log_file_permissions` from `0644` to `0666`.

## [4.0.18] - 2022/9/24

### Changed
- README fixes.

## [4.0.17] - 2022/9/23

### Added
- `form_validation_test` action in sample controller.

## [4.0.16] - 2022/9/23

### Fixed
- Installer bug fix.

## [4.0.15] - 2022/9/23

### Changed
- Update skeleton `.gitignore`.

## [4.0.14] - 2022/9/23

### Changed
- `hostname` and `hostname_or_ipaddress` validations now accept `"localhost"`.

## [4.0.13] - 2022/6/6

### Changed
- `StringHelper#ellipsis()` now supports Unicode.

## [4.0.12] - 2021/11/10

### Fixed
- File deletion bug fix.

## [4.0.11] - 2021/11/10

### Changed
- `FileHelper::delete()` — add lock enable/disable option.

## [4.0.10] - 2021/10/20

### Changed
- Clear file stat cache before reading file size.

## [4.0.9] - 2021/9/27

### Changed
- Improve query logging behavior.

## [4.0.8] - 2021/9/22

### Added
- IP address or CIDR validation rule.

## [4.0.7] - 2021/9/16

### Changed
- Rename random character generation function to camelCase.

## [4.0.6] - 2021/9/16

### Added
- Random string generation function.

## [4.0.5] - 2021/8/10

### Changed
- File move/copy methods can now set group and owner on the target file.

## [4.0.4] - 2021/7/29

### Added
- Directory path validation rule.

## [4.0.3] - 2021/6/30

### Added
- Key pair generation and public key OpenSSH encoding.

## [4.0.2] - 2021/6/15

### Fixed
- `Model::exists_by_id()` bug fix.

## [4.0.1] - 2021/5/25

### Added
- Query result caching in models. See [CI3 caching docs](https://www.codeigniter.com/userguide3/database/caching.html).

## [4.0.0] - 2021/5/6

### Changed
- Rekognition client options are now passed as an array.

## [3.9.9] - 2021/4/15

### Changed
- README typo fixes.

## [3.9.8] - 2021/4/15

### Added
- Email validation per the [HTML5 spec](https://html.spec.whatwg.org/multipage/input.html#valid-e-mail-address).

## [3.9.7] - 2021/4/9

### Added
- Auto-inject `$_SESSION` into Twig templates as `session` variable.

## [3.9.6] - 2021/4/8

### Fixed
- PUT request body truncated at `&` characters.

## [3.9.5] - 2021/4/8

### Changed
- Skeleton refactor.

## [3.9.4] - 2021/4/7

### Fixed
- `create-project` error fix.

## [3.9.3] - 2021/3/26

### Added
- `DateHelper`: return dates of a specified month.

## [3.9.2] - 2021/3/24

### Fixed
- Email subclass return type mismatch.

## [3.9.1] - 2021/3/15

### Added
- `ArrayHelper::toTable()` — render arrays as ASCII tables.

## [3.9.0] - 2021/3/15

### Added
- Log output method without path information.

## [3.8.9] - 2021/2/24

### Added
- File lock and advisory lock batch sample in demo app.

## [3.8.8] - 2021/2/23

### Changed
- Organize README, add batch lock test program.

## [3.8.7] - 2021/2/19

### Added
- `FileHelper`: return human-readable file size with units.

## [3.8.6] - 2021/2/18

### Changed
- Changelog typo fixes.

## [3.8.5] - 2021/2/18

### Added
- `@Access(allow_http=false)` annotation for HTTP/CLI access control.

## [3.8.4] - 2021/2/17

### Changed
- `AmazonSesClient` now returns the SES send result object.

## [3.8.3] - 2021/2/11

### Added
- `Validation` utility class for model-level validation.

## [3.8.2] - 2021/2/10

### Changed
- README fixes.

## [3.8.1] - 2021/2/10

### Added
- `StringHelper`: trimmed empty-string check.

## [3.8.0] - 2021/2/10

### Added
- Nginx config sample in README.

## [3.7.9] - 2021/2/9

### Added
- Form validation rules: `datetime`, `hostname`, `ipaddress`, `hostname_or_ipaddress`, `unix_username`, `port`.

## [3.7.8] - 2021/2/6

### Added
- `ArrayHelper`: group associative arrays by key.

## [3.7.7] - 2021/2/3

### Added
- `FormValidation` class with `datetime` validation rule.

## [3.7.6] - 2021/1/27

### Changed
- Remove debug log.

## [3.7.5] - 2021/1/22

### Fixed
- Annotation reader failure.

## [3.7.4] - 2021/1/22

### Changed
- Image resize refactor (`ImageHelper`).

## [3.7.3] - 2020/12/25

### Added
- File search options (`FileHelper`).

## [3.7.2] - 2020/11/17

### Changed
- Remove unused `paginate()` method from Model.

## [3.7.1] - 2020/11/17

### Fixed
- Project creation command bug.

## [3.7.0] - 2020/11/17

### Changed
- Skeleton fixes.

## [3.6.9] - 2020/11/17

### Changed
- README fixes.

## [3.6.8] - 2020/11/17

### Changed
- Project creation process fixes.

## [3.6.7] - 2020/11/16

### Changed
- Prepend slash to PID in logger output.

## [3.6.6] - 2020/11/10

### Changed
- Add PID to log messages.

## [3.6.5] - 2020/11/9

### Fixed
- Ignore directory creation errors in `FileHelper::makeDirectory()`.

## [3.6.4] - 2020/11/6

### Changed
- Remove class/function names from logger output.

## [3.6.3] - 2020/11/2

### Changed
- `AmazonSesClient` — support multiple email destinations via array.

## [3.6.2] - 2020/10/29

### Changed
- Fix OpenSSL encryption/decryption method.

## [3.6.1] - 2020/10/23

### Added
- `IpUtils` class (replaces `HttpSecurity`).

## [3.6.0] - 2020/10/20

### Added
- Add timestamp to CLI log output.

## [3.5.9] - 2020/10/19

### Added
- `Logger#printWithoutPath()` — log without file path.

## [3.5.8] - 2020/10/16

### Fixed
- `HttpSecurity#getIpFromXFF()` fails when XFF is empty.

## [3.5.7] - 2020/10/15

### Added
- `HttpSecurity#getIpFromXFF()` — extract IP from X-Forwarded-For.

## [3.5.5] - 2020/6/4

### Added
- Get total file size of a directory.

## [3.5.4] - 2020/6/4

### Added
- Add encryption key parameter to hash conversion method.

## [3.5.3] - 2020/5/20

### Added
- Force logout for duplicate login sessions (same user on multiple devices).

## [3.5.0] - 2020/5/19

### Fixed
- DB class now correctly inherits `\X\Database\QueryBuilder` for session DB.

## [3.4.8] - 2020/4/28

### Fixed
- `HttpSecurity` IP range check with `/32` subnet mask.

## [3.4.7] - 2020/4/27

### Added
- Rekognition: find multiple faces from a collection.

## [3.4.6] - 2020/4/23

### Added
- Custom columns in session table via `$config['sess_table_additional_columns']`.

## [3.4.5] - 2020/4/10

### Changed
- `Loader::config()` returns empty string when key is not found.

## [3.4.2] - 2020/3/16

### Added
- Template cache configuration via `$config['cache_templates']`.

## [3.3.9] - 2020/3/16

### Added
- Rekognition client class (replaces old face detection class).

## [3.3.8] - 2020/3/14

### Added
- `insert_on_duplicate_update()` and `insert_on_duplicate_update_batch()`.

---

[3.3.8]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v1.0.0...v3.3.8
[3.3.9]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.3.8...v3.3.9
[3.4.2]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.3.9...v3.4.2
[3.4.5]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.4.2...v3.4.5
[3.4.6]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.4.5...v3.4.6
[3.4.7]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.4.6...v3.4.7
[3.4.8]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.4.7...v3.4.8
[3.5.0]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.4.8...v3.5.0
[3.5.3]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.5.0...v3.5.3
[3.5.4]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.5.3...v3.5.4
[3.5.5]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.5.4...v3.5.5
[3.5.7]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.5.5...v3.5.7
[3.5.8]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.5.7...v3.5.8
[3.5.9]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.5.8...v3.5.9
[3.6.0]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.5.9...v3.6.0
[3.6.1]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.6.0...v3.6.1
[3.6.2]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.6.1...v3.6.2
[3.6.3]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.6.2...v3.6.3
[3.6.4]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.6.3...v3.6.4
[3.6.5]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.6.4...v3.6.5
[3.6.6]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.6.5...v3.6.6
[3.6.7]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.6.6...v3.6.7
[3.6.8]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.6.7...v3.6.8
[3.6.9]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.6.8...v3.6.9
[3.7.0]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.6.9...v3.7.0
[3.7.1]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.7.0...v3.7.1
[3.7.2]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.7.1...v3.7.2
[3.7.3]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.7.2...v3.7.3
[3.7.4]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.7.3...v3.7.4
[3.7.5]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.7.4...v3.7.5
[3.7.6]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.7.5...v3.7.6
[3.7.7]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.7.6...v3.7.7
[3.7.8]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.7.7...v3.7.8
[3.7.9]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.7.8...v3.7.9
[3.8.0]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.7.9...v3.8.0
[3.8.1]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.8.0...v3.8.1
[3.8.2]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.8.1...v3.8.2
[3.8.3]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.8.2...v3.8.3
[3.8.4]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.8.3...v3.8.4
[3.8.5]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.8.4...v3.8.5
[3.8.6]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.8.5...v3.8.6
[3.8.7]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.8.6...v3.8.7
[3.8.8]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.8.7...v3.8.8
[3.8.9]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.8.8...v3.8.9
[3.9.0]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.8.9...v3.9.0
[3.9.1]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.9.0...v3.9.1
[3.9.2]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.9.1...v3.9.2
[3.9.3]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.9.2...v3.9.3
[3.9.4]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.9.3...v3.9.4
[3.9.5]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.9.4...v3.9.5
[3.9.6]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.9.5...v3.9.6
[3.9.7]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.9.6...v3.9.7
[3.9.8]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.9.7...v3.9.8
[3.9.9]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.9.8...v3.9.9
[4.0.0]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v3.9.9...v4.0.0
[4.0.1]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.0.0...v4.0.1
[4.0.2]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.0.1...v4.0.2
[4.0.3]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.0.2...v4.0.3
[4.0.4]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.0.3...v4.0.4
[4.0.5]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.0.4...v4.0.5
[4.0.6]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.0.5...v4.0.6
[4.0.7]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.0.6...v4.0.7
[4.0.8]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.0.7...v4.0.8
[4.0.9]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.0.8...v4.0.9
[4.0.10]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.0.9...v4.0.10
[4.0.11]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.0.10...v4.0.11
[4.0.12]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.0.11...v4.0.12
[4.0.13]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.0.12...v4.0.13
[4.0.14]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.0.13...v4.0.14
[4.0.15]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.0.14...v4.0.15
[4.0.16]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.0.15...v4.0.16
[4.0.17]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.0.16...v4.0.17
[4.0.18]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.0.17...v4.0.18
[4.0.19]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.0.18...v4.0.19
[4.0.20]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.0.19...v4.0.20
[4.0.21]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.0.20...v4.0.21
[4.0.22]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.0.21...v4.0.22
[4.0.23]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.0.22...v4.0.23
[4.0.24]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.0.23...v4.0.24
[4.0.25]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.0.24...v4.0.25
[4.1.0]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.0.25...v4.1.0
[4.1.1]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.1.0...v4.1.1
[4.1.2]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.1.1...v4.1.2
[4.1.3]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.1.2...v4.1.3
[4.1.4]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.1.3...v4.1.4
[4.1.5]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.1.4...v4.1.5
[4.1.6]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.1.5...v4.1.6
[4.1.7]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.1.6...v4.1.7
[4.1.8]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.1.7...v4.1.8
[4.1.9]: https://github.com/shumatsumonobu/codeigniter-extension/compare/v4.1.8...v4.1.9
