<?php

/**
 * EGroupware Schulmanager - content of student report
 *
 * @link http://www.egroupware.org
 * @package schulmanager
 * @author Axel Wild <info-AT-wild-solutions.de>
 * @copyright (c) 2023 by info-AT-wild-solutions.de
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

use EGroupware\Api;
use EGroupware\Api\Storage;

define('KEY_GEFAEHRD', 'key_gefaehrd');
define('KEY_ABWEISUNG', 'key_abweisung');

/**
 * substitutions in EGroupware
 * @author axel
 *
 */
class schulmanager_sreportcontent_so extends Api\Storage {


    var $schulmanager_sreportcontent_table = 'egw_schulmanager_sreportcontent';

    var $value_col = array();

    var $customfields = array();

    public function __construct(){
        $this->db = clone($GLOBALS['egw']->db);
        $this->db->set_app('schulmanager');
        $this->all_tables = array($this->schulmanager_sreportcontent_table);


        $this->setup_table('schulmanager', $this->schulmanager_sreportcontent_table);

        $this->debug = 0;

        $this->value_col['id'] = 'sr_id';
        $this->value_col['asv_schueler_stamm_id'] = 'sr_asv_schueler_stamm_id';
        $this->value_col['key'] = 'sr_key';
        $this->value_col['asv_wert_id'] = 'sr_asv_wert_id';
        $this->value_col['asv_wert_kurzform'] = 'sr_asv_wert_kurzform';
        $this->value_col['asv_wert_anzeigeform'] = 'sr_asv_wert_anzeigeform';
        $this->value_col['value'] = 'sr_value';
        $this->value_col['update_date'] = 'sr_update_date';
        $this->value_col['update_user'] = 'sr_update_date';


        $this->customfields = Storage\Customfields::get($app, false, null, $db);
    }

    /**
     * loads all contents of stud
     * @param array $unterricht
     * @return unknown
     */
    function load($asv_schueler_stamm, $key = null){
        $filter = array();
        $filter[] = "sr_asv_schueler_stamm_id = '$asv_schueler_stamm'";

        if(isset($key) && !empty($key)){
            $filter[] = "sr_key = '$key'";
        }
        $result = $this->query_list($this->value_col, '', $filter);



        return $result;
    }

    /**
     * @param $asv_schueler_stamm
     * @param $key
     * @param $val
     * @return false|mixed
     */
    function saveItem($asv_schueler_stamm, $key, $value, $asv_wert_id = null, $asv_wert_kurzform = null, $asv_wert_anzeigeform = null){
        $time = date("Y-m-d H:i:s").'.000';//time();
        $kennung = $GLOBALS['egw_info']['user']['account_lid'];

        $srcExists = $this->load($asv_schueler_stamm, $key);

        $src = array(
            'sr_asv_schueler_stamm_id' => $asv_schueler_stamm,
            'sr_key' => $key,
            'sr_asv_wert_id' => $asv_wert_id,
            'sr_asv_wert_kurzform' => $asv_wert_kurzform,
            'sr_asv_wert_anzeigeform' => $asv_wert_anzeigeform,
            'sr_value' => $value,
            'sr_update_date' => $time,
            'sr_update_user' => $kennung
        );

        if(!empty($srcExists)) {
            $keys = array_keys($srcExists);
            $src['sr_id'] = $srcExists[$keys[0]]['id'];
            $this->data = $src;
            if(parent::update($src, true) != 0) return false;
        }
        else {
            $this->data = $src;
            if(parent::save() != 0) return false;
        }
        return $this->data['sr_id'];
    }

    function truncate(){
        $sql = "TRUNCATE $this->schulmanager_sreportcontent_table";
        return $this->db->query($sql, __LINE__, __FILE__);
    }
}