# 変更履歴アーカイブ (v3.3.8 — v4.1.9)

> 最新の変更は [CHANGELOG_ja.md](CHANGELOG_ja.md) を参照してください。

---

## [4.1.9] - 2023/9/15

### 変更
- パス検証に先頭スラッシュ拒否オプションを追加。デフォルトは許可 (`\X\Util\Validation#is_path`)。

## [4.1.8] - 2023/9/15

### 変更
- パス検証関数名を `directory_path` → `is_path` に変更。

## [4.1.7] - 2023/8/29

### 変更
- `FileHelper::makeDirectory()` が成功時に `true`、失敗時に `false` を返すように修正。エラーログレベルを `error` → `info` に変更。

## [4.1.6] - 2023/8/9

### 変更
- `Rekognition\Client#compareFaces()` — 顔が見つからない場合に例外ではなく類似度 `0` を返すように変更。
- `FileHelper#delete()` — 自ディレクトリ削除前に `clearstatcache` を実行。

## [4.1.5] - 2023/5/25

### 追加
- `ImageHelper::pdf2Image()` で PDF → 画像変換に対応。

## [4.1.4] - 2023/5/11

### 変更
- `Rekognition\Client` のユニットテストを追加。
- `RestClient` のメンバー変数名をリネーム: `$option` → `$options`、`$response_source` → `$responseRaw`、`$headers` → `$responseHeaders`。
- テストディレクトリを `tests/` → `__tests__/` に移動。

## [4.1.3] - 2023/2/28

### 追加
- `ImageHelper` — GIF の先頭フレーム抽出、フレーム数取得。

### 変更
- `ImageHelper` のユニットテストを追加。

## [4.1.2] - 2023/2/10

### 追加
- `ArrayHelper#filteringElements()` — 連想配列リストから特定キーの要素を抽出。

### 修正
- `RestClient` が削除済みのロガーメソッドを参照していたバグを修正。

## [4.1.1] - 2023/1/20

### 追加
- `HttpInput` ユーティリティクラス — リクエストデータの読み取り。

## [4.1.0] - 2023/1/20

### 変更
- CodeIgniter 依存バージョンを 3.1.11 → 3.1.13 にアップグレード。

## [4.0.25] - 2022/12/26

### 修正
- SES 送信バリデーション前にルールをリセットするように修正 (`AmazonSesClient`)。

## [4.0.24] - 2022/12/26

### 変更
- JSON レスポンスに XSS/RFD 対策ヘッダーを追加: `X-Content-Type-Options: nosniff`、`Content-Disposition: attachment`。

## [4.0.23] - 2022/12/26

### 変更
- 内部リダイレクトで適切なコンテンツタイプを設定するように修正。

## [4.0.22] - 2022/12/13

### 変更
- コントローラーのエラーレスポンスに `$forceJsonResponse` オプションを追加。

## [4.0.21] - 2022/12/9

### 変更
- メールアドレス検証を強化 — 先頭末尾のドット・連続ドットを拒否、引用符付きローカルパートと Unicode を許可。

## [4.0.20] - 2022/9/26

### 修正
- `\X\Library\Input` の PUT データ読み込み警告を修正。

## [4.0.19] - 2022/9/25

### 変更
- ロガー出力から PID を削除。
- `Logger::print()` → `Logger::display()` にリネーム。非推奨の `printWithoutPath` / `printHidepath` を削除。
- デフォルト `log_file_permissions` を `0644` → `0666` に変更。

## [4.0.18] - 2022/9/24

### 変更
- README 修正。

## [4.0.17] - 2022/9/23

### 追加
- サンプルコントローラーに `form_validation_test` アクションを追加。

## [4.0.16] - 2022/9/23

### 修正
- インストーラーのバグ修正。

## [4.0.15] - 2022/9/23

### 変更
- スケルトンの `.gitignore` を更新。

## [4.0.14] - 2022/9/23

### 変更
- `hostname`・`hostname_or_ipaddress` 検証で `"localhost"` を許可。

## [4.0.13] - 2022/6/6

### 変更
- `StringHelper#ellipsis()` を Unicode 対応に修正。

## [4.0.12] - 2021/11/10

### 修正
- ファイル削除のバグ修正。

## [4.0.11] - 2021/11/10

### 変更
- `FileHelper::delete()` にロック有効/無効オプションを追加。

## [4.0.10] - 2021/10/20

### 変更
- ファイルサイズ取得前にファイルステータスキャッシュをクリア。

## [4.0.9] - 2021/9/27

### 変更
- クエリロギング動作を改善。

## [4.0.8] - 2021/9/22

### 追加
- IP アドレスまたは CIDR の検証ルール。

## [4.0.7] - 2021/9/16

### 変更
- ランダム文字生成関数名をキャメルケースに変更。

## [4.0.6] - 2021/9/16

### 追加
- ランダム文字列生成関数。

## [4.0.5] - 2021/8/10

### 変更
- ファイル移動/コピーメソッドでグループ・所有者を設定可能に。

## [4.0.4] - 2021/7/29

### 追加
- ディレクトリパス検証ルール。

## [4.0.3] - 2021/6/30

### 追加
- キーペア生成・公開鍵 OpenSSH エンコード処理。

## [4.0.2] - 2021/6/15

### 修正
- `Model::exists_by_id()` のバグ修正。

## [4.0.1] - 2021/5/25

### 追加
- モデルでの検索クエリ結果キャッシュ機能。詳細は [CI3 キャッシュドキュメント](https://www.codeigniter.com/userguide3/database/caching.html) を参照。

## [4.0.0] - 2021/5/6

### 変更
- Rekognition クライアントのオプションを配列で渡すように変更。

## [3.9.9] - 2021/4/15

### 変更
- README のタイポ修正。

## [3.9.8] - 2021/4/15

### 追加
- [HTML5 仕様](https://html.spec.whatwg.org/multipage/input.html#valid-e-mail-address)に準拠したメールアドレス検証。

## [3.9.7] - 2021/4/9

### 追加
- `$_SESSION` を Twig テンプレートの `session` 変数に自動注入。

## [3.9.6] - 2021/4/8

### 修正
- PUT リクエストのボディが `&` で切り詰められる問題を修正。

## [3.9.5] - 2021/4/8

### 変更
- スケルトンのリファクタリング。

## [3.9.4] - 2021/4/7

### 修正
- `create-project` エラーの修正。

## [3.9.3] - 2021/3/26

### 追加
- `DateHelper` — 指定月の日付を返す関数。

## [3.9.2] - 2021/3/24

### 修正
- メールサブクラスの戻り値型の不一致を修正。

## [3.9.1] - 2021/3/15

### 追加
- `ArrayHelper::toTable()` — 配列を ASCII テーブルとしてレンダリング。

## [3.9.0] - 2021/3/15

### 追加
- パス情報なしのログ出力メソッド。

## [3.8.9] - 2021/2/24

### 追加
- デモアプリにファイルロック・アドバイザリロックのバッチサンプルを追加。

## [3.8.8] - 2021/2/23

### 変更
- README 整理、バッチロックテストプログラム追加。

## [3.8.7] - 2021/2/19

### 追加
- `FileHelper` — 単位付きファイルサイズを返すメソッド。

## [3.8.6] - 2021/2/18

### 変更
- Changelog のタイポ修正。

## [3.8.5] - 2021/2/18

### 追加
- `@Access(allow_http=false)` アノテーションで HTTP/CLI アクセス制御に対応。

## [3.8.4] - 2021/2/17

### 変更
- `AmazonSesClient` が SES 送信結果オブジェクトを返すように変更。

## [3.8.3] - 2021/2/11

### 追加
- `Validation` ユーティリティクラス — モデルレベルのバリデーション。

## [3.8.2] - 2021/2/10

### 変更
- README 修正。

## [3.8.1] - 2021/2/10

### 追加
- `StringHelper` — トリム後の空文字判定メソッド。

## [3.8.0] - 2021/2/10

### 追加
- README に Nginx 設定サンプルを追加。

## [3.7.9] - 2021/2/9

### 追加
- フォームバリデーションルール: `datetime`、`hostname`、`ipaddress`、`hostname_or_ipaddress`、`unix_username`、`port`。

## [3.7.8] - 2021/2/6

### 追加
- `ArrayHelper` — 連想配列のキーによるグルーピング。

## [3.7.7] - 2021/2/3

### 追加
- `FormValidation` クラスと `datetime` バリデーションルール。

## [3.7.6] - 2021/1/27

### 変更
- デバッグログの削除。

## [3.7.5] - 2021/1/22

### 修正
- アノテーション読み取り失敗のバグ修正。

## [3.7.4] - 2021/1/22

### 変更
- `ImageHelper` の画像リサイズ機能をリファクタリング。

## [3.7.3] - 2020/12/25

### 追加
- `FileHelper` にファイル検索オプションを追加。

## [3.7.2] - 2020/11/17

### 変更
- Model クラスから未使用の `paginate()` メソッドを削除。

## [3.7.1] - 2020/11/17

### 修正
- プロジェクト作成コマンドのバグ修正。

## [3.7.0] - 2020/11/17

### 変更
- スケルトン修正。

## [3.6.9] - 2020/11/17

### 変更
- README 修正。

## [3.6.8] - 2020/11/17

### 変更
- プロジェクト作成処理の修正。

## [3.6.7] - 2020/11/16

### 変更
- ロガー出力の PID にスラッシュを付与。

## [3.6.6] - 2020/11/10

### 変更
- ログメッセージに PID を追加。

## [3.6.5] - 2020/11/9

### 修正
- `FileHelper::makeDirectory()` のディレクトリ作成エラーを無視するように修正。

## [3.6.4] - 2020/11/6

### 変更
- ロガー出力からクラス名・関数名を削除。

## [3.6.3] - 2020/11/2

### 変更
- `AmazonSesClient` — 配列で複数のメール宛先を指定可能に。

## [3.6.2] - 2020/10/29

### 変更
- OpenSSL 暗号化/復号化メソッドを修正。

## [3.6.1] - 2020/10/23

### 追加
- `IpUtils` クラス（`HttpSecurity` を置換）。

## [3.6.0] - 2020/10/20

### 追加
- CLI ログ出力にタイムスタンプを追加。

## [3.5.9] - 2020/10/19

### 追加
- `Logger#printWithoutPath()` — パス情報なしのログ出力。

## [3.5.8] - 2020/10/16

### 修正
- XFF が空の場合に `HttpSecurity#getIpFromXFF()` が失敗するバグを修正。

## [3.5.7] - 2020/10/15

### 追加
- `HttpSecurity#getIpFromXFF()` — X-Forwarded-For から IP を取得。

## [3.5.5] - 2020/6/4

### 追加
- ディレクトリ内の全ファイルサイズを返すメソッド。

## [3.5.4] - 2020/6/4

### 追加
- ハッシュ変換メソッドに暗号化キーパラメータを追加。

## [3.5.3] - 2020/5/20

### 追加
- 同一ユーザーの重複ログインセッションを強制ログアウトする機能。

## [3.5.0] - 2020/5/19

### 修正
- セッション DB で DB クラスが `\X\Database\QueryBuilder` を正しく継承しないバグを修正。

## [3.4.8] - 2020/4/28

### 修正
- `HttpSecurity` の `/32` サブネットマスクでの IP 範囲チェックを修正。

## [3.4.7] - 2020/4/27

### 追加
- Rekognition — コレクションから複数の顔を検索する機能。

## [3.4.6] - 2020/4/23

### 追加
- `$config['sess_table_additional_columns']` でセッションテーブルにカスタムカラムを追加。

## [3.4.5] - 2020/4/10

### 変更
- `Loader::config()` — キーが見つからない場合は空文字列を返すように変更。

## [3.4.2] - 2020/3/16

### 追加
- `$config['cache_templates']` でテンプレートキャッシュを設定可能に。

## [3.3.9] - 2020/3/16

### 追加
- Rekognition クライアントクラス（旧顔検出クラスを置換）。

## [3.3.8] - 2020/3/14

### 追加
- `insert_on_duplicate_update()` と `insert_on_duplicate_update_batch()`。

---

[3.3.8]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v1.0.0...v3.3.8
[3.3.9]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.3.8...v3.3.9
[3.4.2]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.3.9...v3.4.2
[3.4.5]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.4.2...v3.4.5
[3.4.6]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.4.5...v3.4.6
[3.4.7]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.4.6...v3.4.7
[3.4.8]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.4.7...v3.4.8
[3.5.0]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.4.8...v3.5.0
[3.5.3]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.5.0...v3.5.3
[3.5.4]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.5.3...v3.5.4
[3.5.5]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.5.4...v3.5.5
[3.5.7]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.5.5...v3.5.7
[3.5.8]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.5.7...v3.5.8
[3.5.9]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.5.8...v3.5.9
[3.6.0]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.5.9...v3.6.0
[3.6.1]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.6.0...v3.6.1
[3.6.2]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.6.1...v3.6.2
[3.6.3]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.6.2...v3.6.3
[3.6.4]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.6.3...v3.6.4
[3.6.5]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.6.4...v3.6.5
[3.6.6]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.6.5...v3.6.6
[3.6.7]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.6.6...v3.6.7
[3.6.8]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.6.7...v3.6.8
[3.6.9]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.6.8...v3.6.9
[3.7.0]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.6.9...v3.7.0
[3.7.1]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.7.0...v3.7.1
[3.7.2]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.7.1...v3.7.2
[3.7.3]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.7.2...v3.7.3
[3.7.4]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.7.3...v3.7.4
[3.7.5]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.7.4...v3.7.5
[3.7.6]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.7.5...v3.7.6
[3.7.7]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.7.6...v3.7.7
[3.7.8]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.7.7...v3.7.8
[3.7.9]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.7.8...v3.7.9
[3.8.0]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.7.9...v3.8.0
[3.8.1]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.8.0...v3.8.1
[3.8.2]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.8.1...v3.8.2
[3.8.3]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.8.2...v3.8.3
[3.8.4]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.8.3...v3.8.4
[3.8.5]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.8.4...v3.8.5
[3.8.6]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.8.5...v3.8.6
[3.8.7]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.8.6...v3.8.7
[3.8.8]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.8.7...v3.8.8
[3.8.9]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.8.8...v3.8.9
[3.9.0]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.8.9...v3.9.0
[3.9.1]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.9.0...v3.9.1
[3.9.2]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.9.1...v3.9.2
[3.9.3]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.9.2...v3.9.3
[3.9.4]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.9.3...v3.9.4
[3.9.5]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.9.4...v3.9.5
[3.9.6]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.9.5...v3.9.6
[3.9.7]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.9.6...v3.9.7
[3.9.8]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.9.7...v3.9.8
[3.9.9]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.9.8...v3.9.9
[4.0.0]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v3.9.9...v4.0.0
[4.0.1]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.0.0...v4.0.1
[4.0.2]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.0.1...v4.0.2
[4.0.3]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.0.2...v4.0.3
[4.0.4]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.0.3...v4.0.4
[4.0.5]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.0.4...v4.0.5
[4.0.6]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.0.5...v4.0.6
[4.0.7]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.0.6...v4.0.7
[4.0.8]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.0.7...v4.0.8
[4.0.9]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.0.8...v4.0.9
[4.0.10]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.0.9...v4.0.10
[4.0.11]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.0.10...v4.0.11
[4.0.12]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.0.11...v4.0.12
[4.0.13]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.0.12...v4.0.13
[4.0.14]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.0.13...v4.0.14
[4.0.15]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.0.14...v4.0.15
[4.0.16]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.0.15...v4.0.16
[4.0.17]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.0.16...v4.0.17
[4.0.18]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.0.17...v4.0.18
[4.0.19]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.0.18...v4.0.19
[4.0.20]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.0.19...v4.0.20
[4.0.21]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.0.20...v4.0.21
[4.0.22]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.0.21...v4.0.22
[4.0.23]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.0.22...v4.0.23
[4.0.24]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.0.23...v4.0.24
[4.0.25]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.0.24...v4.0.25
[4.1.0]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.0.25...v4.1.0
[4.1.1]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.1.0...v4.1.1
[4.1.2]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.1.1...v4.1.2
[4.1.3]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.1.2...v4.1.3
[4.1.4]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.1.3...v4.1.4
[4.1.5]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.1.4...v4.1.5
[4.1.6]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.1.5...v4.1.6
[4.1.7]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.1.6...v4.1.7
[4.1.8]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.1.7...v4.1.8
[4.1.9]: https://github.com/takuya-motoshima/codeigniter-extension/compare/v4.1.8...v4.1.9
