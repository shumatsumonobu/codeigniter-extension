<?php
namespace X\Annotation;
use \Doctrine\Common\Annotations\Annotation;

/**
 * Access control annotation for controller methods.
 *
 * Define access permissions for controller methods based on login state, user role, and request type.
 *
 * Usage:
 * ```php
 * /**
 *  * @Access(allow_login=true, allow_logoff=false, allow_role="admin,user")
 *  *\/
 * public function dashboard() {}
 * ```
 *
 * @Annotation
 */
class Access {
  /**
   * Allow access for logged-in users.
   *
   * @var bool Set to true to allow, false to deny.
   */
  public $allow_login = true;

  /**
   * Allow access for logged-off (guest) users.
   *
   * @var bool Set to true to allow, false to deny.
   */
  public $allow_logoff = true;

  /**
   * Allowed role names for logged-in users.
   *
   * Multiple roles can be specified with comma separation (e.g., "admin,editor,user").
   * Empty string means all roles are allowed.
   *
   * @var string Comma-separated role names.
   */
  public $allow_role = '';

  /**
   * Allow access from HTTP requests.
   *
   * Set to false to restrict access to CLI only.
   *
   * @var bool Set to true to allow HTTP access, false for CLI only.
   */
  public $allow_http = true;
}