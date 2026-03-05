<p align="center">
  <img src="https://img.shields.io/packagist/v/takuya-motoshima/codeigniter-extension?style=flat-square&color=007bff" alt="Version">
  <img src="https://img.shields.io/packagist/dt/takuya-motoshima/codeigniter-extension?style=flat-square&color=28a745" alt="Downloads">
  <img src="https://img.shields.io/packagist/php-v/takuya-motoshima/codeigniter-extension?style=flat-square&color=6f42c1" alt="PHP">
  <img src="https://img.shields.io/packagist/l/takuya-motoshima/codeigniter-extension?style=flat-square&color=6c757d" alt="License">
</p>

<h1 align="center">CodeIgniter Extension</h1>

<p align="center">
  <strong>CodeIgniter 3 の開発体験を、もっと快適に。</strong><br>
  コントローラー・モデル・ユーティリティ・AWS 連携を一括拡張するパッケージ。
</p>

<p align="center">
  <a href="README.md">English</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="CHANGELOG.md">Changelog</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="CHANGELOG_ja.md">変更履歴</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="https://shumatsumonobu.github.io/codeigniter-extension/">API Docs</a>
</p>

---

## なぜ CodeIgniter Extension？

CodeIgniter 3 は軽量で高速 — でもモダンな開発に必要な機能が足りない。このパッケージがそのギャップを埋めます。

| | 何が手に入るか |
|---|---|
| **コントローラー** | JSON / HTML / Twig レスポンス、CORS、アノテーションベースのアクセス制御 |
| **モデル** | 流れるようなクエリビルダー、`INSERT ... ON DUPLICATE KEY UPDATE`、ヘルパーメソッド |
| **ユーティリティ** | 画像・動画処理、暗号化、CSV、REST クライアント、バリデーション、ロギング |
| **AWS** | Rekognition（顔検出・比較）、SES（メール配信） |
| **スキャフォールド** | コマンド一発で認証・ダッシュボード・フロントエンド付きアプリを生成 |

---

## クイックスタート

```sh
composer create-project takuya-motoshima/codeigniter-extension myapp
cd myapp
```

パーミッションと Web サーバーの設定：

```sh
sudo chmod -R 755 public/upload application/{logs,cache,session}
sudo chown -R nginx:nginx public/upload application/{logs,cache,session}
sudo cp nginx.sample.conf /etc/nginx/conf.d/myapp.conf
sudo systemctl restart nginx
```

データベースのインポートとアセットのビルド：

```sh
mysql -u root -p your_database < skeleton/init.sql
cd client && npm install && npm run build
```

`http://{your-server-ip}:3000/` を開く — デフォルト認証情報: `robin@example.com` / `password`

<p align="center">
  <img alt="サインイン" src="https://raw.githubusercontent.com/shumatsumonobu/codeigniter-extension/master/screencaps/sign-in.png" width="45%">
  <img alt="ユーザーリスト" src="https://raw.githubusercontent.com/shumatsumonobu/codeigniter-extension/master/screencaps/list-of-users.png" width="45%">
</p>

---

## 使い方

### コントローラー

```php
use \X\Annotation\Access;

class Users extends AppController {

  /** @Access(allow_login=true, allow_logoff=false, allow_role="admin") */
  public function index() {
    $users = $this->UserModel->get()->result_array();
    parent::set('users', $users)->view('users/index');
  }

  /** @Access(allow_http=true) */
  public function api() {
    parent::set(['message' => 'Success'])->json();
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

### ユーティリティ

```php
use \X\Util\{ImageHelper, FileHelper, Cipher, RestClient};

// 画像処理
ImageHelper::resize('/path/to/image.jpg', '/path/to/output.jpg', 800, 600);

// ファイル操作
FileHelper::makeDirectory('/path/to/dir', 0755);

// 暗号化
$encrypted = Cipher::encrypt('secret data', 'encryption-key');

// REST クライアント
$client = new RestClient(['base_url' => 'https://api.example.com']);
$response = $client->get('/users');
```

### Twig テンプレート

セッション変数は自動的に利用可能：

```twig
{% if session.user is defined %}
  <p>ようこそ、{{ session.user.name }}さん！</p>
  {% if session.user.role == 'admin' %}
    <a href="/admin">管理パネル</a>
  {% endif %}
{% endif %}
```

---

## API 早見表

<details>
<summary><strong>コントローラーメソッド</strong></summary>

| メソッド | 説明 |
|---------|------|
| `json()` | JSON レスポンスを送信 |
| `view($template)` | Twig テンプレートをレンダリング |
| `html($html)` | HTML レスポンスを送信 |
| `text($text)` | プレーンテキストレスポンスを送信 |
| `image($path)` | 画像レスポンスを送信 |
| `download($filename, $content)` | ファイルダウンロードを強制 |
| `set($key, $value)` | レスポンスデータを設定 |
| `setCorsHeader($origin)` | CORS ヘッダーを設定 |

</details>

<details>
<summary><strong>モデルメソッド</strong></summary>

| メソッド | 説明 |
|---------|------|
| `get_all()` | 全レコードを取得 |
| `get_by_id($id)` | ID でレコードを取得 |
| `count_by_id($id)` | ID でレコード数をカウント |
| `exists_by_id($id)` | レコードの存在確認 |
| `insert_on_duplicate_update()` | 単一レコードの UPSERT |
| `insert_on_duplicate_update_batch()` | バッチ UPSERT |

</details>

<details>
<summary><strong>ユーティリティクラス</strong></summary>

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

</details>

---

## 設定

<details>
<summary><strong>推奨 config.php 設定</strong></summary>

| 設定項目 | デフォルト | 推奨 |
|---------|-----------|------|
| `base_url` | *空* | 動的: `'//' . $_SERVER['HTTP_HOST'] . ...` |
| `enable_hooks` | `FALSE` | `TRUE` |
| `permitted_uri_chars` | `a-z 0-9~%.:_\-` | `a-z 0-9~%.:_\-,` |
| `sess_save_path` | `NULL` | `APPPATH . 'session'` |
| `cookie_httponly` | `FALSE` | `TRUE` |
| `composer_autoload` | `FALSE` | `realpath(APPPATH . '../vendor/autoload.php')` |
| `index_page` | `index.php` | *空* |

</details>

<details>
<summary><strong>アクセス制御 (hooks.php)</strong></summary>

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
```

</details>

<details>
<summary><strong>データベースセッションドライバー</strong></summary>

PHP 7.0+ 互換のセッションハンドラー。カスタムカラムもサポート：

```php
$config['sess_driver'] = 'database';
$config['sess_save_path'] = 'session';
$config['sess_table_additional_columns'] = ['email'];
```

`updateTimestamp()` を実装し、`Failed to write session data` 警告を防止。

</details>

---

## 要件

- **PHP** 8.0+
- **Composer**
- **拡張:** php-gd, php-mbstring, php-xml, php-imagick（オプション）

<details>
<summary><strong>ImageMagick のインストール</strong></summary>

`\X\Util\ImageHelper` の `extractFirstFrameOfGif()` に必要。

**Amazon Linux 2:**
```sh
sudo yum -y install ImageMagick php-imagick
```

**Amazon Linux 2023:**
```sh
sudo dnf -y install ImageMagick ImageMagick-devel php-pear.noarch
sudo pecl install imagick
echo "extension=imagick.so" | sudo tee -a /etc/php.ini
sudo systemctl restart nginx php-fpm
```

</details>

---

## アーキテクチャ

```
src/X/
├── Annotation/        アクセス制御アノテーション
├── Composer/          インストール後のスキャフォールド
├── Constant/          環境定数・HTTP ステータス定数
├── Controller/        レスポンスヘルパー付き基底コントローラー
├── Core/              拡張 Loader, Router, URI
├── Database/          クエリビルダー、ドライバー、結果セット
├── Exception/         カスタム例外
├── Hook/              認証フック
├── Library/           フォームバリデーション、入力、セッションドライバー
├── Model/             クエリヘルパー付き基底モデル
├── Rekognition/       AWS 顔検出・比較
└── Util/              21 のユーティリティクラス
```

---

## テスト

```sh
composer test
```

---

## ドキュメント

- [API リファレンス](https://shumatsumonobu.github.io/codeigniter-extension/)
- [デモアプリケーション](demo/)
- [変更履歴](CHANGELOG_ja.md)
- [CodeIgniter 3 ユーザーガイド](https://codeigniter.com/userguide3/)

---

## トラブルシューティング

| 問題 | 解決策 |
|------|--------|
| 「セッションデータの書き込みに失敗しました」警告 | `$config['sess_driver'] = 'database'` を使用 — 同梱の `SessionDatabaseDriver` が PHP 7.0+ 互換性を処理 |
| Imagick 拡張が見つからない | ImageMagick + php-imagick をインストール（要件を参照） |
| `@Access` アノテーションが無視される | フックを有効化: `$config['enable_hooks'] = TRUE` + `hooks.php` を設定 |
| Twig テンプレートが更新されない | キャッシュをクリア: `rm -rf application/cache/templates/*` |

---

## コントリビュート

プルリクエスト歓迎です！お気軽にご投稿ください。

## 著者

**Takuya Motoshima** — [GitHub](https://github.com/shumatsumonobu) / [Twitter](https://x.com/shumatsumonobu) / [Facebook](https://www.facebook.com/takuya.motoshima.7)

## ライセンス

[MIT](LICENSE)
