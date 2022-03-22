<?php

/**
 * EGroupware Schulmanager - werteliste storage object
 *
 * @link http://www.egroupware.org
 * @package schulmanager
 * @author Axel Wild <info-AT-wild-solutions.de>
 * @copyright (c) 2022 by info-AT-wild-solutions.de
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

use EGroupware\Api;
use EGroupware\Api\Storage;
/**
 * Description of class
 * @author axel
 */
class schulmanager_werteliste_so extends Api\Storage
{
    var $schulmanager_werteliste_table = 'egw_schulmanager_asv_werteliste';

    var $value_col = array();

    var $customfields = array();

    public function __construct()
    {
        $this->db = clone($GLOBALS['egw']->db);
        $this->db->set_app('schulmanager');
        $this->all_tables = array($this->schulmanager_werteliste_table);

        $this->setup_table('schulmanager', $this->schulmanager_werteliste_table);

        $this->debug = 0;

        foreach(array('id','asv_wl_id','asv_wl_schluessel','asv_wl_bezeichnung','asv_wl_schulartspezifisch','asv_wert_id','asv_wert_schluessel','asv_wert_kurzform','asv_wert_anzeigeform','asv_wert_langform') as $name)
        {
            $this->value_col[$name] = 'wl_'.$name;
        }
        $this->customfields = Storage\Customfields::get('schulmanager', false, null, $this->db);
    }

    /**
     * @param $asv_wl_schluessel
     * @param $rows
     * @return int
     */
    function loadWerteliste($asv_wl_schluessel, &$rows){
        $filter = array();
        $filter[] = "wl_asv_wl_schluessel = ".$this->db->quote($asv_wl_schluessel);

        $result = $this->query_list($this->value_col, '', $filter);
        $index = 0;
        foreach($result as $ro){
            $rows[$index] = $ro;
            $index++;
        }
        return count($result);
    }
}