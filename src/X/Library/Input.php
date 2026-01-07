<?php
namespace X\Library;
use \X\Util\HttpInput;

/**
 * Extended input handling class.
 *
 * Provides additional input methods for PUT and DELETE requests
 * beyond CodeIgniter's built-in input handling.
 *
 * Usage:
 * ```php
 * // In controller
 * $name = $this->input->put('name');
 * $id = $this->input->delete('id');
 * ```
 */
abstract class Input extends \CI_Input {
  /**
   * Fetch an item from PUT request data.
   *
   * @param mixed|null $index Key to fetch from PUT data. Null returns all data.
   * @param bool|null $xssClean Apply XSS filtering if true. Default is null (uses config).
   * @return mixed PUT data value or array of all PUT data.
   */
  public function put($index=null, $xssClean=null) {
    return HttpInput::put($index, $xssClean);
  }

  /**
   * Fetch an item from DELETE request data.
   *
   * @param mixed|null $index Key to fetch from DELETE data. Null returns all data.
   * @param bool|null $xssClean Apply XSS filtering if true. Default is null (uses config).
   * @return mixed DELETE data value or array of all DELETE data.
   */
  public function delete($index=null, $xssClean=null) {
    return parent::input_stream($index, $xssClean);
  }
}