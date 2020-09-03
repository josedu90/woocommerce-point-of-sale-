<?php

class WC_Pos_Cloud_Print_Printer
{
    private $printer_mac;

    public function __construct($printer_mac)
    {
        $this->printer_mac = $printer_mac;
    }

    // Returns all the data of a specific printer that is or has in the past polled the server
    public function getPrinterData()
    {
        $printerpath = STAR_CLOUDPRNT_PRINTER_DATA_SAVE_PATH."/".WC_POS_CPI()->star_cloudprnt_get_printer_folder($this->printer_mac);

        $printerdata = array();
        $jsonpath = $printerpath."/communication.json";
        if (file_exists($jsonpath)) $printerdata = json_decode(file_get_contents($jsonpath), true);

        $jsonpath = $printerpath."/data.json";
        if (file_exists($jsonpath))
        {
            $printerdata2 = json_decode(file_get_contents($jsonpath), true);
            $printerdata['name'] = $printerdata2['name'];
            $printerdata['ipAddress'] = $printerdata2['ipAddress'];
            $printerdata['lastActive'] = filemtime($jsonpath);
        }

        $jsonpath = $printerpath."/additional_communication.json";
        if (file_exists($jsonpath))
        {
            $printerdata3 = json_decode(file_get_contents($jsonpath), true);
            foreach ($printerdata3['clientAction'] as $data)
            {
                $printerdata[$data['request']] = $data['result'];
            }
            $printerdata['printerOnline'] = WC_POS_CPI()->star_cloudprnt_is_printer_online($printerdata['GetPollInterval'], filemtime($printerpath."/data.json"));
        }

        return $printerdata;
    }

    // Creates printer data file for a newly joined printer
    public function createPrinterData($ip)
    {
        $jsonpath = STAR_CLOUDPRNT_PRINTER_DATA_SAVE_PATH."/".WC_POS_CPI()->star_cloudprnt_get_printer_folder($this->printer_mac)."/data.json";

        if (!file_exists($jsonpath))
        {
            $printerdata = array("name" => $this->printer_mac,
                "macAddress" => $this->printer_mac,
                "ipAddress" => $ip,
                "isOnline" => true);
            $fp = fopen($jsonpath, 'w');
            fwrite($fp, json_encode($printerdata));
            fclose($fp);
        }
        else $this->updatePrinterData("ipAddress", $ip);

        return true;
    }

    public function updatePrinterData($variable, $value)
    {
        $jsonpath = STAR_CLOUDPRNT_PRINTER_DATA_SAVE_PATH."/".WC_POS_CPI()->star_cloudprnt_get_printer_folder($this->printer_mac)."/data.json";
        if (file_exists($jsonpath))
        {
            $printerdata = json_decode(file_get_contents($jsonpath), true);
            $printerdata[$variable] = $value;
            $fp = fopen($jsonpath, 'w');
            fwrite($fp, json_encode($printerdata));
            fclose($fp);
        }
    }

    public function deleteDirectory($dir)
    {
        if (!file_exists($dir)) return true;

        if (!is_dir($dir)) return unlink($dir);

        foreach (scandir($dir) as $item)
        {
            if ($item == '.' || $item == '..') continue;

            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) return false;
        }
        return rmdir($dir);
    }

    public function deletePrinter()
    {
        $printerdata = $this->getPrinterData($this->printer_mac);
        if (!$printerdata['printerOnline'])
        {
            $dirpath = STAR_CLOUDPRNT_PRINTER_DATA_SAVE_PATH."/".WC_POS_CPI()->star_cloudprnt_get_printer_folder($this->printer_mac);
            $this->deleteDirectory($dirpath);
        }
    }

}