<?php

/**
 * EGroupware Schulmanager - teacher - storage object
 *
 * @link http://www.egroupware.org
 * @package schulmanager
 * @author Axel Wild <info-AT-wild-solutions.de>
 * @copyright (c) 2022 by info-AT-wild-solutions.de
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

use EGroupware\Api;

require_once(EGW_INCLUDE_ROOT . '/schulmanager/inc/class.schulmanager_klassengr_schuelerfa.inc.php');

class schulmanager_schueler_so {

	/**
	 * name of the main lehrer table and prefix for all other calendar tables
	 */
    var $sm_schueler_table = 'egw_schulmanager_asv_schueler';
	var $stamm_table,$schuljahr_table,$all_tables;

	/**
	 * reference to global db-object
	 * @var Api\Db
	 */
	var $db;

    var $customfields = array();

	/**
	 * instance of the async object
	 *
	 * @var Api\Asyncservice
	 */
	var $async;

    function __construct() {
        $this->db = clone($GLOBALS['egw']->db);
        $this->db->set_app('stundenplan');
        $this->all_tables = array($this->sm_schueler_table);
		foreach(array('stamm','schuljahr') as $name)
		{
			$vname = $name.'_table';
			$this->all_tables[] = $this->$vname = $this->sm_schueler_table.'_'.$name;
		}
    }

    /**
     * returns an array with all class groups
     * @param $st_asv_id
     * @return void
     */
    function getKlassenGruppen($st_asv_id, &$rows){
        $tables = $this->sm_schueler_table."_stamm";

        $cols =  'ss_asv_klassengruppe_id';

        $where = array(
            "sch_asv_id = ".$this->db->quote($st_asv_id),
        );

        $join = "INNER JOIN egw_schulmanager_asv_schueler_schuljahr ON egw_schulmanager_asv_schueler_schuljahr.ss_asv_schueler_stamm_id = egw_schulmanager_asv_schueler_stamm.sch_asv_id";

        $append = "";

        $result = $this->db->select($tables, $cols, $where, '', '', False, $append, False, 0, $join);

        $rowIndex = 0;
        foreach($result as $item){
            $rows[$rowIndex] = $item['ss_asv_klassengruppe_id'];
            $rowIndex++;
        }
    }

    /**
     * returns an array with all subjects
     * @param $st_asv_id
     * @return void
     */
    function getSchuelerFaecher($st_asv_id, &$rows){
        $tables = $this->sm_schueler_table."_stamm";
        $cols =  'bf_asv_schuelerfach_id';
        $where = array(
            "sch_asv_id = ".$this->db->quote($st_asv_id),
        );

        $join = "INNER JOIN egw_schulmanager_asv_schueler_schuljahr ON egw_schulmanager_asv_schueler_schuljahr.ss_asv_schueler_stamm_id = egw_schulmanager_asv_schueler_stamm.sch_asv_id "
            ."INNER JOIN egw_schulmanager_asv_besuchtes_fach ON egw_schulmanager_asv_besuchtes_fach.bf_asv_schueler_schuljahr_id = egw_schulmanager_asv_schueler_schuljahr.ss_asv_id";

        $append = "";

        $result = $this->db->select($tables, $cols, $where, '', '', False, $append, False, 0, $join);

        $rowIndex = 0;
        foreach($result as $item){
            $rows[$rowIndex] = $item['bf_asv_schuelerfach_id'];
            $rowIndex++;
        }
    }

    /**
     * returns an array with all subjects and its data
     * @param $st_asv_id
     * @return void
     */
    function getSchuelerFaecherData($st_asv_id, &$fach){
        $tables = $this->sm_schueler_table."_stamm";
        $cols =  'sf_asv_id,sf_asv_kurzform,sf_asv_anzeigeform,bf_asv_wl_belegart_id,bf_asv_unterrichtsart';
        $where = array(
            "sch_asv_id = ".$this->db->quote($st_asv_id),
            "bf_asv_unterrichtsart = 'P'"
        );

        $join = "INNER JOIN egw_schulmanager_asv_schueler_schuljahr ON egw_schulmanager_asv_schueler_schuljahr.ss_asv_schueler_stamm_id = egw_schulmanager_asv_schueler_stamm.sch_asv_id "
            ."INNER JOIN egw_schulmanager_asv_besuchtes_fach ON egw_schulmanager_asv_besuchtes_fach.bf_asv_schueler_schuljahr_id = egw_schulmanager_asv_schueler_schuljahr.ss_asv_id "
            ."INNER JOIN egw_schulmanager_asv_schuelerfach ON egw_schulmanager_asv_schuelerfach.sf_asv_id = egw_schulmanager_asv_besuchtes_fach.bf_asv_schuelerfach_id "
            ."INNER JOIN egw_schulmanager_config ON egw_schulmanager_asv_schuelerfach.sf_asv_kurzform = egw_schulmanager_config.cnf_val ";

        $append = "ORDER BY egw_schulmanager_config.cnf_extra, egw_schulmanager_asv_schuelerfach.sf_asv_kurzform";

        $result = $this->db->select($tables, 'DISTINCT '.$cols, $where, '', '', False, $append, False, 0, $join);

        $rowIndex = 0;
        $colsArr = explode(',', $cols);
        foreach($result as $item){
            $fach[$rowIndex] = array();
            foreach($colsArr as $colName){
                $fach[$rowIndex][$colName] = $item[$colName];
            }
            $rowIndex++;
        }
    }


    /**
     * Creates abstract of grades
     * @param $schueler_schuljahr_id
     * @param $rows
     * @param $rowid
     * @return mixed
     * @throws Api\Db\Exception\InvalidSql
     */
	function &getNotenAbstract($schueler_schuljahr_id, &$rows, $rowid){

		$sql = "select
			egw_schulmanager_asv_schuelerfach.sf_asv_id AS asv_id,
			egw_schulmanager_asv_schuelerfach.sf_asv_kurzform AS asv_kurzform,
			egw_schulmanager_asv_schuelerfach.sf_asv_anzeigeform AS asv_anzeigeform,

			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'alt_b' THEN egw_schulmanager_note.note_note END) AS alt_b,
			-- 1. HJ
			GROUP_CONCAT(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'glnw_hj_1' AND egw_schulmanager_note.note_index_im_block >= 0 THEN egw_schulmanager_note.note_note END ORDER BY egw_schulmanager_note.note_index_im_block SEPARATOR ' | ') AS glnw_hj_1,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'glnw_hj_1' AND egw_schulmanager_note.note_index_im_block = -1 THEN egw_schulmanager_note.note_note END) AS glnw_hj_1_avg,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'glnw_hj_1' AND egw_schulmanager_note.note_index_im_block = -1 AND egw_schulmanager_note.note_asv_note_manuell = '1' THEN '1' END) AS glnw_hj_1_avg_manuell,

		    GROUP_CONCAT(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'klnw_hj_1' AND egw_schulmanager_note.note_index_im_block >= 0 THEN egw_schulmanager_note.note_note END ORDER BY egw_schulmanager_note.note_index_im_block SEPARATOR ' | ') AS klnw_hj_1,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'klnw_hj_1' AND egw_schulmanager_note.note_index_im_block = -1 THEN egw_schulmanager_note.note_note END) AS klnw_hj_1_avg,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'klnw_hj_1' AND egw_schulmanager_note.note_index_im_block = -1 AND egw_schulmanager_note.note_asv_note_manuell = '1' THEN '1' END) AS klnw_hj_1_avg_manuell,

			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'schnitt_hj_1' THEN egw_schulmanager_note.note_note END) AS schnitt_hj_1,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'schnitt_hj_1' AND egw_schulmanager_note.note_asv_note_manuell = '1' THEN '1' END) AS schnitt_hj_1_manuell,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'note_hj_1' THEN egw_schulmanager_note.note_note END) AS note_hj_1,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'note_hj_1' AND egw_schulmanager_note.note_asv_note_manuell = '1' THEN '1' END) AS note_hj_1_manuell,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'm_hj_1' THEN egw_schulmanager_note.note_note END) AS m_hj_1,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'v_hj_1' THEN egw_schulmanager_note.note_note END) AS v_hj_1,
			-- 2. HJ
			GROUP_CONCAT(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'glnw_hj_2' AND egw_schulmanager_note.note_index_im_block >= 0 THEN egw_schulmanager_note.note_note END ORDER BY egw_schulmanager_note.note_index_im_block SEPARATOR ' | ') AS glnw_hj_2,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'glnw_hj_2' AND egw_schulmanager_note.note_index_im_block = -1 THEN egw_schulmanager_note.note_note END) AS glnw_hj_2_avg,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'glnw_hj_2' AND egw_schulmanager_note.note_index_im_block = -1 AND egw_schulmanager_note.note_asv_note_manuell = '1' THEN '1' END) AS glnw_hj_2_avg_manuell,

			GROUP_CONCAT(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'klnw_hj_2' AND egw_schulmanager_note.note_index_im_block >= 0 THEN egw_schulmanager_note.note_note END ORDER BY egw_schulmanager_note.note_index_im_block SEPARATOR ' | ') AS klnw_hj_2,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'klnw_hj_2' AND egw_schulmanager_note.note_index_im_block = -1 THEN egw_schulmanager_note.note_note END) AS klnw_hj_2_avg,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'klnw_hj_2' AND egw_schulmanager_note.note_index_im_block = -1 AND egw_schulmanager_note.note_asv_note_manuell = '1' THEN '1' END) AS klnw_hj_2_avg_manuell,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'schnitt_hj_2' THEN egw_schulmanager_note.note_note END) AS schnitt_hj_2,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'schnitt_hj_2' AND egw_schulmanager_note.note_asv_note_manuell = '1' THEN '1' END) AS schnitt_hj_2_manuell,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'note_hj_2' THEN egw_schulmanager_note.note_note END) AS note_hj_2,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'note_hj_2' AND egw_schulmanager_note.note_asv_note_manuell = '1' THEN '1' END) AS note_hj_2_manuell,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'm_hj_2' THEN egw_schulmanager_note.note_note END) AS m_hj_2,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'v_hj_2' THEN egw_schulmanager_note.note_note END) AS v_hj_2

			FROM egw_schulmanager_asv_schueler_schuljahr

			INNER JOIN egw_schulmanager_asv_klassengruppe ON egw_schulmanager_asv_klassengruppe.kg_asv_id = egw_schulmanager_asv_schueler_schuljahr.ss_asv_klassengruppe_id
			INNER JOIN egw_schulmanager_asv_besuchtes_fach ON egw_schulmanager_asv_besuchtes_fach.bf_asv_schueler_schuljahr_id = egw_schulmanager_asv_schueler_schuljahr.ss_asv_id
			INNER JOIN egw_schulmanager_asv_schuelerfach ON egw_schulmanager_asv_schuelerfach.sf_asv_id = egw_schulmanager_asv_besuchtes_fach.bf_asv_schuelerfach_id

            LEFT JOIN egw_schulmanager_config ON egw_schulmanager_asv_schuelerfach.sf_asv_kurzform = egw_schulmanager_config.cnf_val
			LEFT JOIN egw_schulmanager_note ON egw_schulmanager_note.note_asv_schueler_schuljahr_id = egw_schulmanager_asv_schueler_schuljahr.ss_asv_id AND egw_schulmanager_note.note_asv_schueler_schuelerfach_id = egw_schulmanager_asv_schuelerfach.sf_asv_id

			WHERE egw_schulmanager_asv_schueler_schuljahr.ss_asv_id= '".$schueler_schuljahr_id."'
				AND egw_schulmanager_asv_besuchtes_fach.bf_asv_unterrichtsart = 'P'

			 GROUP BY egw_schulmanager_asv_schuelerfach.sf_asv_id, egw_schulmanager_asv_schuelerfach.sf_asv_kurzform, egw_schulmanager_asv_schuelerfach.sf_asv_anzeigeform, egw_schulmanager_config.cnf_extra

			ORDER BY egw_schulmanager_config.cnf_extra, egw_schulmanager_asv_schuelerfach.sf_asv_kurzform";

		$rs = $this->db->query($sql, __LINE__, __FILE__, 0, -1);

		$id = ($rowid +1) * 100;
		foreach($rs as $row){
			//$rows[] = array(
			$schueler = array(
				'rownr'	    => '',
				'nm_id'		=> $rowid * 100 + $id,
				'nm_st'		=> array(
					'st_asv_id'			  => '',
					'sch_schuljahr_asv_id' => '',
					'st_asv_familienname' => '',
					'st_asv_rufname'	  => $row['asv_anzeigeform']
				),
				'fachname' => $row['asv_anzeigeform'],
				'noten'		=> array(
					'alt_b'				  => array(
						-1 => array(
							'note'   => $row['alt_b'],
							'note_id'=> '',
							'img' => '',
							'checked' => false
						),
					),
					'glnw_hj_1'			  => array(
						'concat' => $row['glnw_hj_1'],
						-1 => array(
							'note'   =>$row['glnw_hj_1_avg'],
							'manuell'=>$row['glnw_hj_1_avg_manuell'],
						),
					),
					'klnw_hj_1'			  => array(
						'concat' => $row['klnw_hj_1'],
						-1 => array(
							'note'   =>$row['klnw_hj_1_avg'],
							'manuell'=>$row['klnw_hj_1_avg_manuell'],
						),
					),
					'schnitt_hj_1'		  => array(
						-1 => array(
							'note'   =>$row['schnitt_hj_1'],
							'manuell'=>$row['schnitt_hj_1_manuell'],
						),
					),
					'note_hj_1'			  => array(
						-1 => array(
							'note'   =>$row['note_hj_1'],
							'manuell'=>$row['note_hj_1_manuell'],
						),
					),
					'm_hj_1'			  => array(
						-1 => array(
							'note'   =>$row['m_hj_1'],
						),
					),
					'v_hj_1'			  => array(
						-1 => array(
							'note'   =>$row['v_hj_1'],
						),
					),
					// 2. HJ
					'glnw_hj_2'			  => array(
						'concat' => $row['glnw_hj_2'],
						-1 => array(
							'note'   =>$row['glnw_hj_2_avg'],
							'manuell'=>$row['glnw_hj_2_avg_manuell'],
						),
					),
					'klnw_hj_2'			  => array(
						'concat' => $row['klnw_hj_2'],
						-1 => array(
							'note'   =>$row['klnw_hj_2_avg'],
							'manuell'=>$row['klnw_hj_2_avg_manuell'],
						),
					),
					'schnitt_hj_2'		  => array(
						-1 => array(
							'note'   =>$row['schnitt_hj_2'],
							'manuell'=>$row['schnitt_hj_2_manuell'],
						),
					),
					'note_hj_2'			  => array(
						-1 => array(
							'note'   =>$row['note_hj_2'],
							'manuell'=>$row['note_hj_2_manuell'],
						),
					),
					'm_hj_2'			  => array(
						-1 => array(
							'note'   =>$row['m_hj_2'],
						),
					),
					'v_hj_2'			  => array(
						-1 => array(
							'note'   =>$row['v_hj_2'],
						),
					),
				),
				'is_par' => 0
			);

			$this->beforeSendToClient($schueler);

			$rows[$id] = $schueler;
			$id++;
		}
		return $rows;
	}






















    /**
     * Creates a short abstract of grades
     * @param $schueler_schuljahr_id
     * @param $rows
     * @param $rowid
     * @return mixed
     * @throws Api\Db\Exception\InvalidSql
     */
    function &getNotenAbstractShort($schueler_schuljahr_id, &$rows, $rowid){
        $sql = "select
			egw_schulmanager_asv_schuelerfach.sf_asv_id AS asv_id,
			egw_schulmanager_asv_schuelerfach.sf_asv_kurzform AS asv_kurzform,
			egw_schulmanager_asv_schuelerfach.sf_asv_anzeigeform AS asv_anzeigeform,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'alt_b' THEN egw_schulmanager_note.note_note END) AS alt_b,
			GROUP_CONCAT(CASE WHEN (egw_schulmanager_note.note_blockbezeichner = 'klnw_hj_1' OR egw_schulmanager_note.note_blockbezeichner = 'klnw_hj_2') AND egw_schulmanager_note.note_index_im_block >= 0 THEN egw_schulmanager_note.note_note END ORDER BY egw_schulmanager_note.note_index_im_block SEPARATOR ' | ') AS klnw,
			GROUP_CONCAT(CASE WHEN (egw_schulmanager_note.note_blockbezeichner = 'glnw_hj_1' OR egw_schulmanager_note.note_blockbezeichner = 'glnw_hj_2') AND egw_schulmanager_note.note_index_im_block >= 0 THEN egw_schulmanager_note.note_note END ORDER BY egw_schulmanager_note.note_index_im_block SEPARATOR ' | ') AS glnw,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'klnw_hj_2' AND egw_schulmanager_note.note_index_im_block = -1 THEN egw_schulmanager_note.note_note END) AS klnw_avg,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'glnw_hj_2' AND egw_schulmanager_note.note_index_im_block = -1 THEN egw_schulmanager_note.note_note END) AS glnw_avg,
			-- MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'glnw_hj_2' AND egw_schulmanager_note.note_index_im_block = -1 AND egw_schulmanager_note.note_asv_note_manuell = '1' THEN '1' END) AS glnw_hj_2_avg_manuell,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'schnitt_hj_2' THEN egw_schulmanager_note.note_note END) AS schnitt,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'note_hj_2' THEN egw_schulmanager_note.note_note END) AS note
			FROM egw_schulmanager_asv_schueler_schuljahr
			INNER JOIN egw_schulmanager_asv_klassengruppe ON egw_schulmanager_asv_klassengruppe.kg_asv_id = egw_schulmanager_asv_schueler_schuljahr.ss_asv_klassengruppe_id
			INNER JOIN egw_schulmanager_asv_besuchtes_fach ON egw_schulmanager_asv_besuchtes_fach.bf_asv_schueler_schuljahr_id = egw_schulmanager_asv_schueler_schuljahr.ss_asv_id
			INNER JOIN egw_schulmanager_asv_schuelerfach ON egw_schulmanager_asv_schuelerfach.sf_asv_id = egw_schulmanager_asv_besuchtes_fach.bf_asv_schuelerfach_id
            LEFT JOIN egw_schulmanager_config ON egw_schulmanager_asv_schuelerfach.sf_asv_kurzform = egw_schulmanager_config.cnf_val
			LEFT JOIN egw_schulmanager_note ON egw_schulmanager_note.note_asv_schueler_schuljahr_id = egw_schulmanager_asv_schueler_schuljahr.ss_asv_id AND egw_schulmanager_note.note_asv_schueler_schuelerfach_id = egw_schulmanager_asv_schuelerfach.sf_asv_id
			WHERE egw_schulmanager_asv_schueler_schuljahr.ss_asv_id= '".$schueler_schuljahr_id."'
				AND egw_schulmanager_asv_besuchtes_fach.bf_asv_unterrichtsart = 'P'
			GROUP BY egw_schulmanager_asv_schuelerfach.sf_asv_id, egw_schulmanager_asv_schuelerfach.sf_asv_kurzform, egw_schulmanager_asv_schuelerfach.sf_asv_anzeigeform, egw_schulmanager_config.cnf_extra
			ORDER BY egw_schulmanager_config.cnf_extra, egw_schulmanager_asv_schuelerfach.sf_asv_kurzform";

        $rs = $this->db->query($sql, __LINE__, __FILE__, 0, -1);

        $id = ($rowid +1) * 100;
        foreach($rs as $row){
            //$rows[] = array(
            $schueler = array(
                'rownr'	    => '',
                'nm_id'		=> $rowid * 100 + $id,
                'nm_st'		=> array(
                    'st_asv_id'			  => '',
                    'sch_schuljahr_asv_id' => '',
                    'st_asv_familienname' => '',
                    'st_asv_rufname'	  => $row['asv_anzeigeform']
                ),
                'fachname' => $row['asv_anzeigeform'],
                'noten'		=> array(
                    'alt_b'	=> $row['alt_b'] ? '1:1' : '2:1',
                    'glnw'	=> $row['glnw'],
                    'klnw'	=> $row['klnw'],
                    'glnw_avg'	=> str_replace('.', ',', $row['glnw_avg']),
                    'klnw_avg'	=> str_replace('.', ',', $row['klnw_avg']),
                    'schnitt'	=> str_replace('.', ',', $row['schnitt']),
                    'note'	=> $row['note'],
                ),
                'is_par' => 0
            );

            $rows[$id] = $schueler;
            $id++;
        }
        return $rows;
    }














	/**
	 * return average values from schnit_hj1, note_hj_1, m_hj1, v_hj1 and all rom  hj2
	 * @param type $schueler_schuljahr_id
	 * @param type $rows
	 * @return type array
	 */
	function getSchuelerAVG($schueler_schuljahr_id, &$schueler){
		$sql = "select
			-- 1. HJ
			AVG(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'schnitt_hj_1' THEN REPLACE(egw_schulmanager_note.note_note, ',', '.') END) AS schnitt_hj_1_avg,
			AVG(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'note_hj_1' THEN REPLACE(egw_schulmanager_note.note_note, ',', '.') END) AS note_hj_1_avg,
			AVG(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'm_hj_1' THEN REPLACE(egw_schulmanager_note.note_note, ',', '.') END) AS m_hj_1_avg,
			AVG(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'v_hj_1' THEN REPLACE(egw_schulmanager_note.note_note, ',', '.') END) AS v_hj_1_avg,
			-- 2. HJ
			AVG(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'schnitt_hj_2' THEN REPLACE(egw_schulmanager_note.note_note, ',', '.') END) AS schnitt_hj_2_avg,
			AVG(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'note_hj_2' THEN REPLACE(egw_schulmanager_note.note_note, ',', '.') END) AS note_hj_2_avg,
			AVG(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'm_hj_2' THEN REPLACE(egw_schulmanager_note.note_note, ',', '.') END) AS m_hj_2_avg,
			AVG(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'v_hj_2' THEN REPLACE(egw_schulmanager_note.note_note, ',', '.') END) AS v_hj_2_avg

			FROM egw_schulmanager_note
			WHERE egw_schulmanager_note.note_index_im_block = -1  AND egw_schulmanager_note.note_asv_schueler_schuljahr_id= '".$schueler_schuljahr_id."'";

		$rs = $this->db->query($sql, __LINE__, __FILE__, 0, -1);
		//$rowid = 0;
		$id = 0;
		foreach($rs as $row){
			//$rows[] = array(
			$noten		= array(
					'schnitt_hj_1'		  => array(
						-1 => array(
							'note'   => $this->formatDecimal($row['schnitt_hj_1_avg']),
						),
					),
					'note_hj_1'			  => array(
						-1 => array(
							'note'   => $this->formatDecimal($row['note_hj_1_avg']),
						),
					),
					'm_hj_1'			  => array(
						-1 => array(
							'note'   => $this->formatDecimal($row['m_hj_1_avg'], 1),
						),
					),
					'v_hj_1'			  => array(
						-1 => array(
							'note'   => $this->formatDecimal($row['v_hj_1_avg'], 1),
						),
					),
					'schnitt_hj_2'		  => array(
						-1 => array(
							'note'   => $this->formatDecimal($row['schnitt_hj_2_avg']),
						),
					),
					'note_hj_2'			  => array(
						-1 => array(
							'note'   => $this->formatDecimal($row['note_hj_2_avg']),
						),
					),
					'm_hj_2'			  => array(
						-1 => array(
							'note'   => $this->formatDecimal($row['m_hj_2_avg'], 1),
						),
					),
					'v_hj_2'			  => array(
						-1 => array(
							'note'   => $this->formatDecimal($row['v_hj_2_avg'], 1),
						),
					),
			);
		}
		$schueler['noten'] = $noten;
	}

    /**
     * Formats a decimal number
     * @param $noteDec
     * @param $dec
     * @return string
     */
	function formatDecimal($noteDec, $dec = 2){
		if(!empty($noteDec)){
			return number_format(floatval(str_replace(',', '.', $noteDec)), $dec, ',', '');
		}
		else{
			return '-,--';
		}
	}

	/**
	 * recalculates avg and formats them
	 * @param type $schueler
     * @deprected
	 */
	function beforeSendToClient(&$schueler){
		if(isset($schueler['noten'])){

			// alternative Berechnung
			if($schueler['noten']['alt_b'][-1]['note'] === '1'){
				$schueler['noten']['alt_b'][-1]['checked'] = true;
				$schueler['noten']['alt_b'][-1]['img'] = 'check.svg';
			}
			else{
				$schueler['noten']['alt_b'][-1]['checked'] = false;
				$schueler['noten']['alt_b'][-1]['img'] = '';
                $schueler['noten']['alt_b'][-1]['label'] = '';
			}
		}
	}

    /**
     * delete grades in period a and/or b
     * @param $schueler
     * @param $PerA
     * @param $perB
     * @return void
     */
    function delLnwPer($schueler, $perA = false, $perB = false){
        $schueler_schuljahr_id = $schueler['nm_st']['sch_schuljahr_asv_id'];

        if(!isset($schueler_schuljahr_id) OR strlen($schueler_schuljahr_id) == 0){
            return;
        }
        if($perA) {
            $where = "note_asv_schueler_schuljahr_id = '" . $schueler_schuljahr_id . "'
                AND note_blockbezeichner IN ('klnw_hj_1', 'glnw_hj_1', 'schnitt_hj_1', 'note_hj_1', 'm_hj_1', 'v_hj_1')";
            $rs = $this->db->delete('egw_schulmanager_note', $where, __LINE__, __FILE__, 0, -1);
        }
        elseif ($perB){
            $where = "note_asv_schueler_schuljahr_id = '" . $schueler_schuljahr_id . "'
                AND note_blockbezeichner IN ('klnw_hj_2', 'glnw_hj_2', 'schnitt_hj_2', 'note_hj_2', 'm_hj_2', 'v_hj_2', 'schnitt_hj_2', 'note_hj_2', 'm_hj_2', 'v_hj_2')";
            $rs = $this->db->delete('egw_schulmanager_note', $where, __LINE__, __FILE__, 0, -1);
        }
    }
}