<?php
/**
 * Merokala - Central Configuration (for college projects)
 * Set BASE_PATH to '' if project is at document root (e.g. htdocs/merokala),
 * or '/merokala' if served from http://localhost/merokala/
 */
define('BASE_PATH', '/merokala');

function base_url($path = '') {
    $p = ltrim($path, '/');
    $base = rtrim(BASE_PATH, '/');
    return $base . ($p ? '/' . $p : '');
}
