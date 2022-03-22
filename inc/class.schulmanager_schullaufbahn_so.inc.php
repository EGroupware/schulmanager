<?php

/**
 * EGroupware Schulmanager - career of a student - storage object
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

class schulmanager_schullaufbahn_so extends Api\Storage
{
    /**
     * name of the main schullaufbahn table
     */
    var $sm_schullaufbahn_table = 'egw_schulmanager_asv_schullaufbahn';

    var $value_col = array();

    var $customfields = array();

    /**
     * Constructor
     * @throws Api\Exception\WrongParameter
     */
    function __construct() {
        $this->db = clone($GLOBALS['egw']->db);
        $this->db->set_app('schulmanager');
        $this->all_tables = array($this->sm_schullaufbahn_table);

        $this->setup_table('schulmanager', $this->sm_schullaufbahn_table);
        $this->debug = 0;
        foreach(array('asv_id','asv_schueler_stamm_id','asv_schulverzeichnis_id','asv_datum','asv_schuljahr_id','asv_jahrgangsstufe_id','asv_schulbesuchsjahr','asv_bildungsgang_id','asv_wl_vorgang_id','asv_wl_vorgang_zusatz_id','asv_vorgang_bemerkung','asv_klassenname') as $name)
        {
            $this->value_col[$name] = 'sla_'.$name;
        }
        $this->customfields = Storage\Customfields::get('schulmanager', false, null, $this->db);
    }

    /**
     * returns student school history
     * @param $rows
     * @param $schueler_stamm_id
     * @return array
     */
    function queryBySchueler(&$query_in, &$rows, $schueler_stamm_id){

        $result = array();
        $tables = $this->sm_schullaufbahn_table;
        $cols =   implode(', ', $this->value_col).', wl_vorgang.wl_asv_wert_langform AS wl_vorgang_lang, wl_zusatz.wl_asv_wert_langform AS wl_zusatz_lang';   //'DISTINCT egw_untissync_teacher.te_name, egw_untissync_teacher.te_egw_uid';
        $where = array(
            "sla_asv_schueler_stamm_id = ".$this->db->quote($schueler_stamm_id),
        );

        $join = " INNER JOIN egw_schulmanager_asv_werteliste AS wl_vorgang ON wl_vorgang.wl_asv_wert_id = egw_schulmanager_asv_schullaufbahn.sla_asv_wl_vorgang_id ".
                    " LEFT JOIN egw_schulmanager_asv_werteliste AS wl_zusatz ON wl_zusatz.wl_asv_wert_id = egw_schulmanager_asv_schullaufbahn.sla_asv_wl_vorgang_zusatz_id ";
        $append = "ORDER BY sla_asv_schuljahr_id, sla_asv_datum";
        $result = $this->db->select($tables, $cols, $where, '', '', False, $append, False, 0, $join);

        $rowIndex = 0;
        foreach($result as $item){
            $rows[$rowIndex] = array(
                'sla_nm_id' => $rowIndex,
                'sla_nr' => $rowIndex + 1,
                'sla_datum' => $item['sla_asv_datum'],
                'sla_schuljahr' => $item['sla_asv_schuljahr_id'],
                'sla_klasse' => $item['sla_asv_klassenname'],
                'sla_vorgang' => $item['wl_vorgang_lang'],
                'sla_zusatz' => $item['wl_zusatz_lang'],
                'sla_bemerkung' => $item['sla_asv_vorgang_bemerkung'],
            );

            $rowIndex++;
        }

        if($query_in['start'] == 0){
            $query_in['total'] = sizeof($rows);//->NumRows();
        }
    }
}