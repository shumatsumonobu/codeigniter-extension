<?php
use \X\Annotation\Access;
use \X\Util\Logger;
use \X\Util\GoogleAuthenticator;

/**
 * Users API controller.
 *
 * RESTful API endpoints for user authentication and management.
 * All responses are JSON format.
 */
class Users extends AppController {
  protected $model = [
    'UserModel',
    'UserLogModel'
  ];

  /**
   * Authenticate user (POST).
   *
   * Request body: { email, password }
   * Response:
   *   - { success: true } on success without MFA (MFA not enabled)
   *   - { success: true, mfa_required: true } if MFA verification needed
   *   - { success: true, mfa_setup_prompt: true } if MFA not setup (first login)
   *   - { success: false } on failure
   *
   * @Access(allow_login=false, allow_logoff=true)
   */
  public function login() {
    try {
      $this->form_validation
        ->set_data($this->input->post())
        ->set_rules('email', 'email', 'required')
        ->set_rules('password', 'password', 'required');
      if (!$this->form_validation->run())
        throw new \RuntimeException('Invalid parameter');

      if (!$this->UserModel->login($this->input->post('email'), $this->input->post('password')))
        return parent::set(['success' => false])::json();

      // Check if MFA is enabled for this user
      $userId = $_SESSION[SESSION_NAME]['id'];
      $userName = $_SESSION[SESSION_NAME]['name'];

      if ($this->UserModel->isMfaEnabled($userId)) {
        // MFA is required - store user ID temporarily and clear session
        $_SESSION['mfa_pending_user_id'] = $userId;
        unset($_SESSION[SESSION_NAME]);

        return parent::set([
          'success' => true,
          'mfa_required' => true
        ])::json();
      }

      // MFA not enabled - prompt for setup (user can skip)
      $this->UserLogModel->createUserLog($userName, 'Logged in.');
      parent::set([
        'success' => true,
        'mfa_setup_prompt' => true  // Frontend will redirect to MFA setup (skippable)
      ])::json();
    } catch (\Throwable $e) {
      Logger::error($e);
      parent::error($e->getMessage(), 400);
    }
  }

  /**
   * Logout user and redirect to home.
   *
   * @Access(allow_login=true, allow_logoff=false)
   */
  public function logout() {
    try {
      $this->UserLogModel->createUserLog($_SESSION[SESSION_NAME]['name'], 'Logged out.');
      $this->UserModel->logout();
      redirect('/');
    } catch (\Throwable $e) {
      Logger::error($e);
      parent::error($e->getMessage(), 400);
    }
  }

  /**
   * Query users with pagination (GET).
   *
   * Query params: start, length, order, dir, search[keyword], draw
   * Response: DataTables compatible JSON.
   *
   * @Access(allow_login=true, allow_logoff=false, allow_role="admin,member")
   */
  public function query() {
    try {
      $this->form_validation
        ->set_data($this->input->get())
        ->set_rules('start', 'start', 'required|is_natural')
        ->set_rules('length', 'length', 'required|is_natural')
        ->set_rules('order', 'order', 'required')
        ->set_rules('dir', 'dir', 'required|in_list[asc,desc]');
      if (!$this->form_validation->run())
        throw new \RuntimeException('Invalid parameter');
      $loginUserId = $_SESSION[SESSION_NAME]['id'];
      $data = $this->UserModel->paginate(
        $this->input->get('start'),
        $this->input->get('length'),
        $this->input->get('order'),
        $this->input->get('dir'),
        $this->input->get('search'),
        $loginUserId
      );
      $data['draw'] = $this->input->get('draw');
      parent::set($data)::json();
    } catch (\Throwable $e) {
      Logger::error($e);
      parent::error($e->getMessage(), 400);
    }
  }

  /**
   * Create new user (POST).
   *
   * Request body: { user: { role, email, name, password, icon } }
   * Response: true on success.
   *
   * @Access(allow_login=true, allow_logoff=false, allow_role="admin,member")
   */
  public function post() {
    try {
      $set = $this->input->post();
      $this->formValidation($set, 'create');
      $this->UserModel->createUser($set['user']);
      $this->UserLogModel->createUserLog($_SESSION[SESSION_NAME]['name'], 'Created user ' . $set['user']['name']);
      parent::set(true)::json();
    } catch (\Throwable $e) {
      Logger::error($e);
      parent::error($e->getMessage(), 400);
    }
  }

  /**
   * Check if email exists (GET).
   *
   * Query params: user[email], excludeUserId (optional)
   * Response: { valid: true/false }
   *
   * @Access(allow_login=true, allow_logoff=false, allow_role="admin,member")
   */
  public function emailExists() {
    try {
      $exists = $this->UserModel->emailExists(
        $this->input->get('user')['email'],
        $this->input->get('excludeUserId') ?? null);
      parent::set(['valid' => !$exists])::json();
    } catch (\Throwable $e) {
      Logger::error($e);
      parent::error($e->getMessage(), 400);
    }
  }

  /**
   * Get user by ID (GET).
   *
   * @param int $userId User ID.
   * Response: User data object.
   *
   * @Access(allow_login=true, allow_logoff=false, allow_role="admin,member")
   */
  public function get(int $userId) {
    try {
      parent::set($this->UserModel->getUserById($userId))::json();
    } catch (\Throwable $e) {
      Logger::error($e);
      parent::error($e->getMessage(), 400);
    }
  }

  /**
   * Update user by ID (PUT).
   *
   * @param int $userId User ID.
   * Request body: { user: { email, name, role, password, icon, changePassword } }
   * Response: true on success, { error: 'userNotFound' } if user not found.
   *
   * @Access(allow_login=true, allow_logoff=false, allow_role="admin,member")
   */
  public function put(int $userId) {
    try {
      $set = $this->input->put();
      $this->formValidation($set, 'update');
      $this->UserModel->updateUser($userId, $set['user']);
      $this->UserLogModel->createUserLog($_SESSION[SESSION_NAME]['name'], 'Updated User ' . $set['user']['name']);
      parent::set(true)::json();
    } catch (UserNotFoundException $e) {
      parent::set('error', 'userNotFound')::json();
    } catch (\Throwable $e) {
      Logger::error($e);
      parent::error($e->getMessage(), 400);
    }
  }

  /**
   * Delete user by ID (DELETE).
   *
   * @param int $userId User ID.
   * Response: true on success, { error: 'userNotFound' } if user not found.
   *
   * @Access(allow_login=true, allow_logoff=false, allow_role="admin,member")
   */
  public function delete(int $userId) {
    try {
      // Get user name for logging before deletion
      $userName = $this->UserModel
        ->select('name')
        ->where('id', $userId)
        ->get()
        ->row_array()['name'] ?? null;
      $this->UserModel->deleteUser($userId);
      $this->UserLogModel->createUserLog($_SESSION[SESSION_NAME]['name'], "User {$userName} is deleted");
      parent::set(true)::json();
    } catch (UserNotFoundException $e) {
      parent::set('error', 'userNotFound')::json();
    } catch (\Throwable $e) {
      Logger::error($e);
      parent::error($e->getMessage(), 400);
    }
  }

  /**
   * Validate password security requirements (GET).
   *
   * Query params: user[password]
   * Response: { valid: true/false }
   *
   * @Access(allow_login=true, allow_logoff=false)
   */
  public function passwordSecurityCheck() {
    try {
      $set = $this->input->get();
      $this->form_validation
        ->set_data($set)
        ->set_rules('user[password]', 'user[password]', 'required');
      if (!$this->form_validation->run())
        throw new \RuntimeException('Invalid parameter');
      parent
        ::set('valid', $this->UserModel->passwordSecurityCheck($_SESSION[SESSION_NAME]['id'], $set['user']['password']))
        ::json();
    } catch (\Throwable $e) {
      Logger::error($e);
      parent::error($e->getMessage(), 400);
    }
  }

  /**
   * Update current user's profile (PUT).
   *
   * Request body: { user: { email, name, password, icon, changePassword } }
   * Response: true on success, { error: 'userNotFound' } if user not found.
   *
   * @Access(allow_login=true, allow_logoff=false)
   */
  public function updateProfile() {
    try {
      $set = $this->input->put();
      $this->formValidation($set, 'updateProfile');
      $this->UserModel->updateUser($_SESSION[SESSION_NAME]['id'], $set['user']);
      $this->UserLogModel->createUserLog($_SESSION[SESSION_NAME]['name'], 'Updated profile');
      parent::set(true)::json();
    } catch (UserNotFoundException $e) {
      parent::set('error', 'userNotFound')::json();
    } catch (\Throwable $e) {
      Logger::error($e);
      parent::error($e->getMessage(), 400);
    }
  }

  // =========================================
  // MFA (Multi-Factor Authentication) Endpoints
  // =========================================

  /**
   * Get MFA status for current user (GET).
   *
   * Response: { enabled: bool, has_secret: bool, backup_codes_remaining: int }
   *
   * @Access(allow_login=true, allow_logoff=false)
   */
  public function mfaStatus() {
    try {
      $userId = $_SESSION[SESSION_NAME]['id'];
      $status = $this->UserModel->getMfaStatus($userId);
      parent::set($status)::json();
    } catch (\Throwable $e) {
      Logger::error($e);
      parent::error($e->getMessage(), 400);
    }
  }

  /**
   * Initialize MFA setup (POST).
   *
   * Response: { secret, qr_code_url, otpauth_url, backup_codes }
   *
   * @Access(allow_login=true, allow_logoff=false)
   */
  public function mfaSetup() {
    try {
      $userId = $_SESSION[SESSION_NAME]['id'];
      $email = $_SESSION[SESSION_NAME]['email'];

      // Check if MFA is already enabled
      if ($this->UserModel->isMfaEnabled($userId)) {
        return parent::set(['error' => 'MFA already enabled'])::json();
      }

      $setup = $this->UserModel->initMfaSetup($userId, $email);

      // Store backup hashes in session temporarily until setup is verified
      $_SESSION['mfa_setup_hashes'] = $setup['backup_hashes'];

      parent::set([
        'secret' => $setup['secret'],
        'qr_code_url' => $setup['qr_code_url'],
        'otpauth_url' => $setup['otpauth_url'],
        'backup_codes' => $setup['backup_codes']
      ])::json();
    } catch (\Throwable $e) {
      Logger::error($e);
      parent::error($e->getMessage(), 400);
    }
  }

  /**
   * Verify and complete MFA setup (POST).
   *
   * Request body: { code: string }
   * Response: { success: bool }
   *
   * @Access(allow_login=true, allow_logoff=false)
   */
  public function mfaVerifySetup() {
    try {
      $this->form_validation
        ->set_data($this->input->post())
        ->set_rules('code', 'code', 'required|numeric|exact_length[6]');
      if (!$this->form_validation->run())
        throw new \RuntimeException('Invalid parameter');

      $userId = $_SESSION[SESSION_NAME]['id'];
      $code = $this->input->post('code');
      $secret = $this->UserModel->getMfaSecret($userId);

      if (empty($secret)) {
        return parent::set(['success' => false, 'error' => 'Setup not initialized'])::json();
      }

      // Verify the TOTP code
      if (!GoogleAuthenticator::verifyCode($secret, $code)) {
        return parent::set(['success' => false, 'error' => 'Invalid code'])::json();
      }

      // Complete setup with stored backup hashes
      $backupHashes = $_SESSION['mfa_setup_hashes'] ?? [];
      $this->UserModel->completeMfaSetup($userId, $backupHashes);
      unset($_SESSION['mfa_setup_hashes']);

      $this->UserLogModel->createUserLog($_SESSION[SESSION_NAME]['name'], 'Enabled MFA');
      parent::set(['success' => true])::json();
    } catch (\Throwable $e) {
      Logger::error($e);
      parent::error($e->getMessage(), 400);
    }
  }

  /**
   * Verify MFA code during login (POST).
   *
   * Request body: { code: string }
   * Response: { success: bool, backup_codes_remaining?: int }
   *
   * @Access(allow_login=true, allow_logoff=true)
   */
  public function mfaVerifyLogin() {
    try {
      $this->form_validation
        ->set_data($this->input->post())
        ->set_rules('code', 'code', 'required');
      if (!$this->form_validation->run())
        throw new \RuntimeException('Invalid parameter');

      // Check for pending MFA verification
      if (empty($_SESSION['mfa_pending_user_id'])) {
        return parent::set(['success' => false, 'error' => 'No pending MFA verification'])::json();
      }

      $userId = $_SESSION['mfa_pending_user_id'];
      $code = $this->input->post('code');

      $result = $this->UserModel->verifyMfaCode($userId, $code);

      if ($result['valid']) {
        // Complete login - restore full session
        $user = $this->UserModel->getUserById($userId);
        $_SESSION[SESSION_NAME] = $user;
        unset($_SESSION['mfa_pending_user_id']);

        $this->UserLogModel->createUserLog($user['name'], 'Logged in with MFA');

        $response = ['success' => true];
        if (isset($result['backup_codes_remaining'])) {
          $response['backup_codes_remaining'] = $result['backup_codes_remaining'];
        }
        parent::set($response)::json();
      } else {
        parent::set(['success' => false, 'error' => 'Invalid MFA code'])::json();
      }
    } catch (\Throwable $e) {
      Logger::error($e);
      parent::error($e->getMessage(), 400);
    }
  }

  /**
   * Disable MFA for current user (POST).
   *
   * Request body: { code: string }
   * Response: { success: bool }
   *
   * @Access(allow_login=true, allow_logoff=false)
   */
  public function mfaDisable() {
    try {
      Logger::debug('mfaDisable called. POST data: ' . json_encode($this->input->post()));

      $this->form_validation
        ->set_data($this->input->post())
        ->set_rules('code', 'code', 'required');
      if (!$this->form_validation->run()) {
        Logger::debug('Form validation failed');
        throw new \RuntimeException('Invalid parameter');
      }

      $userId = $_SESSION[SESSION_NAME]['id'];
      $code = $this->input->post('code');
      Logger::debug("User ID: $userId, Code: $code");

      // Verify current MFA code before disabling
      $result = $this->UserModel->verifyMfaCode($userId, $code);
      Logger::debug('Verification result: ' . json_encode($result));

      if (!$result['valid']) {
        Logger::debug('MFA code invalid');
        return parent::set(['success' => false, 'error' => 'Invalid MFA code'])::json();
      }
      Logger::debug('MFA code valid, disabling MFA');

      $this->UserModel->disableMfa($userId);
      $this->UserLogModel->createUserLog($_SESSION[SESSION_NAME]['name'], 'Disabled MFA');
      parent::set(['success' => true])::json();
    } catch (\Throwable $e) {
      Logger::error($e);
      parent::error($e->getMessage(), 400);
    }
  }

  /**
   * Regenerate backup codes (POST).
   *
   * Request body: { code: string }
   * Response: { success: bool, backup_codes: array }
   *
   * @Access(allow_login=true, allow_logoff=false)
   */
  public function mfaRegenerateBackupCodes() {
    try {
      $this->form_validation
        ->set_data($this->input->post())
        ->set_rules('code', 'code', 'required');
      if (!$this->form_validation->run())
        throw new \RuntimeException('Invalid parameter');

      $userId = $_SESSION[SESSION_NAME]['id'];
      $code = $this->input->post('code');

      // Verify current MFA code
      $result = $this->UserModel->verifyMfaCode($userId, $code);

      if (!$result['valid']) {
        return parent::set(['success' => false, 'error' => 'Invalid MFA code'])::json();
      }

      $backupCodes = $this->UserModel->regenerateBackupCodes($userId);
      $this->UserLogModel->createUserLog($_SESSION[SESSION_NAME]['name'], 'Regenerated backup codes');

      parent::set([
        'success' => true,
        'backup_codes' => $backupCodes
      ])::json();
    } catch (\Throwable $e) {
      Logger::error($e);
      parent::error($e->getMessage(), 400);
    }
  }

  /**
   * Request MFA recovery (POST).
   *
   * Request body: { email: string }
   * Response: { success: bool, message: string }
   *
   * @Access(allow_login=false, allow_logoff=true)
   */
  public function mfaRequestRecovery() {
    try {
      $this->form_validation
        ->set_data($this->input->post())
        ->set_rules('email', 'email', 'required|valid_email');
      if (!$this->form_validation->run())
        throw new \RuntimeException('Invalid parameter');

      $email = $this->input->post('email');

      // Find user by email
      $user = $this->UserModel
        ->select('id, name, mfa_enabled')
        ->where('email', $email)
        ->get()
        ->row_array();

      if (empty($user) || !$user['mfa_enabled']) {
        // Don't reveal if user exists or has MFA
        return parent::set([
          'success' => true,
          'message' => 'If the email exists and has MFA enabled, a recovery link will be sent.'
        ])::json();
      }

      $token = $this->UserModel->createRecoveryToken($user['id']);

      // In production, send this via email
      // For demo, we'll return it (REMOVE IN PRODUCTION!)
      Logger::info("MFA Recovery token for {$email}: {$token}");

      parent::set([
        'success' => true,
        'message' => 'Recovery email sent.',
        // DEMO ONLY - Remove in production!
        'demo_token' => $token
      ])::json();
    } catch (\Throwable $e) {
      Logger::error($e);
      parent::error($e->getMessage(), 400);
    }
  }

  /**
   * Verify MFA recovery token (POST).
   *
   * Request body: { email: string, token: string }
   * Response: { success: bool }
   *
   * @Access(allow_login=false, allow_logoff=true)
   */
  public function mfaVerifyRecovery() {
    try {
      $this->form_validation
        ->set_data($this->input->post())
        ->set_rules('email', 'email', 'required|valid_email')
        ->set_rules('token', 'token', 'required');
      if (!$this->form_validation->run())
        throw new \RuntimeException('Invalid parameter');

      $email = $this->input->post('email');
      $token = $this->input->post('token');

      // Find user by email
      $user = $this->UserModel
        ->select('id, name')
        ->where('email', $email)
        ->get()
        ->row_array();

      if (empty($user)) {
        return parent::set(['success' => false, 'error' => 'Invalid recovery request'])::json();
      }

      if ($this->UserModel->verifyRecoveryToken($user['id'], $token)) {
        $this->UserLogModel->createUserLog($user['name'], 'Recovered account via MFA reset');
        parent::set(['success' => true, 'message' => 'MFA has been disabled. You can now login.'])::json();
      } else {
        parent::set(['success' => false, 'error' => 'Invalid or expired recovery token'])::json();
      }
    } catch (\Throwable $e) {
      Logger::error($e);
      parent::error($e->getMessage(), 400);
    }
  }

  /**
   * Validate user form data.
   *
   * @param array $set Form data to validate.
   * @param string $mode Validation mode: 'create', 'update', or 'updateProfile'.
   * @return void
   * @throws \RuntimeException If validation fails.
   */
  private function formValidation(array $set, string $mode) {
    $this->form_validation
      ->set_data($set)
      ->set_rules('user[email]', 'user[email]', 'required')
      ->set_rules('user[name]', 'user[name]', 'required')
      ->set_rules('user[icon]', 'user[icon]', 'required|regex_match[/^data:image\/[a-z]+;base64,[a-zA-Z0-9\/\+=]+$/]');
    if ($mode === 'create' || $mode === 'update')
      $this->form_validation->set_rules('user[role]', 'user[role]', 'required|in_list[admin,member]');
    if ($mode === 'create' || !empty($set['user']['changePassword']))
      $this->form_validation->set_rules('user[password]', 'user[password]', 'required|min_length[8]|max_length[128]');
    if (!$this->form_validation->run()) {
      Logger::debug('error=', $this->form_validation->error_array());
      throw new \RuntimeException('Invalid parameter');
    }
  }
}
