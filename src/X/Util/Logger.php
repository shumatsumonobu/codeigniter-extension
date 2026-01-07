<?php
namespace X\Util;

/**
 * Logging utility class.
 *
 * Provides static methods for logging messages at different levels (debug, info, error)
 * with automatic file/line/class information. Logs are written to the path defined
 * in `application/config/config.php#log_path`.
 */
final class Logger {
  /**
   * Debug log.
   *
   * ```php
   * use \X\Util\Logger;
   *
   * Logger::debug('User logged in', ['user_id' => 123]);
   * Logger::debug($user, $request->all());
   * ```
   *
   * @param mixed ...$params Log Message.
   * @return void
   */
  public static function debug(...$params): void {
    log_message('debug', self::createMessage($params, debug_backtrace()));
  }

  /**
   * Info log.
   *
   * ```php
   * use \X\Util\Logger;
   *
   * Logger::info('Processing started');
   * Logger::info('Order created', ['order_id' => 456, 'total' => 1000]);
   * ```
   *
   * @param mixed ...$params Log Message.
   * @return void
   */
  public static function info(...$params): void {
    log_message('info', self::createMessage($params, debug_backtrace()));
  }

  /**
   * Error log.
   *
   * ```php
   * use \X\Util\Logger;
   *
   * Logger::error('Database connection failed');
   * Logger::error($exception);  // Exception objects are automatically formatted
   * Logger::error('API error', ['status' => 500, 'response' => $body]);
   * ```
   *
   * @param mixed ...$params Log Message.
   * @return void
   */
  public static function error(...$params): void {
    $message = $params[0] instanceof \Exception
      ? $params[0]->getMessage() . PHP_EOL . $params[0]->getTraceAsString()
      : self::createMessage($params, debug_backtrace());
    log_message('error', $message);
  }

  /**
   * Log to browser or console.
   *
   * ```php
   * use \X\Util\Logger;
   *
   * // In browser: displays formatted HTML
   * // In CLI: outputs to console and writes to log file
   * Logger::display('Debug value:', $someVariable);
   * Logger::display($user, $request->all());
   * ```
   *
   * @param mixed ...$params Log Message.
   * @return void
   */
  public static function display(...$params): void {
    if (!is_cli()) {
      $message = self::createMessage($params, null, true);
      echo '<p style="border-bottom:1px solid #efefef; padding:4px;">' . $message . '</p>';
    } else {
      $message = self::createMessage($params, null);
      echo $message . PHP_EOL;
      log_message('debug', self::createMessage($params, debug_backtrace()));
    }
  }

  /**
   * Create a log message.
   * @param array $params Log Message.
   * @param array|null $trace (optional) Stack traces.
   * @param bool $isBrowser (optional) If true, escapes HTML special characters in log messages. Default is false.
   * @return string Log Message.
   */
  private static function createMessage(array $params, ?array $trace, bool $isBrowser=false): string {
    $message = '';
    if (!empty($trace)) {
      if (defined('FCPATH')) {
        $docRoot = realpath(\FCPATH . '../') . '/';
        $filePath = str_replace($docRoot, '', $trace[0]['file']);
        $message = $filePath . '(' . $trace[0]['line'] . ')';
      }
      if (isset($trace[1]['class']))
        $message .= ' ' . $trace[1]['class'] . '.' . $trace[1]['function'];
      else if (isset($trace[1]['function']))
        $message .= ' ' . $trace[1]['function'];
      $message .= ':';
    }
    foreach ($params as $param) {
      if (is_array($param) || is_object($param))
        $message .= $isBrowser ? '<pre>' . htmlspecialchars(print_r($param, true), ENT_QUOTES) . '</pre>' : print_r($param, true);
      else
        $message .= $isBrowser ? htmlspecialchars($param, ENT_QUOTES) : $param;
    }
    return $message;
  }
}