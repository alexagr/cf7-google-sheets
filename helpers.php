<?php

/**
 * helpers
 */

function cf7_sheets_url($append = '')
{
    return plugins_url($append, __FILE__);
}

function cf7_sheets_view_pagename($step = '')
{
    $view_url_part = '';
    if($step){
        $view_url_part = '_' . $step;
    }

    return admin_url('admin.php?page=cf7-sheets-forms' . $view_url_part);
}

function cf7_sheets_submit($submit_text, $hide_class = "sr-only") { ?>
    <p class="submit <?php echo $hide_class ?>"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo esc_html__($submit_text, 'cf7-sheets') ?>" /></p></form>
<?php
}

function cf7_sheets_message($message, $msg_type = 'info') {
    return "<div id='message' class='alert alert-$msg_type'>$message</div>";
}

function cf7_sheets_log($text)
{
    $base_path = WP_PLUGIN_DIR . '/' . CF7_SHEETS_DIR . '/';
    @mkdir($base_path . 'log');
    file_put_contents($base_path . 'log/log.txt', date( 'Y-m-d H:i:s', time() ) . '    ' . $text . "\n", FILE_APPEND | LOCK_EX);
}

function cf7_sheets_log_exists()
{
    $base_path = WP_PLUGIN_DIR . '/' . CF7_SHEETS_DIR . '/';
    $log_file = $base_path . 'log/log.txt';
    return file_exists($log_file);
}
