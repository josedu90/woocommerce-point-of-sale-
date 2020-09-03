<?php

class WC_Pos_Cloud_Print_Handler
{
    private function check_invalid_utf8($string)
    {
        $string = (string) $string;
        if (0 === strlen( $string )) return '';
        // Check for support for utf8 in the installed PCRE library once and store the result in a static
        static $utf8_pcre = null;
        if (!isset( $utf8_pcre )) $utf8_pcre = @preg_match('/^./u', 'a');
        // We can't demand utf8 in the PCRE installation, so just return the string in those cases
        if (!$utf8_pcre) return $string;
        // preg_match fails when it encounters invalid UTF8 in $string
        if (1 === @preg_match('/^./us', $string)) return $string;
        return '';
    }

    // Remove whitespace
    private function strip_all_tags($string, $remove_breaks = false)
    {
        $string = preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $string);
        $string = strip_tags($string);
        if ($remove_breaks) $string = preg_replace('/[\r\n\t ]+/', ' ', $string);
        return trim($string);
    }

    private function sanitize_parameter($str, $keep_newlines = false)
    {
        $filtered = self::check_invalid_utf8($str);
        if ( strpos($filtered, '<') !== false )
        {
            $filtered = preg_replace_callback('%<[^>]*?((?=<)|>|$)%', 'wp_pre_kses_less_than_callback', $filtered);
            $filtered = self::strip_all_tags($filtered);
            $filtered = str_replace("<\n", "&lt;\n", $filtered);
        }
        if (!$keep_newlines) $filtered = preg_replace('/[\r\n\t ]+/', ' ', $filtered);
        $filtered = trim($filtered);
        $found = false;
        while (preg_match('/%[a-f0-9]{2}/i', $filtered, $match))
        {
            $filtered = str_replace($match[0], '', $filtered);
            $found = true;
        }
        // Strip out the whitespace that may now exist after removing the octets.
        if ($found) $filtered = trim( preg_replace('/ +/', ' ', $filtered) );
        return $filtered;
    }

    public static function is_valid_mac($mac)
    {
        if (strlen($mac) != 17) return false;
        if ($mac[2] != ':' || $mac[5] != ':' || $mac[8] != ':'||
            $mac[11] != ':' || $mac[14] != ':') return false;
        if (substr($mac, 0, 5) != '00:11') return false;
        return true;
    }

    public function handle_post()
    {
        // Check is valid POST request type
        if (strtolower($_SERVER['CONTENT_TYPE']) != 'application/json') return;
        // The response to a POST request is always a JSON document
        header('Content-Type: application/json');
        $arr = array();
        // Get JSON payload recieved from the request and parse it
        $receivedJson = file_get_contents("php://input");
        $parsedJson = json_decode($receivedJson, true);
        // Sanitize JSON params
        foreach ($parsedJson as $key=>$parameter)
        {
            if (!is_array($parsedJson[$key])) $parsedJson[$key] = self::sanitize_parameter($parameter);
        }
        // Validate JSON params
        if (!isset($parsedJson['printerMAC']) || !isset($parsedJson['statusCode']) || !isset($parsedJson['status'])) return;
        if (!self::is_valid_mac($parsedJson['printerMAC'])) return;
        // Setup printer storage directories
        $printerDir = STAR_CLOUDPRNT_PRINTER_DATA_SAVE_PATH."/".WC_POS_CPI()->star_cloudprnt_get_printer_folder($parsedJson['printerMAC']);
        $queueDir = $printerDir."/queue";
        // Create any local directories for storing printer data if they do not exist already (i.e. the first time the printer polls to the server)
        if (!is_dir($printerDir)) mkdir($printerDir, 0755);
        if (!is_dir($queueDir)) mkdir($queueDir, 0755);

        // The $path variable is used to deicde which printer data file needs to be updated depending on
        // the request type.  Initially, we assume the request is a general communication request (occurs after each poll interval)
        // So the $path variable is set to communication.json
        $path = $printerDir."/communication.json";
        // If the JSON request contains a request object in the clientAction then the printer is responding to a additional information
        // request (i.e. to get variables like the poll interval from the printer), so in this case the $path variable is set to
        // additional_communication.json to save this additional data
        if (isset($parsedJson["clientAction"][0]["request"]))
        {
            $arr = array("jobReady" => false);
            $path = $printerDir."/additional_communication.json";
        }
        // Else if we have a general communication request and we still don't have additional data about the printer (like the poll interval)
        // then we need to make sure our response contains a request for the additional information immediately
        else if (!file_exists($printerDir."/additional_communication.json"))
        {
            $arr = array("jobReady" => false,
                "clientAction" => [array("request" => "GetPollInterval"),
                    array("request" => "Encodings"),
                    array("request" => "ClientType"),
                    array("request" => "ClientVersion")]
            );
        }
        // Else if we have a general communication request and we already have additional data about the printer (like the poll interval) then
        // we will only request the same additioanl data again after a specified interval (to reduce load on the server and printer), this is usually
        // set to 60 seconds (stored in the STAR_CLOUDPRNT_ADDITIONAL_DATA_INTERVAL variable).  So if the poll interval was changed, we will always know within 60 seconds
        else if ((time()-filemtime($printerDir."/additional_communication.json")) > STAR_CLOUDPRNT_ADDITIONAL_DATA_INTERVAL)
        {
            $arr = array("jobReady" => false,
                "clientAction" => [array("request" => "GetPollInterval"),
                    array("request" => "Encodings"),
                    array("request" => "ClientType"),
                    array("request" => "ClientVersion")]
            );
        }
        // Else if we have a general communication request and no additional data requests are required, then we can check if a new print job
        // is ready, the star_cloudprnt_queue_get_next_job function checks this and returns the job name to print
        else if (WC_POS_CPI()->star_cloudprnt_queue_get_next_job($parsedJson['printerMAC']) != "")
        {
            $arr = array("jobReady" => true,
                "mediaTypes" => array('text/plain'),
                "deleteMethod" => "GET");
        }
        else $arr = array("jobReady" => false);

        $file = fopen($path, "w");
        fwrite($file, $receivedJson);
        fclose($file);
        $printer = new WC_Pos_Cloud_Print_Printer($parsedJson['printerMAC']);
        $printer->createPrinterData($_SERVER['REMOTE_ADDR']);

        echo json_encode($arr);
    }

    public function handle_get()
    {
        // Sanitize
        $sanitized_mac = self::sanitize_parameter($_GET['mac']);
        if ($sanitized_mac === '') return;
        // Validate
        if (!self::is_valid_mac($sanitized_mac)) return;

        $nextPrintJob = WC_POS_CPI()->star_cloudprnt_queue_get_next_job($sanitized_mac);
        if ($nextPrintJob == "") return;

        $printerDir = STAR_CLOUDPRNT_PRINTER_DATA_SAVE_PATH."/".WC_POS_CPI()->star_cloudprnt_get_printer_folder($sanitized_mac);
        $queueDir = $printerDir."/queue";
        $file = $queueDir."/".$nextPrintJob;

        if (file_exists($file))
        {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if (strtolower($ext) == "txt")
            {
                ini_set('default_charset', '');
                header('Content-Type: text/plain');
                echo file_get_contents($file);
            }
            else if (strtolower($ext) == "png")
            {
                header('Content-Type: image/png');
                // Get image size
                list($width, $height) = getimagesize($file);
                $fh = fopen($file, 'rb');
                fpassthru($fh);
            }
            else if (strtolower($ext) == "jpg")
            {
                header('Content-Type: image/jpeg');
                // Get image size
                list($width, $height) = getimagesize($file);
                $fh = fopen($file, 'rb');
                fpassthru($fh);
            }
            else if( strtolower($ext) == "pdf"){
                header('Content-Disposition: attachment; filename=' . urlencode($file));
//                header('Content-Type: application/force-download');
                header('Content-Type: application/octet-stream');
//                header('Content-Type: application/download');
                header('Content-Description: File Transfer');
                header('Content-Length: ' . filesize($file));
                echo file_get_contents($file);
//                header('Content-Type: application/vnd.star.starprnt');
//                echo readfile($file);
            }
            else if (strtolower($ext) == "bin")
            {
                header('Content-Type: application/vnd.star.line');
                echo file_get_contents($file);
            }
        }
        exit;
    }

    // Return a blank text file, then proceed with deleteing the print job
    public function handle_delete()
    {
        ini_set('default_charset', '');
        // Sanitize
        $sanitized_mac = self::sanitize_parameter($_GET['mac']);
        if ($sanitized_mac === '') return;
        // Validate
        if (!self::is_valid_mac($sanitized_mac)) return;
        if (isset($_GET['code']) && ($_GET['code'][0] === '2')) WC_POS_CPI()->star_cloudprnt_queue_remove_last_print_job($sanitized_mac);
    }

}