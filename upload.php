<?php
/**
 * EGroupware - schulmanager
 *
 * API call for uploading
 * curl --user <username>:<password> --data @<filename> -X POST https://egw.example.org/egroupware/schulmanager/upload.php
 *
 * Hook will give the following http status:
 * - "200 OK" on success / credentials are changed
 * - "401 Unauthorized", if new password is wrong or not supplied via basic auth
 * - "500 Internal server error" on error
 *
 * exported datab from postresql by COPY(...) TO ... WITH DELIMITER ';' ESCAPE '\' QUOTE '"' CSV HEADER;
 *
 * @link http://www.egroupware.org
 * @package schulmanager
 * @author Axel Wild <info-AT-wild-solutions.de>
 * @copyright (c) 2022 by info-AT-wild-solutions.de
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

use EGroupware\Api;

$GLOBALS['egw_info'] = array(
    'flags' => array(
        'disable_Template_class' => True,
        'noheader'  => True,
        'currentapp' => 'admin',
        'autocreate_session_callback' => 'EGroupware\Api\Header\Authenticate::autocreate_session_callback',
    )
);

require(dirname(__DIR__).'/header.inc.php');

/**
 * Enable extra debug messages via error_log (error always get logged)
 */
$debug = true;

/**
 * csv field seperator
 */
// TODO $CSV_SEP = ",";

/**
 * array contains names valid csv-filenames and corresponding table name
 * array
 */
$file2table = array(
    'svp_besuchtes_fach.csv' => 'egw_schulmanager_asv_besuchtes_fach',
    'svp_fachgruppe.csv' => 'egw_schulmanager_asv_fachgruppe',
    'svp_klasse.csv' => 'egw_schulmanager_asv_klasse',
    'svp_klassengruppe.csv' => 'egw_schulmanager_asv_klassengruppe',
    'svp_klassenleitung.csv' => 'egw_schulmanager_asv_klassenleitung',
    'svp_lehrer_schuljahr.csv' => 'egw_schulmanager_asv_lehrer_schuljahr',
    'svp_lehrer_schuljahr_schule.csv' => 'egw_schulmanager_asv_lehrer_schuljahr_schule',
    'svp_lehrer_stamm.csv' => 'egw_schulmanager_asv_lehrer_stamm',
    'svp_lehrer_unterr_faecher.csv' => 'egw_schulmanager_asv_lehrer_unterr_faecher',
    'svp_schueler_schuljahr.csv' => 'egw_schulmanager_asv_schueler_schuljahr',
    'svp_schueler_stamm.csv' => 'egw_schulmanager_asv_schueler_stamm',
    'svp_schueleranschrift.csv' => 'egw_schulmanager_asv_schueleranschrift',
    'svp_schuelerfach.csv' => 'egw_schulmanager_asv_schuelerfach',
    'svp_schuelerkommunikation.csv' => 'egw_schulmanager_asv_schuelerkommunikation',
    'svp_schule_fach.csv' => 'egw_schulmanager_asv_schule_fach',
    'svp_schullaufbahn.csv' => 'egw_schulmanager_asv_schullaufbahn',
    'svp_unterrichtselement.csv' => 'egw_schulmanager_asv_unterrichtselement',
    'svp_werteliste.csv' => 'egw_schulmanager_asv_werteliste',
    'svp_wl_jahrgangsstufe.csv' => 'egw_schulmanager_asv_jahrgangsstufe',
);

$output = array();
$response_code = 200;

try {
    $db = clone($GLOBALS['egw']->db);
    foreach ($_FILES as $file) {
        $filename = $file['name'];
        $table = $file2table[$filename];

        if($table){
            if ($debug) error_log(__METHOD__.'(start reading '.$filename);
            $tmp_filename= $file['tmp_name'];
            $fhandle = fopen($tmp_filename, "r") or die("Unable to open file!");
            // db import
            if($fhandle) {
                $result = import($fhandle, $table, $db);
                // close and remove
                fclose($fhandle);
                unlink($tmp_filename);

                // output and loging
                if ($debug) error_log(__METHOD__ . ' imported ' . $result . ' records from ' . $filename . ' into table ' . $table);
                if ($result > 0){
                    $output[] = "OK - ".$result.' records imported from '.$filename.' into table '.$table;
                }
                else {
                    error_log(__METHOD__ . ' WARNING: imported ' . $result . ' records from ' . $filename . ' into table ' . $table);
                    $output[] = "ERROR - " . $result . ' records imported from ' . $filename . ' into table ' . $table;
                }
            }
            else{
                $output[] = "ERROR - could not open ".$filename;
                error_log(__METHOD__.' could not open '.$filename);
            }
        }
        else{
            $output[] = "ERROR - table does not exists for: ".$filename;
            $response_code = 400;
            break;
        }

    }
    http_response_code($response_code);	// No Content
}
catch (\Exception $e) {
    $excMsg = $e->getMessage();
    $output[] = "ERROR - ".substr($excMsg, 0, 250);
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    error_log(__METHOD__.$e->getMessage());
}

echo implode('\n', $output);


/**
 * reads a single csv-file and imports records to db
 * @param File $file
 */
function import($fhandle, String $table, $db){
    // truncate table
    $sql = "TRUNCATE $table";
    $db->query($sql, __LINE__, __FILE__);

    // import
    $tableDef = $db->get_table_definitions('schulmanager', $table);
    $sqlImport = '';
    $result = createSQLImport($fhandle, $table, $sqlImport, ",", $db, $tableDef);#
    $db->query($sqlImport, __LINE__, __FILE__);
    return $result;
}

/**
 * Creates sql query to import csv data
 * @param $fhandle
 * @param $table
 * @param $sql
 * @return int number of records
 */
function createSQLImport($fhandle, $table, &$sql, $csv_sep, $db, $tableDef){
    // TODO set 1000 to 0 or null
    $csvMaxLength = 2000;
    $sql = 'INSERT INTO '.$table;

    $colNames = array_keys($tableDef['fd']);
    // validate number of csv header and fd

    $header = fgetcsv($fhandle, $csvMaxLength, $csv_sep);

    if(count($header) != count($colNames)){
        error_log(__METHOD__."() invalid number of columns: columns in csv file=".count($header)." (".implode(',', $header)."); columns in table ".$table."=".count($colNames)."(".implode(',', $colNames).")");
        return -1;
    }
    $sql = 'INSERT INTO '.$table.' ('.implode(',', $colNames).') VALUES ';

    $lineIndex = 0;
    $fd = array_values($tableDef['fd']);
    while(($data = fgetcsv($fhandle, $csvMaxLength, $csv_sep)) !== FALSE) {
        // read values
        if($lineIndex > 0){
            $sql .= ',';
        }

        $quotedValues = array();
        foreach($data as $key => $val){
            $type = $fd[$key]['type'];
            $val = stripcslashes($val);
            $quotedValues[] = $db->quote($val, $type);
        }
        $sql .= '('.implode(',', $quotedValues).')';
        $lineIndex++;
    }
    return $lineIndex;
}
