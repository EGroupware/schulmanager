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

		$id = 0;
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
	 */
	function beforeSendToClient(&$schueler){
		if(isset($schueler['noten'])){

			// alternative Berechnung
			if($schueler['noten']['alt_b'][-1]['note'] === '1'){
				$schueler['noten']['alt_b'][-1]['checked'] = true;
				$schueler['noten']['alt_b'][-1]['img'] = 'done';
			}
			else{
				$schueler['noten']['alt_b'][-1]['checked'] = false;
				$schueler['noten']['alt_b'][-1]['img'] = '';
			}
		}
	}
}