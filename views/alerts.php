<?php
$form_errors = get_transient("settings_errors");
delete_transient("settings_errors");

if(!empty($form_errors)){
    foreach($form_errors as $error){
        echo cf7_sheets_message($error['message'], $error['type']);
    }
}