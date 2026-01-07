<?php
namespace X\Controller;
use \X\Util\HttpResponse;
use \X\Util\Loader;

/**
 * Base controller class extending CI_Controller.
 *
 * Provides enhanced response handling, auto-loading of models/libraries,
 * CORS support, and hook points for response processing.
 *
 * Usage:
 * ```php
 * class UserController extends \X\Controller\Controller {
 *   protected $model = ['UserModel', 'RoleModel'];
 *   protected $library = 'session';
 *
 *   public function index() {
 *     $this->set('users', $this->UserModel->findAll())->view('user/index');
 *   }
 * }
 * ```
 */
#[\AllowDynamicProperties]
abstract class Controller extends \CI_Controller {
  /**
   * Model(s) to auto-load on instantiation.
   *
   * @var string|string[] Single model name or array of model names.
   */
  protected $model;

  /**
   * Library(s) to auto-load on instantiation.
   *
   * @var string|string[] Single library name or array of library names.
   */
  protected $library;

  /**
   * HTTP response handler instance.
   *
   * @var HttpResponse
   */
  protected $httpResponse;

  /**
   * Initialize controller and auto-load models/libraries.
   */
  public function __construct() {
    parent::__construct();
    Loader::model($this->model);
    Loader::library($this->library);
    // $this->load->helper('url');
    $this->httpResponse = new HttpResponse();
  }

  /**
   * Set CORS (Cross-Origin Resource Sharing) headers.
   *
   * @example Allow all origins
   * ```php
   * $this->setCorsHeader('*');
   * ```
   *
   * @example Allow specific origins (space-separated)
   * ```php
   * $this->setCorsHeader('http://example.com https://example.com');
   * ```
   *
   * @example Set CORS for all responses via hook
   * ```php
   * protected function beforeResponse(string $referer) {
   *   $this->setCorsHeader('*');
   * }
   * ```
   *
   * @param string $origin Allowed origin(s). Use '*' for all or space-separated URLs.
   * @return Controller Method chaining.
   */
  protected function setCorsHeader(string $origin='*') {
    $this->httpResponse->setCorsHeader($origin);
    return $this;
  }

  /**
   * Set response data.
   *
   * @example Set single field
   * ```php
   * $this->set('username', 'John')->json();
   * // Output: {"username": "John"}
   * ```
   *
   * @example Set multiple fields
   * ```php
   * $this->set('id', 1)->set('name', 'John')->json();
   * // Output: {"id": 1, "name": "John"}
   * ```
   *
   * @example Set entire response data at once
   * ```php
   * $this->set(['id' => 1, 'name' => 'John'])->json();
   * // Output: {"id": 1, "name": "John"}
   * ```
   *
   * @param mixed $key Response data (1 arg) or field name (2 args).
   * @param mixed|null $value Field value when using 2 arguments.
   * @return Controller Method chaining.
   */
  protected function set($key, $value=null) {
    func_num_args() === 1
      ? $this->httpResponse->set($key)
      : $this->httpResponse->set($key, $value);
    return $this;
  }

  /**
   * Clear all response data.
   *
   * @return Controller Method chaining.
   */
  protected function clear() {
    $this->httpResponse->clear($key, $value);
    return $this;
  }

  /**
   * Set HTTP response status code.
   *
   * @param int $httpStatus HTTP status code (e.g., 200, 404, 500).
   * @return Controller Method chaining.
   */
  protected function status(int $httpStatus) {
    $this->httpResponse->status($httpStatus);
    return $this;
  }

  /**
   * Send JSON response.
   *
   * @example Basic JSON response
   * ```php
   * public function getUser() {
   *   $user = $this->UserModel->get_by_id(1);
   *   $this->set('user', $user)->json();
   * }
   * // Output: {"user": {"id": 1, "name": "John"}}
   * ```
   *
   * @example Force object output for empty arrays
   * ```php
   * $this->set('items', [])->json(true);
   * // Output: {"items": {}} instead of {"items": []}
   * ```
   *
   * @example Pretty-printed JSON for debugging
   * ```php
   * $this->set('data', $complexData)->json(false, true);
   * ```
   *
   * @param bool $forceObject Force object output for non-associative arrays.
   * @param bool $prettyrint Pretty-print JSON with whitespace.
   * @return void
   */
  protected function json(bool $forceObject=false, bool $prettyrint=false): void {
    $this->beforeResponse($this->getReferer());
    $this->beforeResponseJson($this->getReferer());
    $this->httpResponse->json($forceObject, $prettyrint);
  }

  /**
   * Send HTML response.
   *
   * @param string $html HTML content string.
   * @return void
   */
  protected function html(string $html): void {
    $this->beforeResponse($this->getReferer());
    $this->beforeResponseHtml($this->getReferer());
    $this->httpResponse->html($html);
  }

  /**
   * Render and send Twig template response.
   *
   * @example Render a template with data
   * ```php
   * public function index() {
   *   $users = $this->UserModel->get_all();
   *   $this->set('users', $users)->set('title', 'User List')->view('user/index');
   * }
   * ```
   *
   * @example Template file (views/user/index.twig)
   * ```twig
   * <h1>{{ title }}</h1>
   * {% for user in users %}
   *   <p>{{ user.name }}</p>
   * {% endfor %}
   * ```
   *
   * @param string $templatePath Path to Twig template file.
   * @return void
   */
  protected function view(string $templatePath): void {
    $this->beforeResponse($this->getReferer());
    $this->beforeResponseView($this->getReferer());
    $this->httpResponse->view($templatePath);
  }

  /**
   * Send JavaScript response.
   *
   * @param string $js JavaScript code.
   * @return void
   */
  protected function js(string $js): void {
    $this->beforeResponse($this->getReferer());
    $this->beforeResponseJs($this->getReferer());
    $this->httpResponse->js($js);
  }

  /**
   * Send plain text response.
   *
   * @param string $plainText Plain text content.
   * @return void
   */
  protected function text(string $plainText):void {
    $this->beforeResponse($this->getReferer());
    $this->beforeResponseText($this->getReferer());
    $this->httpResponse->text($plainText);
  }

  /**
   * Send file download response.
   *
   * @example Download generated content
   * ```php
   * public function exportCsv() {
   *   $csv = "id,name\n1,John\n2,Jane";
   *   $this->download('users.csv', $csv, 'text/csv');
   * }
   * ```
   *
   * @example Download existing file
   * ```php
   * public function downloadReport() {
   *   $this->download('report.pdf', file_get_contents('/path/to/report.pdf'));
   * }
   * ```
   *
   * @param string $filename Download filename for the browser.
   * @param string $content File content or path to file.
   * @param bool $mime MIME type. False for auto-detection.
   * @return void
   */
  protected function download(string $filename, string $content='', bool $mime=false): void {
    $this->beforeResponse($this->getReferer());
    $this->beforeDownload($this->getReferer());
    $this->httpResponse->download($filename, $content, $mime);
  }

  /**
   * Send image response.
   *
   * Outputs an image file with appropriate Content-Type header.
   *
   * @example Display user avatar
   * ```php
   * public function avatar($userId) {
   *   $user = $this->UserModel->get_by_id($userId);
   *   $this->image(FCPATH . 'uploads/avatars/' . $user['avatar']);
   * }
   * ```
   *
   * @example Display dynamically generated image
   * ```php
   * public function thumbnail($imageId) {
   *   $path = $this->ImageService->getThumbnailPath($imageId);
   *   $this->image($path);
   * }
   * ```
   *
   * @param string $imagePath Path to the image file.
   * @return void
   */
  protected function image(string $imagePath): void {
    $this->beforeResponse($this->getReferer());
    $this->beforeResponseImage($this->getReferer());
    $this->httpResponse->image($imagePath);
  }

  /**
   * Perform internal redirect (X-Accel-Redirect).
   *
   * Allows internal redirection to a location determined by a header returned from the backend.
   * This enables the backend to authenticate and perform processing, then serve content
   * from an internally redirected location while freeing the backend for other requests.
   *
   * @example Nginx configuration
   * ```nginx
   * # Will serve /var/www/files/myfile when passed URI /protected/myfile
   * location /protected {
   *   internal;
   *   alias /var/www/files;
   * }
   * ```
   *
   * @example PHP usage
   * ```php
   * class FileController extends \X\Controller\Controller {
   *   public function download() {
   *     // Authenticate user, then serve protected file
   *     $this->internalRedirect('/protected/secret-document.pdf');
   *   }
   * }
   * ```
   *
   * @param string $redirectPath Internal redirect path.
   * @return void
   */
  public function internalRedirect(string $redirectPath): void {
    $this->beforeResponse($this->getReferer());
    $this->beforeInternalRedirect($this->getReferer());
    $this->httpResponse->internalRedirect($redirectPath);
  }

  /**
   * Send error response.
   *
   * Outputs an error message with the specified HTTP status code.
   *
   * @example Return 404 error
   * ```php
   * public function show($id) {
   *   $user = $this->UserModel->get_by_id($id);
   *   if (!$user) {
   *     $this->error('User not found', 404);
   *     return;
   *   }
   *   $this->set('user', $user)->json();
   * }
   * ```
   *
   * @example Force JSON error response with additional data
   * ```php
   * public function create() {
   *   if (!$this->validate()) {
   *     $this->set('errors', $this->validation_errors())
   *          ->error('Validation failed', 400, true);
   *     return;
   *   }
   * }
   * ```
   *
   * @param string $message Error message to display.
   * @param int $httpStatus HTTP status code. Default is 500.
   * @param bool $forceJsonResponse Force JSON response format. Default is false.
   * @return void
   */
  protected function error(string $message, int $httpStatus=500, bool $forceJsonResponse=false): void {
    $this->httpResponse->error($message, $httpStatus, $forceJsonResponse);
  }

  /**
   * Get HTTP referrer URL.
   *
   * Returns the HTTP_REFERER if available, otherwise constructs the current URL.
   *
   * @return string Referrer URL.
   */
  private function getReferer(): string {
    if (!empty($_SERVER['HTTP_REFERER']))
      return $_SERVER['HTTP_REFERER'];
    $protocol = 'http';
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
      $protocol = 'https';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
  }

  // =========================================================================
  // Response Hook Methods
  // Override these methods to add custom processing before responses.
  // =========================================================================

  /**
   * Hook called before any response. Override to add global response processing.
   *
   * @param string $referer Referrer URL.
   * @return void
   */
  protected function beforeResponse(string $referer) {}

  /**
   * Hook called before JSON response.
   *
   * @param string $referer Referrer URL.
   * @return void
   */
  protected function beforeResponseJson(string $referer) {}

  /**
   * Hook called before template view response.
   *
   * @param string $referer Referrer URL.
   * @return void
   */
  protected function beforeResponseView(string $referer) {}

  /**
   * Hook called before HTML response.
   *
   * @param string $referer Referrer URL.
   * @return void
   */
  protected function beforeResponseHtml(string $referer) {}

  /**
   * Hook called before JavaScript response.
   *
   * @param string $referer Referrer URL.
   * @return void
   */
  protected function beforeResponseJs(string $referer) {}

  /**
   * Hook called before plain text response.
   *
   * @param string $referer Referrer URL.
   * @return void
   */
  protected function beforeResponseText(string $referer) {}

  /**
   * Hook called before file download response.
   *
   * @param string $referer Referrer URL.
   * @return void
   */
  protected function beforeDownload(string $referer) {}

  /**
   * Hook called before image response.
   *
   * @param string $referer Referrer URL.
   * @return void
   */
  protected function beforeResponseImage(string $referer) {}

  /**
   * Hook called before internal redirect.
   *
   * @param string $referer Referrer URL.
   * @return void
   */
  protected function beforeInternalRedirect(string $referer) {}
}