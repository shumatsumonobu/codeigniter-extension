<?php
use \X\Util\Logger;
use \X\Util\IpUtils;

/**
 * User activity log model.
 *
 * Records user actions such as login, logout, and CRUD operations.
 * Logs are stored in the 'userLog' table with user name, message, and IP address.
 */
class UserLogModel extends \AppModel {
  const TABLE = 'userLog';

  /**
   * Create a user activity log entry.
   *
   * @param string $name User name who performed the action.
   * @param string $message Description of the action.
   * @return void
   */
  public function createUserLog(string $name, string $message) {
    try {
      $this
        ->set('name', $name)
        ->set('message', $message)
        ->set('ip', IpUtils::getClientIpFromXFF())
        ->insert();
    } catch (\Throwable $e) {
      Logger::error($e);
    }
  }

  /**
   * Get paginated log list for DataTables.
   *
   * @param int $offset Starting record offset.
   * @param int $limit Number of records to return.
   * @param string $order Column name to sort by.
   * @param string $direction Sort direction ('asc' or 'desc').
   * @param array|null $search Search filters (name to filter by user).
   * @return array DataTables response with recordsTotal, recordsFiltered, and data.
   */
  public function paginate(int $offset, int $limit, string $order, string $direction, ?array $search): array {
    // Helper function to apply search filters
    function setWhere(CI_Model $model, ?array $search) {
      if (!empty($search['name']))
        $model->where('name', $search['name']);
    }

    // Get filtered data
    setWhere($this, $search);
    $rows = $this
      ->select('name, message, ip, created')
      ->order_by($order, $direction)
      ->limit($limit, $offset)
      ->get()
      ->result_array();
    Logger::debug($this->last_query());

    // Count filtered records
    setWhere($this, $search);
    $recordsFiltered = $this->count_all_results();

    // Count total records
    $recordsTotal = $this->count_all_results();
    return ['recordsTotal' => $recordsTotal, 'recordsFiltered' => $recordsFiltered, 'data' => $rows];
  }

  /**
   * Get unique user names for filter dropdown.
   *
   * @return array List of user names.
   */
  public function getUsernameOptions(): array {
    return $this
      ->select('name')
      ->group_by('name')
      ->get()
      ->result_array();
  }
}
