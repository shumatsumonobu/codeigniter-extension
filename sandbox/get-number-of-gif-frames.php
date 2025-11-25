<?php
/**
 * ```sh
 * php sandbox/get-number-of-gif-frames.php
 * ```
 */
$im = new \Imagick(__DIR__ . '/input/animated.gif');
$frameCount = $im->getNumberImages();
echo '$frameCount=' . $frameCount . PHP_EOL;