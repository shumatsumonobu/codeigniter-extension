<?php
namespace X\Util;
use \X\Util\Logger;

/**
 * CodeIgniter resource loader utility class.
 *
 * Provides static methods for loading models, libraries, databases,
 * and configuration files with extended query builder support.
 */
final class Loader {
  /**
   * Load one or more models into the CI instance.
   *
   * @param string|string[]|null $models Model name or array of model names.
   * @return void
   */
  public static function model($models): void {
    if (empty($models))
      return;
    if (is_string($models))
      $models = [$models];
    $CI =& \get_instance();
    foreach ($models as $model)
      $CI->load->model($model);
  }

  /**
   * Load one or more libraries into the CI instance.
   *
   * @param string|string[]|null $libraries Library name or array of library names.
   * @return void
   */
  public static function library($libraries): void {
    if (empty($libraries))
      return;
    if (is_string($libraries))
      $libraries = [$libraries];
    $CI =& \get_instance();
    foreach ($libraries as $library)
      $CI->load->library($library);
  }

  /**
   * Load a database connection.
   *
   * @param mixed $config Connection group name or configuration array. Default is "default".
   * @param bool $return Return the DB instance instead of assigning to CI. Default is false.
   * @param mixed $queryBuilder Custom query builder instance override.
   * @param bool $overwrite Overwrite existing CI DB instance. Default is false.
   * @return \X\Database\DB|null|false DB object if $return is true, false if already loaded, null otherwise.
   */
  public static function database($config='default', bool $return=false, $queryBuilder=null, bool $overwrite=false) {
    $CI =& \get_instance();

    // Do we even need to load the database class?
    if (!$return && $queryBuilder === null && isset($CI->db) && is_object($CI->db) && !empty($CI->db->conn_id) && !$overwrite)
      return false;
    $db = \X\Database\DB($config, $queryBuilder);
    if (!$return || $overwrite) {
      // Initialize the db variable. Needed to prevent reference errors with some configurations.
      $CI->db = '';

      // Load the DB class.
      $CI->db =& $db;
    }
    if ($return)
      return $db;
    return null;
  }

  /**
   * Load and retrieve a configuration value.
   *
   * Results are cached after the first load.
   *
   * @param string $configFile Configuration file name (without .php extension).
   * @param string|null $itemName Specific item to retrieve. Null returns all items.
   * @return mixed Configuration value, or empty string if item not found.
   */
  public static function config(string $configFile, string $itemName=null) {
    static $config;
    if (isset($config[$configFile])) {
      if (empty($itemName))
        return $config[$configFile];
      return $config[$configFile][$itemName] ?? '';
    }
    $CI =& \get_instance();
    $CI->config->load($configFile, true);
    $config[$configFile] = $CI->config->item($configFile);
    if (empty($itemName))
      return $config[$configFile];
    return $config[$configFile][$itemName] ?? '';
  }
}