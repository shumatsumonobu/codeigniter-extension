<?php
namespace X\Util;
use \X\Util\Logger;

/**
 * CSV file utility class.
 *
 * Provides methods for reading and writing CSV files with
 * proper encoding support and row manipulation callbacks.
 */
final class CsvHelper {
  /**
   * Append a row to a CSV file.
   *
   * Uses file locking (LOCK_EX) to prevent concurrent write corruption.
   *
   * @param string $filePath CSV file path.
   * @param array $row Array of field values to write.
   * @return void
   * @throws \RuntimeException If unable to acquire file lock.
   */
  public static function putRow(string $filePath, array $row): void {
    if (empty($row))
      return;
    $fp = fopen($filePath, 'a');
    if (!flock($fp, LOCK_EX))
      throw new \RuntimeException('Unable to get file lock. path=' . $filePath);
    fputcsv($fp, $row);
    flock($fp, LOCK_UN);
    fclose($fp);
  }

  /**
   * Read all rows from a CSV file.
   *
   * An optional callback can transform or filter each row.
   * If the callback returns null/empty, the row is excluded.
   *
   * @param string $filePath CSV file path.
   * @param callable|null $callback Row transformer: `function(array $row): ?array`. Return null to skip row.
   * @return array[]|null Array of rows, or null if file does not exist or is empty.
   */
  public static function read(string $filePath, callable $callback=null) {
    if (!file_exists($filePath))
      return null;
    $file = new \SplFileObject($filePath);
    $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::READ_AHEAD | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);
    $rows = [];
    foreach ($file as $row) {
      if (is_null($row[0]))
        break;
      if (is_callable($callback))
        $row = $callback($row);
      if (!empty($row))
        $rows[] = $row;
    }
    return !empty($rows) ? $rows : null;
  }
}