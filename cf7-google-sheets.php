<?php
/**
 *
 * @wordpress-plugin
 * Plugin Name:       Integration with Google Sheets for Contact Form 7
 * Description:       Send your Contact Forms 7 submissions directly to your Google Sheets spreadsheet.
 * Version:           1.3
 * Author:            Alex Agranov
 * Text Domain:       cf7-google-sheets
 * License:           GNU General Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'CF7_SHEETS_VERSION', '1.3' );
define( 'CF7_SHEETS_DIR', 'cf7-google-sheets' );
define( 'CF7_SHEETS_BASE_NAME', plugin_basename(__FILE__) );

// require_once plugin_dir_path( __FILE__ ) . 'lib/vendor/autoload.php';
require_once plugin_dir_path( __FILE__ ) . 'helpers.php';
require_once plugin_dir_path( __FILE__ ) . 'admin-form.php';
require_once plugin_dir_path( __FILE__ ) . 'client.php';
require_once plugin_dir_path( __FILE__ ) . 'service.php';
require_once plugin_dir_path( __FILE__ ) . 'cfdb7-plugin.php';


function cf7_sheets_init() {
    $admin_form = new CF7_Sheets_Admin_Form();
    $admin_form->init();

    $service = new CF7_Sheets_Service();
    $service->init();

    $cfdb7_plugin = new CF7_Sheets_CFDB7_Plugin();
    $cfdb7_plugin->init();
}
cf7_sheets_init();

