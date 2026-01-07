<?php
namespace X\Database;

/**
 * Query result extension trait.
 *
 * Provides additional result processing methods for database queries.
 */
trait Result {
  /**
   * Get results as associative array keyed by a column value.
   *
   * Converts query results to an array where the specified column value becomes the key.
   *
   * @example
   * ```php
   * $rows = $this->db
   *   ->select('id, name')
   *   ->from('users')
   *   ->get()
   *   ->result_keyvalue('id');
   *
   * // Result:
   * // [
   * //   1 => ['id' => 1, 'name' => 'Oliver'],
   * //   2 => ['id' => 2, 'name' => 'Harry'],
   * // ]
   * ```
   *
   * @param string $column Column name to use as array key. Default is 'id'.
   * @return array Associative array keyed by the specified column value.
   * @throws \RuntimeException If the specified column does not exist in results.
   */
  public function result_keyvalue(string $column='id'): array {
    $rows = $this->result_array();
    if (empty($rows))
      return [];
    if (array_key_exists($column, $rows[0]) === false)
      throw new RuntimeException('result has no ' . $column . 'key');
    return array_combine(array_column($rows, $column), $rows);
  }
}