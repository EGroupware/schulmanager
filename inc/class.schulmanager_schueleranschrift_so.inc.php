<?php

/**
* EGroupware Schulmanager - contact of a student - storage object
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

class schulmanager_schueleranschrift_so extends Api\Storage
{
    /**
     * name of the main schueleranschrift table
     */
    var $sm_schueleranschrift_table = 'egw_schulmanager_asv_schueleranschrift';

    var $value_col = array();

    var $customfields = array();

    /**
     * Constructor
     * @throws Api\Exception\WrongParameter
     */
    function __construct() {
        $this->db = clone($GLOBALS['egw']->db);
        $this->db->set_app('schulmanager');
        $this->all_tables = array($this->sm_schueleranschrift_table);

        $this->setup_table('schulmanager', $this->sm_schueleranschrift_table);

        $this->debug = 0;

        foreach(array('asv_id','asv_schueler_stamm_id','asv_wl_anschriftstyp_id','asv_auskunftsberechtigt','asv_hauptansprechpartner',
                    'asv_im_verteiler_schriftverkehr','asv_strasse','asv_nummer','asv_postleitzahl','asv_ortsbezeichnung','asv_ortsteil',
                    'asv_anredetext','asv_anschrifttext','asv_wl_staat_id','asv_wl_personentyp_id','asv_familienname','asv_vornamen',
                    'asv_wl_akademischer_grad_id','asv_wl_anrede_id') as $name)
        {
            $this->value_col[$name] = 'san_'.$name;
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
        $tables = $this->sm_schueleranschrift_table;

        $cols =   implode(', ', $this->value_col).', wl_anschrift.wl_asv_wert_kurzform AS anschrift_kurz, wl_anschrift.wl_asv_wert_anzeigeform AS anschrift_anzeige,
                                                            wl_staat.wl_asv_wert_kurzform AS staat_kurz, wl_staat.wl_asv_wert_anzeigeform AS staat_anzeige, 
                                                            wl_personentyp.wl_asv_wert_kurzform AS personentyp_kurz, wl_personentyp.wl_asv_wert_anzeigeform AS personentyp_anzeige, 
                                                            wl_akad.wl_asv_wert_kurzform AS akad_kurz, wl_akad.wl_asv_wert_anzeigeform AS akad_anzeige, 
                                                            wl_anrede.wl_asv_wert_kurzform AS anrede_kurz, wl_anrede.wl_asv_wert_anzeigeform AS anrede_anzeige';

        $where = array(
            "san_asv_schueler_stamm_id = ".$this->db->quote($schueler_stamm_id),
        );

        $join = " LEFT JOIN egw_schulmanager_asv_werteliste AS wl_anschrift ON wl_anschrift.wl_asv_wert_id = egw_schulmanager_asv_schueleranschrift.san_asv_wl_anschriftstyp_id 
                  LEFT JOIN egw_schulmanager_asv_werteliste AS wl_staat ON wl_staat.wl_asv_wert_id = egw_schulmanager_asv_schueleranschrift.san_asv_wl_staat_id 
                  LEFT JOIN egw_schulmanager_asv_werteliste AS wl_personentyp ON wl_personentyp.wl_asv_wert_id = egw_schulmanager_asv_schueleranschrift.san_asv_wl_personentyp_id 
                  LEFT JOIN egw_schulmanager_asv_werteliste AS wl_akad ON wl_akad.wl_asv_wert_id = egw_schulmanager_asv_schueleranschrift.san_asv_wl_akademischer_grad_id 
                  LEFT JOIN egw_schulmanager_asv_werteliste AS wl_anrede ON wl_anrede.wl_asv_wert_id = egw_schulmanager_asv_schueleranschrift.san_asv_wl_anrede_id";

        $append = "";

        $result = $this->db->select($tables, $cols, $where, '', '', False, $append, False, 0, $join);

        $rowIndex = 0;
        foreach($result as $item){
            $rows[$rowIndex] = array(
                'san_nm_id' => $rowIndex,
                'san_nr' => $rowIndex + 1,
                'san_asv_auskunftsberechtigt' => $item['san_asv_auskunftsberechtigt'],

                'san_asv_hauptansprechpartner' => $item['san_asv_hauptansprechpartner'],
                'san_asv_im_verteiler_schriftverkehr' => $item['san_asv_im_verteiler_schriftverkehr'],
                'san_asv_strasse' => $item['san_asv_strasse'],
                'san_asv_nummer' => $item['san_asv_nummer'],
                'san_asv_postleitzahl' => $item['san_asv_postleitzahl'],
                'san_asv_ortsbezeichnung' => $item['san_asv_ortsbezeichnung'],

                'san_asv_ortsteil' => $item['san_asv_ortsteil'],
                'san_asv_anredetext' => $item['san_asv_anredetext'],
                'san_asv_anschrifttext' => $item['san_asv_anschrifttext'],
                'san_asv_familienname' => $item['san_asv_familienname'],
                'san_asv_vornamen' => $item['san_asv_vornamen'],
                'san_anschrift_kurz' => $item['anschrift_kurz'],
                'san_anschrift_anzeige' => $item['anschrift_anzeige'],
                'san_staat_kurz' => $item['staat_kurz'],
                'san_staat_anzeige' => $item['staat_anzeige'],
                'san_personentyp_kurz' => $item['personentyp_kurz'],
                'san_personentyp_anzeige' => $item['personentyp_anzeige'],
                'san_akad_kurz' => $item['akad_kurz'],
                'san_akad_anzeige' => $item['akad_anzeige'],
                'san_anrede_kurz' => $item['anrede_kurz'],
                'san_anrede_anzeige' => $item['anrede_anzeige'],
            );
            $rowIndex++;
        }

        if($query_in['start'] == 0){
            $query_in['total'] = sizeof($rows);//->NumRows();
        }
    }
}