<?php
namespace X\Util;
use \X\Util\Logger;
use \X\Util\Template;

/**
 * Amazon SES email client wrapper class.
 *
 * Provides fluent interface for sending emails via AWS Simple Email Service
 * with support for XML-based templates, CC/BCC recipients, and configuration sets.
 *
 * ```php
 * use \X\Util\AmazonSesClient;
 *
 * $ses  = new AmazonSesClient([
 *   'region' => $_ENV['AMS_SES_REGION'],
 *   'credentials' => [
 *     'key' => $_ENV['AMS_SES_ACCESS_KEY'],
 *     'secret' => $_ENV['AMS_SES_SECRET_KEY']
 *   ],
 *   'configuration' => $_ENV['AMS_SES_CONFIGURATION'],
 * ]);
 * $ses
 *   ->from('from@example.com')
 *   ->to('to@example.com')
 *   ->messageFromXml('email/sample', ['name' => 'Alex'])
 *   ->send();
 * ```
 *
 * Email body and subject: application/views/email/sample.xml.
 * ```xml
 * <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
 * <mail>
 * <subject>Test email.</subject>
 * <message>
 * Hi {{ name }}.
 * </message>
 * </mail>
 * ```
 */
class AmazonSesClient {
  /**
   * SES client options.
   *
   * - credentials: array with 'key' and 'secret' for AWS credentials
   * - configuration: string|null configuration set name
   * - region: string AWS region (e.g., 'ap-northeast-1')
   * - version: string API version (default: 'latest')
   *
   * @var array
   */
  private $options = null;

  /**
   * Character code of the email. Default is "UTF-8".
   * @var string
   */
  private $charset = 'UTF-8';

  /**
   * Sender's email address.
   * @var string
   */
  private $from = null;

  /**
   * Sender name.
   * @var string
   */
  private $fromName = null;

  /**
   * Destination email address.
   * @var string
   */
  private $to = null;

  /**
   * BCC email address.
   * @var string
   */
  private $bcc = null;

  /**
   * CC email address.
   * @var string
   */
  private $cc = null;

  /**
   * Subject.
   * @var string
   */
  private $subject = null;

  /**
   * Body.
   * @var string
   */
  private $message = null;

  /**
   * Initialize AmazonSesClient.
   *
   * @param array{
   *   credentials?: array{key: string, secret: string},
   *   configuration?: string|null,
   *   region?: string,
   *   version?: string
   * } $options Configuration options:
   *   - `credentials.key`: AWS access key ID.
   *   - `credentials.secret`: AWS secret access key.
   *   - `configuration`: SES configuration set name. Default is null.
   *   - `region`: AWS region for service requests.
   *   - `version`: SES API version. Default is "latest".
   */
  public function __construct(array $options=[]) {
    $this->options = array_replace_recursive([
      'credentials' => [
        'key' => null,
        'secret' => null,
      ],
      'configuration' => null,
      'region' => null,
      'version' => 'latest',
    ], $options);
  }

  /**
   * Set the email character encoding.
   *
   * @param string $charset Character encoding (e.g., "UTF-8", "ISO-2022-JP").
   * @return AmazonSesClient Method chaining.
   */
  public function charset(string $charset): AmazonSesClient {
    $this->charset = $charset;
    return $this;
  }

  /**
   * Set the sender address.
   *
   * @param string $from Sender's email address.
   * @param string|null $fromName Display name for the sender.
   * @return AmazonSesClient Method chaining.
   */
  public function from(string $from, string $fromName=null): AmazonSesClient {
    $this->from = $from;
    $this->fromName = $fromName;
    return $this;
  }

  /**
   * Set the recipient address.
   *
   * @param string|string[] $to Recipient email address or array of addresses.
   * @return AmazonSesClient Method chaining.
   */
  public function to($to): AmazonSesClient {
    $this->to = $to;
    return $this;
  }

  /**
   * Set BCC recipient address.
   *
   * @param string|string[] $bcc BCC email address or array of addresses.
   * @return AmazonSesClient Method chaining.
   */
  public function bcc($bcc): AmazonSesClient {
    $this->bcc = $bcc;
    return $this;
  }

  /**
   * Set CC recipient address.
   *
   * @param string|string[] $cc CC email address or array of addresses.
   * @return AmazonSesClient Method chaining.
   */
  public function cc($cc): AmazonSesClient {
    $this->cc = $cc;
    return $this;
  }

  /**
   * Set the email subject line.
   *
   * @param string $subject Subject text.
   * @return AmazonSesClient Method chaining.
   */
  public function subject(string $subject): AmazonSesClient {
    $this->subject = $subject;
    return $this;
  }

  /**
   * Set the email body text.
   *
   * @param string $message Email body content.
   * @return AmazonSesClient Method chaining.
   */
  public function message(string $message): AmazonSesClient {
    $this->message = $message;
    return $this;
  }

  /**
   * Set subject and body from an XML template.
   *
   * The XML file should contain `<subject>` and `<message>` elements.
   * Twig variables can be used in the template.
   *
   * @param string $xmlPath XML template path relative to `application/views/`.
   * @param array $params Template variables for interpolation.
   * @return AmazonSesClient Method chaining.
   */
  public function messageFromXml(string $xmlPath, array $params=[]): AmazonSesClient {
    static $template;
    if (!isset($template))
      $template = new Template();
    $xml = new \SimpleXMLElement($template->load($xmlPath, $params, 'xml'));
    $this
      ->subject((string) $xml->subject)
      ->message(preg_replace('/^(\r\n|\n|\r)|(\r\n|\n|\r)$/', '', (string) $xml->message));
    return $this;
  }

  /**
   * Send the email via Amazon SES.
   *
   * After sending, all recipient and message fields are reset.
   *
   * @return \Aws\Result SES API response containing MessageId.
   * @throws \InvalidArgumentException If the sender address is invalid.
   */
  public function send(): \Aws\Result {
    $CI =& get_instance();
    $CI->load->library('form_validation'); 
    $CI->form_validation
      ->reset_validation()
      ->set_data([
        // 'to' => $this->to,
        'from' => $this->from
      ])
      // ->set_rules('to', 'To Email', 'required|valid_email')
      ->set_rules('from', 'From Email', 'required|valid_email');
    if (!$CI->form_validation->run())
      throw new \InvalidArgumentException(implode('', $CI->form_validation->error_array()));
    $destination['ToAddresses'] = is_array($this->to) ? $this->to : [$this->to];
    isset($this->cc) && $destination['CcAddresses'] = $this->cc;
    isset($this->bcc) && $destination['BccAddresses'] = $this->bcc;
    $res = $this->client()->sendEmail([
      'Destination' => $destination,
      'ReplyToAddresses' => [$this->from],
      'Source' => isset($this->fromName) ? sprintf('%s <%s>', $this->fromName, $this->from) : $this->from,
      'Message' => [
        'Body' => [
          // 'Html' => [
          //     'Charset' => $this->charset,
          //     'Data' => $this->message,
          // ],
          'Text' => [
            'Charset' => $this->charset,
            'Data' => $this->message,
          ],
        ],
        'Subject' => [
          'Charset' => $this->charset,
          'Data' => $this->subject,
        ],
      ],
      'ConfigurationSetName' => $this->options['configuration'],
    ]);
    $this->reset();
    return $res;
  }

  /**
   * Get or create singleton SES client instance.
   *
   * @return \Aws\Ses\SesClient Cached SES client instance.
   */
  private function client(): \Aws\Ses\SesClient {
    static $client;
    if (!isset($client))
      $client = new \Aws\Ses\SesClient([
        'credentials' => $this->options['credentials'],
        'version' => $this->options['version'],
        'region' => $this->options['region'],
      ]);
    return $client;
  }

  /**
   * Reset all message fields to defaults.
   *
   * @return void
   */
  private function reset(): void {
    $this->charset = 'UTF-8';
    $this->from = null;
    $this->fromName = null;
    $this->to = null;
    $this->bcc = null;
    $this->cc = null;
    $this->subject = null;
    $this->message = null;
  }
}