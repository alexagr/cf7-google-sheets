<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Admin form
 */
class CF7_Sheets_Admin_Form
{

    const ID = 'cf7-sheets-forms';

    const NONCE_KEY = 'cf7_sheets';

    const WHITELISTED_KEYS = array(
        'cf7-sheets-config'
    );

    public function init()
    {
        add_action('admin_menu', array($this, 'add_menu_page'), 20);

        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));

        add_action('admin_post_cf7_sheets_update', array($this, 'submit_update'));

        add_action('admin_post_cf7_sheets_action', array($this, 'submit_action'));

        add_filter('plugin_action_links_' . CF7_SHEETS_BASE_NAME, array($this, 'add_settings_link'));

    }

    public function get_id()
    {
        return self::ID;
    }

    public function get_nonce_key()
    {
        return self::NONCE_KEY;
    }

    public function get_whitelisted_keys()
    {
        return self::WHITELISTED_KEYS;
    }

    private function get_defaults()
    {
        foreach ($this->get_whitelisted_keys() as $key => $val) {
            $defaults[$val] = get_option($val);
        }
        return $defaults;
    }

    public function add_menu_page()
    {
        add_submenu_page(
            'wpcf7',
            esc_html__('Google Sheets', 'cf7-google-sheets'),
            esc_html__('Google Sheets', 'cf7-google-sheets'),
            'manage_options',
            $this->get_id(),
            array(&$this, 'show')
        );
    }

    public function add_settings_link($links) {
        array_push($links, '<a href="' . esc_url(admin_url('admin.php?page=cf7-sheets-forms')) . '">' . __('Settings', 'cf7-google-sheets') . '</a>');
        return $links;
    }

    function show()
    {
        $client = new CF7_Sheets_Client();
        $client_data = $client->client_data();

        $default_values = $this->get_defaults();

        $config_text = isset($default_values['cf7-sheets-config']['credentials']) ? stripslashes($default_values['cf7-sheets-config']['credentials']) : '{}';
        $config = json_decode($config_text, true);

        echo '<div class="cf7-sheets-forms">';
        echo '<div class="inner">';

        $form_errors = get_transient("cf7_sheets_errors");
        delete_transient("cf7_sheets_errors");

        if(!empty($form_errors)){
            foreach($form_errors as $error){
                echo '<div id="message" class="alert alert-' . esc_attr($error['type']) . '">' . esc_html($error['message']) . '</div>';
            }
        }

        echo '
  <div class="wrap">

    <h1>' . esc_html__('Google Sheets for Contact Form 7', 'cf7-google-sheets') . '</h1>

    <h2>' . esc_html__('Step 1: create application credentials', 'cf7-google-sheets') . '</h2>
    <div class="card">
        <ul style="list-style: circle">
        <li>' . esc_html__('Go to', 'cf7-google-sheets') . ' <a href="https://console.developers.google.com">https://console.developers.google.com</a></li>
        <li>' . esc_html__('Choose existing project or create a new one.', 'cf7-google-sheets') . '</li>
        <li>' . wp_kses(__('Click <b>Enable APIs And Services', 'cf7-google-sheets'), array('b' => array())) . '</b>.
            <ul style="list-style: disclosure-closed; padding-left: 1em;">
            <li>' . wp_kses(__('Search for <b>Google Sheet API</b> and enable it.', 'cf7-google-sheets'), array('b' => array())) . '</li>
            </ul>
        </li>
        <li>' . wp_kses(__('Click <b>Credentials &gt; Create Credentials &gt; Service Account</b> - this will open <b>Create Service Account</b> screen.', 'cf7-google-sheets'), array('b' => array())) . '
            <ul style="list-style: disclosure-closed; padding-left: 1em;">
            <li>' . wp_kses(__('Under <b>Service account details</b>:', 'cf7-google-sheets'), array('b' => array())) . '
                <ul style="list-style: disc; padding-left: 1em;">
                <li>' . wp_kses(__('For <b>Service account name</b> select &quot;Google Sheets for Contact Form 7&quot;.', 'cf7-google-sheets'), array('b' => array())) . '</li>
                <li>' . wp_kses(__('Click <b>CREATE AND CONTINUE</b>.', 'cf7-google-sheets'), array('b' => array())) . '</li>
                </ul>
            </li>
            <li>' . wp_kses(__('Under <b>Grant this service account access to the project</b>:', 'cf7-google-sheets'), array('b' => array())) . '
                <ul style="list-style: disc; padding-left: 1em;">
                <li>' . wp_kses(__('For <b>Role</b> enter &quot;Editor&quot;.', 'cf7-google-sheets'), array('b' => array())) . '</li>
                <li>' . wp_kses(__('Click <b>CONTINUE</b>.', 'cg7-google-sheets'), array('b' => array())) . '</li>
                </ul>
            </li>
            <li>' . wp_kses(__('Click <b>DONE</b>.', 'cf7-google-sheets'), array('b' => array())) . '</li>
            </ul>
        </li>
        <li>' . wp_kses(__('Click on the created service account under <b>Service Accounts</b> - this will open <b>Edit Service Account</b> screen.', 'cf7-google-sheets'), array('b' => array())) . '
            <ul style="list-style: disclosure-closed; padding-left: 1em;">
            <li>' . wp_kses(__('Switch to <b>KEYS</b> tab.', 'cf7-google-sheets'), array('b' => array())) . '</li>
            <li>' . wp_kses(__('Click <b>ADD KEY &gt; Create new key</b>.', 'cf7-google-sheets'), array('b' => array())) . '
                <ul style="list-style: disc; padding-left: 1em;">
                <li>' . wp_kses(__('For <b>Key type</b> choose &quot;JSON&quot;.', 'cf7-google-sheets'), array('b' => array())) . '</li>
                <li>' . wp_kses(__('Click <b>CREATE</b> - credentials.json file will be downloaded.', 'cf7-google-sheets'), array('b' => array())) . '</li>
                </ul>
            </li>
            </ul>
        </li>
        </ul>
    </div>

    <br>
    <hr>
    
    <h2>' . esc_html('Step 2: upload credentials.json file', 'cf7-google-sheets') . '</h2>
            
    <div class="card">
      <div class="row">
        <input type="file" id="upload-credentials" name="file" accept=".json">
      </div>
      <p class="submit sr-only"><input type="submit" name="upload" id="upload" class="button button-primary" onclick="loadCredentials()" value="Upload"></p>
    
      <form id="config-form" method="POST" action="' . esc_url(admin_url('admin-post.php')) . '">
      <input type="hidden" name="action" value="cf7_sheets_update">
      ' . wp_nonce_field('cf7_sheets_update', 'cf7_sheets', true, false) . '
      <input type="hidden" name="redirectToUrl" value="' . esc_url(admin_url('admin.php?page=cf7-sheets-forms')) . '">
      <input type="hidden" id="credentials" name="cf7-sheets-config[credentials]" value="">
      </form>
    
      <script>
        function loadCredentials()
        {
          let fileInput = document.getElementById("upload-credentials");
          if (fileInput.files.length <= 0) {
            return;
          }
        
          let file = fileInput.files[0];
          if (file.size > 10 * 1024) {
            alert("File is too big");
            return;
          }
        
          const reader = new FileReader();
          reader.addEventListener("load", (event) => {
            try {
              JSON.parse(event.target.result);
            } catch (error) {
              alert("Invalid JSON file");
              return;
            }
       
            let elem = document.getElementById("credentials");
            elem.value = event.target.result;
            let form = document.getElementById("config-form");
            form.submit();
          });
          reader.readAsText(file);
        }
      </script>
    
      <br>
      <table class="form-table" role="presentation">
        <tr>
          <th scope="row"><label for="client_id">Client ID</label></th>
          <td>' . (isset($config['client_id']) ? esc_html($config['client_id']) : '') . '</td>
        </tr>
        <tr>
          <th scope="row"><label for="client_email">Client email</label></th>
          <td>' . (isset($config['client_email']) ? esc_html($config['client_email']) : '') . '</td>
        </tr>
      </table>
    </div>
    
    <br>
    <hr>
    
    <h2>' . esc_html('Step 3: (optional) test connection to Google Sheets', 'cf7-google-sheets') . '</h2>

    <div class="card">
      <ul style="list-style: circle">
        <li>' . esc_html('Go to', 'cf7-google-sheets') . ' <a href="https://docs.google.com/spreadsheets">https://docs.google.com/spreadsheets</a></li>
        <li>' . esc_html('Open an existing sheet or create a new one.', 'cf7-google-sheets') . '</li>
        <li>' . wp_kses(__('Click <b>Share</b> and grant <b>Editor</b> permissions to <b>Client email</b> as shown above.', 'cf7-google-sheets'), array('b' => array())) . '</li>
        <li>' . wp_kses(__('Determine <b>Sheet ID</b> from the Google Sheets URL, that looks as follows:', 'cf7-google-sheets'), array('b' => array())) . ' https://docs.google.com/spreadsheets/d/<b>&lt;sheet-id&gt;</b>/edit</;li>
        <li>' . wp_kses(__('Enter <b>Sheet ID</b> and click <b>Test</b>.', 'cf7-google-sheets'), array('b' => array())) . '</li>
      </ul>

      <form method="POST" action="' . esc_url(admin_url('admin-post.php')) . '" onkeydown="return event.key != \'Enter\';">
      <input type="hidden" name="action" value="cf7_sheets_action">
      <input type="hidden" name="action_name" value="test_access">
      ' . wp_nonce_field('cf7_sheets_action', 'cf7_sheets', false, true) . '
      <input type="hidden" name="redirectToUrl" value="' . esc_url(admin_url('admin.php?page=cf7-sheets-forms')) . '"

      <table class="form-table" role="presentation">
        <tr>
          <th scope="row"><label for="sheets_id">Sheet ID</label></th>
          <td><input type="text" id="sheet-id" name="cf7-sheet-id" class="regular-text" value=""</td>
        </tr>
      </table>

      <p class="submit sr-only"><input type="submit" name="submit" id="submit" class="button button-primary" value="' .  esc_attr__('Test', 'cf7-google-sheets') . '" /></p>
      </form>
    </div>
  </div>';
  
    if (cf7_sheets_log_exists()) {
        echo '<br>
  <hr>
  <br>
  <a href="' . esc_attr(cf7_sheets_url('/log/log.txt')) . '"' . esc_html__('View log', 'cf7-google-sheets') . '</a>';
    }

  echo '  </div>
</div>';
    }

    public function admin_enqueue_scripts($hook_suffix)
    {
        if (strpos($hook_suffix, $this->get_id()) === false) {
            return;
        }

        wp_enqueue_style('cf7-sheets-form', cf7_sheets_url('assets/style.css'), CF7_SHEETS_VERSION);

        wp_enqueue_script('cf7-sheets-form-js', cf7_sheets_url('assets/custom.js'),
            array('jquery'),
            CF7_SHEETS_VERSION,
            true
        );
    }

    public function submit_update()
    {
        if (!isset($_POST[$this->get_nonce_key()]) || !isset($_POST['action'])) {
            print 'Sorry, your request is missing mandatory parameters.';
            exit;
        }

        $nonce = sanitize_text_field(wp_unslash($_POST[$this->get_nonce_key()]));
        $action = sanitize_text_field($_POST['action']);
        if (!wp_verify_nonce($nonce, $action)) {
            print 'Sorry, your nonce did not verify.';
            exit;
        }

        if (!current_user_can('manage_options')) {
            print 'You can\'t manage options';
            exit;
        }
        
        /**
         * whitelist keys that can be updated
         */
        $whitelisted_keys = $this->get_whitelisted_keys();

        $fields_to_update = [];

        foreach ($whitelisted_keys as $key) {
            if (array_key_exists($key, $_POST)) {
                $fields_to_update[$key] = sanitize_post_field($key, $_POST[$key], 0, 'db');
            }
        }

        /**
         * Loop through form fields keys and update data in DB (wp_options)
         */

        $this->db_update_options($fields_to_update);

       if (isset($_POST['redirectToUrl'])) {
            $redirect_to = esc_url_raw($_POST['redirectToUrl']);
            add_settings_error('cf7_sheets_msg', 'cf7_sheets_msg_option', __("Changes saved."), 'success');
            set_transient('cf7_sheets_errors', get_settings_errors(), 30);
            wp_safe_redirect($redirect_to);
            exit;
        }
    }

    private function db_update_options($group)
    {
        foreach ($group as $key => $fields) {
            $db_opts = get_option($key);
            $db_opts = ($db_opts === '') ? array() : $db_opts;

            if(!$db_opts){
                $db_opts = array();
            }

            $updated = array_merge($db_opts, $fields);
            update_option($key, $updated);
        }
    }

    public function submit_action()
    {
        if (!isset($_POST[$this->get_nonce_key()]) || !isset($_POST['action']) || !isset($_POST['action_name'])) {
            print 'Sorry, your request is missing mandatory parameters.';
            exit;
        }

        $nonce = sanitize_text_field(wp_unslash($_POST[$this->get_nonce_key()]));
        $action = sanitize_text_field($_POST['action']);
        $action_name = sanitize_text_field($_POST['action_name']);

        if (!wp_verify_nonce($nonce, $action)) {
           print 'Sorry, your nonce did not verify.';
           exit;
       }

       $error = '';
       $action_func_name = $action_name . '_action';
       if (method_exists($this, $action_func_name)) {
           $error = $this->$action_func_name();
       }

       if (isset($_POST['redirectToUrl'])) {
            $redirect_to = esc_url_raw($_POST['redirectToUrl']);
            if (empty($error)) {
                add_settings_error('cf7_sheets_msg', 'cf7_sheets_msg_option', __("Operation completed."), 'success');
            } elseif (substr_compare($error, 'ERROR', 0, 5) != 0) {
                add_settings_error('cf7_sheets_msg', 'cf7_sheets_msg_option', $error, 'success');
            } else {
                add_settings_error('cf7_sheets_msg', 'cf7_sheets_msg_option', $error, 'danger');
            }

            set_transient('cf7_sheets_errors', get_settings_errors(), 30);
            wp_safe_redirect($redirect_to);
            exit;
       }
    }

    /**
     * Actions
     */

    private function test_access_action()
    {
        if (!isset($_POST[$this->get_nonce_key()]) || !isset($_POST['action']) || !isset($_POST['cf7-sheet-id'])) {
            print 'Sorry, your request is missing mandatory parameters.';
            exit;
        }

        $nonce = sanitize_text_field(wp_unslash($_POST[$this->get_nonce_key()]));
        $action = sanitize_text_field($_POST['action']);
        if (!wp_verify_nonce($nonce, $action)) {
            print 'Sorry, your nonce did not verify.';
            exit;
        }

        $sheet_id = sanitize_text_field($_POST['cf7-sheet-id']);
        if (empty($sheet_id)) {
            return 'ERROR: Sheet ID is empty';
        }
        
        $client = new CF7_Sheets_Client();
        return $client->test($sheet_id);
    }
} 
