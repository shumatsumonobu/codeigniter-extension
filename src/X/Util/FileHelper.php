<?php
namespace X\Util;
use \X\Util\Logger;

/**
 * File system utility class.
 *
 * Provides file and directory operations including creation, deletion,
 * copying, permission handling, and path manipulation.
 */
final class FileHelper {
  /**
   * Create a directory recursively.
   *
   * Does nothing if the directory already exists.
   *
   * @param string $dir Directory path to create.
   * @param int $mode Directory permissions. Default is 0755.
   * @return bool True if created, false if already exists or creation failed.
   */
  public static function makeDirectory(string $dir, int $mode=0755): bool {
    if (file_exists($dir))
      // If the directory already exists, do nothing.
      return false;

    // Create a directory.
    if (@mkdir($dir, $mode, true) === false) {
      // If the directory creation fails, get the reason.
      $error = error_get_last();
      $reason = !empty($error['message']) ? $error['message'] : 'unknown';
      Logger::info("{$dir} directory creation failed, reason is \"{$reason}\"");
      return false;
    }
    return true;
  }

  /**
   * Move (rename) a file or directory.
   *
   * If the destination contains no directory separator, the file is moved
   * within the same directory. Missing extension is inherited from source.
   *
   * @param string $src Source path.
   * @param string $dest Destination path or filename.
   * @param string|null $group Ownership group to set on destination.
   * @param string|null $user Ownership user to set on destination.
   * @return void
   * @throws \RuntimeException If source does not exist or rename fails.
   */
  public static function move(string $src, string $dest, $group=null, $user=null): void {
    if (!file_exists($src))
      throw new \RuntimeException('Not found file ' . $src);
    if (strpos($dest, '/') === false) {
      if (strpos($dest, '.') === false)
        $dest = $dest . '.' . pathinfo($src, PATHINFO_EXTENSION);
      $dest = pathinfo($src, PATHINFO_DIRNAME) . '/' . $dest;
    } else
      self::makeDirectory(dirname($dest));
    if (rename($src, $dest) === false)
      throw new \RuntimeException('Can not rename from ' . $src . ' to ' . $dest);
    if (isset($group))
      chgrp($dest, $group);
    if (isset($user))
      $res = chown($dest, $user);
  }

  /**
   * Copy a single file.
   *
   * Creates destination directory if it does not exist.
   *
   * @param string $src Source file path.
   * @param string $dest Destination file path.
   * @param string|null $group Ownership group to set on destination.
   * @param string|null $user Ownership user to set on destination.
   * @return void
   * @throws \RuntimeException If source does not exist, is not a file, or copy fails.
   */
  public static function copyFile(string $src, string $dest, $group=null, $user=null): void {
    if (!file_exists($src))
      throw new \RuntimeException('Not found file ' . $src);
    else if (!is_file($src))
      throw new \RuntimeException($src . ' is not file');
    self::makeDirectory(dirname($dest));
    if (copy($src, $dest) === false)
      throw new \RuntimeException('Can not copy from ' . $src . ' to ' . $dest);
    if (isset($group))
      chgrp($dest, $group);
    if (isset($user))
      chown($dest, $user);
  }

  /**
   * Copy a directory recursively.
   *
   * @param string $src Source directory path.
   * @param string $dest Destination directory path.
   * @return void
   * @throws \RuntimeException If source does not exist or is not a directory.
   */
  public static function copyDirectory(string $src, string $dest): void {
    if (!file_exists($src))
      throw new \RuntimeException('Not found directory ' . $src);
    else if (!is_dir($src))
      throw new \RuntimeException($src . ' is not directory');
    self::makeDirectory($dest);
    $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($src, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);
    foreach ($it as $info) {
      if ($info->isDir())
        self::makeDirectory($dest . '/' . $it->getSubPathName());
      else
        self::copyFile($info, $dest . '/' . $it->getSubPathName());
    }
  }

  /**
   * Delete directory or file.
   * ```php
   * use \X\Util\FileHelper;
   * 
   * // Delete all files and folders in "/ path"..
   * FileHelper::delete('/test');
   * 
   * // Delete all files and folders in the "/ path" folder and also in the "/ path" folder.
   * $deleteSelf = true;
   * FileHelper::delete('/test', $deleteSelf);
   * 
   * // Lock before deleting, Locks are disabled by default.
   * $deleteSelf = true;
   * $enableLock = true;
   * FileHelper::delete('/test', $deleteSelf, $enableLock);
   * ```
   * @param string ...$paths Path to be deleted.
   * @return void
   */
  public static function delete(...$paths): void {
    if (is_array(reset($paths)))
      $paths = reset($paths);
    $deleteSelf = true;
    $enableLock = false;
    if (count($paths) > 2 && is_bool(end($paths)) && is_bool($paths[count($paths) - 2])) {
      $enableLock = end($paths);
      unset($paths[count($paths) - 1]);
      $deleteSelf = end($paths);
      unset($paths[count($paths) - 1]);
    } else if (count($paths) > 1 && is_bool(end($paths))) {
      $deleteSelf = end($paths);
      unset($paths[count($paths) - 1]);
    }
    foreach ($paths as $path) {
      if (!file_exists($path))
        continue;
      if (is_file($path))
        unlink($path);
      else {
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($it as $info) {
          if ($info->isDir())
            rmdir($info);
          else {
            if ($enableLock) {
              // 'w' mode truncates file, you don't want to do that yet!
              $fp = fopen($info->getPathname(), 'c');
              flock($fp, LOCK_EX);
              ftruncate($fp, 0);
              fclose($fp);
              unlink($info->getPathname());
            } else
              unlink($info);
          }
        }
        if ($deleteSelf) {
          // Clear the cache of file statuses.
          clearstatcache();
          rmdir($path);
        }
      }
    }
  }

  /**
   * Replace strings in a file using search/replace pairs.
   *
   * @param string $filePath File path to modify.
   * @param array $replacement Associative array of search => replace pairs.
   * @return void
   */
  public static function replace(string $filePath, array $replacement): void {
    $content = file_get_contents($filePath);
    $content = str_replace(array_keys($replacement), array_values($replacement), $content);
    file_put_contents($filePath, $content, LOCK_EX);
  }

  /**
   * Find file.
   * ```php
   * use \X\Util\FileHelper;
   *
   * // Search only image files.
   * FileHelper::find('/img/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
   * ```
   * @param string $pattern Patterns of files to find.
   * @param int $flags GLOB_MARK: Adds a slash (a backslash on Windows) to each directory returned
   *                   GLOB_NOSORT: Return files as they appear in the directory (no sorting). When this flag is not used, the pathnames are sorted alphabetically
   *                   GLOB_NOCHECK: Return the search pattern if no files matching it were found
   *                   GLOB_NOESCAPE: Backslashes do not quote metacharacters
   *                   GLOB_BRACE: Expands {a,b,c} to match 'a', 'b', or 'c'
   *                   GLOB_ONLYDIR: Return only directory entries which match the pattern
   *                   GLOB_ERR: Stop on read errors (like unreadable directories), by default errors are ignored.
   * @return array List of files found.
   */
  public static function find(string $pattern, int $flags=0): array {
    $files = [];
    foreach (glob($pattern, $flags) as $file)
      $files[] = basename($file);
    return $files;
  }

  /**
   * Find a single file matching a glob pattern.
   *
   * @param string $pattern Glob pattern.
   * @return string|null Basename of the first match, or null if none found.
   */
  public static function findOne(string $pattern): ?string {
    $files = self::find($pattern);
    if (empty($files))
      return null;
    return $files[0];
  }

  /**
   * Get a random filename from files matching a glob pattern.
   *
   * @param string $pattern Glob pattern.
   * @return string Basename of a randomly selected file.
   */
  public static function findRandomFileName(string $pattern): string {
    $files = self::find($pattern);
    $key = array_rand($files, 1);
    return $files[$key];
  }

  /**
   * Read the contents of a random file matching a glob pattern.
   *
   * @param string $pattern Glob pattern.
   * @return string File contents of a randomly selected file.
   */
  public static function getRandomFileContent(string $pattern): string {
    return file_get_contents(dirname($pattern) . '/' . self::findRandomFileName($pattern));
  }

  /**
   * Detect MIME type from file content using finfo.
   *
   * @param string $filePath File path.
   * @return string MIME type string (e.g., "image/png").
   * @throws \RuntimeException If file does not exist or is not a file.
   */
  public static function getMimeByConent(string $filePath): string {
    if (!file_exists($filePath))
      throw new \RuntimeException('Not found file ' . $filePath);
    else if (!is_file($filePath))
      throw new \RuntimeException($filePath . ' is not file');
    $finfo = new \finfo(FILEINFO_MIME_TYPE);
    return $finfo->file($filePath);
  }

  /**
   * Validate that a file matches the expected MIME type.
   *
   * @param string $filePath File path.
   * @param string $mime Expected MIME type (e.g., "image/jpeg").
   * @return bool True if the file's detected MIME type matches.
   */
  public static function validationMime(string $filePath, string $mime): bool {
    return self::getMimeByConent($filePath) ===  $mime;
  }

  /**
   * Get directory size (bytes).
   * ```php
   * use \X\Util\FileHelper;
   *
   * // Returns the total size of all files in a directory
   * FileHelper::getDirectorySize('/var/log');
   *
   * // Returns the total size of all files in multiple directories
   * FileHelper::getDirectorySize([ '/var/log/php-fpm' '/var/log/nginx' ]);
   * ```
   * @param string|string[] $dirs Directory path. Multiple can also be specified in an array.
   * @param array $infos (optional) Information on directories found.
   * @return int Directory size in bytes.
   */
  public static function getDirectorySize($dirs, array &$infos=[]): int {
    if (is_string($dirs))
      $dirs = [ $dirs ];
    else if (!is_array($dirs))
      throw new RuntimeException('The file path type only allows strings or arrays of strings');
    $size = 0;
    foreach ($dirs as $dir) {
      $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::SKIP_DOTS));
      foreach($it as $info) {
        $infos[] = $info;
        $size += $info->getSize();
      }
    }
    return $size;
  }

  /**
   * Get the file size converted to the appropriate unit.
   * ```php
   * use \X\Util\FileHelper;
   *
   * FileHelper::humanFilesize('/var/somefile.txt', 0);// 12B
   * FileHelper::humanFilesize('/var/somefile.txt', 4);// 1.1498GB
   * FileHelper::humanFilesize('/var/somefile.txt', 1);// 117.7MB
   * FileHelper::humanFilesize('/var/somefile.txt', 5);// 11.22833TB
   * FileHelper::humanFilesize('/var/somefile.txt', 3);// 1.177MB
   * FileHelper::humanFilesize('/var/somefile.txt');// 120.56KB
   * ```
   * @param string $filePath File Path.
   * @param int $decimals (optional) Decimal digits. Default is 2.
   * @return string File size with units.
   */
  public static function humanFilesize(string $filePath, int $decimals=2): string {
    $bytes = 0;
    if (file_exists($filePath)) {
      clearstatcache(true, $filePath);
      $bytes = filesize($filePath);
    }
    $factor = floor((strlen($bytes) - 1) / 3);
    if ($factor > 0)
      $sz = 'KMGT';
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor - 1] . 'B';
  }
}