# CodeIgniter Extension

[![PHP Version](https://img.shields.io/packagist/php-v/takuya-motoshima/codeigniter-extension)](https://packagist.org/packages/takuya-motoshima/codeigniter-extension)
[![License](https://img.shields.io/packagist/l/takuya-motoshima/codeigniter-extension)](LICENSE)
[![Packagist Downloads](https://img.shields.io/packagist/dt/takuya-motoshima/codeigniter-extension)](https://packagist.org/packages/takuya-motoshima/codeigniter-extension)
[![Latest Version](https://img.shields.io/packagist/v/takuya-motoshima/codeigniter-extension)](https://packagist.org/packages/takuya-motoshima/codeigniter-extension)

[English](README.md) | [Changelog](CHANGELOG.md) | [変更履歴](CHANGELOG_ja.md)

CodeIgniter 3の拡張パッケージで、拡張されたコアクラス(コントローラー、モデル、ビュー)とユーティリティクラスを提供します。

## 目次

- [機能](#機能)
- [要件](#要件)
- [インストール](#インストール)
- [クイックスタート](#クイックスタート)
- [設定](#設定)
- [アーキテクチャ](#アーキテクチャ)
- [使用例](#使用例)
- [APIリファレンス](#apiリファレンス)
- [トラブルシューティング](#トラブルシューティング)
- [テスト](#テスト)
- [ドキュメント](#ドキュメント)
- [ライセンス](#ライセンス)

## 機能

### コア拡張
- **拡張コントローラー** - JSONレスポンス、テンプレートレンダリング、アクセス制御
- **拡張モデル** - クエリキャッシュ、バッチ操作、ヘルパーメソッド
- **拡張ルーター** - アノテーションベースのアクセス制御

### ユーティリティクラス
- **画像処理** - リサイズ、クロップ、フォーマット変換、GIFフレーム抽出、PDFから画像へ変換
- **動画処理** - 動画ファイルの操作と変換
- **ファイル操作** - ロック機能付き高度なファイルとディレクトリ操作
- **CSV処理** - インポート/エクスポートユーティリティ
- **メール** - テンプレートベースのメール、Amazon SES統合
- **RESTクライアント** - API統合用HTTPクライアント
- **セキュリティ** - 暗号化/復号化、IP検証
- **バリデーション** - カスタムルール(ホスト名、IP、CIDR、日時、パス)
- **セッション管理** - カスタムカラム付きデータベースバックセッション、PHP 7.0+ SessionHandlerInterface準拠
- **ロギング** - コンテキスト付き拡張ロギング
- **テンプレートエンジン** - セッション変数統合Twig
- **Google Authenticator MFA** - バックアップコードとリカバリーフロー付きTOTPベース二要素認証

### AWS統合
- **Amazon Rekognition** - 顔検出、比較、分析
- **Amazon SES** - 信頼性の高いメール配信サービス

## 要件

- **PHP** 7.3.0以上
- **Composer**
- **PHP拡張:**
  - php-gd
  - php-mbstring
  - php-xml
  - php-imagick (オプション、GIF操作用)

### オプション: ImageMagickインストール

`\X\Util\ImageHelper`の`extractFirstFrameOfGif`メソッドに必要です。

**Amazon Linux 2:**
```sh
sudo yum -y install ImageMagick php-imagick
```

**Amazon Linux 2023:**
```sh
# ImageMagickとPECLをインストール
sudo dnf -y install ImageMagick ImageMagick-devel php-pear.noarch

# imagick拡張をインストール
sudo pecl install imagick
echo "extension=imagick.so" | sudo tee -a /etc/php.ini

# サービスを再起動
sudo systemctl restart nginx php-fpm
```

## インストール

Composerを使用して新しいプロジェクトを作成:

```sh
composer create-project takuya-motoshima/codeigniter-extension myapp
cd myapp
```

## クイックスタート

### 1. パーミッション設定

```sh
sudo chmod -R 755 public/upload application/{logs,cache,session}
sudo chown -R nginx:nginx public/upload application/{logs,cache,session}
```

### 2. Webサーバー設定

Nginx設定をコピー:

```sh
sudo cp nginx.sample.conf /etc/nginx/conf.d/myapp.conf
sudo systemctl restart nginx
```

### 3. データベースセットアップ

データベーススキーマをインポート:

```sh
mysql -u root -p your_database < skeleton/init.sql
```

### 4. フロントエンドアセットのビルド

```sh
cd client
npm install
npm run build
```

### 5. アプリケーションへアクセス

ブラウザで`http://{your-server-ip}:3000/`を開きます。

**デフォルト認証情報:**
- メール: `robin@example.com`
- パスワード: `password`

### スクリーンショット

<p align="left">
  <img alt="サインイン" src="https://raw.githubusercontent.com/takuya-motoshima/codeigniter-extension/master/screencaps/sign-in.png" width="45%">
  <img alt="ユーザーリスト" src="https://raw.githubusercontent.com/takuya-motoshima/codeigniter-extension/master/screencaps/list-of-users.png" width="45%">
</p>

#### MFAスクリーンショット

<p align="left">
  <img alt="ログインページ" src="https://raw.githubusercontent.com/takuya-motoshima/codeigniter-extension/master/screencaps/01-login-page.png" width="45%">
  <img alt="MFA設定（無効）" src="https://raw.githubusercontent.com/takuya-motoshima/codeigniter-extension/master/screencaps/02-mfa-settings-disabled.png" width="45%">
</p>
<p align="left">
  <img alt="MFA設定（有効）" src="https://raw.githubusercontent.com/takuya-motoshima/codeigniter-extension/master/screencaps/03-mfa-settings-enabled.png" width="45%">
  <img alt="MFAセットアップ画面" src="https://raw.githubusercontent.com/takuya-motoshima/codeigniter-extension/master/screencaps/04-mfa-settings-setup-screen.png" width="45%">
</p>
<p align="left">
  <img alt="MFAバックアップコード" src="https://raw.githubusercontent.com/takuya-motoshima/codeigniter-extension/master/screencaps/05-mfa-settings-setup-recover-code.png" width="45%">
  <img alt="MFA検証" src="https://raw.githubusercontent.com/takuya-motoshima/codeigniter-extension/master/screencaps/07-mfa-verify.png" width="45%">
</p>
<p align="left">
  <img alt="MFAリカバリー" src="https://raw.githubusercontent.com/takuya-motoshima/codeigniter-extension/master/screencaps/08-mfa-recovery.png" width="45%">
</p>

## 設定

### 基本設定 (`application/config/config.php`)

<table>
  <thead>
    <tr>
      <th>設定項目</th>
      <th>デフォルト</th>
      <th>推奨</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>base_url</td>
      <td><em>空</em></td>
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
      <td><em>空</em></td>
    </tr>
  </tbody>
</table>

### アクセス制御のセットアップ

#### 1. デフォルトルートの定義

`application/config/routes.php`:

```php
$route['default_controller'] = 'users/login';
```

#### 2. セッション定数の設定

`application/config/constants.php`:

```php
const SESSION_NAME = 'session';
```

#### 3. フックの設定

`application/config/hooks.php`:

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

### セッション管理

このパッケージは、CodeIgniterのデータベースセッションドライバーをPHP 7.0+ SessionHandlerInterface準拠に拡張しています:

**機能:**
- **カスタムセッションカラム** - 追加データ(例: email, user_id)をセッションテーブルに直接保存
- **updateTimestamp実装** - PHP 7.0+ SessionHandlerInterface要件に準拠
- **警告ログなし** - PHP 7.0+での「セッションデータの書き込みに失敗しました」警告を防止

**設定** (`application/config/config.php`):

```php
$config['sess_driver'] = 'database';
$config['sess_save_path'] = 'session';
$config['sess_table_additional_columns'] = ['email'];
```

**技術詳細:**

`SessionDatabaseDriver`クラスは、PHP 7.0+のSessionHandlerInterfaceで必要な`updateTimestamp()`メソッドを実装しています。これにより、PHPがデフォルトのファイルハンドラーにフォールバックすることを防ぎ、以下のような警告を回避します:

```
Warning: session_write_close(): Failed to write session data using user defined save handler.
```

詳細については、[PHP SessionHandlerInterfaceドキュメント](https://www.php.net/manual/ja/class.sessionhandlerinterface.php)を参照してください。

## アーキテクチャ

### ディレクトリ構造

```
src/X/
├── Annotation/          # アクセス制御アノテーション
│   ├── Access.php           # @Accessアノテーション定義
│   └── AnnotationReader.php # アノテーションパーサー
├── Composer/            # Composerインストーラー
│   └── Installer.php        # create-project後処理
├── Constant/            # 定数
│   ├── Environment.php      # 環境定数 (DEVELOPMENT, TESTING, PRODUCTION)
│   └── HttpStatus.php       # HTTPステータスコード定数
├── Controller/          # コントローラー拡張
│   └── Controller.php       # レスポンスヘルパー付き基底コントローラー
├── Core/                # CodeIgniterコア拡張
│   ├── Loader.php           # 拡張ローダー
│   ├── Router.php           # 拡張ルーター
│   └── URI.php              # 拡張URI
├── Database/            # データベース拡張
│   ├── DB.php               # DBファクトリー
│   ├── Driver.php           # 基底ドライバー
│   ├── QueryBuilder.php     # 拡張クエリビルダー
│   └── Result.php           # 拡張結果セット
├── Exception/           # カスタム例外
│   ├── AccessDeniedException.php
│   └── RestClientException.php
├── Hook/                # フック
│   └── Authenticate.php     # 認証フック
├── Library/             # ライブラリ拡張
│   ├── FormValidation.php   # 拡張フォームバリデーション
│   ├── Input.php            # 拡張入力ライブラリ
│   ├── Router.php           # ルーターライブラリ
│   └── SessionDatabaseDriver.php # DBセッションドライバー (PHP 7.0+)
├── Model/               # モデル拡張
│   ├── Model.php            # クエリビルダー付き基底モデル
│   ├── AddressModel.php     # 住所モデル
│   ├── SessionModel.php     # セッションモデル
│   └── SessionModelInterface.php
├── Rekognition/         # AWS Rekognition
│   └── Client.php           # 顔検出/比較クライアント
└── Util/                # ユーティリティクラス (22クラス)
    ├── AmazonSesClient.php  # Amazon SESメール
    ├── ArrayHelper.php      # 配列操作
    ├── Cipher.php           # 暗号化 (AES-256-CTR)
    ├── CsvHelper.php        # CSVインポート/エクスポート
    ├── DateHelper.php       # 日付操作
    ├── EMail.php            # テンプレート付きメール
    ├── FileHelper.php       # ファイル/ディレクトリ操作
    ├── GoogleAuthenticator.php # バックアップコード付きTOTP MFA
    ├── HtmlHelper.php       # HTMLユーティリティ
    ├── HttpInput.php        # HTTP入力処理
    ├── HttpResponse.php     # HTTPレスポンスビルダー
    ├── ImageHelper.php      # 画像処理
    ├── IpUtils.php          # IPアドレスユーティリティ
    ├── Iterator.php         # 組み合わせ演算
    ├── Loader.php           # リソースローダー
    ├── Logger.php           # ロギング
    ├── RestClient.php       # REST APIクライアント
    ├── SessionHelper.php    # セッションユーティリティ
    ├── StringHelper.php     # 文字列操作
    ├── Template.php         # Twig統合
    ├── UrlHelper.php        # URLユーティリティ
    ├── Validation.php       # データバリデーション
    └── VideoHelper.php      # 動画処理
```

### アプリケーション構造 (skeleton/)

このパッケージで作成されるプロジェクトは以下の構造に従います:

```
application/
├── core/
│   ├── AppController.php    # \X\Controller\Controller を継承
│   └── AppModel.php         # \X\Model\Model を継承
├── config/
│   ├── hooks.php            # AnnotationReaderによるアクセス制御
│   └── constants.php        # SESSION_NAME, ENV_DIR 定数
├── controllers/             # アプリケーションコントローラー
├── models/                  # アプリケーションモデル
└── views/                   # Twigテンプレート
```

## 使用例

### コントローラー

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

### モデル

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

### Twigテンプレート

セッション変数は自動的に利用可能です:

```php
// PHP
$_SESSION['user'] = ['name' => 'John Smith', 'role' => 'admin'];
```

```twig
{# テンプレート #}
{% if session.user is defined %}
  <p>ようこそ、{{ session.user.name }}さん！</p>
  {% if session.user.role == 'admin' %}
    <a href="/admin">管理パネル</a>
  {% endif %}
{% endif %}
```

### ユーティリティの使用

```php
// 画像処理
use \X\Util\ImageHelper;
ImageHelper::resize('/path/to/image.jpg', '/path/to/output.jpg', 800, 600);

// ファイル操作
use \X\Util\FileHelper;
FileHelper::makeDirectory('/path/to/dir', 0755);

// 暗号化
use \X\Util\Cipher;
$encrypted = Cipher::encrypt('secret data', 'encryption-key');

// RESTクライアント
use \X\Util\RestClient;
$client = new RestClient(['base_url' => 'https://api.example.com']);
$response = $client->get('/users');
```

### Google Authenticator MFA

TOTPコード、バックアップコード、アカウントリカバリー機能付きの完全な二要素認証実装。

#### ユーザーのMFAセットアップ

```php
use \X\Util\GoogleAuthenticator;

// 完全なMFAセットアップバンドルを生成
$setup = GoogleAuthenticator::createMfaSetup('user@example.com', 'MyApp');

// データベースに保存
$user['mfa_secret'] = $setup['secret'];
$user['mfa_enabled'] = true;
$user['backup_codes'] = GoogleAuthenticator::serializeBackupHashes($setup['backup_hashes']);

// ユーザーに表示（一度だけ！）
echo "このQRコードをスキャン: " . $setup['qr_code_url'];
echo "または手動で入力: " . $setup['secret'];
echo "バックアップコード: " . implode(', ', $setup['backup_codes']);
```

#### ログイン時のTOTPコード検証

```php
use \X\Util\GoogleAuthenticator;

// MFAが必要かチェック
if (GoogleAuthenticator::isMfaEnforced($user['mfa_secret'], $user['mfa_enabled'])) {
    $code = $_POST['mfa_code'];
    $backupHashes = GoogleAuthenticator::deserializeBackupHashes($user['backup_codes']);

    // TOTPまたはバックアップコードを検証
    $result = GoogleAuthenticator::verifyTotpOrBackup($user['mfa_secret'], $code, $backupHashes);

    if ($result['valid']) {
        if ($result['type'] === 'backup') {
            // 使用済みバックアップコードを削除
            $backupHashes = GoogleAuthenticator::removeUsedBackupCode($backupHashes, $result['backup_index']);
            $user['backup_codes'] = GoogleAuthenticator::serializeBackupHashes($backupHashes);
            // データベースを更新
        }
        // アクセスを許可
        $_SESSION['mfa_verified'] = true;
    } else {
        // 無効なコード
        echo "無効な認証コード";
    }
}
```

#### アカウントリカバリー

```php
use \X\Util\GoogleAuthenticator;

// リカバリートークンを生成（1時間で期限切れ）
$recovery = GoogleAuthenticator::generateRecoveryToken(3600);

// データベースにハッシュを保存
$user['recovery_hash'] = $recovery['hash'];
$user['recovery_expires'] = $recovery['expires_at'];

// ユーザーにトークンをメールで送信
sendEmail($user['email'], "リカバリートークン: " . $recovery['token']);

// 後でトークンを検証
if (GoogleAuthenticator::verifyRecoveryToken($submittedToken, $user['recovery_hash'], $user['recovery_expires'])) {
    // MFAを無効化してアクセスを回復
}
```

## APIリファレンス

### コントローラーメソッド

| メソッド | 説明 |
|---------|------|
| `json()` | JSONレスポンスを送信 |
| `view($template)` | Twigテンプレートをレンダリング |
| `html($html)` | HTMLレスポンスを送信 |
| `text($text)` | プレーンテキストレスポンスを送信 |
| `image($path)` | 画像レスポンスを送信 |
| `download($filename, $content)` | ファイルダウンロードを強制 |
| `set($key, $value)` | レスポンスデータを設定 |
| `setCorsHeader($origin)` | CORSヘッダーを設定 |

### モデルメソッド

| メソッド | 説明 |
|---------|------|
| `get_all()` | 全レコードを取得 |
| `get_by_id($id)` | IDでレコードを取得 |
| `count_by_id($id)` | IDでレコード数をカウント |
| `exists_by_id($id)` | レコードの存在確認 |
| `insert_on_duplicate_update()` | INSERT ... ON DUPLICATE KEY UPDATE |
| `insert_on_duplicate_update_batch()` | バッチアップサート |

### ユーティリティクラス

| クラス | 主要メソッド |
|--------|-------------|
| `ImageHelper` | `resize()`, `crop()`, `writeDataURLToFile()`, `pdf2Image()` |
| `FileHelper` | `makeDirectory()`, `delete()`, `copyFile()`, `move()` |
| `Cipher` | `encrypt()`, `decrypt()`, `encode_sha256()` |
| `RestClient` | `get()`, `post()`, `put()`, `delete()` |
| `Logger` | `debug()`, `info()`, `error()`, `display()` |
| `Validation` | `hostname()`, `ipaddress()`, `email()`, `is_path()` |
| `IpUtils` | `isIPv4()`, `isIPv6()`, `inRange()` |
| `Template` | `load($template, $params)` |
| `GoogleAuthenticator` | `createMfaSetup()`, `verifyCode()`, `verifyTotpOrBackup()`, `generateBackupCodes()` |

## トラブルシューティング

### よくある問題

#### 「セッションデータの書き込みに失敗しました」警告

**問題:** PHP 7.0+でセッション書き込み警告が表示される。

**解決策:** このパッケージにはPHP 7.0+互換の`updateTimestamp()`を実装した`SessionDatabaseDriver`が含まれています。以下を使用していることを確認してください:

```php
$config['sess_driver'] = 'database';
```

#### Imagick拡張が見つからない

**問題:** `extractFirstFrameOfGif()`がエラーをスローする。

**解決策:** ImageMagickとphp-imagickをインストール:

```sh
# Amazon Linux 2023
sudo dnf -y install ImageMagick ImageMagick-devel php-pear.noarch
sudo pecl install imagick
echo "extension=imagick.so" | sudo tee -a /etc/php.ini
sudo systemctl restart php-fpm
```

#### Accessアノテーションが機能しない

**問題:** `@Access`アノテーションが無視される。

**解決策:**
1. `config.php`でフックを有効化: `$config['enable_hooks'] = TRUE;`
2. `hooks.php`で`AnnotationReader::getAccessibility()`を設定

#### テンプレートキャッシュの問題

**問題:** Twigテンプレートが更新されない。

**解決策:** キャッシュディレクトリをクリア:

```sh
rm -rf application/cache/templates/*
```

## テスト

ユニットテストの実行:

```sh
composer test
```

テストファイルの場所:
- `__tests__/*.php` - テストケース
- `phpunit.xml` - 設定
- `phpunit-printer.yml` - 出力フォーマット

## ドキュメント

- **[APIドキュメント](https://takuya-motoshima.github.io/codeigniter-extension/)** - 完全なAPIリファレンス
- **[デモアプリケーション](demo/)** - 完全な動作例
- **[変更履歴](CHANGELOG_ja.md)** - バージョン履歴と変更内容
- **[CodeIgniter 3ガイド](https://codeigniter.com/userguide3/)** - 公式フレームワークドキュメント

### PHPDocの生成

```sh
# phpDocumentorをダウンロード(初回のみ)
wget https://phpdoc.org/phpDocumentor.phar
chmod +x phpDocumentor.phar

# ドキュメント生成
php phpDocumentor.phar run -d src/ --ignore vendor --ignore src/X/Database/Driver/ -t docs/
```

## 貢献

プルリクエストを歓迎します！お気軽にご投稿ください。

## 著者

**Takuya Motoshima**
- GitHub: [@takuya-motoshima](https://github.com/takuya-motoshima)
- Twitter: [@TakuyaMotoshima](https://x.com/takuya_motech)
- Facebook: [takuya.motoshima.7](https://www.facebook.com/takuya.motoshima.7)

## ライセンス

[MIT License](LICENSE)
