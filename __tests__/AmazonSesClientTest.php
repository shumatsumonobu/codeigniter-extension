<?php
use PHPUnit\Framework\TestCase;
use \X\Util\AmazonSesClient;

final class AmazonSesClientTest extends TestCase {
  /**
   * An instance of Amazon SES Client.
   * @var \X\Util\AmazonSesClient
   */
  private $client;

  protected function setUp(): void {
    // Load environment variables.
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    // An instance of Amazon SES Client.
    $this->client = new AmazonSesClient([
      'region' => $_ENV['AWS_SES_REGION'],
      'credentials' => [
        'key' => $_ENV['AWS_SES_ACCESS_KEY'],
        'secret' => $_ENV['AWS_SES_SECRET_KEY'],
      ],
      'configuration' => $_ENV['AWS_SES_CONFIGURATION'],
    ]);
  }

  /**
   * When credentials are omitted the client must NOT throw at construction;
   * credentials are resolved lazily by the AWS SDK default provider chain
   * (e.g. EC2 instance profile / IAM role).
   */
  public function testCanInstantiateWithoutCredentials(): void {
    $client = new AmazonSesClient([
      'region' => $_ENV['AWS_SES_REGION'],
    ]);
    $this->assertInstanceOf(AmazonSesClient::class, $client);
  }

  /**
   * When credentials are provided the client must NOT throw at construction.
   */
  public function testCanInstantiateWithCredentials(): void {
    $this->assertInstanceOf(AmazonSesClient::class, $this->client);
  }

  /**
   * Fluent setters must return the client instance for method chaining.
   */
  public function testFluentSettersReturnSelf(): void {
    $result = $this->client
      ->from('from@example.com')
      ->to('to@example.com')
      ->subject('Test')
      ->message('Hello');
    $this->assertInstanceOf(AmazonSesClient::class, $result);
  }

  /**
   * Send a plain text email via SES.
   * Requires valid AWS credentials and verified email addresses in .env.
   *
   * @group ses-send
   */
  public function testSendPlainTextEmail(): void {
    $result = $this->client
      ->from($_ENV['AWS_SES_FROM'])
      ->to($_ENV['AWS_SES_TO'])
      ->subject('AmazonSesClientTest - plain text')
      ->message('This is a test email sent by PHPUnit.')
      ->send();
    $this->assertNotEmpty($result->get('MessageId'));
  }

  /**
   * Send an email using IAM role authentication (no explicit credentials).
   * Only works on EC2 instances with an IAM role that has SES permissions.
   *
   * @group iam-role
   */
  public function testSendEmailWithIamRole(): void {
    $client = new AmazonSesClient([
      'region' => $_ENV['AWS_SES_REGION'],
      'configuration' => $_ENV['AWS_SES_CONFIGURATION'],
    ]);
    $result = $client
      ->from($_ENV['AWS_SES_FROM'])
      ->to($_ENV['AWS_SES_TO'])
      ->subject('AmazonSesClientTest - IAM role')
      ->message('This is a test email sent by PHPUnit using IAM role auth.')
      ->send();
    $this->assertNotEmpty($result->get('MessageId'));
  }
}
