<?php
/**
 * CodeIgniter 3 Form Validation Test
 *
 * Usage:
 * ```sh
 * php sandbox/form-validation-test.php
 * ```
 */

// Define constants for CodeIgniter
define('BASEPATH', __DIR__ . '/../demo/vendor/codeigniter/framework/system/');
define('APPPATH', __DIR__ . '/../demo/application/');
define('ENVIRONMENT', 'development');

// Load CodeIgniter's core classes
require_once BASEPATH . 'core/Common.php';
require_once BASEPATH . 'core/Loader.php';
require_once BASEPATH . 'core/Model.php';
require_once BASEPATH . 'libraries/Form_validation.php';

// Create Loader mock
class CI_Loader_Mock {
  public function helper($helpers = []) {
    return TRUE;
  }

  public function library($library = '', $params = NULL, $object_name = NULL) {
    return TRUE;
  }
}

// Create Input mock
class CI_Input_Mock {
  public function method($default = 'post') {
    return strtoupper($default);
  }

  public function post($index = NULL, $xss_clean = NULL) {
    if ($index === NULL) {
      return $_POST;
    }
    return isset($_POST[$index]) ? $_POST[$index] : NULL;
  }
}

// Create minimal CI mock object
class CI_Mock {
  public $load;
  public $lang;
  public $input;

  public function __construct() {
    $this->load = new CI_Loader_Mock();
    $this->lang = new CI_Lang();
    $this->input = new CI_Input_Mock();
  }
}

// CI_Lang class
class CI_Lang {
  public $language = [];

  public function __construct() {
    // Set default form validation error messages
    $this->language = [
      'form_validation_required' => 'The {field} field is required.',
      'form_validation_valid_email' => 'The {field} field must contain a valid email address.',
      'form_validation_min_length' => 'The {field} field must be at least {param} characters in length.',
      'form_validation_max_length' => 'The {field} field cannot exceed {param} characters in length.',
      'form_validation_matches' => 'The {field} field does not match the {param} field.',
      'form_validation_alpha' => 'The {field} field may only contain alphabetical characters.',
      'form_validation_alpha_numeric' => 'The {field} field may only contain alpha-numeric characters.',
      'form_validation_numeric' => 'The {field} field must contain only numbers.',
      'form_validation_integer' => 'The {field} field must contain an integer.',
      'form_validation_in_list' => 'The {field} field must be one of: {param}.',
    ];
  }

  public function load($file, $lang = '') {
    return TRUE;
  }

  public function line($line, $log_errors = TRUE) {
    return isset($this->language[$line]) ? $this->language[$line] : $line;
  }
}

// Create CI instance
$CI = new CI_Mock();
function &get_instance() {
  global $CI;
  return $CI;
}

// Initialize Form_validation
$CI->form_validation = new CI_Form_validation();


// Define form validation helper functions
if (!function_exists('validation_errors')) {
  function validation_errors($prefix = '<p>', $suffix = '</p>') {
    $CI =& get_instance();
    if (isset($CI->form_validation)) {
      return $CI->form_validation->error_string($prefix, $suffix);
    }
    return '';
  }
}

if (!function_exists('form_error')) {
  function form_error($field = '', $prefix = '<p>', $suffix = '</p>') {
    $CI =& get_instance();
    if (isset($CI->form_validation)) {
      return $CI->form_validation->error($field, $prefix, $suffix);
    }
    return '';
  }
}

// Test data
$testData = [
  'username' => '',
  'email' => 'invalid-email',
  'password' => '123',
  'password_confirm' => '456',
];

// Set test data
$CI->form_validation->set_data($testData);

// Set validation rules
$CI->form_validation->set_rules('username', 'Username', 'required|min_length[3]|max_length[20]');
$CI->form_validation->set_rules('email', 'Email', 'required|valid_email');
$CI->form_validation->set_rules('password', 'Password', 'required|min_length[8]');
$CI->form_validation->set_rules('password_confirm', 'Password Confirmation', 'required|matches[password]');

echo "=== Form Validation Test (Invalid Data) ===\n\n";

// Run validation
if ($CI->form_validation->run() === FALSE) {
  echo "Result: FAILED\n\n";
  echo validation_errors();
} else {
  echo "Result: PASSED\n";
}

echo "\n=== Form Validation Test (Valid Data) ===\n\n";

// Test with valid data
$validData = [
  'username' => 'testuser',
  'email' => 'test@example.com',
  'password' => 'password123',
  'password_confirm' => 'password123',
];

// Reset validation and set new data
$CI->form_validation->reset_validation();
$CI->form_validation->set_data($validData);

// Set validation rules again
$CI->form_validation->set_rules('username', 'Username', 'required|min_length[3]|max_length[20]');
$CI->form_validation->set_rules('email', 'Email', 'required|valid_email');
$CI->form_validation->set_rules('password', 'Password', 'required|min_length[8]');
$CI->form_validation->set_rules('password_confirm', 'Password Confirmation', 'required|matches[password]');

// Run validation again
if ($CI->form_validation->run() === FALSE) {
  echo "Result: FAILED\n\n";
  echo validation_errors();
} else {
  echo "Result: PASSED\n";
}

echo "\n=== Checkbox Array Validation Test ===\n\n";

// Test checkbox array validation
$checkboxData = [
  'colors' => ['red', 'blue'], // Valid: red and blue are in the allowed list
];

echo "Input: [" . implode(', ', $checkboxData['colors']) . "]\n";
echo "Allowed: [red, blue, green]\n\n";

$CI->form_validation->reset_validation();
$CI->form_validation->set_data($checkboxData);
$CI->form_validation->set_rules('colors[]', 'Colors', 'required|in_list[red,blue,green]');

if ($CI->form_validation->run() === FALSE) {
  echo "Result: FAILED\n\n";
  echo validation_errors();
} else {
  echo "Result: PASSED\n";
}

echo "\n=== Checkbox Array Validation Test (Invalid) ===\n\n";

// Test with invalid checkbox value
$invalidCheckboxData = [
  'colors' => ['red', 'yellow'], // Invalid: yellow is not in the allowed list
];

echo "Input: [" . implode(', ', $invalidCheckboxData['colors']) . "]\n";
echo "Allowed: [red, blue, green]\n\n";

$CI->form_validation->reset_validation();
$CI->form_validation->set_data($invalidCheckboxData);
$CI->form_validation->set_rules('colors[]', 'Colors', 'required|in_list[red,blue,green]');

if ($CI->form_validation->run() === FALSE) {
  echo "Result: FAILED\n\n";
  echo validation_errors();
} else {
  echo "Result: PASSED\n";
}

echo "\n=== Checkbox Array Validation Test (Empty) ===\n\n";

// Test with no checkbox selected (no 'colors' key at all)
$emptyCheckboxData = [];

echo "Input: []\n";
echo "Allowed: [red, blue, green]\n\n";

$CI->form_validation->reset_validation();
$CI->form_validation->set_data($emptyCheckboxData);
$CI->form_validation->set_rules('colors[]', 'Colors', 'required|in_list[red,blue,green]');

if ($CI->form_validation->run() === FALSE) {
  echo "Result: FAILED\n\n";
  echo validation_errors();
} else {
  echo "Result: PASSED\n";
}
