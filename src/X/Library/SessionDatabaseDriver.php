<?php
namespace X\Library;
use \X\Util\Loader;
use \X\Util\FileHelper;
use \X\Util\ArrayHelper;
use \X\Util\Logger;

/**
 * Extended session database driver.
 *
 * Extends CodeIgniter's session database driver to support additional columns
 * in the session table. Configure additional columns via `sess_table_additional_columns`
 * in application/config/config.php.
 *
 * Configuration example:
 * ```php
 * // In application/config/config.php
 * $config['sess_table_additional_columns'] = ['user_id', 'username'];
 * ```
 *
 * @implements \SessionHandlerInterface
 */
class SessionDatabaseDriver extends \CI_Session_database_driver {
  /**
   * Initialize session database driver.
   *
   * @param array $params Configuration parameters from CI session config.
   */
  public function __construct(&$params) {
    parent::__construct($params);
    $this->_config['table_additional_columns'] = Loader::config('config', 'sess_table_additional_columns');
  }

  /**
   * Read session data and acquire lock.
   *
   * @param string $sessionId Session ID.
   * @return string Serialized session data, or empty string if not found.
   */
  public function read($sessionId) {
    if ($this->_get_lock($sessionId) === false)
      return $this->_failure;

    // Prevent previous QB calls from messing with our queries.
    $this->_db->reset_query();

    // Needed by write() to detect session_regenerate_id() calls.
    $this->_session_id = $sessionId;
    $this->_db
      ->select('data')
      ->from($this->_config['save_path'])
      ->where('id', $sessionId);
    if ($this->_config['match_ip'])
      $this->_db->where('ip_address', $_SERVER['REMOTE_ADDR']);
    if (!($result = $this->_db->get()) OR ($result = $result->row()) === null) {
      // PHP7 will reuse the same SessionHandler object after ID regeneration, so we need to explicitly set this to FALSE instead of relying on the default ...
      $this->_row_exists = false;
      $this->_fingerprint = md5('');
      return '';
    }

    // PostgreSQL's variant of a BLOB datatype is Bytea, which is a PITA to work with, so we use base64-encoded data in a TEXT field instead.
    $result = ($this->_platform === 'postgre')
      ? base64_decode(rtrim($result->data))
      : $result->data;
    $this->_fingerprint = md5($result);
    $this->_row_exists = true;
    return $result;
  }

  /**
   * Write session data (create or update).
   *
   * @param string $sessionId Session ID.
   * @param string $sessionData Serialized session data.
   * @return bool True on success, false on failure.
   */
  public function write($sessionId, $sessionData) {
    try {
      // Prevent previous QB calls from messing with our queries.
      $this->_db->reset_query();

      // Was the ID regenerated?
      if (isset($this->_session_id) && $sessionId !== $this->_session_id) {
        if (!$this->_release_lock() OR !$this->_get_lock($sessionId))
          return $this->_failure;
        $this->_row_exists = false;
        $this->_session_id = $sessionId;
      } elseif ($this->_lock === false)
        return $this->_failure;
      if ($this->_row_exists === false) {
        $insertData = [
          'id' => $sessionId,
          'ip_address' => $_SERVER['REMOTE_ADDR'],
          'timestamp' => time(),
          'data' => ($this->_platform === 'postgre' ? base64_encode($sessionData) : $sessionData)
        ];
        if (!empty($this->_config['table_additional_columns']))
          $insertData = $this->addAdditionalColumnsToTableData($insertData, $sessionData);
        if ($this->_db->insert($this->_config['save_path'], $insertData)) {
          $this->_fingerprint = md5($sessionData);
          $this->_row_exists = true;
          return $this->_success;
        }
        return $this->_failure;
      }
      $this->_db->where('id', $sessionId);
      if ($this->_config['match_ip'])
        $this->_db->where('ip_address', $_SERVER['REMOTE_ADDR']);
      $updateData = ['timestamp' => time()];
      if ($this->_fingerprint !== md5($sessionData))
        $updateData['data'] = ($this->_platform === 'postgre')
          ? base64_encode($sessionData)
          : $sessionData;
      if (!empty($this->_config['table_additional_columns']))
        $updateData = $this->addAdditionalColumnsToTableData($updateData, $sessionData);
      if ($this->_db->update($this->_config['save_path'], $updateData)) {
        $this->_fingerprint = md5($sessionData);
        return $this->_success;
      }
      return $this->_failure;
    } catch (\Throwable $e) {
      Logger::error($e->getMessage());
      return $this->_failure;
    }
  }

  /**
   * Update session timestamp without modifying data.
   *
   * Required by PHP 7.0+ SessionHandlerInterface for lazy write support.
   *
   * @param string $sessionId Session ID.
   * @param string $sessionData Serialized session data (unused).
   * @return bool True on success, false on failure.
   */
  public function updateTimestamp($sessionId, $sessionData) {
    try {
      // Prevent previous QB calls from messing with our queries.
      $this->_db->reset_query();

      $this->_db->where('id', $sessionId);
      if ($this->_config['match_ip'])
        $this->_db->where('ip_address', $_SERVER['REMOTE_ADDR']);

      $updateData = ['timestamp' => time()];
      return $this->_db->update($this->_config['save_path'], $updateData) ? $this->_success : $this->_failure;
    } catch (\Throwable $e) {
      Logger::error($e->getMessage());
      return $this->_failure;
    }
  }

  /**
   * Unserialize session data to array.
   *
   * @param string $data Serialized session data.
   * @return array|null Unserialized data array, or null if empty.
   */
  private function unserialize(string $data): ?array {
    $fieldset = preg_split('/([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\|/', $data, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    if (empty($fieldset))
      return null;
    for ($i=0,$length=count($fieldset); $i<$length; $i+=2)
      $result[$fieldset[$i]] = unserialize($fieldset[$i+1]);
    return $result;
  }

  /**
   * Add configured additional columns to session table data.
   *
   * @param array $insertData Base session data for INSERT/UPDATE.
   * @param string $sessionData Serialized session data to extract values from.
   * @return array Session data with additional column values added.
   * @throws \RuntimeException If configured column does not exist in session table.
   */
  private function addAdditionalColumnsToTableData(array $insertData, string $sessionData): array {
    $additionalColumns = !is_array($this->_config['table_additional_columns'])
      ? [$this->_config['table_additional_columns']]
      : $this->_config['table_additional_columns'];
    $defaultColumns = $this->_db->list_fields($this->_config['save_path']);
    $unserialized = $this->unserialize($sessionData);
    if (empty($unserialized))
      $unserialized = [];
    foreach ($additionalColumns as $additionalColumn) {
      if (in_array($additionalColumn, $defaultColumns)) {
        $additionalColumnValue = ArrayHelper::searchArrayByKey($additionalColumn, $unserialized);
        $insertData[$additionalColumn] = $additionalColumnValue;
      } else
        throw new \RuntimeException("Column {$additionalColumn} is not found in the session table");
    }
    return $insertData;
  }
}