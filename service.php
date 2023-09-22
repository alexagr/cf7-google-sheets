<?php

/**
 * integration with Contact Forms 7
 */

class CF7_Sheets_Service
{
    protected $files = array();

    public function init()
    {
        add_filter( 'wpcf7_editor_panels', array( $this, 'editor_panels' ) );
        add_action( 'wpcf7_before_send_mail', array( $this, 'store_files' ) );
        add_action( 'wpcf7_after_save', array( $this, 'save_settings' ) );
        add_action( 'wpcf7_mail_sent', array( $this, 'send_to_sheets' ) );
    }
    
    public function editor_panels( $panels ) 
    {
        if ( current_user_can( 'wpcf7_edit_contact_forms' ) ) {
            $panels['google_sheets'] = array(
                'title' => __( 'Google Sheets', 'contact-form-7' ),
                'callback' => array( $this, 'editor_panel_google_sheet' )
            );
        }
        return $panels;
    }

    public function editor_panel_google_sheet( $post ) 
    {
        $form_id = sanitize_text_field( $_GET['post'] );
        # store settings in the same meta key as https://wordpress.org/plugins/cf7-google-sheets-connector to simplify migration
        $form_data = get_post_meta( $form_id, 'gs_settings' );
        ?>
        <form method="post">
            <div id="cf7-sheets">
                <h2><span><?php echo esc_html( __( 'Google Sheets', 'cf7-sheets' ) ); ?></span></h2>
                <fieldset>
                    <legend>Determine <b>Sheet ID</b> and <b>Tab ID</b> from the Google Sheets URL, that looks as follows:<br>https://docs.google.com/spreadsheets/d/<b>&lt;sheet-id&gt;</b>/edit#gid=<b>&lt;tab-id&gt;</b></legend>
                    <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="cf7-sheet-id"><?php echo esc_html(__('Sheet ID', 'cf7-sheets')); ?></label>
                            </th>
                            <td>
                                <input type="text" name="cf7-sheets[sheet-id]" id="cf7-sheet-id" class="large-text code" value="<?php echo ( isset($form_data[0]['sheet-id']) ) ? esc_attr($form_data[0]['sheet-id']) : ''; ?>"/>
                            </td>
                        </tr>                    
                        <tr>
                            <th scope="row">
                                <label for="cf7-sheet-id"><?php echo esc_html(__('Tab ID', 'cf7-sheets')); ?></label>
                            </th>
                            <td>
                                <input type="text" name="cf7-sheets[tab-id]" id="cf7-tab-id" class="large-text code" value="<?php echo ( isset($form_data[0]['tab-id']) ) ? esc_attr($form_data[0]['tab-id']) : ''; ?>"/>
                            </td>
                        </tr>                    
                    </tbody>
                    </table>
                </fieldset>
               
                <?php if ( ( isset( $form_data[0]['sheet-id'] ) ) && ( isset( $form_data[0]['tab-id'] ) ) ) {
                    $link = "https://docs.google.com/spreadsheets/d/".$form_data[0]['sheet-id']."/edit#gid=".$form_data[0]['tab-id']; 
                    ?>
                <p>
                    <a href="<?php echo $link; ?>" target="_blank" class="cf7_sheets_link" >Google Sheet Link</a>
                </p>
                <?php }
                    $client = new CF7_Sheets_Client();
                    $data = $client->client_data();
                    if (! empty($data['client_email'] ) ) {
                        ?>
                <p>
                    Note: don't forget to grant <b>Editor</b> permissions to <b><?php echo $data['client_email']; ?></b> in your sheet.
                </p>
                        <?php
                    } else {
                        ?>
                <p style="color: darkRed">
                    Warning: couldn't find application credentials needed for connection with Google Sheets - click <a href="<?php echo esc_html(admin_url('admin.php')) . '?page=cf7-sheets-forms'; ?>">here</a> to fix that.
                </p>
                        <?php
                    }
                    ?>
            </div>
        </form>
        <?php
    }
   
    public function save_settings( $post )
    {
        $default = array(
            "sheet-id" => "",
            "tab-id" => ""
        );
        $sheet_data = isset( $_POST['cf7-sheets'] ) ? $_POST['cf7-sheets'] : $default;
        update_post_meta( $post->id(), 'gs_settings', $sheet_data );
    }

    public function store_files()
    {
        $form = WPCF7_Submission::get_instance();
        if ( $form ) {
            $this->files = array();

            $files = $form->uploaded_files();
            foreach ( $files as $name => $path ) {
                if ( isset( $_FILES[$name] ) )
                    $this->files[$name] = $_FILES[$name]['name'];
            }
        }
    }

    public function send_to_sheets( $form )
    {
        $submission = WPCF7_Submission::get_instance();
        
        $form_id = $form->id();
        $form_data = get_post_meta( $form_id, 'gs_settings' );

        if ( $submission && (! empty( $form_data[0]['sheet-id'] ) ) && ( (! empty( $form_data[0]['tab-id'] ) ) || ( $form_data[0]['tab-id'] === '0' ) ) ) {
            $posted_data = $submission->get_posted_data();

            $data = array();
            foreach ( $posted_data as $key => $value ) {
                if ( ( strpos( $key, '_wpcf7' ) === false ) && ( strpos( $key, '_wpnonce' ) === false ) ) {
                    if ( isset( $this->files[ $key ] ) ) {
                        $data[ $key ] = sanitize_file_name( $this->files[ $key ] );
                    } elseif ( is_array( $value ) ) {
                        $data[ $key ] = sanitize_text_field( implode( ', ', $value ) );
                    } else {
                        $data[ $key ] = sanitize_textarea_field(stripcslashes($value));
                    }
                }
            }

            $special_mail_tags = array( 'remote_ip', 'user_agent', 'url', 'date', 'time', 'post_id', 'post_name', 'post_title', 'post_url', 'post_author', 'post_author_email', 'site_title', 'site_description', 'site_url', 'site_admin_email', 'user_login', 'user_email', 'user_url', 'user_first_name', 'user_last_name', 'user_nickname', 'user_display_name' );
            
            $meta = array();
            foreach ( $special_mail_tags as $smt ) {
                $tagname = sprintf( '_%s', $smt );

                $mail_tag = new WPCF7_MailTag(
                    sprintf( '[%s]', $tagname ),
                    $tagname,
                    ''
                );
                
                $key = str_replace('_', '-', $smt);
                $meta[ $key ] = apply_filters( 'wpcf7_special_mail_tags', '', $tagname, false, $mail_tag );
            }
            
            $meta[ 'datetime' ] = $meta[ 'date' ] . ' ' . $meta[ 'time' ];

            $client = new CF7_Sheets_Client();
            $client->add_row($form_data[0]['sheet-id'], $form_data[0]['tab-id'], $data, $meta);
        }
    }
}
