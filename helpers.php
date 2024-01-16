<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Helpers
 */

function cf7_sheets_url($append = '')
{
    return plugins_url($append, __FILE__);
}

function cf7_sheets_log($text)
{
    $base_path = WP_PLUGIN_DIR . '/' . CF7_SHEETS_DIR . '/';
    @mkdir($base_path . 'log');
    error_log(gmdate( 'Y-m-d H:i:s', time() ) . '    ' . $text . PHP_EOL, 3, $base_path . 'log/log.txt');
}

function cf7_sheets_log_exists()
{
    $base_path = WP_PLUGIN_DIR . '/' . CF7_SHEETS_DIR . '/';
    $log_file = $base_path . 'log/log.txt';
    return file_exists($log_file);
}
