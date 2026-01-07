<?php
namespace X\Util;
use \X\Util\FileHelper;
use \X\Util\Logger;

/**
 * Video processing utility class.
 *
 * Provides video Data URL handling including conversion to blob,
 * format detection, and file writing.
 */
final class VideoHelper {
  /**
   * Write video Data URL to a file.
   * ```php
   * use \X\Util\VideoHelper;
   *
   * VideoHelper::writeDataURLToFile('data:video/mp4;base64,iVBOR...', '/tmp', 'sample');
   * VideoHelper::writeDataURLToFile('data:video/mp4;base64,iVBOR...', '/tmp/sample.mp4');
   * ```
   * @param string $dataURL Video Data URL.
   * @param string $dir Destination directory or file path.
   * @param string|null $filename (optional) File name. If omitted, extracted from $dir.
   * @return string Output file name.
   */
  public static function writeDataURLToFile(string $dataURL, string $dir, ?string $filename=null): string {
    if (empty($filename)) {
      $filename = pathinfo($dir, PATHINFO_BASENAME);
      $dir =  pathinfo($dir, PATHINFO_DIRNAME);
    }
    $dir = rtrim($dir, '/')  . '/';
    $blob = self::dataURL2Blob($dataURL, $mime);
    if (empty(pathinfo($filename, PATHINFO_EXTENSION)))
      $filename .= '.' . $mime;
    FileHelper::makeDirectory($dir);
    file_put_contents($dir . $filename, $blob, LOCK_EX);
    return $filename;
  }

  /**
   * Convert video Data URL to blob.
   * @param string $dataURL Video Data URL.
   * @param string|null &$mime (optional) If specified, the MIME type detected from the Data URL is set.
   * @return string Blob data.
   * @throws \RuntimeException If the Data URL is invalid or decoding fails.
   */
  public static function dataURL2Blob(string $dataURL, &$mime=null): string {
    if (!self::isDataURL($dataURL, $mime))
      throw new \RuntimeException('Did not match data URI with video data');
    $blob = base64_decode(substr($dataURL, strpos($dataURL, ',') + 1));
    if ($blob === false)
      throw new \RuntimeException('Base64 decode failed');
    return $blob;
  }

  /**
   * Check if string is a video Data URL.
   * @param string $dataURL String to check.
   * @param string|null &$mime (optional) If specified, the MIME type detected from the Data URL is set.
   * @return bool True if valid video Data URL.
   */
  public static function isDataURL(string $dataURL, &$mime=null): bool {
    if (!preg_match('/^data:video\/(\w+);base64,/', $dataURL, $matches))
      return false;
    $mime = strtolower($matches[1]);
    return true;
  }
}