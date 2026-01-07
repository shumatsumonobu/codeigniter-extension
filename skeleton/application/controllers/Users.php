<?php
use \X\Annotation\Access;
use \X\Util\Logger;

/**
 * Users page controller.
 *
 * Handles user-related page views (login page, user list, personal settings).
 * For API endpoints, see controllers/api/Users.php.
 */
class Users extends AppController {
  protected $model = 'UserModel';

  /**
   * Display login page.
   *
   * @Access(allow_login=false, allow_logoff=true)
   */
  public function login() {
    parent::view('login');
  }

  /**
   * Display user management page.
   *
   * @Access(allow_login=true, allow_logoff=false)
   */
  public function index() {
    parent::view('users');
  }

  /**
   * Display personal profile page.
   *
   * @Access(allow_login=true, allow_logoff=false)
   */
  public function personal() {
    parent
      ::set('user', $this->UserModel->getUserById($_SESSION[SESSION_NAME]['id']))
      ::view('personal');
  }

  /**
   * Display personal profile edit page.
   *
   * @Access(allow_login=true, allow_logoff=false)
   */
  public function editPersonal() {
    parent
      ::set('user', $this->UserModel->getUserById($_SESSION[SESSION_NAME]['id']))
      ::view('edit-personal');
  }
}