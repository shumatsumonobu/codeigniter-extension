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
   * @param string $dataURL Video Data URL (e.g., "data:video/mp4;base64,...").
   * @param string $dir Destination directory, or full file path when $filename is omitted.
   * @param string|null $filename Output filename. If omitted, extracted from $dir. Extension auto-appended if missing.
   * @return string Name of the output file.
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
   * Convert a video Data URL to binary blob data.
   *
   * @param string $dataURL Video Data URL string.
   * @param string|null &$mime Receives the detected MIME subtype (e.g., "mp4", "webm").
   * @return string Decoded binary data.
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
   * Check if a string is a valid video Data URL.
   *
   * @param string $dataURL String to validate.
   * @param string|null &$mime Receives the detected MIME subtype (e.g., "mp4", "webm").
   * @return bool True if the string matches the video Data URL format.
   */
  public static function isDataURL(string $dataURL, &$mime=null): bool {
    if (!preg_match('/^data:video\/(\w+);base64,/', $dataURL, $matches))
      return false;
    $mime = strtolower($matches[1]);
    return true;
  }
}