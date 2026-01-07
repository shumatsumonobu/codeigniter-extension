<?php
namespace X\Database;

/**
 * Abstract database driver class.
 *
 * Base class for database-specific drivers (MySQL, PostgreSQL, etc.).
 * Extends CI_DB_driver for CodeIgniter compatibility.
 */
#[\AllowDynamicProperties]
abstract class Driver extends \CI_DB_driver {}