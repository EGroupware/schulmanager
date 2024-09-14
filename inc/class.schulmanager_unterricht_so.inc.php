<?php

/**
 * EGroupware - Schulmanager - lessons
 *
 * @link http://www.egroupware.org
 * @author Axel Wild <info-AT-wild-solutions.de>
 * @package schulmanager
 * @copyright (c) 2024 by Axel Wild <info-AT-wild-solutions.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

use EGroupware\Api;


class schulmanager_unterricht_so extends Api\Storage {

    var $schulmanager_unterricht_table = 'egw_schulmanager_unterrichtselement2';

    var $schulmanager_unterricht_schueler_table = 'egw_schulmanager_unterrichtselement2_schueler';
    var $schulmanager_unterricht_lehrer_table = 'egw_schulmanager_unterrichtselement2_lehrer';

    var $value_col = array();

    public function __construct(){
        $this->db = clone($GLOBALS['egw']->db);
        $this->db->set_app('schulmanager');
        $this->all_tables = array($this->schulmanager_unterricht_table);

        $this->setup_table('schulmanager', $this->schulmanager_unterricht_table);

        $this->debug = 0;

        $this->value_col['unt_id'] = $this->schulmanager_unterricht_table.'.unt_id';
        $this->value_col['koppel_id'] = $this->schulmanager_unterricht_table.'.koppel_id';
        $this->value_col['bezeichnung'] = $this->schulmanager_unterricht_table.'.bezeichnung';
        $this->value_col['kg_id'] = $this->schulmanager_unterricht_table.'.kg_id';
        $this->value_col['untart_id'] = $this->schulmanager_unterricht_table.'.untart_id';
        $this->value_col['fach_id'] = $this->schulmanager_unterricht_table.'.fach_id';
    }

    /**
     * @param type $kennung lehrer kennung
     * @return array of schulmanager_klassengr_schuelerfach
     */
    function &loadLehrerUnterricht(array $lehrerStammIDs){
        $unterricht = array();
        if(empty($lehrerStammIDs)){
            return $unterricht;
        }
        foreach($lehrerStammIDs as &$lsid){
            $lsid = "'".$lsid."'";
        }
        $csvIDs = implode(',', $lehrerStammIDs);

        if(empty($csvIDs)){ // called by non-teacher, return empty result from db
            $csvIDs = "FALSE"; // SQL FALSE
        }

        $tables = $this->schulmanager_unterricht_table;
        $cols =   implode(', ', $this->value_col).', egw_schulmanager_asv_schuelerfach.sf_asv_anzeigeform';
        $where = array(
            "lehrer_stamm_id IN (".$csvIDs.")"
        );

        $join = " INNER JOIN egw_schulmanager_unterrichtselement2_lehrer ON egw_schulmanager_unterrichtselement2_lehrer.koppel_id = egw_schulmanager_unterrichtselement2.koppel_id"
                ." INNER JOIN egw_schulmanager_asv_schuelerfach ON egw_schulmanager_asv_schuelerfach.sf_asv_id = egw_schulmanager_unterrichtselement2.fach_id";
        $append = 'ORDER BY '.$this->schulmanager_unterricht_table.'.bezeichnung, '.$this->schulmanager_unterricht_table.".koppel_id";
        $rs = $this->db->select($tables, $cols, $where, '', '', False, $append, False, 0, $join);

        $uhset = array();

        foreach($rs as $row){
            $duplicateKey = $row['kg_id'].'#'.$row['fach_id'];
            // ignore lessons with identical class group and subject
            if(!array_key_exists($duplicateKey, $uhset) && !array_key_exists($row['koppel_id'], $uhset)){
                $unterricht[] = array(
                    'unt_id' => $row['unt_id'],
                    'koppel_id' => $row['koppel_id'],
                    'bezeichnung' => $row['bezeichnung'],
                    'kg_id' => $row['kg_id'],
                    'untart_id' => $row['untart_id'],
                    'fach_id' => $row['fach_id'],
                    'fach_name' => $row['sf_asv_anzeigeform']
                );
            }
            if(!empty($row['kg_id'])){
                $uhset[$duplicateKey] = $unterricht;
            }
            $uhset[$row['koppel_id']] = $unterricht;
        }

        // load substitutions
        $substitution_so = new schulmanager_substitution_so();
        $subs = $substitution_so->load($unterricht, $GLOBALS['egw_info']['user']['account_lid']);

        foreach($subs as $row){
            $duplicateKey = $row['kg_id'].'#'.$row['fach_id'];
            // ignore lessons with identical class group ans subject
            if(!array_key_exists($duplicateKey, $uhset) && !array_key_exists($row['koppel_id'], $uhset)){
                $unterricht[] = array(
                    'unt_id' => $row['unt_id'],
                    'koppel_id' => $row['koppel_id'],
                    'bezeichnung' => $row['bezeichnung'],
                    'kg_id' => $row['kg_id'],
                    'untart_id' => $row['untart_id'],
                    'fach_id' => $row['fach_id']
                );
                if(!empty($row['kg_asv_id'])){
                    $uhset[$duplicateKey] = $row['koppel_id'];
                }
                $uhset[$row['koppel_id']] = $row['koppel_id'];
            }
        }
        return $unterricht;
    }





    /**
     * Loads lessons with subject by student
     *
     * Lessons that are made up of several elements (e.g. Ph, C) can be created using the ident. Class group and subject ID can be combined.
     * There is no class group for seminars, although the subject ID could be identical.
     *
     * @param type $kennung lehrer kennung
     * @return array of schulmanager_klassengr_schuelerfach
     */
    function &loadSchuelerUnterricht($schueler_id){
        $unterricht = array();
        if(empty($schueler_id)){
            return $unterricht;
        }

        $tables = $this->schulmanager_unterricht_schueler_table;
        $cols =   'DISTINCT '.$this->schulmanager_unterricht_schueler_table.'.koppel_id, '
            .$this->schulmanager_unterricht_schueler_table.'.schueler_id, '
            .$this->schulmanager_unterricht_schueler_table.'.belegart_id, '
            .$this->schulmanager_unterricht_schueler_table.'.untart, '
            .$this->schulmanager_unterricht_table.'.kg_id, '
            .$this->schulmanager_unterricht_table.'.fach_id, '
            ."egw_schulmanager_asv_schuelerfach.sf_asv_id, "
            ."egw_schulmanager_asv_schuelerfach.sf_asv_kurzform, "
            ."egw_schulmanager_asv_schuelerfach.sf_asv_anzeigeform, "
            ."egw_schulmanager_asv_schuelerfach.sf_asv_pflichtfach";
        $where = array(
            "schueler_id='".$schueler_id."'"
        );

        $join = " INNER JOIN egw_schulmanager_unterrichtselement2 ON  egw_schulmanager_unterrichtselement2.koppel_id = egw_schulmanager_unterrichtselement2_schueler.koppel_id"
                ." INNER JOIN egw_schulmanager_asv_schuelerfach ON  egw_schulmanager_asv_schuelerfach.sf_asv_id = egw_schulmanager_unterrichtselement2.fach_id"
                ." INNER JOIN egw_schulmanager_config ON egw_schulmanager_asv_schuelerfach.sf_asv_kurzform = egw_schulmanager_config.cnf_val ";

        $append = "ORDER BY egw_schulmanager_config.cnf_extra, egw_schulmanager_asv_schuelerfach.sf_asv_kurzform, ".$this->schulmanager_unterricht_schueler_table.".koppel_id";

        $rs = $this->db->select($tables, $cols, $where, '', '', False, $append, False, 0, $join);

        // TODO Merge lessons by class-group,subject-id and by koppel_id

        $uhset = array();
        foreach($rs as $row){
            $duplicateKey = $row['kg_id'].'#'.$row['fach_id'];
            if(!array_key_exists($duplicateKey, $uhset) && !array_key_exists($row['koppel_id'], $uhset)) {
                $unterricht[] = array(
                    'koppel_id' => $row['koppel_id'],
                    'schueler_id' => $row['schueler_id'],
                    'belegart_id' => $row['belegart_id'],
                    'untart' => $row['untart'],
                    'sf_asv_id' => $row['sf_asv_id'],
                    'sf_asv_kurzform' => $row['sf_asv_kurzform'],
                    'sf_asv_anzeigeform' => $row['sf_asv_anzeigeform'],
                    'sf_asv_pflichtfach' => $row['sf_asv_pflichtfach']
                );
            }

            if (!empty($row['kg_id'])) {
                $uhset[$duplicateKey] = $row['koppel_id'];
            }
            $uhset[$row['koppel_id']] = $row['koppel_id'];
        }
        return $unterricht;
    }


    /**
     * Loads teacher by lesson of given student
     * @param $koppel_id
     * @return void
     */
    function &loadUnterrichtLehrer($schueler_id, $untart, $belegart_id, $fach_id){ //$koppel_id){
        $teacher = array();

        $tables = $this->schulmanager_unterricht_schueler_table;
        $cols =   'DISTINCT '
            ."egw_schulmanager_asv_lehrer_stamm.ls_asv_familienname, "
            ."egw_schulmanager_asv_lehrer_stamm.ls_asv_rufname, "
            ."egw_schulmanager_asv_lehrer_stamm.ls_asv_zeugnisname1, "
            ."egw_schulmanager_asv_lehrer_stamm.ls_asv_zeugnisname2, "
            ."egw_schulmanager_asv_lehrer_stamm.ls_asv_amtsbezeichnung_id";
        $where = array(
            "egw_schulmanager_unterrichtselement2_schueler.schueler_id='".$schueler_id."'",
            "egw_schulmanager_unterrichtselement2_schueler.untart='".$untart."'",
            "egw_schulmanager_unterrichtselement2_schueler.belegart_id='".$belegart_id."'",
            "egw_schulmanager_unterrichtselement2.fach_id='".$fach_id."'",
        );

        $join = " INNER JOIN egw_schulmanager_unterrichtselement2 ON egw_schulmanager_unterrichtselement2.koppel_id = egw_schulmanager_unterrichtselement2_schueler.koppel_id".
                " INNER JOIN egw_schulmanager_unterrichtselement2_lehrer ON egw_schulmanager_unterrichtselement2_lehrer.koppel_id = egw_schulmanager_unterrichtselement2.koppel_id".
                " INNER JOIN egw_schulmanager_asv_lehrer_stamm ON egw_schulmanager_asv_lehrer_stamm.ls_asv_id = egw_schulmanager_unterrichtselement2_lehrer.lehrer_stamm_id";

        $append = "ORDER BY egw_schulmanager_asv_lehrer_stamm.ls_asv_familienname";

        $rs = $this->db->select($tables, $cols, $where, '', '', False, $append, False, 0, $join);

        foreach($rs as $row){
            $teacher[] = array(
                'ls_asv_rufname' => $row['ls_asv_rufname'],
                'ls_asv_familienname' => $row['ls_asv_familienname'],
                'ls_asv_wl_geschlecht_id' => $row['ls_asv_wl_geschlecht_id'],
                'ls_asv_zeugnisname1' => $row['ls_asv_zeugnisname1'],
                'ls_asv_zeugnisname2' => $row['ls_asv_zeugnisname2'],
                'ls_asv_amtsbezeichnung_id' => $row['ls_asv_amtsbezeichnung_id'],
                'geschlecht' => schulmanager_werteliste_bo::getGeschlecht($row['ls_asv_wl_geschlecht_id'], 'kurzform')
            );
        }
        return $teacher;
    }

    /**
     * Returns a list with students by lesson id (koppel_id) with marks
     * @param $query_in
     * @param $koppel_id
     * @param $rows
     * @param $gewichtungen
     * @return int
     */
    function &loadSchuelerNotenList(&$query_in, $koppel_id, &$rows, $gewichtungen){
        $note_bo = new schulmanager_note_bo();
        $limit = '';
        if(isset($query_in['start']) && isset($query_in['num_rows'])){
            $limit = " LIMIT ".$query_in['start'].", ".$query_in['num_rows'];
        }

        $tables = $this->schulmanager_unterricht_schueler_table;
        $cols =   'egw_schulmanager_asv_schueler_stamm.sch_asv_id AS st_asv_id,
					egw_schulmanager_asv_schueler_stamm.sch_asv_familienname AS st_asv_familienname,
					egw_schulmanager_asv_schueler_stamm.sch_asv_rufname AS st_asv_rufname,
					egw_schulmanager_asv_schueler_stamm.sch_asv_austrittsdatum AS st_asv_austrittsdatum,
                    egw_schulmanager_asv_klassengruppe.kg_asv_id AS kg_id,
                    egw_schulmanager_asv_klassengruppe.kg_asv_kennung AS kg_kennung,
                    egw_schulmanager_asv_klasse.kl_asv_id AS kl_id,
                    egw_schulmanager_asv_klasse.kl_asv_klassenname AS kl_name';
        $where = array(
            $this->schulmanager_unterricht_schueler_table.".koppel_id='".$koppel_id."'",
        );

        $join = ' INNER JOIN egw_schulmanager_asv_schueler_stamm ON egw_schulmanager_asv_schueler_stamm.sch_asv_id = '.$this->schulmanager_unterricht_schueler_table.'.schueler_id'
                .' INNER JOIN egw_schulmanager_asv_schueler_schuljahr ON egw_schulmanager_asv_schueler_schuljahr.ss_asv_schueler_stamm_id = egw_schulmanager_asv_schueler_stamm.sch_asv_id'
                .' INNER JOIN egw_schulmanager_asv_klassengruppe ON egw_schulmanager_asv_klassengruppe.kg_asv_id = egw_schulmanager_asv_schueler_schuljahr.ss_asv_klassengruppe_id'
                .' INNER JOIN egw_schulmanager_asv_klasse ON egw_schulmanager_asv_klasse.kl_asv_id = egw_schulmanager_asv_klassengruppe.kg_asv_klasse_id';

        $append = "ORDER BY st_asv_familienname, st_asv_rufname COLLATE 'utf8_general_ci'";

        $rs = $this->db->select($tables, $cols, $where, '', '', False, $append, False, 0, $join);
        $rowid = 0;
        $id = 1;

        foreach($rs as $row){
            $schueler_id = $row['st_asv_id'];
            $schueler = array(
                'nm_id'		=> $id,
                'nm_st'		=> array(
                    'st_asv_id'			  => $schueler_id,
                    'sch_schuljahr_asv_id' => $row['asv_schueler_schuljahr_id'],
                    'st_asv_familienname' => $row['st_asv_familienname'],
                    'st_asv_rufname'	  => $row['st_asv_rufname'],
                    'st_asv_austrittsdatum' => $row['st_asv_austrittsdatum'],
                    'nm_st_class'		=> ''
                ),
                'klasse'     => array(
                    'id' => $row['kl_id'],
                    'name' => $row['kl_name'],
                    'kg_id' => $row['kg_id'],
                    'kg_kennung' => $row['kg_kennung'],
                ),
            );

            $schueler['noten'] = $note_bo->getNotenTemplate();
            $note_bo->loadNotenBySchueler($schueler_id, $koppel_id, $schueler);

            $note_bo->beforeSendToClient($schueler, $gewichtungen);
            $rows[$rowid] = $schueler;
            $id++;
            $rowid++;
        }
        // writes calculated values to Database
        $note_bo->writeAutoValues($rows, $koppel_id);
        $total = count($rows);
        return $total;
    }
}