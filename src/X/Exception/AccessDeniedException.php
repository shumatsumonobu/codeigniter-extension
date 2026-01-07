<?php
namespace X\Exception;

/**
 * Exception thrown when access to a resource is denied.
 *
 * Typically thrown when @Access annotation restrictions are violated
 * (e.g., unauthorized user role, unauthenticated access attempt).
 *
 * Usage:
 * ```php
 * throw new AccessDeniedException('You do not have permission to access this resource');
 * ```
 */
class AccessDeniedException extends \Exception {}