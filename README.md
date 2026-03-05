<p align="center">
  <img src="https://img.shields.io/packagist/v/takuya-motoshima/codeigniter-extension?style=flat-square&color=007bff" alt="Version">
  <img src="https://img.shields.io/packagist/dt/takuya-motoshima/codeigniter-extension?style=flat-square&color=28a745" alt="Downloads">
  <img src="https://img.shields.io/packagist/php-v/takuya-motoshima/codeigniter-extension?style=flat-square&color=6f42c1" alt="PHP">
  <img src="https://img.shields.io/packagist/l/takuya-motoshima/codeigniter-extension?style=flat-square&color=6c757d" alt="License">
</p>

<h1 align="center">CodeIgniter Extension</h1>

<p align="center">
  <strong>Supercharge your CodeIgniter 3 workflow.</strong><br>
  Extended controllers, models, utilities, and AWS integrations — all in one package.
</p>

<p align="center">
  <a href="README_ja.md">日本語</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="CHANGELOG.md">Changelog</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="CHANGELOG_ja.md">変更履歴</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="https://takuya-motoshima.github.io/codeigniter-extension/">API Docs</a>
</p>


## Why CodeIgniter Extension?

CodeIgniter 3 is fast and lightweight — but it lacks modern conveniences. This package fills the gaps without compromising simplicity.

| | What you get |
|---|---|
| **Controllers** | JSON / HTML / Twig responses, CORS, annotation-based access control |
| **Models** | Fluent query builder, `INSERT ... ON DUPLICATE KEY UPDATE`, helper methods |
| **Utilities** | Image/video processing, encryption, CSV, REST client, validation, logging |
| **AWS** | Rekognition (face detection & comparison), SES (email delivery) |
| **Scaffold** | One command to create a fully working app with auth, dashboard & frontend |


## Quick Start

```sh
composer create-project takuya-motoshima/codeigniter-extension myapp
cd myapp
```

Set up permissions and web server:

```sh
sudo chmod -R 755 public/upload application/{logs,cache,session}
sudo chown -R nginx:nginx public/upload application/{logs,cache,session}
sudo cp nginx.sample.conf /etc/nginx/conf.d/myapp.conf
sudo systemctl restart nginx
```

Import the database and build assets:

```sh
mysql -u root -p your_database < skeleton/init.sql
cd client && npm install && npm run build
```

Open `http://{your-server-ip}:3000/` — default credentials: `robin@example.com` / `password`

<p align="center">
  <img alt="Sign In" src="https://raw.githubusercontent.com/takuya-motoshima/codeigniter-extension/master/screencaps/sign-in.png" width="45%">
  <img alt="User List" src="https://raw.githubusercontent.com/takuya-motoshima/codeigniter-extension/master/screencaps/list-of-users.png" width="45%">
</p>


## Usage

### Controller

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

### Model

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

### Utilities

```php
use \X\Util\{ImageHelper, FileHelper, Cipher, RestClient};

// Image
ImageHelper::resize('/path/to/image.jpg', '/path/to/output.jpg', 800, 600);

// Files
FileHelper::makeDirectory('/path/to/dir', 0755);

// Encryption
$encrypted = Cipher::encrypt('secret data', 'encryption-key');

// REST
$client = new RestClient(['base_url' => 'https://api.example.com']);
$response = $client->get('/users');
```

### Twig Templates

Session variables are automatically available:

```twig
{% if session.user is defined %}
  <p>Welcome, {{ session.user.name }}!</p>
  {% if session.user.role == 'admin' %}
    <a href="/admin">Admin Panel</a>
  {% endif %}
{% endif %}
```


## API at a Glance

<details>
<summary><strong>Controller Methods</strong></summary>

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

</details>

<details>
<summary><strong>Model Methods</strong></summary>

| Method | Description |
|--------|-------------|
| `get_all()` | Get all records |
| `get_by_id($id)` | Get record by ID |
| `count_by_id($id)` | Count records by ID |
| `exists_by_id($id)` | Check if record exists |
| `insert_on_duplicate_update()` | Upsert single record |
| `insert_on_duplicate_update_batch()` | Batch upsert |

</details>

<details>
<summary><strong>Utility Classes</strong></summary>

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

</details>


## Configuration

<details>
<summary><strong>Recommended config.php settings</strong></summary>

| Setting | Default | Recommended |
|---------|---------|-------------|
| `base_url` | *empty* | Dynamic: `'//' . $_SERVER['HTTP_HOST'] . ...` |
| `enable_hooks` | `FALSE` | `TRUE` |
| `permitted_uri_chars` | `a-z 0-9~%.:_\-` | `a-z 0-9~%.:_\-,` |
| `sess_save_path` | `NULL` | `APPPATH . 'session'` |
| `cookie_httponly` | `FALSE` | `TRUE` |
| `composer_autoload` | `FALSE` | `realpath(APPPATH . '../vendor/autoload.php')` |
| `index_page` | `index.php` | *empty* |

</details>

<details>
<summary><strong>Access Control (hooks.php)</strong></summary>

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
<summary><strong>Database Session Driver</strong></summary>

PHP 7.0+ compatible session handler with custom column support:

```php
$config['sess_driver'] = 'database';
$config['sess_save_path'] = 'session';
$config['sess_table_additional_columns'] = ['email'];
```

Implements `updateTimestamp()` to prevent `Failed to write session data` warnings.

</details>


## Requirements

- **PHP** 8.0+
- **Composer**
- **Extensions:** php-gd, php-mbstring, php-xml, php-imagick (optional)

<details>
<summary><strong>ImageMagick Installation</strong></summary>

Required for `extractFirstFrameOfGif()` in `\X\Util\ImageHelper`.

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


## Architecture

```
src/X/
├── Annotation/        Access control annotations
├── Composer/          Post-install scaffolding
├── Constant/          Environment & HTTP status constants
├── Controller/        Base controller with response helpers
├── Core/              Extended Loader, Router, URI
├── Database/          Query builder, drivers, result set
├── Exception/         Custom exceptions
├── Hook/              Authentication hook
├── Library/           Form validation, input, session driver
├── Model/             Base model with query helpers
├── Rekognition/       AWS face detection & comparison
└── Util/              21 utility classes
```


## Testing

```sh
composer test
```


## Documentation

- [API Reference](https://takuya-motoshima.github.io/codeigniter-extension/)
- [Demo Application](demo/)
- [Changelog](CHANGELOG.md)
- [CodeIgniter 3 User Guide](https://codeigniter.com/userguide3/)


## Troubleshooting

| Problem | Solution |
|---------|----------|
| "Failed to write session data" warning | Use `$config['sess_driver'] = 'database'` — the included `SessionDatabaseDriver` handles PHP 7.0+ compatibility |
| Imagick extension not found | Install ImageMagick + php-imagick (see Requirements) |
| `@Access` annotations ignored | Enable hooks: `$config['enable_hooks'] = TRUE` and configure `hooks.php` |
| Twig templates not updating | Clear cache: `rm -rf application/cache/templates/*` |


## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Author

**Takuya Motoshima** — [GitHub](https://github.com/takuya-motoshima) / [Twitter](https://x.com/takuya_motech) / [Facebook](https://www.facebook.com/takuya.motoshima.7)

## License

[MIT](LICENSE)
