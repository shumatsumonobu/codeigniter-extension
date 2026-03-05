<?php
namespace X\Util;

/**
 * Email sending utility class.
 *
 * Provides email sending functionality using CodeIgniter's email library
 * with support for HTML/text emails, attachments, and CC/BCC recipients.
 */
abstract class EMail {
  /**
   * Email default settings.
   * @var array{wordwrap: bool, mailtype: 'text'|'html', charset: string, priority: int, crlf: string, newline: string}
   */
  private static $defaultConfig = [
    // 'useragent' => 'CodeIgniter',
    // 'protocol' => 'mail',
    // 'mailpath' => '/usr/sbin/sendmail',
    // 'smtp_host' => null,
    // 'smtp_user' => null,
    // 'smtp_pass' => null,
    // 'smtp_port' => 25,
    // 'smtp_timeout' => 5,
    // 'smtp_keepalive' => false,
    // 'smtp_crypto' => null,
    'wordwrap' => false,
    // 'wrapchars' => 76,
    'mailtype' => 'text',
    'charset' => 'utf-8',
    // 'validate' => false,
    'priority' => 1,
    'crlf' => "\r\n",
    'newline' => "\r\n",
    // 'bcc_batch_mode' => false,
    // 'bcc_batch_size' => 200,
    // 'dsn' => false,
  ];

  /**
   * Initialize the email library with custom settings.
   *
   * @param array $config Email configuration (merges with defaults).
   * @return string This class name for static method chaining.
   */
  public static function initialize(array $config=array()): string {
    self::email()->initialize(array_merge(self::$defaultConfig, $config));
    return __CLASS__;
  }

  /**
   * Send the email.
   *
   * @param bool $autoClear Clear recipients and message data after sending. Default is true.
   * @return bool True on success.
   */
  public static function send($autoClear=true) {
    return call_user_func_array([self::email(), __FUNCTION__], func_get_args());
  }

  /**
   * Set the sender address.
   *
   * @param string $from Sender's email address.
   * @param string $fromName Display name for the sender.
   * @param string|null $returnPath Return-Path header value.
   * @return string This class name for static method chaining.
   */
  public static function from($from, $fromName='', $returnPath=null): string {
    call_user_func_array([self::email(), __FUNCTION__], func_get_args());
    return __CLASS__;
  }

  /**
   * Set the recipient address.
   *
   * @param string|string[] $to Recipient email address or array of addresses.
   * @return string This class name for static method chaining.
   */
  public static function to($to): string {
    call_user_func_array([self::email(), __FUNCTION__], func_get_args());
    return __CLASS__;
  }

  /**
   * Set BCC recipient address.
   *
   * @param string|string[] $bcc BCC email address or array of addresses.
   * @param string $limit BCC batch max size. Default is blank.
   * @return string This class name for static method chaining.
   */
  public static function bcc($bcc, $limit=''): string {
    call_user_func_array([self::email(), __FUNCTION__], func_get_args());
    return __CLASS__;
  }

  /**
   * Set the email subject line.
   *
   * @param string $subject Subject text.
   * @return string This class name for static method chaining.
   */
  public static function subject($subject): string {
    call_user_func_array([self::email(), __FUNCTION__], func_get_args());
    return __CLASS__;
  }

  /**
   * Set the email body text.
   *
   * @param string $body Email body content.
   * @return string This class name for static method chaining.
   */
  public static function message($body): string {
    call_user_func_array([self::email(), __FUNCTION__], func_get_args());
    return __CLASS__;
  }

  /**
   * Set the email body from a Twig template.
   *
   * @param string $templatePath Template path relative to `application/views/`.
   * @param array $params Template variables for interpolation.
   * @return string This class name for static method chaining.
   */
  public static function messageFromTemplate(string $templatePath, array $params=[]): string {
    self::message(self::template()->load($templatePath, $params));
    return __CLASS__;
  }

  /**
   * Set subject and body from an XML template.
   *
   * The XML file should contain `<subject>` and `<message>` elements.
   *
   * @param string $xmlPath XML template path relative to `application/views/`.
   * @param array $params Template variables for interpolation.
   * @return string This class name for static method chaining.
   */
  public static function messageFromXml(string $xmlPath, array $params=[]): string {
    $xml = new \SimpleXMLElement(self::template()->load($xmlPath, $params, 'xml'));
    self
      ::subject((string) $xml->subject)
      ::message(preg_replace('/^(\r\n|\n|\r)|(\r\n|\n|\r)$/', '', (string) $xml->message));
    return __CLASS__;
  }

  /**
   * Set the email content type.
   *
   * @param 'text'|'html' $type Mail content type. Default is "text".
   * @return string This class name for static method chaining.
   */
  public static function setMailType($type='text'): string {
    call_user_func_array([self::email(), 'set_mailtype'], func_get_args());
    return __CLASS__;
  }

  /**
   * Add a file attachment.
   *
   * @param string $file File path or buffered data.
   * @param string $disposition Content-Disposition ("attachment" or "inline").
   * @param string|null $newname Custom filename in the email.
   * @param string $mime MIME type (required for buffered data).
   * @return string This class name for static method chaining.
   */
  public static function attach($file, $disposition='', $newname=null, $mime='') {
    call_user_func_array([self::email(), __FUNCTION__], func_get_args());
    return __CLASS__;
  }

  /**
   * Get the Content-ID for an inline attachment.
   *
   * Useful for embedding images in HTML emails.
   *
   * @param string $filename Existing attachment filename.
   * @return string|false Content-ID string, or false if attachment not found.
   */
  public static function attachmentCid($filename) {
    return call_user_func_array([self::email(), 'attachment_cid'], func_get_args());
  }

  /**
   * Get or create singleton CI_Email instance.
   *
   * @return \CI_Email Cached email library instance.
   */
  private static function email() {
    static $instance;
    if (!isset($instance)) {
      $CI =& \get_instance();
      $CI->load->library('email', self::$defaultConfig);
      $instance = $CI->email;
    }
    return $instance;
  }

  /**
   * Get Template instance.
   * @return \X\Util\Template Template instance.
   */
  private static function template(): \X\Util\Template {
    static $template;
    return $template ?? new \X\Util\Template();
  }
}