<?php
use X\Util\Logger;
require_once(dirname(__FILE__) . '/../exception/UserNotFoundException.php');

/**
 * Application base controller.
 *
 * All application controllers should extend this class.
 * Provides common response hooks with session data injection for views.
 */
abstract class AppController extends \X\Controller\Controller {
  /**
   * Hook called before any response.
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
   * Automatically injects session data into the view context.
   *
   * @param string $referer Referrer URL.
   * @return void
   */
  protected function beforeResponseView(string $referer) {
    // Inject session data into view context for Twig templates
    if (isset($_SESSION[SESSION_NAME]))
      parent::set(SESSION_NAME, $_SESSION[SESSION_NAME]);
  }

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