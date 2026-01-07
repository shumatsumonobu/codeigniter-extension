# CLAUDE.md

このファイルは、Claude Code (claude.ai/code) がこのリポジトリで作業する際のガイダンスを提供します。

## プロジェクト概要

CodeIgniter Extension は、CodeIgniter 3 を拡張した PHP ライブラリです。コントローラー、モデル、ユーティリティクラス、AWS 連携機能を強化し、迅速な開発のためのスケルトンアプリケーション構造を提供します。

## コマンド

### テスト
```sh
composer test                    # 全 PHPUnit テストを実行
./vendor/bin/phpunit             # 代替テストコマンド
./vendor/bin/phpunit __tests__/ValidationTest.php  # 単一テストファイルを実行
```

### 構文チェック
```sh
composer syntax-check            # src/X/ の PHP 構文チェック
```

### ドキュメント生成
```sh
php phpDocumentor.phar run -d src/ --ignore vendor --ignore src/X/Database/Driver/ -t docs/
```

### フロントエンドビルド (skeleton アプリ)
```sh
cd client && npm install && npm run build
```

## アーキテクチャ

### ソースコード構造 (`src/X/`)

ライブラリは `\X\` 名前空間で提供されます:

```
src/X/
├── Annotation/          # アクセス制御アノテーション
│   ├── Access.php           # @Access アノテーション定義
│   └── AnnotationReader.php # アノテーション読み取り
├── Composer/            # Composer インストーラー
│   └── Installer.php        # create-project 後処理
├── Constant/            # 定数定義
│   ├── Environment.php      # 環境定数 (DEVELOPMENT, TESTING, PRODUCTION)
│   └── HttpStatus.php       # HTTP ステータスコード定数
├── Controller/          # コントローラー拡張
│   └── Controller.php       # 基底コントローラー
├── Core/                # CodeIgniter コア拡張
│   ├── Loader.php           # 拡張ローダー
│   ├── Router.php           # 拡張ルーター
│   └── URI.php              # 拡張 URI
├── Database/            # データベース拡張
│   ├── DB.php               # DB ファクトリ
│   ├── Driver.php           # 基底ドライバー
│   ├── QueryBuilder.php     # クエリビルダー拡張
│   ├── Result.php           # 結果セット拡張
│   └── Driver/              # 各 DB ドライバー (Mysqli, Pdo, Postgre 等)
├── Exception/           # カスタム例外
│   ├── AccessDeniedException.php
│   └── RestClientException.php
├── Hook/                # フック
│   └── Authenticate.php     # 認証フック
├── Library/             # ライブラリ拡張
│   ├── FormValidation.php       # フォームバリデーション拡張
│   ├── Input.php                # 入力ライブラリ拡張
│   ├── Router.php               # ルーターライブラリ
│   └── SessionDatabaseDriver.php # DB セッションドライバー (PHP 7.0+ 対応)
├── Model/               # モデル拡張
│   ├── Model.php            # 基底モデル
│   ├── AddressModel.php     # 住所モデル
│   ├── SessionModel.php     # セッションモデル
│   └── SessionModelInterface.php
├── Rekognition/         # AWS Rekognition
│   └── Client.php           # 顔検出・比較・分析クライアント
└── Util/                # ユーティリティクラス
    ├── AmazonSesClient.php  # Amazon SES メール送信
    ├── ArrayHelper.php      # 配列操作
    ├── Cipher.php           # 暗号化/復号化 (AES-256-CTR, SHA-256)
    ├── CsvHelper.php        # CSV インポート/エクスポート
    ├── DateHelper.php       # 日付操作
    ├── EMail.php            # メール送信 (テンプレート対応)
    ├── FileHelper.php       # ファイル/ディレクトリ操作
    ├── HtmlHelper.php       # HTML ヘルパー
    ├── HttpInput.php        # HTTP 入力処理
    ├── HttpResponse.php     # HTTP レスポンス生成
    ├── ImageHelper.php      # 画像処理 (リサイズ, 切り抜き, 変換, GIF/PDF)
    ├── IpUtils.php          # IP アドレス検証
    ├── Iterator.php         # イテレーター
    ├── Loader.php           # ユーティリティローダー
    ├── Logger.php           # ログ出力 (debug, info, error)
    ├── RestClient.php       # REST API クライアント (GET/POST/PUT/DELETE)
    ├── SessionHelper.php    # セッション操作
    ├── StringHelper.php     # 文字列操作
    ├── Template.php         # Twig テンプレートエンジン
    ├── UrlHelper.php        # URL ヘルパー
    ├── Validation.php       # バリデーション (hostname, IP, CIDR, email, path)
    └── VideoHelper.php      # 動画処理
```

### 主要クラス詳細

#### Controller (`\X\Controller\Controller`)
- `$model`, `$library` プロパティでモデル/ライブラリを自動読み込み
- レスポンスメソッド: `json()`, `view()`, `html()`, `text()`, `image()`, `download()`, `internalRedirect()`
- `setCorsHeader()` で CORS 設定
- `set($key, $value)` でレスポンスデータ設定
- フックメソッド: `beforeResponse()`, `beforeResponseJson()`, `beforeResponseView()` 等

#### Model (`\X\Model\Model`)
- `const TABLE = 'tablename'` でテーブル名を自動バインド
- クエリビルダーメソッドチェーン (`select()`, `where()`, `join()` 等) が `Model` を返す
- 拡張メソッド: `insert_on_duplicate_update()`, `insert_on_duplicate_update_batch()`
- ヘルパー: `get_all()`, `get_by_id()`, `count_by_id()`, `exists_by_id()`
- 静的メソッド: `Model::db()` で DB インスタンス取得

#### Annotation (`\X\Annotation\Access`)
```php
@Access(allow_login=true, allow_logoff=false, allow_role="admin,user", allow_http=true)
```
- `allow_login`: ログインユーザーのアクセス許可
- `allow_logoff`: 未ログインユーザーのアクセス許可
- `allow_role`: 許可するロール (カンマ区切り)
- `allow_http`: HTTP アクセス許可 (CLI 専用の場合は false)

#### Util クラス
- **ImageHelper**: `writeDataURLToFile()`, `resize()`, `crop()`, `convert()`, `extractFirstFrameOfGif()`, `putText()`
- **FileHelper**: `makeDirectory()`, `move()`, `copyFile()`, `copyDirectory()`, `delete()`
- **Cipher**: `encode_sha256()`, `encrypt()`, `decrypt()`, `generateInitialVector()`
- **RestClient**: `get()`, `post()`, `put()`, `delete()` - SSL/Basic 認証対応
- **Logger**: `debug()`, `info()`, `error()`, `display()` - ファイル/スタックトレース情報付き
- **Validation**: `hostname()`, `ipaddress()`, `ipaddress_or_cidr()`, `email()`, `is_path()`, `port()`
- **Template**: Twig ベース、`cache_busting()` 関数、`session` グローバル変数自動設定

#### Rekognition (`\X\Rekognition\Client`)
- `addCollection()`, `getCollection()`, `deleteCollection()`
- `addFace()`, `getFaces()`, `deleteFace()`
- `compareFaces()`, `detectFaces()`

### アプリケーション構造 (skeleton/)

このパッケージで作成されるプロジェクトは以下の構造に従います:

```
application/
  core/
    AppController.php    # \X\Controller\Controller を継承
    AppModel.php         # \X\Model\Model を継承
  config/
    hooks.php            # AnnotationReader によるアクセス制御
    constants.php        # SESSION_NAME, ENV_DIR 定数
  controllers/           # アプリケーションコントローラー
  models/                # アプリケーションモデル
  views/                 # Twig テンプレート
```

### アクセス制御フロー

1. コントローラーメソッドに `@Access` アノテーションを定義
2. `hooks.php` が `post_controller_constructor` フックを登録
3. フックが `AnnotationReader::getAccessibility()` を呼び出してアノテーションを読み取り
4. ログイン状態とロール権限に基づいてリダイレクト

### 主要パターン

- コントローラー継承: `AppController` → `\X\Controller\Controller` → `CI_Controller`
- モデル継承: `AppModel` → `\X\Model\Model` → `CI_Model`
- モデルは `const TABLE = 'tablename'` を定義してクエリビルダーを自動バインド
- セッションデータは `$_SESSION[SESSION_NAME]` でアクセス
- 環境変数は vlucas/phpdotenv により `ENV_DIR` から読み込み

## 依存関係

- PHP 8.0+
- CodeIgniter 3.1.x
- Twig 2.x (テンプレートエンジン)
- Doctrine Common (アノテーション)
- AWS SDK (Rekognition/SES)
- Intervention Image (画像処理)
