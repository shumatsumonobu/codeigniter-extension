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
   * Display MFA verification page (during login).
   * URL: /users/mfa-verify
   *
   * @Access(allow_login=false, allow_logoff=true)
   */
  public function mfa_verify() {
    // Redirect if no pending MFA verification
    if (empty($_SESSION['mfa_pending_user_id'])) {
      redirect('/users/login');
      return;
    }
    parent::view('mfa-verify');
  }

  /**
   * Display MFA setup page.
   * URL: /users/mfa-setup
   *
   * @Access(allow_login=true, allow_logoff=false)
   */
  public function mfa_setup() {
    $userId = $_SESSION[SESSION_NAME]['id'];
    $email = $_SESSION[SESSION_NAME]['email'];

    // Check if MFA is already enabled
    if ($this->UserModel->isMfaEnabled($userId)) {
      redirect('/users/mfa-settings');
      return;
    }

    // Handle POST (code verification)
    if ($this->input->method() === 'post') {
      $code = $this->input->post('code');
      $secret = $this->UserModel->getMfaSecret($userId);

      if (!empty($secret) && !empty($code)) {
        // Verify the code
        if (\X\Util\GoogleAuthenticator::verifyCode($secret, $code)) {
          // Complete setup
          $backupHashes = $_SESSION['mfa_setup_hashes'] ?? [];
          $this->UserModel->completeMfaSetup($userId, $backupHashes);
          unset($_SESSION['mfa_setup_hashes']);

          // Redirect to success page with backup codes
          $_SESSION['mfa_setup_complete'] = true;
          $_SESSION['mfa_backup_codes'] = $_SESSION['mfa_setup_backup_codes'] ?? [];
          redirect('/users/mfa-complete');
          return;
        }
      }

      // Code verification failed
      parent::set('error', 'Invalid code. Please try again.');
    }

    // Generate MFA setup data (or get existing)
    $setup = $this->UserModel->initMfaSetup($userId, $email);

    // Store backup data in session
    $_SESSION['mfa_setup_hashes'] = $setup['backup_hashes'];
    $_SESSION['mfa_setup_backup_codes'] = $setup['backup_codes'];

    parent
      ::set('setup', $setup)
      ::view('mfa-setup');
  }

  /**
   * Display MFA setup complete page with backup codes.
   * URL: /users/mfa-complete
   *
   * @Access(allow_login=true, allow_logoff=false)
   */
  public function mfa_complete() {
    if (empty($_SESSION['mfa_setup_complete'])) {
      redirect('/users/mfa-setup');
      return;
    }

    $backupCodes = $_SESSION['mfa_backup_codes'] ?? [];
    unset($_SESSION['mfa_setup_complete']);
    unset($_SESSION['mfa_backup_codes']);

    parent
      ::set('backup_codes', $backupCodes)
      ::view('mfa-complete');
  }

  /**
   * Display MFA settings/management page.
   * URL: /users/mfa-settings
   *
   * @Access(allow_login=true, allow_logoff=false)
   */
  public function mfa_settings() {
    $userId = $_SESSION[SESSION_NAME]['id'];
    $mfaStatus = $this->UserModel->getMfaStatus($userId);

    parent
      ::set('mfa', $mfaStatus)
      ::view('mfa-settings');
  }

  /**
   * Display MFA recovery page.
   * URL: /users/mfa-recovery
   *
   * @Access(allow_login=false, allow_logoff=true)
   */
  public function mfa_recovery() {
    parent::view('mfa-recovery');
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
    $userId = $_SESSION[SESSION_NAME]['id'];
    parent
      ::set('user', $this->UserModel->getUserById($userId))
      ::set('mfa', $this->UserModel->getMfaStatus($userId))
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
