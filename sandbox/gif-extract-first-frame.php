<?php
/**
 * ```sh
 * php sandbox/gif-extract-first-frame.php
 * ```
 */
$im = new \Imagick(__DIR__ . '/input/sample-animated.gif');

// Write the first frame as an image.
$im = $im->coalesceImages();
$im->setIteratorIndex(0);
$im->writeImage(__DIR__ . '/output/sample_0.gif');

// Destroy resources.
$im->clear();