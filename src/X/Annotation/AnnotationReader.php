<?php
namespace X\Annotation;
use \X\Annotation\Access;
use \X\Util\Logger;

/**
 * Annotation reader for controller methods.
 *
 * Reads and parses Doctrine annotations from controller methods to extract
 * access control information.
 *
 * Usage:
 * ```php
 * $access = AnnotationReader::getAccessibility('UserController', 'edit');
 * if (!$access->allow_logoff) {
 *   redirect('/login');
 * }
 * ```
 */
final class AnnotationReader {
  /**
   * Get Access annotation information from a controller method.
   *
   * Returns the access control settings defined by @Access annotation.
   * If no annotation is found, returns default Access values (all allowed).
   *
   * @param string $class Controller class name (without namespace).
   * @param string $method Method name.
   * @return object{allow_login: bool, allow_logoff: bool, allow_role: string, allow_http: bool} Access settings.
   */
  public static function getAccessibility(string $class, string $method): object {
    $annotation = self::getMethodAnnotation($class, $method, 'Access');
    if (empty($annotation))
      return json_decode(json_encode(new Access()));
    return $annotation;
  }

  /**
   * Get a specific annotation from a method.
   *
   * @param string $class Controller class name.
   * @param string $method Method name.
   * @param string $annotationName Annotation class short name (e.g., "Access").
   * @return object|null Annotation object or null if not found.
   */
  private static function getMethodAnnotation(string $class, string $method, string $annotationName): ?object {
    $annotations = self::reader()->getMethodAnnotations(new \ReflectionMethod(ucfirst($class), $method));
    if (empty($annotations))
      return null;
    foreach ($annotations as $annotation) {
      if ((new \ReflectionClass($annotation))->getShortName() === $annotationName)
        return json_decode(json_encode($annotation));
    }
    return null;
  }

  /**
   * Get singleton Doctrine AnnotationReader instance.
   *
   * @return \Doctrine\Common\Annotations\AnnotationReader Cached reader instance.
   */
  private static function reader(): \Doctrine\Common\Annotations\AnnotationReader {
    static $reader = null;
    if (isset($reader))
      return $reader;
    \Doctrine\Common\Annotations\AnnotationRegistry::registerFile(__DIR__ . '/Access.php');
    $reader = new \Doctrine\Common\Annotations\AnnotationReader();
    return $reader;
  }
}