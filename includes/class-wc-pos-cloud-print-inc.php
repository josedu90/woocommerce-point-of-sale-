<?php

class WC_Pos_Cloud_Print_Inc
{
    public static $_instance = null;

    public function __construct()
    {
        // Store paper widths in pixels
        define('STAR_CLOUDPRNT_PAPER_SIZE_THREE_INCH', 576);
        define('STAR_CLOUDPRNT_PAPER_SIZE_FOUR_INCH', 832);
        define('STAR_CLOUDPRNT_PAPER_SIZE_ESCPOS_THREE_INCH', 512);
        define('STAR_CLOUDPRNT_PAPER_SIZE_DOT_THREE_INCH', 210);

        // Store paper widths in pixels
        define('STAR_CLOUDPRNT_MAX_CHARACTERS_THREE_INCH', 48);
        define('STAR_CLOUDPRNT_MAX_CHARACTERS_DOT_THREE_INCH', 42);
        define('STAR_CLOUDPRNT_MAX_CHARACTERS_FOUR_INCH', 69);

        // Used to determine what operating system the server is running
        define("STAR_CLOUDPRNT_WINDOWS", 0);
        define("STAR_CLOUDPRNT_UNIX", 1);
        define('STAR_CLOUDPRNT_WINDOWS_PATH_SEPERATOR', '\\');
        define('STAR_CLOUDPRNT_UNIX_PATH_SEPERATOR', '/');

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
            define("STAR_CLOUDPRNT_OPERATING_SYSTEM", STAR_CLOUDPRNT_WINDOWS);
        else
            define("STAR_CLOUDPRNT_OPERATING_SYSTEM", STAR_CLOUDPRNT_UNIX);

        // STAR_CLOUDPRNT_PRINTER_DATA_SAVE_PATH = Sets a working directory where all printer data can be stored
        define('STAR_CLOUDPRNT_DATA_FOLDER_PATH', $this->star_cloudprnt_get_plugin_root().$this->star_cloudprnt_get_os_path('/wc-pos-cloudprnt'));
        define('STAR_CLOUDPRNT_PRINTER_DATA_SAVE_PATH', STAR_CLOUDPRNT_DATA_FOLDER_PATH.$this->star_cloudprnt_get_os_path('/printerdata'));
        define('STAR_CLOUDPRNT_PRINTER_PENDING_SAVE_PATH', STAR_CLOUDPRNT_DATA_FOLDER_PATH.$this->star_cloudprnt_get_os_path('/pending'));
        // STAR_CLOUDPRNT_ADDITIONAL_DATA_INTERVAL = Adjust how often (in seconds) the server requests printer configuration data (e.g. poll interval)
        define('STAR_CLOUDPRNT_ADDITIONAL_DATA_INTERVAL', 120);
    }

    /**
     * get instance of current class
     *
     * @return null|WC_Pos_Cloud_Print_Inc
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function star_cloudprnt_get_os_path($path)
    {
        switch (STAR_CLOUDPRNT_OPERATING_SYSTEM)
        {
            case STAR_CLOUDPRNT_WINDOWS:
                return str_replace(STAR_CLOUDPRNT_UNIX_PATH_SEPERATOR, STAR_CLOUDPRNT_WINDOWS_PATH_SEPERATOR, $path);
            case STAR_CLOUDPRNT_UNIX:
                return str_replace(STAR_CLOUDPRNT_WINDOWS_PATH_SEPERATOR, STAR_CLOUDPRNT_UNIX_PATH_SEPERATOR, $path);
            default:
                return str_replace(STAR_CLOUDPRNT_WINDOWS_PATH_SEPERATOR, STAR_CLOUDPRNT_UNIX_PATH_SEPERATOR, $path);
        }
    }

    public function star_cloudprnt_get_previous_dir_path($path, $amount)
    {
        $dir = $path;
        $dir_length = strlen($dir)-1;
        if ($dir[$dir_length] === '/') $dir = substr($dir, 0, $dir_length);
        else if ($dir[$dir_length] === '\\') $dir = substr($dir, 0, $dir_length);
        for ($i = 0; $i < $amount; $i++)
        {
            $pos = strrpos($dir, '/');
            if ($pos === false) $pos = strrpos($dir, '\\');
            if ($pos === false) break;
            else $dir = substr($dir, 0, $pos);
        }
        return $dir;
    }

    public function star_cloudprnt_get_plugin_root()
    {
        $plugin_dir = '';
        if (defined( 'ABSPATH' ))
        {
            $plugin_dir = plugin_dir_path(__FILE__);
            if (basename($plugin_dir) === 'includes') $plugin_dir = $this->star_cloudprnt_get_previous_dir_path($plugin_dir, 3);
            else $plugin_dir = $this->star_cloudprnt_get_previous_dir_path($plugin_dir, 2);
        }
        else
        {
            $plugin_dir = getcwd();
            $plugin_dir = $this->star_cloudprnt_get_previous_dir_path($plugin_dir, 3);
        }
        return $plugin_dir;
    }

    public function star_cloudprnt_get_printer_folder($printerMac)
    {
        return str_replace(":", ".", $printerMac);
    }

    public function star_cloudprnt_get_printer_mac($printerFolder)
    {
        return str_replace(".", ":", $printerFolder);
    }

    public function star_cloudprnt_get_printer_list()
    {
        $list = array();
        foreach (glob(STAR_CLOUDPRNT_PRINTER_DATA_SAVE_PATH."/*", GLOB_ONLYDIR) as $printerpath)
        {
            $printerMac = $this->star_cloudprnt_get_printer_mac(basename($printerpath));

            $jsonpath = $printerpath."/communication.json";
            $printerdata = json_decode(file_get_contents($jsonpath), true);
            $printerdata['lastActive'] = filemtime($jsonpath);

            $jsonpath = $printerpath."/data.json";
            $printerdata2 = json_decode(file_get_contents($jsonpath), true);
            $printerdata['name'] = $printerdata2['name'];
            $printerdata['ipAddress'] = $printerdata2['ipAddress'];
            $printerdata['printerLocation'] = isset($printerdata2['location']) ? $printerdata2['location'] : "N/A";

            $jsonpath = $printerpath."/additional_communication.json";
            $printerdata3 = json_decode(file_get_contents($jsonpath), true);
            foreach ($printerdata3['clientAction'] as $data)
            {
                $printerdata[$data['request']] = $data['result'];
            }

            $printerdata['printerOnline'] = $this->star_cloudprnt_is_printer_online($printerdata['GetPollInterval'], filemtime($printerpath."/communication.json"));

            $list[$printerMac] = $printerdata;
        }
        return $list;
    }

    public function star_cloudprnt_is_printer_online($pollRate, $lastCommunicationTime)
    {
        if ((time()-$lastCommunicationTime) > ($pollRate+5)) return false;
        return true;
    }

    public function star_cloudprnt_queue_get_next_position($printerMac)
    {
        $heighestQueueNumber = 0;
        $queuepath = $this->star_cloudprnt_get_os_path(STAR_CLOUDPRNT_PRINTER_DATA_SAVE_PATH."/".$this->star_cloudprnt_get_printer_folder($printerMac)."/queue/");
        if ($handle = opendir($queuepath))
        {
            while (false !== ($entry = readdir($handle)))
            {
                if ($entry != "." && $entry != "..")
                {
                    // Remove file extension
                    $filename = preg_replace('/\\.[^.\\s]{3,4}$/', '', $entry);
                    if (is_numeric($filename))
                    {
                        $queueNumber = intval($filename);
                        if ($heighestQueueNumber < $queueNumber) $heighestQueueNumber = $queueNumber;
                    }
                }
            }
            closedir($handle);
        }
        return ++$heighestQueueNumber;
    }

    // Returns file name of the next job to print in the queue or empty string if there are no jobs in the queue
    public function star_cloudprnt_queue_get_next_job($printerMac)
    {
        $file = "";
        $lowestQueueNumber = -1;
        $queuepath = $this->star_cloudprnt_get_os_path(STAR_CLOUDPRNT_PRINTER_DATA_SAVE_PATH."/".$this->star_cloudprnt_get_printer_folder($printerMac)."/queue/");
        if ($handle = opendir($queuepath))
        {
            $firstLoop = true;
            while (false !== ($entry = readdir($handle)))
            {
                if ($entry != "." && $entry != "..")
                {
                    // Remove file extension
                    $filename = preg_replace('/\\.[^.\\s]{3,4}$/', '', $entry);
                    if (is_numeric($filename))
                    {
                        $queueNumber = intval($filename);
                        if ($firstLoop && strpos($entry, ".") !== false)
                        {
                            $firstLoop = false;
                            $file = $entry;
                            $lowestQueueNumber = $queueNumber;
                        }
                        else if ($lowestQueueNumber > $queueNumber && strpos($entry, ".") !== false)
                        {
                            $file = $entry;
                            $lowestQueueNumber = $queueNumber;
                        }
                    }
                }
            }
            closedir($handle);
        }
//        if ($file != "")
//        {
//            // Remove file extension
//            $filename = preg_replace('/\\.[^.\\s]{3,4}$/', '', $file);
//            $print_order_data = file_get_contents($queuepath.$filename);
//            $exploded = explode('_', $print_order_data);
//            $current_copy = $exploded[0];
//            $order_id = $exploded[2];
//            $last_copy = $this->star_cloudprnt_queue_get_last_copy_count($order_id);
//            // Duplicate detected
//            if ($current_copy <= $last_copy)
//            {
//                // Delete duplicate print job
//                unlink($queuepath.$file);
//                unlink($queuepath.$filename);
//                // Call method again to check next file
//                $file = $this->star_cloudprnt_queue_get_next_job($printerMac);
//            }
//        }
        return $file;
    }

    // Adds a file to the next available queue position
    public function star_cloudprnt_queue_add_print_job($printerMac, $filepath, $copycount = 1)
    {
        for ($i = 0; $i < $copycount; $i++)
        {
            $filename = $this->star_cloudprnt_queue_get_next_position($printerMac);
            $extension = pathinfo($filepath, PATHINFO_EXTENSION);
            $printerFolder = $this->star_cloudprnt_get_printer_folder($printerMac);
            copy($filepath, $this->star_cloudprnt_get_os_path(STAR_CLOUDPRNT_PRINTER_DATA_SAVE_PATH.'/'.$printerFolder.'/queue/'.$filename.'.'.$extension));
            $fh = fopen(STAR_CLOUDPRNT_PRINTER_DATA_SAVE_PATH.'/'.$printerFolder.'/queue/'.$filename, "w");
            fwrite($fh, $i.'_'.basename($filepath));
            fclose($fh);
        }
    }

    public function star_cloudprnt_queue_remove_last_print_job($printerMac)
    {
        // Get last print job details
        $filename = $this->star_cloudprnt_queue_get_next_job($printerMac);
        $fileparts = explode('.', $filename);
        $filename2 = $fileparts[0];
        $printerFolder = $this->star_cloudprnt_get_printer_folder($printerMac);
        $filepath = $this->star_cloudprnt_get_os_path(STAR_CLOUDPRNT_PRINTER_DATA_SAVE_PATH.'/'.$printerFolder.'/queue/'.$filename);
        $filepath2 = $this->star_cloudprnt_get_os_path(STAR_CLOUDPRNT_PRINTER_DATA_SAVE_PATH.'/'.$printerFolder.'/queue/'.$filename2);

        // Save successful printed job in order history
        $history_path = STAR_CLOUDPRNT_DATA_FOLDER_PATH.$this->star_cloudprnt_get_os_path("/order_history.txt");
        if (file_exists($history_path))
        {
            $fh = fopen($history_path, 'a');
            fwrite($fh, preg_replace('/\\.[^.\\s]{3,4}$/', '', file_get_contents($filepath2)).'_'.time().PHP_EOL);
            fclose($fh);
        }

        // Delete the print job
        if ($filename != "" && file_exists($filepath))
        {
            unlink($filepath);
            if (file_exists($filepath2)) unlink($filepath2);
        }
    }

    public function star_cloudprnt_queue_get_order_history()
    {
        $history = array();
        $history_path = STAR_CLOUDPRNT_DATA_FOLDER_PATH.$this->star_cloudprnt_get_os_path("/order_history.txt");
        $fh = fopen($history_path, "r");
        if ($fh)
        {
            while (($line = fgets($fh)) !== false)
            {
                $history[] = $line;
            }
            fclose($fh);
        }
        return $history;
    }

    public function star_cloudprnt_queue_get_last_copy_count($order_id)
    {
        $history = array();
        $history_path = STAR_CLOUDPRNT_DATA_FOLDER_PATH.$this->star_cloudprnt_get_os_path("/order_history.txt");
        $fh = fopen($history_path, "r");
        $count = -1;
        if ($fh)
        {
            while (($line = fgets($fh)) !== false)
            {
                $exploded = explode('_', $line);
                $copy = intval($exploded[0]);
                $oid = intval($exploded[2]);
                if (($oid == $order_id) && ($copy > $count)) $count = $copy;
            }
            fclose($fh);
        }
        return $count;
    }

    // Returns a list of queue items in priority order
    public function star_cloudprnt_queue_get_queue_list($printerMac)
    {
        $queueItems = array();
        $queuepath = $this->star_cloudprnt_get_os_path(STAR_CLOUDPRNT_PRINTER_DATA_SAVE_PATH."/".$this->star_cloudprnt_get_printer_folder($printerMac)."/queue/");
        if ($handle = opendir($queuepath))
        {
            while (false !== ($entry = readdir($handle)))
            {
                if ($entry != "." && $entry != "..")
                {
                    // Remove file extension
                    $filename = preg_replace('/\\.[^.\\s]{3,4}$/', '', $entry);
                    if (is_numeric($filename) && strpos($entry, '.') === false)
                    {
                        $filepath = $this->star_cloudprnt_get_os_path(STAR_CLOUDPRNT_PRINTER_DATA_SAVE_PATH."/".$this->star_cloudprnt_get_printer_folder($printerMac)."/queue/".$filename);
                        $queueItems[$filename] = preg_replace('/\\.[^.\\s]{3,4}$/', '', file_get_contents($filepath));
                    }
                }
            }
            closedir($handle);
        }
        if (!empty($queueItems)) ksort($queueItems);
        return $queueItems;
    }

    public function star_cloudprnt_queue_clear_list($printerMac)
    {
        $queuepath = $this->star_cloudprnt_get_os_path(STAR_CLOUDPRNT_PRINTER_DATA_SAVE_PATH."/".$this->star_cloudprnt_get_printer_folder($printerMac)."/queue/");
        if ($handle = opendir($queuepath))
        {
            while (false !== ($entry = readdir($handle)))
            {
                if ($entry != "." && $entry != "..")
                {
                    $filepath = $this->star_cloudprnt_get_os_path(STAR_CLOUDPRNT_PRINTER_DATA_SAVE_PATH."/".$this->star_cloudprnt_get_printer_folder($printerMac)."/queue/".$entry);
                    unlink($filepath);
                }
            }
            closedir($handle);
        }
    }

}
function WC_POS_CPI(){
    return WC_Pos_Cloud_Print_Inc::instance();
}
WC_POS_CPI();