<?php
namespace X\Database;

/**
 * Extended Query Builder class.
 *
 * Provides enhanced query building with additional methods like
 * INSERT ON DUPLICATE KEY UPDATE and improved error handling.
 *
 * Usage:
 * ```php
 * // Insert with duplicate key update
 * $id = $this->db
 *   ->set(['name' => 'John', 'email' => 'john@example.com'])
 *   ->insert_on_duplicate_update('users');
 *
 * // Batch insert with duplicate key update
 * $affected = $this->db->insert_on_duplicate_update_batch('users', [
 *   ['name' => 'John', 'email' => 'john@example.com'],
 *   ['name' => 'Jane', 'email' => 'jane@example.com']
 * ]);
 * ```
 */
#[\AllowDynamicProperties]
abstract class QueryBuilder extends \CI_DB_query_builder {
  /**
   * Initialize query builder.
   *
   * @param array $config Database configuration array.
   */
  public function __construct($config) {
    parent::__construct($config);
  }

  /**
   * Insert or update on duplicate key (MySQL specific).
   *
   * Performs INSERT and updates existing row if duplicate key is found.
   *
   * @param string $table Table name. Uses qb_from if empty.
   * @param array|object|null $set Associative array of field/value pairs.
   * @param bool|null $escape Whether to escape values and identifiers.
   * @return int Insert ID of the new or existing row.
   * @throws \RuntimeException If no data or table specified.
   */
  public function insert_on_duplicate_update($table='', $set=null, $escape=null): int {
    if ($set !== null)
      parent::set($set, '', $escape);
    if (count($this->qb_set) === 0)
      // No valid data array. Folds in cases where keys and values did not match up
      return ($this->db_debug) ? parent::display_error('db_must_use_set') : false;
    if ($table === '') {
      if (!isset($this->qb_from[0]))
        return ($this->db_debug) ? parent::display_error('db_must_set_table') : false;
      $table = $this->qb_from[0];
    }
    $sql = $this->_insert_on_duplicate_update(
      parent::protect_identifiers($table, true, $escape, false),
      array_keys($this->qb_set),
      array_values($this->qb_set)
    );
    $this->query($sql);
    parent::_reset_write();
    return (int) $this->insert_id();
  }

  /**
   * Batch insert or update on duplicate key (MySQL specific).
   *
   * Performs batch INSERT and updates existing rows if duplicate keys are found.
   *
   * @param string $table Table name. Uses qb_from if empty.
   * @param array|object|null $set Array of associative arrays with field/value pairs.
   * @param bool|null $escape Whether to escape values and identifiers.
   * @param int $batchSize Number of rows to insert per query. Default is 100.
   * @return int Number of affected rows.
   * @throws \RuntimeException If no data or table specified.
   */
  public function insert_on_duplicate_update_batch(string $table='', $set=null, bool $escape=null, int $batchSize=100): int {
    if ($set !== null)
      parent::set_insert_batch($set, '', $escape);
    if (count($this->qb_set) === 0)
      // No valid data array. Folds in cases where keys and values did not match up
      return ($this->db_debug) ? parent::display_error('db_must_use_set') : false;
    if ($table === '') {
      if (!isset($this->qb_from[0]))
        return ($this->db_debug) ? parent::display_error('db_must_set_table') : false;
      $table = $this->qb_from[0];
    }

    // Batch this baby
    $affectedRows = 0;
    for ($i = 0, $total = count($this->qb_set); $i < $total; $i += $batchSize) {
      $sql = $this->_insert_on_duplicate_update_batch(
        parent::protect_identifiers($table, true, $escape, false),
        $this->qb_keys,
        array_slice($this->qb_set, $i, $batchSize)
      );
      $this->query($sql);
      $affectedRows += $this->affected_rows();
    }
    parent::_reset_write();
    return $affectedRows;
  }

  /**
   * Insert a single row.
   *
   * Performs INSERT and returns the auto-increment ID.
   *
   * @param string $table Table name.
   * @param array|object|null $set Associative array of field/value pairs.
   * @param bool|null $escape Whether to escape values and identifiers.
   * @return int Insert ID.
   * @throws \RuntimeException On query failure.
   */
  public function insert($table='', $set=null, $escape=null): int {
    $result = parent::insert($table, $set, $escape);
    if ($result === false) {
      $error = parent::error();
      throw new \RuntimeException($error['message'], $error['code']);
    }
    return (int) $this->insert_id();
  }

  /**
   * Insert multiple rows.
   *
   * Performs batch INSERT and returns array of insert IDs.
   *
   * @param string $table Table name.
   * @param array|object|null $set Array of associative arrays with field/value pairs.
   * @param bool|null $escape Whether to escape values and identifiers.
   * @param int $batchSize Number of rows to insert per query. Default is 100.
   * @return int[] Array of insert IDs.
   * @throws \RuntimeException On query failure.
   */
  public function insert_batch($table, $set=null, $escape=null, $batchSize=100):array {
    if (parent::insert_batch($table, $set, $escape, $batchSize) === false) {
      $error = parent::error();
      throw new \RuntimeException($error['message'], $error['code']);
    }
    $firstId = $this->insert_id();
    return range($firstId, $firstId + count($set) - 1);
  }

  /**
   * Update rows.
   *
   * Performs UPDATE query with optional WHERE and LIMIT clauses.
   *
   * @param string $table Table name.
   * @param array|object|null $set Associative array of field/value pairs.
   * @param string|array|null $where WHERE clause conditions.
   * @param int|null $limit Maximum number of rows to update.
   * @return void
   * @throws \RuntimeException On query failure.
   */
  public function update($table='', $set=null, $where=null, $limit=null): void {
    if (parent::update($table, $set, $where, $limit) === false) {
      $error = parent::error();
      throw new \RuntimeException($error['message'], $error['code']);
    }
  }

  /**
   * Update multiple rows in batch.
   *
   * Performs batch UPDATE using a common index field.
   *
   * @param string $table Table name.
   * @param array|object|null $set Array of associative arrays with field/value pairs.
   * @param string|null $index Index field name used to match rows.
   * @param int $batchSize Number of rows to update per query. Default is 100.
   * @return int Number of affected rows.
   * @throws \RuntimeException On query failure.
   */
  public function update_batch($table, $set=null, $value=null, $batchSize=100):int {
    $affectedRows = parent::update_batch($table, $set, $value, $batchSize);
    if ($affectedRows === false) {
      $error = parent::error();
      throw new \RuntimeException($error['message'], $error['code']);
    }
    return $affectedRows;
  }

  /**
   * Execute a raw SQL query.
   *
   * Executes the given SQL statement with optional parameter binding.
   *
   * @param string $sql SQL statement to execute.
   * @param array|false $binds Parameter bindings for prepared statements. Default is false.
   * @param bool|null $returnObject Return result object for SELECT queries.
   * @return mixed True for write queries, result object for SELECT queries.
   * @throws \RuntimeException On query failure.
   */
  public function query($sql, $binds=false, $returnObject=null) {
    $result = parent::query($sql, $binds, $returnObject);
    if ($result === false) {
      $error = parent::error();
      throw new \RuntimeException($error['message'], $error['code']);
    }
    return $result;
  }

  /**
   * Load the result driver class.
   *
   * Creates and returns the appropriate result driver class name for the current database driver.
   *
   * @return string Fully qualified result driver class name.
   */
  public function load_rdriver(): string {
    $driver = '\X\Database\\' . ucfirst($this->dbdriver) . 'Driver';
    if ( ! class_exists($driver, false)) {
      require_once(BASEPATH.'database/DB_result.php');
      require_once(BASEPATH.'database/drivers/'.$this->dbdriver.'/'.$this->dbdriver.'_result.php');
      eval('namespace X\Database {class ' . ucfirst($this->dbdriver) . 'Driver extends \X\Database\Driver\\' . ucfirst($this->dbdriver) . '\Result {use \X\Database\Result;}}');
    }
    return $driver;
  }

  /**
   * Check if FROM table is set in query builder.
   *
   * @param int $index Index of the table in FROM clause. Default is 0.
   * @return bool True if table is set at the given index.
   */
  public function isset_qb_from(int $index=0): bool {
    return isset($this->qb_from[$index]);
  }

  /**
   * Generate INSERT ON DUPLICATE KEY UPDATE SQL statement.
   *
   * @param string $table Table name.
   * @param array $keys Column names.
   * @param array $values Column values.
   * @return string SQL query string.
   */
  private function _insert_on_duplicate_update(string $table, array $keys, array $values): string {
    foreach ($keys as $key)
      $update_fields[] = $key . '= VALUES(' . $key . ')';
    return 'INSERT INTO ' . $table . ' (' . implode(', ', $keys) . ') VALUES (' . implode(', ', $values) . ') ON DUPLICATE KEY UPDATE ' . implode(', ', $update_fields);
  }

  /**
   * Generate batch INSERT ON DUPLICATE KEY UPDATE SQL statement.
   *
   * @param string $table Table name.
   * @param array $keys Column names.
   * @param array $values Array of value sets.
   * @return string SQL query string.
   */
  private function _insert_on_duplicate_update_batch(string $table, array $keys, array $values): string {
    foreach ($keys as $key)
      $update_fields[] = $key . '= VALUES(' . $key . ')';
    return 'INSERT INTO ' . $table . ' (' . implode(', ', $keys) . ') VALUES ' . implode(', ', $values) . ' ON DUPLICATE KEY UPDATE ' . implode(', ', $update_fields);
  }
}