<?php

class CF7_Sheets_Admin_Form
{

    const ID = 'cf7-sheets-forms';

    const NONCE_KEY = 'cf7_sheets';

    const WHITELISTED_KEYS = array(
        'cf7-sheets-test'
    );

    protected $views = array(
        'connect' => 'views/connect',
        'alerts' => 'views/alerts',
        'not-found' => 'views/not-found'
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
            esc_html__('Google Sheets', 'cf7-sheets'),
            esc_html__('Google Sheets', 'cf7-sheets'),
            'manage_options',
            $this->get_id(),
            array(&$this, 'load_view')
        );
    }

    public function add_settings_link($links) {
        array_push($links, '<a href="' . admin_url('admin.php?page=cf7-sheets-forms') . '">' . __('Settings', 'cf7-sheets') . '</a>');
        return $links;
    }

    function load_view()
    {
        $this->default_values = $this->get_defaults();
        $this->current_page = $this->current_view();
        
        $current_views = isset($this->views[$this->current_page]) ? $this->views[$this->current_page] : $this->views['not-found'];

        $step_data_func_name = $this->current_page . '_data';

        $args = [];
        /**
         * prepare data for view
         */
        if (method_exists($this, $step_data_func_name)) {
            $args = $this->$step_data_func_name();
        }
        /**
         * Default Admin Form Template
         */

        echo '<div class="cf7-sheets-forms ' . $this->current_page . '">';

        echo '<div class="inner">';

        $this->include_with_variables($this->template_server_path('views/alerts'));

        $this->include_with_variables($this->template_server_path($current_views), $args);

        echo '</div>';

        echo '</div>';
    }

    function include_with_variables($filePath, $variables = array(), $print = true)
    {
        $output = NULL;
        if (file_exists($filePath)) {
            // Extract the variables to a local namespace
            extract($variables);

            // Start output buffering
            ob_start();

            // Include the template file
            include $filePath;

            // End buffering and return its contents
            $output = ob_get_clean();
        }
        if ($print) {
            print $output;
        }
        return $output;
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
        $nonce = sanitize_text_field($_POST[$this->get_nonce_key()]);
        $action = sanitize_text_field($_POST['action']);

        if (!isset($nonce) || !wp_verify_nonce($nonce, $action)) {
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
                $fields_to_update[$key] = $_POST[$key];
            }
        }

        /**
         * Loop through form fields keys and update data in DB (wp_options)
         */

        $this->db_update_options($fields_to_update);

        $redirect_to = $_POST['redirectToUrl'];

        if ($redirect_to) {
            add_settings_error('cf7_sheets_msg', 'cf7_sheets_msg_option', __("Changes saved."), 'success');
            set_transient('settings_errors', get_settings_errors(), 30);
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
        $nonce = sanitize_text_field($_POST[$this->get_nonce_key()]);
        $action = sanitize_text_field($_POST['action']);
        $action_name = sanitize_text_field($_POST['action_name']);

        if (!isset($nonce) || !wp_verify_nonce($nonce, $action)) {
           print 'Sorry, your nonce did not verify.';
           exit;
       }

       $error = '';
       $action_func_name = $action_name . '_action';
       if (method_exists($this, $action_func_name)) {
           $error = $this->$action_func_name();
       }

       $redirect_to = $_POST['redirectToUrl'];
       if ($redirect_to) {
            if (empty($error)) {
                add_settings_error('cf7_sheets_msg', 'cf7_sheets_msg_option', __("Operation completed."), 'success');
            } elseif (substr_compare($error, 'ERROR', 0, 5) != 0) {
                add_settings_error('cf7_sheets_msg', 'cf7_sheets_msg_option', $error, 'success');
            } else {
                add_settings_error('cf7_sheets_msg', 'cf7_sheets_msg_option', $error, 'danger');
            }

            set_transient('settings_errors', get_settings_errors(), 30);
            wp_safe_redirect($redirect_to);
            exit;
       }
    }

    /**
     * Form elements outputs
     */
    private function render_input($group, $key, $required = false)
    {
        $inputValue = isset($this->default_values[$group][$key]) ? stripslashes($this->default_values[$group][$key]) : '';
        $requiredAttr = ($required) ? "required" : '';
 
        return '<input type="text" id="' . $key . '" name="' . $group . '[' . $key . ']" class="regular-text" value="' . $inputValue . '" ' . $requiredAttr . '>';
    }
 
    private function render_textarea($group, $key)
    {
        $defaultValue = isset($this->default_values[$group][$key]) ? stripslashes($this->default_values[$group][$key]) : '';
 
        return '<textarea class="form-control" rows="6" autocomplete="off" id="' . $key . '" name="' . $group . '[' . $key . ']">' . $defaultValue . '</textarea>';
    }
 
    private function render_select($group, $key, $options)
    {
        $selectedVal = isset($this->default_values[$group][$key]) ? $this->default_values[$group][$key] : '';
 
        $html = '';
        $html .= '<select class="form-control" id="' . $key . '" name="' . $group . '[' . $key . ']">';
        $html .= ($selectedVal == '') ? '<option value=""></option>' : '';
        foreach ($options as $key => $opt) {
            $selectedOpt = '';
            if ($selectedVal == $key) {
                $selectedOpt = 'selected="selected"';
            }
            $html .= '<option value="' . $key . '" ' . $selectedOpt . '>' . $opt . '</option>';
        }
        $html .= '</select>';
        return $html;
    }
 
    private function render_checkbox($group, $key)
    {
        $checkedVal = isset($this->default_values[$group][$key]) ? $this->default_values[$group][$key] : '';

        $checkedAttr = "";
        if ($checkedVal != '') {
            $checkedAttr = "checked";
        }
        $html = '';

        $html .= '
        <input type="hidden" name="' . $group . '[' . $key . ']" value="">
        <input class="form-check-input" type="checkbox" value="on" id="' . $key . '" name="' . $group . '[' . $key . ']" ' . $checkedAttr . '>';

        return $html;
    }

    /**
     * Prepare data for views
    */
 
    private function connect_data()
    {
        $client = new CF7_Sheets_Client();
        $client_data = $client->client_data();
        
        $args = array(
            'client_id' => $client_data['client_id'],
            'client_email' => $client_data['client_email'],
            'sheet_id' => $this->render_input('cf7-sheets-test', 'sheet_id')
        );
        return $args;
    }

    /**
     * Actions
     */

    private function upload_credentials_action()
    {
        if ($_FILES['file']['size'] > 4096) {
            return 'ERROR: file is too large';
        }
        
        if($_FILES['file']['error'] != 0) {
            return 'ERROR: failed to upload the file';
        }

        $base_path = WP_PLUGIN_DIR . '/' . CF7_SHEETS_DIR . '/';
        @mkdir($base_path . 'data');
        move_uploaded_file($_FILES['file']['tmp_name'], $base_path . 'data/credentials.json');
        file_put_contents($base_path . 'data/.htaccess', 'deny from all');
        return '';
    }

    private function test_access_action()
    {
        $sheet_id = $_POST['cf7-sheets-test']['sheet_id'];
        if (empty($sheet_id)) {
            return 'ERROR: Sheet ID is empty';
        }
        
        $client = new CF7_Sheets_Client();
        return $client->test($sheet_id);
    }
    

    /**
     * Helper functions 
     */

    private function current_view()
    {
        $current_step = isset($_GET['page']) ? $_GET['page'] : 'connect';
    
        if (strpos($current_step, '_') === false) {
            return 'connect';
        }
    
        return str_replace($this->get_id() . "_", "", $current_step);
    }

    private function template_server_path($file_path, $include = false)
    {
        $base_path = WP_PLUGIN_DIR . '/' . CF7_SHEETS_DIR . '/';
    
        $path_to_file = $base_path . $file_path . '.php';

        if ($include) {
            include $path_to_file;
        }

        return $path_to_file;
    }
 } 
