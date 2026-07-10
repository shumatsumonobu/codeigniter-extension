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
 * // Explicit credentials (IAM user access key)
 * $ses = new AmazonSesClient([
 *   'region' => $_ENV['AMS_SES_REGION'],
 *   'credentials' => [
 *     'key' => $_ENV['AMS_SES_ACCESS_KEY'],
 *     'secret' => $_ENV['AMS_SES_SECRET_KEY'],
 *   ],
 *   'configuration' => $_ENV['AMS_SES_CONFIGURATION'],
 * ]);
 *
 * // IAM role (EC2 instance profile) — credentials resolved by the
 * // AWS SDK default provider chain when credentials are omitted
 * $ses = new AmazonSesClient([
 *   'region' => $_ENV['AMS_SES_REGION'],
 *   'configuration' => $_ENV['AMS_SES_CONFIGURATION'],
 * ]);
 *
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
   *   region?: string,
   *   credentials?: array{key: string, secret: string},
   *   configuration?: string|null,
   *   version?: string,
   *   debug?: bool
   * } $options Configuration options:
   *   - `region`: AWS region for service requests.
   *   - `credentials`: AWS credentials array with `key` and `secret`. Optional.
   *     When omitted, credentials are resolved by the AWS SDK default provider chain
   *     (e.g. EC2 instance profile / IAM role).
   *   - `configuration`: SES configuration set name. Default is null.
   *   - `version`: SES API version. Default is "latest".
   *   - `debug`: Output SES options to debug log. Default is false.
   */
  public function __construct(array $options=[]) {
    $this->options = array_replace_recursive([
      'credentials' => null,
      'configuration' => null,
      'region' => null,
      'version' => 'latest',
      'debug' => false,
    ], $options);
    if ($this->options['debug']) {
      $maskedOptions = $this->options;
      if (!empty($maskedOptions['credentials']['key']))
        $maskedOptions['credentials']['key'] = '***';
      if (!empty($maskedOptions['credentials']['secret']))
        $maskedOptions['credentials']['secret'] = '***';
      Logger::debug('AmazonSesClient options: ', $maskedOptions);
    }
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
   */
  public function send(): \Aws\Result {
    $destination['ToAddresses'] = is_array($this->to) ? $this->to : [$this->to];
    isset($this->cc) && $destination['CcAddresses'] = $this->cc;
    isset($this->bcc) && $destination['BccAddresses'] = $this->bcc;
    $params = [
      'Destination' => $destination,
      'ReplyToAddresses' => [$this->from],
      'Source' => isset($this->fromName) ? sprintf('%s <%s>', $this->fromName, $this->from) : $this->from,
      'Message' => [
        'Body' => [
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
    ];
    if (!empty($this->options['configuration']))
      $params['ConfigurationSetName'] = $this->options['configuration'];
    $res = $this->client()->sendEmail($params);
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
    if (!isset($client)) {
      $config = [
        'version' => $this->options['version'],
        'region' => $this->options['region'],
      ];
      if (!empty($this->options['credentials']))
        $config['credentials'] = $this->options['credentials'];
      $client = new \Aws\Ses\SesClient($config);
    }
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