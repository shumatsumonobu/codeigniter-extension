<?php
namespace X\Util;
// use \X\Constant\HttpStatus;
use \X\Util\Loader;

/**
 * HTTP response builder utility class.
 *
 * Provides fluent interface for building HTTP responses including JSON, HTML,
 * JavaScript, plain text, file downloads, images, and error responses.
 * Supports CORS headers and internal redirects for Nginx X-Accel-Redirect.
 */
final class HttpResponse {
  /**
   * Response data.
   * @var array
   */
  private $data = [];

  /**
   * HTTP Status.
   * @var int
   */
  private $httpStatus;

  /**
   * CI_Controller instance.
   * @var CI_Controller
   */
  private $CI;

  /**
   * Initialize HttpResponse.
   */
  public function __construct() {
    $this->CI =& \get_instance();
  }

  /**
   * Set response data.
   *
   * With one argument, replaces all response data.
   * With two arguments, sets a single field.
   *
   * @param mixed $key Response data (1 arg) or field name (2 args).
   * @param mixed|null $value Field value when using 2 arguments.
   * @return HttpResponse Method chaining.
   */
  public function set($key, $value=null): HttpResponse {
    if (func_num_args() === 2) {
      if (!is_array($this->data))
        $this->data = [];
      $this->data[$key] = $value;
    } else if (func_num_args() === 1)
      $this->data = $key;
    return $this;
  }

  /**
   * Clear all response data.
   *
   * @return HttpResponse Method chaining.
   */
  public function clear(): HttpResponse {
    $this->data = [];
    return $this;
  }

  /**
   * Set the HTTP response status code.
   *
   * @param int $httpStatus HTTP status code (e.g., 200, 404, 500).
   * @return HttpResponse Method chaining.
   */
  public function status(int $httpStatus): HttpResponse {
    $this->httpStatus = $httpStatus;
    return $this;
  }

  /**
   * Send JSON response.
   *
   * Outputs response data as JSON with Content-Type application/json.
   *
   * @param bool $forceObject Force object output for non-associative arrays.
   * @param bool $prettyrint Pretty-print JSON with indentation.
   * @return void
   * @throws \LogicException If JSON encoding fails.
   */
 public function json(bool $forceObject=false, bool $prettyrint=false): void {
    $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
    if ($forceObject)
      $options = $options | JSON_FORCE_OBJECT;
    if ($prettyrint)
      $options = $options | JSON_PRETTY_PRINT;
    $json = json_encode($this->data, $options);
    if ($json === false)
      throw new \LogicException(sprintf('Failed to parse json string \'%s\', error: \'%s\'', $this->data, json_last_error_msg()));
    ob_clean();
    // $this->setCorsHeader('*');
    $this->CI->load->helper('url');
    $attachmentFileName = basename(current_url()) . '.json';
    $this->CI->output
      ->set_status_header($this->httpStatus ?? 200)
      ->set_content_type('application/json', 'UTF-8')
  		// Prevent mime based attacks.
      ->set_header('X-Content-Type-Options: nosniff')
      // Prevent the browser from rendering the file by explicitly marking it as a download file.
      ->set_header("Content-Disposition: attachment; filename=\"{$attachmentFileName}\"")
      ->set_output($json);
  }

  /**
   * Send HTML response.
   *
   * @param string $html HTML content string.
   * @return void
   */
  public function html(string $html): void {
    // $this->setCorsHeader('*');
    $this->CI->output
      ->set_content_type('text/html', 'UTF-8')
      ->set_output($html);
  }

  /**
   * Render a Twig template and send as HTML response.
   *
   * @param string $templatePath Template path relative to views directory.
   * @return void
   */
  public function view(string $templatePath): void {
    static $template;
    $template = $template ?? new \X\Util\Template();
    self::html($template->load($templatePath, $this->data));
  }

  /**
   * Send JavaScript response.
   *
   * @param string $js JavaScript code.
   * @return void
   */
  public function js(string $js): void {
    ob_clean();
    // $this->setCorsHeader('*');
    $this->CI->output
      ->set_content_type('application/javascript', 'UTF-8')
      ->set_output($js);
  }

  /**
   * Send plain text response.
   *
   * @param string $plainText Plain text content.
   * @return void
   */
  public function text(string $plainText): void {
    ob_clean();
    // $this->setCorsHeader('*');
    $this->CI->output
      ->set_content_type('text/plain', 'UTF-8')
      ->set_output($plainText);
  }

  /**
   * Send file download response.
   *
   * @param string $filename Download filename for the browser.
   * @param string $content File content to download.
   * @param bool $mime MIME type. False for auto-detection.
   * @return void
   */
  public function download(string $filename, string $content='', bool $mime=false): void {
    ob_clean();
    $this->CI->load->helper('download');
    force_download($filename, $content, $mime);
  }

  /**
   * Send image response with appropriate Content-Type.
   *
   * @param string $imagePath Path to the image file.
   * @return void
   */
  public function image(string $imagePath): void {
    ob_clean();
    $this->CI->load->helper('file');
    $this->CI->output
      ->set_content_type(get_mime_by_extension($imagePath))
      ->set_output(file_get_contents($imagePath));
  }

  /**
   * Send error response.
   *
   * For AJAX requests or when forced, responds with JSON.
   * Otherwise, uses CodeIgniter's show_error() for HTML error pages.
   *
   * @param string $message Error message.
   * @param int $httpStatus HTTP status code. Default is 500.
   * @param bool $forceJsonResponse Force JSON response format. Default is false.
   * @return void
   * @throws \LogicException If JSON encoding fails.
   */
  public function error(string $message, int $httpStatus=500, bool $forceJsonResponse=false): void {
    if ($forceJsonResponse || $this->CI->input->is_ajax_request()) {
      ob_clean();
      // $this->setCorsHeader('*');
      $json = json_encode($this->data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
      if ($json === false)
        throw new \LogicException(sprintf('Failed to parse json string \'%s\', error: \'%s\'', $this->data, json_last_error_msg()));
      $this->CI->output
        ->set_header('Cache-Control: no-cache, must-revalidate')
        ->set_status_header($httpStatus, rawurlencode($message))
        ->set_content_type('application/json', 'UTF-8')
        ->set_output($json);
    } else {
      show_error($message, $httpStatus);
    }
  }

  /**
   * Internal redirect.
   * Allows for internal redirection to a location determined by a header returned from a backend.
   * This allows the backend to authenticate and perform any other processing,
   * provide content to the end user from the internally redirected location,
   * and free up the backend to handle other requests.
   *
   * Nginx:
   * ```nginx
   * # Will serve /var/www/files/myfile
   * # When passed URI /protected/myfile
   * location /protected {
   *   internal;
   *   alias /var/www/files;
   * }
   * ``
   * 
   * PHP:
   * ```php
   * class Sample extends \X\Controller\Controller {
   *   public function index() {
   *     parent::internalRedirect('/protected/myfile');
   *   }
   * }
   * ```
   * @param string $redirectPath Path to internal redirect.
   * @return void
   */
  public function internalRedirect(string $redirectPath): void {
    // $this->setCorsHeader('*');
    $extension = pathinfo($redirectPath, PATHINFO_EXTENSION);
    if (!empty($extension))
      $this->CI->output->set_content_type(strtolower($extension));
    else 
      $this->CI->output->set_header('Content-Type: true');
    $this->CI->output
      ->set_header('X-Accel-Redirect: ' . $redirectPath)
      ->set_status_header(200);
  }

  /**
   * Set CORS (Cross-Origin Resource Sharing) headers.
   *
   * ```php
   * // Allow all.
   * parent::setCorsHeader('*');
   *
   * // Only any origin is allowed.
   * parent::setCorsHeader('http://www.example.jp');
   * parent::setCorsHeader('http://www.example.jp https://www.example.jp http://sub.example.jp');
   *
   * // To set the same Access-Control-Allow-Origin for all responses, use the hook point called before the response.
   * abstract class AppController extends \X\Controller\Controller {
   *   protected function beforeResponse(string $referer) {
   *     $this->setCorsHeader('*');
   *   }
   * }
   * ```
   * @param string $origin Allowable Origins.
   * @return void
   */
  public function setCorsHeader(string $origin): void {
    if ($origin === '*') {
      if (!empty($_SERVER['HTTP_ORIGIN']))
        $origin = $_SERVER['HTTP_ORIGIN'];
      else if (!empty($_SERVER['HTTP_REFERER']))
        $origin = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_SCHEME) . '://' . parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
    }
    $this->CI->output
      ->set_header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization')
      ->set_header('Access-Control-Allow-Methods: GET, POST, OPTIONS')
      ->set_header('Access-Control-Allow-Credentials: true')
      ->set_header('Access-Control-Allow-Origin: ' . $origin);
  }
}