<?php
namespace X\Constant;

/**
 * Environment constants for CodeIgniter application.
 *
 * Use these constants to check or set the application environment.
 *
 * Usage:
 * ```php
 * use const X\Constant\ENV_PRODUCTION;
 *
 * if (ENVIRONMENT === ENV_PRODUCTION) {
 *   // Production-only code
 * }
 * ```
 */

/**
 * Production environment identifier.
 *
 * @var string
 */
const ENV_PRODUCTION = 'production';

/**
 * Testing environment identifier.
 *
 * @var string
 */
const ENV_TESTING = 'testing';

/**
 * Development environment identifier.
 *
 * @var string
 */
const ENV_DEVELOPMENT = 'development';
