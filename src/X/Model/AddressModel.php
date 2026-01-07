<?php
namespace X\Model;

/**
 * Japanese address lookup model.
 *
 * Provides address lookup by postal code using bundled address data.
 *
 * Usage:
 * ```php
 * $address = $this->AddressModel->getAddressByPostCode('100-0001');
 * // Returns: ['prefectureCode' => '13', 'address' => '東京都 千代田区 千代田']
 * ```
 */
class AddressModel extends Model {
  /**
   * Get address by postal code.
   *
   * @param string $postCode Postal code (format: xxx-xxxx or xxxxxxx).
   * @return array{prefectureCode: string, address: string}|string Address data or empty string if not found.
   */
  public function getAddressByPostCode(string $postCode): array {
    if (!preg_match('/^\d{3}-?\d{4}$/', $postCode))
      return '';
    $postCode = str_replace('-', '', $postCode);
    $addresses = json_decode(file_get_contents(X_APP_PATH . 'Data/address.json'), true);
    if (!isset($addresses[$postCode]))
      return '';
    return [
      'prefectureCode' => $addresses[$postCode][0],
      'address' => implode(' ', array_slice($addresses[$postCode], 1))
    ];
  }
}