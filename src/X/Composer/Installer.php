<?php
namespace X\Composer;

use Composer\Script\Event;
use Composer\IO\ConsoleIO;
use \X\Util\FileHelper;

/**
 * Composer post-create-project installer.
 *
 * Handles the setup of a new codeigniter-extension project after
 * `composer create-project` is executed. Configures CodeIgniter,
 * copies skeleton files, and sets up frontend assets.
 */
final class Installer {
  /**
   * Path to the CodeIgniter framework directory.
   *
   * @var string
   */
  const FRAMEWORK = 'vendor/codeigniter/framework';

  /**
   * Path to the public document root directory.
   *
   * @var string
   */
  const DOCROOT = 'public';

  /**
   * Run the project installation process.
   *
   * Performs the following steps:
   * 1. Copy and configure application files
   * 2. Create entry point (index.php)
   * 3. Copy sample database schema (init.sql)
   * 4. Configure CodeIgniter settings
   * 5. Setup frontend module (npm install & build)
   * 6. Clean up unnecessary files
   *
   * @param Event $event Composer script event.
   * @return void
   */
  public static function run(Event $event) {
    $cwd = getcwd();
    $io = $event->getIO();
    $io->write('Preparing the application file.');
    FileHelper::copyDirectory(self::FRAMEWORK . '/application', 'application');
    FileHelper::copyDirectory('skeleton/application', 'application');

    $io->write('Create an entry point (index.php).');
    FileHelper::copyFile(self::FRAMEWORK . '/index.php', self::DOCROOT . '/index.php');
    FileHelper::copyDirectory('skeleton/public', self::DOCROOT);
    FileHelper::replace(self::DOCROOT . '/index.php', [
      '$system_path = \'system\';' => '$system_path = \'../' . self::FRAMEWORK . '/system\';',
      '$application_folder = \'application\';' => '$application_folder = \'../application\';',
    ]);

    $io->write('Copy the sample DB(init.sql).');
    FileHelper::copyFile('skeleton/init.sql', 'init.sql');

    $io->write('Create a config (config.php).');
    FileHelper::replace('application/config/config.php', [
      '$config[\'base_url\'] = \'\';' => 'if (!empty($_SERVER[\'HTTP_HOST\'])) {$config[\'base_url\'] = "//".$_SERVER[\'HTTP_HOST\'] . str_replace(basename($_SERVER[\'SCRIPT_NAME\']),"",$_SERVER[\'SCRIPT_NAME\']);}',
      '$config[\'enable_hooks\'] = FALSE;' => '$config[\'enable_hooks\'] = TRUE;',
      '$config[\'permitted_uri_chars\'] = \'a-z 0-9~%.:_\-\';' => '$config[\'permitted_uri_chars\'] = \'a-z 0-9~%.:_\-,\';',
      '$config[\'sess_save_path\'] = NULL;' => '$config[\'sess_save_path\'] = \APPPATH . \'session\';',
      '$config[\'cookie_httponly\']  = FALSE;' => '$config[\'cookie_httponly\']  = TRUE;',
      '$config[\'composer_autoload\'] = FALSE;' => '$config[\'composer_autoload\'] = realpath(\APPPATH . \'../vendor/autoload.php\');',
      '$config[\'index_page\'] = \'index.php\';' => '$config[\'index_page\'] = \'\';',
      '$config[\'subclass_prefix\'] = \'MY_\';' => '$config[\'subclass_prefix\'] = \'App\';',
    ]);
    FileHelper::replace('application/config/autoload.php', ['$autoload[\'helper\'] = array();' => '$autoload[\'helper\'] = array(\'url\');']);

    $io->write('Updating composer.');
    FileHelper::copyFile('composer.json.dist', 'composer.json');
    FileHelper::copyFile('.env.dist', '.env');
    passthru('composer update');

    // Preparing the frontend module.
    $io->write('Preparing the frontend module.');
    FileHelper::copyDirectory('skeleton/client', 'client');
    chdir('./client');
    passthru('npm install');
    passthru('npm run build');
    chdir($cwd);
    $io->write('Deleting unnecessary files.');
    FileHelper::delete(
      $cwd . '/CHANGELOG.md',
      $cwd . '/LICENSE',
      $cwd . '/README.md',
      $cwd . '/sandbox',
      $cwd . '/__tests__',
      $cwd . '/composer.json.dist',
      $cwd . '/demo',
      $cwd . '/phpunit-printer.yml',
      $cwd . '/phpunit.xml',
      $cwd . '/screencaps',
      $cwd . '/skeleton',
      $cwd . '/src',
    );
    $io->write('Installation is complete.');
    $io->write('See <https://packagist.org/packages/takuya-motoshima/codeigniter-extensions> for details.');
  }
}