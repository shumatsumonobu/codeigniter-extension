<?php
use PHPUnit\Framework\TestCase;
use \X\Util\AmazonSesClient;

final class AmazonSesClientTest extends TestCase {
  protected function setUp(): void {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
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
    $client = new AmazonSesClient([
      'region' => $_ENV['AWS_SES_REGION'],
      'credentials' => [
        'key' => $_ENV['AWS_SES_ACCESS_KEY'],
        'secret' => $_ENV['AWS_SES_SECRET_KEY'],
      ],
      'configuration' => $_ENV['AWS_SES_CONFIGURATION'],
    ]);
    $this->assertInstanceOf(AmazonSesClient::class, $client);
  }

  /**
   * Fluent setters must return the client instance for method chaining.
   */
  public function testFluentSettersReturnSelf(): void {
    $client = new AmazonSesClient([
      'region' => $_ENV['AWS_SES_REGION'],
      'credentials' => [
        'key' => $_ENV['AWS_SES_ACCESS_KEY'],
        'secret' => $_ENV['AWS_SES_SECRET_KEY'],
      ],
    ]);
    $result = $client
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
    $client = new AmazonSesClient([
      'region' => $_ENV['AWS_SES_REGION'],
      'credentials' => [
        'key' => $_ENV['AWS_SES_ACCESS_KEY'],
        'secret' => $_ENV['AWS_SES_SECRET_KEY'],
      ],
      'configuration' => $_ENV['AWS_SES_CONFIGURATION'],
    ]);
    $result = $client
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

  /**
   * When roleArn is provided the client must NOT throw at construction.
   */
  public function testCanInstantiateWithRoleArn(): void {
    $client = new AmazonSesClient([
      'region' => $_ENV['AWS_SES_REGION'],
      'roleArn' => $_ENV['AWS_SES_ROLE_ARN'],
    ]);
    $this->assertInstanceOf(AmazonSesClient::class, $client);
  }

  /**
   * Send an email using AssumeRole for cross-account SES access.
   * Only works on EC2 instances with an IAM role that has sts:AssumeRole permission.
   *
   * @group assume-role
   */
  public function testSendEmailWithAssumeRole(): void {
    $client = new AmazonSesClient([
      'region' => $_ENV['AWS_SES_REGION'],
      'roleArn' => $_ENV['AWS_SES_ROLE_ARN'],
      'configuration' => $_ENV['AWS_SES_CONFIGURATION'],
    ]);
    $result = $client
      ->from($_ENV['AWS_SES_FROM'])
      ->to($_ENV['AWS_SES_TO'])
      ->subject('AmazonSesClientTest - AssumeRole')
      ->message('This is a test email sent by PHPUnit using AssumeRole auth.')
      ->send();
    $this->assertNotEmpty($result->get('MessageId'));
  }
}
