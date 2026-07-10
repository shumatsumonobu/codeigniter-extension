<?php
require __DIR__ . '/../vendor/autoload.php';

if (!function_exists('log_message')) {
  function log_message($level, $message) {}
}