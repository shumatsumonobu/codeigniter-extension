<?php
namespace X\Core;

/**
 * Extended CodeIgniter Loader class.
 *
 * Provides enhanced database loading with custom query builder support.
 *
 * Usage:
 * ```php
 * // In controller, load default database
 * $this->load->database();
 *
 * // Load specific connection group
 * $this->load->database('secondary');
 *
 * // Get database instance directly
 * $db = $this->load->database('default', true);
 * ```
 */
#[\AllowDynamicProperties]
class Loader extends \CI_Loader {
  /**
   * Load database connection.
   *
   * @param mixed $config Connection group name or database configuration array. Default is 'default'.
   * @param bool $return Return the database object instead of assigning to CI instance. Default is false.
   * @param mixed $queryBuilder Custom query builder instance to use.
   * @return \X\Database\DB|\X\Core\Loader|false Database object if $return is true, Loader instance otherwise, false on failure.
   */
  public function database($config='', $return=false, $queryBuilder=null) {
    $db = \X\Util\Loader::database(empty($config) ? 'default' : $config, $return, $queryBuilder);
    return $db !== null ? $db : $this;
  }
}
