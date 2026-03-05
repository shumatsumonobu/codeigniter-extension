<?php
namespace X\Util;
use \X\Util\FileHelper;
use \X\Util\Loader;
use \X\Util\Logger;

/**
 * Twig template engine wrapper class.
 *
 * Provides Twig-based template rendering with CodeIgniter integration,
 * cache busting for assets, and global variables (baseUrl, session, action).
 */
final class Template {
  /**
   * Twig_Environment instance.
   * @var \Twig_Environment
   */
  private $engine = null;

  /**
   * Initialize Template engine.
   *
   * Automatically registers the `cache_busting()` function and global
   * variables (`baseUrl`, `session`, `action`) in the Twig environment.
   *
   * @param array{
   *   paths?: string[],
   *   environment?: array{cache?: string|false, debug?: bool, autoescape?: string|false},
   *   lexer?: array{tag_comment?: array, tag_block?: array, tag_variable?: array, interpolation?: array}
   * } $options Configuration options:
   *   - `paths`: Template directory paths. Default is [VIEWPATH].
   *   - `environment.cache`: Compiled template cache path. Default from config `cache_templates`.
   *   - `environment.debug`: Enable debug mode. Default is true for non-production.
   *   - `environment.autoescape`: Auto-escaping strategy. Default is "html".
   *   - `lexer.tag_comment`: Comment delimiters. Default is ['{#', '#}'].
   *   - `lexer.tag_block`: Block delimiters. Default is ['{%', '%}'].
   *   - `lexer.tag_variable`: Variable delimiters. Default is ['{{', '}}'].
   *   - `lexer.interpolation`: Interpolation delimiters. Default is ['#{', '}'].
   */
  public function __construct(array $options=[]) {
    $cache = Loader::config('config', 'cache_templates');
    if (!empty($cache))
      FileHelper::makeDirectory($cache);
    $options = array_merge([
      'paths' => [ \VIEWPATH ],
      'environment' => [
        'cache' => !empty($cache) ? $cache : false,
        'debug' => \ENVIRONMENT !== 'production',
        'autoescape' => 'html',
      ],
      'lexer' => [
        'tag_comment' => ['{#','#}'],
        'tag_block' => ['{%','%}'],
        'tag_variable' => ['{{','}}'],
        'interpolation' => ['#{','}'],
      ],
    ], $options);
    $this->engine = new \Twig_Environment(new \Twig_Loader_Filesystem($options['paths']), $options['environment']);
    $this->engine->addFunction(new \Twig_SimpleFunction('cache_busting',
      /**
       * This function generates a new file path with the last date of filechange to support better better client caching via Expires header:
       * e.g. <link rel="stylesheet" href="{{cache_busting('css/style.css')}}">
       *       css/style.css -> css/style.css?1428423235
       */
      function (string $filePath) {
        if (!file_exists(FCPATH . $filePath))
          return \base_url($filePath);
        $modified = filemtime($_SERVER['DOCUMENT_ROOT'] . '/' . $filePath);
        if (!$modified)
          $modified = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        return \base_url($filePath) . '?' . $modified;
        // return preg_replace('{\\.([^./]+)$}', ".$modified.\$1", $filePath);
      }
    ));
    $baseUrl = \base_url();
    $this->engine->addGlobal('baseUrl', $baseUrl);
    $this->engine->addGlobal('session', $_SESSION ?? null);
    $CI =& get_instance();
    $this->engine->addGlobal('action', ($CI->router->directory ?? '') . $CI->router->class . '/' . $CI->router->method);
    $this->engine->setLexer(new \Twig_Lexer($this->engine, $options['lexer']));
  }

  /**
   * Render a Twig template and return the compiled output.
   *
   * @param string $templatePath Template path relative to the template directory (without extension).
   * @param array $params Template variables for interpolation.
   * @param string $ext Template file extension. Default is "html".
   * @return string Rendered template content.
   */
  public function load(string $templatePath, array $params=[], string $ext='html'): string {
    return $this->engine->render($templatePath . '.' . $ext, $params);
  }
}