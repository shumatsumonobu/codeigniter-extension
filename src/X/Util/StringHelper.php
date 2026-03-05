<?php
namespace X\Util;

/**
 * String manipulation utility class.
 *
 * Provides string helper methods including trimming (with full-width space support),
 * empty checking, and text ellipsis truncation.
 */
final class StringHelper {
  /**
   * Trim whitespace from both ends of a string.
   *
   * Handles standard whitespace characters plus full-width spaces.
   *
   * @param string|null $str Input string.
   * @return string Trimmed string.
   */
  public static function trim(?string $str): string {
    return trim($str, " \t\n\r\0\x0B　");
  }

  /**
   * Check if a string is empty after trimming whitespace.
   *
   * @param string|null $str Input string.
   * @return bool True if the trimmed string is empty.
   */
  public static function empty(?string $str): bool {
    return empty(self::trim($str));
  }

  /**
   * Truncate a long string with ellipsis in the middle.
   *
   * Preserves the beginning and end of the string, replacing the
   * middle portion with "..." when the string exceeds the max length.
   *
   * @param string $str Input string.
   * @param int $length Maximum character length. Default is 100.
   * @return string Truncated string, or original if within length.
   */
  public static function ellipsis(string $str, int $length=100): string {
    if (mb_strlen($str) <= $length)
      return $str;
    $dot = '…';
    $length -= mb_strlen($dot);
    $beforeLength = floor($length/2);
    $afterLength = $length - $beforeLength;
    return mb_substr($str, 0, $beforeLength) . '...' . mb_substr($str, -$afterLength);
  }
}