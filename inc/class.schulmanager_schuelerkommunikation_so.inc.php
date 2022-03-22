<?php

/**
 * EGroupware Schulmanager - communication - storage object
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

class schulmanager_schuelerkommunikation_so extends Api\Storage
{
    /**
     * name of the main schuelerkommunikation table
     */
    var $sm_schuelerkommunikation_table = 'egw_schulmanager_asv_schuelerkommunikation';

    var $value_col = array();

    var $customfields = array();


    /**
     * Constructor
     * @throws Api\Exception\WrongParameter
     */
    function __construct() {
        $this->db = clone($GLOBALS['egw']->db);
        $this->db->set_app('schulmanager');
        $this->all_tables = array($this->sm_schuelerkommunikation_table);

        $this->setup_table('schulmanager', $this->sm_schuelerkommunikation_table);

        $this->debug = 0;

        foreach(array('asv_id','asv_schueler_stamm_id','asv_wl_kommunikationstyp_id', 'asv_kommunikationsadresse', 'asv_bemerkung') as $name)
        {
            $this->value_col[$name] = 'sko_'.$name;
        }
        $this->customfields = Storage\Customfields::get('schulmanager', false, null, $this->db);
    }

    /**
     * returns student communication
     * @param $rows
     * @param $schueler_stamm_id
     * @return array
     */
    function queryBySchueler(&$query_in, &$rows, $schueler_stamm_id){

        $result = array();
        $tables = $this->sm_schuelerkommunikation_table;

        $cols =   implode(', ', $this->value_col).', wl_asv_wert_kurzform, wl_asv_wert_anzeigeform';

        $where = array(
            "sko_asv_schueler_stamm_id = ".$this->db->quote($schueler_stamm_id),
        );

        $join = " INNER JOIN egw_schulmanager_asv_werteliste ON egw_schulmanager_asv_werteliste.wl_asv_wert_id = egw_schulmanager_asv_schuelerkommunikation.sko_asv_wl_kommunikationstyp_id";

        $append = "";

        $result = $this->db->select($tables, $cols, $where, '', '', False, $append, False, 0, $join);

        $rowIndex = 0;
        foreach($result as $item){
            $rows[$rowIndex] = array(
                'sko_nm_id' => $rowIndex,
                'sko_nr' => $rowIndex + 1,
                'sko_adress' => $item['sko_asv_kommunikationsadresse'],
                'sko_note' => $item['sko_asv_bemerkung'],
                'sko_type' => $item['wl_asv_wert_anzeigeform'],
            );

            $rowIndex++;
        }

        if($query_in['start'] == 0){
            $query_in['total'] = sizeof($rows);//->NumRows();
        }
    }
}