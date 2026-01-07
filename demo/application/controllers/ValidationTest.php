<?php
use \X\Util\Logger;

/**
 * Form validation test controller.
 *
 * Provides an interactive form for testing CodeIgniter's form validation functionality.
 */
class ValidationTest extends AppController {

  public function __construct() {
    parent::__construct();
  }

  /**
   * Display validation test form page.
   *
   * @return void
   */
  public function index() {
    $this->view('validation-test');
  }

  /**
   * Process form submission and validate input (POST).
   *
   * Validates: username, email, password, password_confirm, colors
   * Response: JSON with success status, errors array or validated data.
   *
   * @return void
   */
  public function submit() {
    // Set validation rules
    $this->form_validation->set_rules('username', 'Username', 'required|min_length[3]|max_length[20]');
    $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
    $this->form_validation->set_rules('password', 'Password', 'required|min_length[8]');
    $this->form_validation->set_rules('password_confirm', 'Password Confirmation', 'required|matches[password]');
    $this->form_validation->set_rules('colors[]', 'Colors', 'required|in_list[red,blue,green]');

    // Run validation
    if ($this->form_validation->run() === FALSE) {
      // Validation failed - return errors
      parent
        ::set([
          'success' => false,
          'errors' => [
            'username' => form_error('username', '', ''),
            'email' => form_error('email', '', ''),
            'password' => form_error('password', '', ''),
            'password_confirm' => form_error('password_confirm', '', ''),
            'colors' => form_error('colors[]', '', ''),
          ]
        ])
        ::json();
    } else {
      // Validation passed - return submitted data
      parent
        ::set([
          'success' => true,
          'message' => 'Form validation passed successfully!',
          'data' => [
            'username' => $this->input->post('username'),
            'email' => $this->input->post('email'),
            'colors' => $this->input->post('colors'),
          ]
        ])
        ::json();
    }
  }
}
