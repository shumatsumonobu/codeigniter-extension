<?php
/**
 * ```sh
 * php sandbox/gif-count-frames.php
 * ```
 */
$im = new \Imagick(__DIR__ . '/input/sample-animated.gif');
$frameCount = $im->getNumberImages();
echo '$frameCount=' . $frameCount . PHP_EOL;