<?php

/**
 * integration with Contact Forms 7
 */

include_once ( plugin_dir_path(__FILE__) . 'lib/vendor/autoload.php' );

class CF7_Sheets_Client
{
    public function get_service()
    {
        $client = new Google_Client();
        $client->setApplicationName('Google Sheets for Contact Form 7');
        $client->setScopes(Google_Service_Sheets::SPREADSHEETS);
        $client->setAccessType('offline');
        $base_path = WP_PLUGIN_DIR . '/' . CF7_SHEETS_DIR . '/';
        $client->setAuthConfig($base_path  . 'data/credentials.json');

        $service = new Google_Service_Sheets($client);
        return $service;
    }

    public function client_data()
    {
        $data = array(
            'client_email' => '',
            'client_id' => ''
        );
        $base_path = WP_PLUGIN_DIR . '/' . CF7_SHEETS_DIR . '/';
        $credentials_file = $base_path . 'data/credentials.json';
        if (file_exists($credentials_file)) {
            $jsonString = file_get_contents($credentials_file);
            $jsonData = json_decode($jsonString, true);
            if (array_key_exists('client_email', $jsonData)) {
                $data['client_email'] = $jsonData['client_email'];
            }
            if (array_key_exists('client_id', $jsonData)) {
                $data['client_id'] = $jsonData['client_id'];
            }
        }
        return $data;
    }
    
    public function test($sheetId)
    {
        try {
            $service = $this->get_service();
            $spreadsheet = $service->spreadsheets->get($sheetId);
            return 'Connection is working';
        } catch (Exception $e) {
            return 'ERROR: ' . $e->getMessage();
        }
    }
    
    public function add_row($sheetId, $tabId, $data, $meta)
    {
        if (empty($data))
            return false;
        
        $client_data = $this->client_data();
        if (empty($client_data['client_email'])) {
            cf7_sheets_log('Failed to add_row() to sheet ' . $sheetId . ' tab ' . $tabId . " - credentials are missing");
            return false;
        }
        
        try {
            $service = $this->get_service();
            $sheets = $service->spreadsheets->get($sheetId);
            if (!empty($sheets)) {
                foreach ($sheets as $sheet) {
                    $properties = $sheet->getProperties();
                    if ($properties->getSheetId() == $tabId) {
                        $tabName = $properties->getTitle();
                       
                        $response = $service->spreadsheets_values->get($sheetId, $tabName . "!1:1");
                        $range = $response->getValues();
                        if (isset($range[0])) {
                            $header = $range[0];
                        } else {
                            $header = [];
                        }
                        
                        $headerUpdate = false;
                        foreach ($data as $name => $value) {
                            if (!in_array($name, $header)) {
                                $header[] = $name;
                                $headerUpdate = true;
                            } 
                        }

                        if ($headerUpdate) {
                            $headerRange = new Google_Service_Sheets_ValueRange();
                            $headerRange->setValues(["values" => $header]);
                            $conf = ["valueInputOption" => "RAW"];
                            $service->spreadsheets_values->update($sheetId, $tabName . "!1:1", $headerRange, $conf);
                        }            

                        $values = array();
                        foreach ($header as $name) {
                            if (isset($data[$name])) {
                                $value = $data[$name];
                            } elseif (isset($meta[$name])) {
                                $value = $meta[$name];
                            } else {
                                $value = '';
                            }

                            if (is_string($value) && !empty($value) && ((substr($value, 0, 1) === "=") || (substr($value, 0, 1) === "+")))
                                $value = "'" . $value;

                            $values[] = $value;
                        }

                        $valueRange = new Google_Service_Sheets_ValueRange();
                        $valueRange->setValues(["values" => $values]);
                        $conf = ["valueInputOption" => "USER_ENTERED", "insertDataOption" => "INSERT_ROWS"];

                        $response = $service->spreadsheets_values->get($sheetId, $tabName . "!A1:Z10000");
                        if (!empty($response["values"])) {
                            $range = "A" . strval(count($response["values"]) + 1);
                        } else {
                            $range = "A1";
                        }

                        $service->spreadsheets_values->append($sheetId, $tabName . "!" . $range, $valueRange, $conf);
                    }
                }
            }
        } catch (Exception $e) {
            cf7_sheets_log('Failed to add_row() to sheet ' . $sheetId . ' tab ' . $tabId . "\n" . $e->getMessage() . "\n" . $e->getTraceAsString());
            return false;
        }
        return true;
    }
}
