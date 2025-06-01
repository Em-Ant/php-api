<?php

/**
 * development router to proxy swagger docs
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if ($uri === '/docs' || strpos($uri, '/docs') === 0) {
  return false;
}

if ($uri === '/openapi.json') {
  $filename = __DIR__ . '/docs/openapi.json';
  if (is_file($filename)) {
    header("Content-Type: application/json");
    readfile($filename);
    exit;
  }
}

require_once __DIR__ . '/public/index.php';
