# 変更履歴

このプロジェクトの主な変更はこのファイルに記録されます。

> 過去のバージョンは [変更履歴アーカイブ (v3.3.8 — v4.1.9)](CHANGELOG_ja_archive.md) を参照してください。

## [5.0.5] - 2026/3/5

### 変更
- `src/X/` 配下の全クラスの PHPDoc を改善 — `@param` の配列型記法を標準化、`@throws` の追記、説明文の精緻化。

## [5.0.4] - 2026/1/7

### 追加
- デモアプリにインタラクティブなフォーム検証テストページ (`/validation-test`) を追加。リアルタイムバリデーションと視覚的フィードバックを実装。
- `SessionDatabaseDriver#updateTimestamp()` — PHP 7.0+ SessionHandlerInterface に準拠し、「セッションデータの書き込みに失敗しました」警告を防止。

### 変更
- `src/X/` 配下の全クラスの PHPDoc を改善 — クラス説明、使用例、アノテーションを標準化。
- README.md / README_ja.md を刷新 — バッジ、アーキテクチャ概要、API リファレンス、トラブルシューティングを追加。

## [5.0.3] - 2025/11/25

### 追加
- フォームバリデーションテストスクリプト (`sandbox/form-validation-test.php`) — チェックボックス配列バリデーション対応。

### 修正
- skeleton で誤って削除された `UserLogModel.php` を復元。

## [5.0.2] - 2025/11/8

### 変更
- LICENSE の著作権年を 2025 に更新。
- デモとスケルトンの構成を改善（コアパッケージへの変更なし）。

## [5.0.1] - 2024/5/14

### 変更
- インストーラーを修正 — インストール後に `prototypes/`、`__tests__/`、`phpunit-printer.yml`、`phpunit.xml` を削除。
- skeleton に `client/package-lock.json` を追加。

## [5.0.0] - 2024/5/13

### 変更
- **PHP 8.0 以上が必須に。** アップグレード時はアプリケーションでコアクラスを継承:

  | ファイル | クラス |
  |---------|--------|
  | `AppController.php` | `extends \X\Controller\Controller` |
  | `AppInput.php` | `extends \X\Library\Input` |
  | `AppLoader.php` | `extends \X\Core\Loader` |
  | `AppModel.php` | `extends \X\Model\Model` |
  | `AppRouter.php` | `extends \X\Core\Router` |
  | `AppURI.php` | `extends \X\Core\URI` |

## [4.2.0] - 2024/5/13

### 変更
- `Rekognition\Client#generateCollectionId()` から `$baseDir` 引数を削除。
- `EMail` クラスの非推奨 snake_case メソッドを削除 — `messageFromTemplate`、`messageFromXml`、`setMailType`、`attachmentCid` を使用。
- メソッド名を統一的にリネーム:

  | 変更前 | 変更後 |
  |--------|--------|
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
