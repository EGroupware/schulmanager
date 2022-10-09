<?php

/**
 * EGroupware - schulmanager  Lehrer storage-object
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
require_once(EGW_INCLUDE_ROOT . '/schulmanager/inc/class.schulmanager_note_bo.inc.php');

class schulmanager_lehrer_so extends Api\Storage{

	/**
	 * name of the main lehrer table and prefix for all other lehrer tables
	 */
    var $sm_lehrer_table = 'egw_schulmanager_asv_lehrer_stamm';//'egw_schulmanager_asv_lehrer';
	var $stamm_table,$schuljahr_table,$schuljahr_schule_table,$unterr_faecher_table,$all_tables;

    var $value_col = array();

	/**
	 * reference to global db-object
	 *
	 * @var Api\Db
	 */
	var $db;

	/**
	 *
	 * @var schulmanager_note_bo
	 */
	var $note_bo;

    /**
     * @var array
     */
    var $customfields = array();

	/**
	 * instance of the async object
	 *
	 * @var Api\Asyncservice
	 */
	var $async;

    function __construct() {
        $this->db = clone($GLOBALS['egw']->db);
        $this->db->set_app('schulmanager');
        $this->all_tables = array($this->sm_lehrer_table);
        $this->setup_table('schulmanager', $this->sm_lehrer_table);
		$this->note_bo = new schulmanager_note_bo();
		foreach(array('stamm','schuljahr','schuljahr_schule','unterr_faecher') as $name)
		{
			$vname = $name.'_table';
			$this->all_tables[] = $this->$vname = $this->sm_lehrer_table.'_'.$name;
		}

        foreach(array('asv_id','asv_familienname','asv_rufname') as $name)
        {
            $this->value_col[$name] = 'ls_'.$name;
        }
    }

	/**
	 * @param type $kennung lehrer kennung
	 * @return array of schulmanager_klassengr_schuelerfach
	 */
	function &loadUnterricht(array $lehrerStammIDs){
        $unterricht = array();
        //$csvIDs = implode(',', $lehrerStammIDs);
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

        $sql = "SELECT
					egw_schulmanager_asv_klassengruppe.kg_asv_id AS kg_asv_id,
					egw_schulmanager_asv_klassengruppe.kg_asv_klasse_id AS kl_asv_id,
                    egw_schulmanager_asv_klassengruppe.kg_asv_kennung AS kg_asv_kennung,                                
					egw_schulmanager_asv_klasse.kl_asv_klassenname AS kl_asv_klassenname,
					egw_schulmanager_asv_schuelerfach.sf_asv_id AS sf_asv_id,
					egw_schulmanager_asv_schuelerfach.sf_asv_kurzform AS sf_asv_kurzform,
					egw_schulmanager_asv_schuelerfach.sf_asv_anzeigeform AS sf_asv_anzeigeform
				FROM egw_schulmanager_asv_lehrer_schuljahr
					INNER JOIN egw_schulmanager_asv_lehrer_schuljahr_schule	ON egw_schulmanager_asv_lehrer_schuljahr_schule.lss_asv_lehrer_schuljahr_id = egw_schulmanager_asv_lehrer_schuljahr.lsj_asv_id
					INNER JOIN egw_schulmanager_asv_unterrichtselement		ON egw_schulmanager_asv_unterrichtselement.ue_asv_lehrer_schuljahr_schule_id = egw_schulmanager_asv_lehrer_schuljahr_schule.lss_asv_id
					INNER JOIN egw_schulmanager_asv_klassengruppe	    	ON egw_schulmanager_asv_klassengruppe.kg_asv_id = egw_schulmanager_asv_unterrichtselement.ue_asv_klassengruppe_id
					INNER JOIN egw_schulmanager_asv_klasse					ON egw_schulmanager_asv_klasse.kl_asv_id = egw_schulmanager_asv_klassengruppe.kg_asv_klasse_id
					INNER JOIN egw_schulmanager_asv_fachgruppe				ON egw_schulmanager_asv_fachgruppe.fg_asv_id = egw_schulmanager_asv_unterrichtselement.ue_asv_fachgruppe_id
					INNER JOIN egw_schulmanager_asv_schuelerfach				ON egw_schulmanager_asv_schuelerfach.sf_asv_id = egw_schulmanager_asv_fachgruppe.fg_asv_schuelerfach_id
				WHERE egw_schulmanager_asv_lehrer_schuljahr.lsj_asv_lehrer_stamm_id IN (".$csvIDs.") 
				    AND NOT (kg_asv_jahrgangsstufe_id = 'JAHRGA_111' AND kg_asv_bildungsgang_id = 'BILDU_0603017031')
				    AND NOT (kg_asv_jahrgangsstufe_id = 'JAHRGA_121' AND kg_asv_bildungsgang_id = 'BILDU_0603017031')";

		$rs = $this->db->query($sql, __LINE__, __FILE__, 0, -1);

		foreach($rs as $row){
			$unterricht[] = new schulmanager_klassengr_schuelerfa($row['kg_asv_id'],
			                                                        $row['kg_asv_kennung'],
																	$row['kl_asv_id'],
																	$row['kl_asv_klassenname'],
																	$row['sf_asv_id'],
																	$row['sf_asv_kurzform'],
																	$row['sf_asv_anzeigeform']);
		}
		
		// load substitutions
		$substitution_so = new schulmanager_substitution_so();
		$subs = $substitution_so->load($unterricht, $GLOBALS['egw_info']['user']['account_lid']);
		
		foreach($subs as $row){
		    $unterricht[] = new schulmanager_klassengr_schuelerfa($row['kg_asv_id'],
		                                                                $row['kg_asv_kennung'],
                                                        		        $row['kl_asv_id'],
                                                        		        $row['kl_asv_klassenname'],
                                                        		        $row['sf_asv_id'],
                                                        		        $row['sf_asv_kurzform'],
                                                        		        $row['sf_asv_anzeigeform']);
		}
		
		
		return $unterricht;
	}

	/**
	 * returns all schule_fach by klassengruppe
	 * @param type $kennung lehrer kennung
	 * @return array of schulmanager_klassengr_schuelerfach
	 */
	function &loadUnterrichtByKlassengruppe($kgasvid){
		$unterricht = array();
		$sql = "SELECT DISTINCT
					egw_schulmanager_asv_schuelerfach.sf_asv_id AS asv_id,
					egw_schulmanager_asv_schuelerfach.sf_asv_schule_fach_id AS schule_fach_id,
					egw_schulmanager_asv_schuelerfach.sf_asv_kurzform AS kurzform,
					egw_schulmanager_asv_schuelerfach.sf_asv_anzeigeform AS anzeigeform
				FROM egw_schulmanager_asv_klassengruppe
				   INNER JOIN egw_schulmanager_asv_unterrichtselement		ON egw_schulmanager_asv_unterrichtselement.ue_asv_klassengruppe_id = egw_schulmanager_asv_klassengruppe.kg_asv_id
				   INNER JOIN egw_schulmanager_asv_fachgruppe				ON egw_schulmanager_asv_fachgruppe.fg_asv_id = egw_schulmanager_asv_unterrichtselement.ue_asv_fachgruppe_id
				   INNER JOIN egw_schulmanager_asv_schuelerfach				ON egw_schulmanager_asv_schuelerfach.sf_asv_id = egw_schulmanager_asv_fachgruppe.fg_asv_schuelerfach_id
				WHERE kg_asv_id = '".$kgasvid."'
				ORDER BY kurzform";

		$rs = $this->db->query($sql, __LINE__, __FILE__, 0, -1);

		$id = 0;

		foreach($rs as $row){
			//$rows[$id] = $row['klassenname'];
			$unterricht[$id] = array(
				'asvid' => $row['asv_id'],
				'schulefachid' => $row['schule_fach_id'],
				'kurzform' => $row['kurzform'],
				'anzeigeform' => $row['anzeigeform']
			);
			$id++;
		}
		return $unterricht;
	}

	/**
	 * Liefert alle Klasse mit beiden Klassleitungen
	 * 0 =array( name => '5A', asvid => 'ihgz233Hj')
	 */
	function &loadClassleaderClasses(array $lehrerStammIDs, &$rows, $showAllKlassen = true, $search = ''){
		//$selection = '';

        foreach($lehrerStammIDs as &$lsid){
            $lsid = "'".$lsid."'";
        }
        $csvIDs = implode(',', $lehrerStammIDs);
        if(empty($csvIDs)){ // called by non-teacher, return empty result from db
            $csvIDs = "FALSE";  // SQL FALSE
        }

		$searchSQL = $this->db->quote('%'.$search.'%');

		if(!$showAllKlassen){
			// only groups, where user is one of two leaders
            $sql = "SELECT DISTINCT egw_schulmanager_asv_klassenleitung.kl_klasse_id AS klasse_asv_id,
				egw_schulmanager_asv_klassenleitung.kl_lehrer_schuljahr_schule_id AS schuljahr_id,
				egw_schulmanager_asv_klassenleitung.kl_art AS art,
				egw_schulmanager_asv_klasse.kl_asv_klassenname AS klassenname

				FROM egw_schulmanager_asv_klassenleitung
				INNER JOIN egw_schulmanager_asv_klasse ON egw_schulmanager_asv_klasse.kl_asv_id = egw_schulmanager_asv_klassenleitung.kl_klasse_id
				INNER JOIN egw_schulmanager_asv_lehrer_schuljahr_schule ON egw_schulmanager_asv_lehrer_schuljahr_schule.lss_asv_id = egw_schulmanager_asv_klassenleitung.kl_lehrer_schuljahr_schule_id
				INNER JOIN egw_schulmanager_asv_lehrer_schuljahr ON egw_schulmanager_asv_lehrer_schuljahr.lsj_asv_id = egw_schulmanager_asv_lehrer_schuljahr_schule.lss_asv_lehrer_schuljahr_id
				WHERE egw_schulmanager_asv_lehrer_schuljahr.lsj_asv_lehrer_stamm_id IN (".$csvIDs.") ORDER BY egw_schulmanager_asv_klasse.kl_asv_klassenname";
		}
		else{
			$sql = "SELECT DISTINCT egw_schulmanager_asv_klasse.kl_asv_id AS klasse_asv_id,
				egw_schulmanager_asv_klasse.kl_asv_klassenname AS klassenname
				FROM egw_schulmanager_asv_klasse
				WHERE egw_schulmanager_asv_klasse.kl_asv_klassenname LIKE ".$searchSQL."
				ORDER BY egw_schulmanager_asv_klasse.kl_asv_klassenname";
		}

		$rs = $this->db->query($sql, __LINE__, __FILE__, 0, -1);
		$id = 0;
		$klassenasvids = array();

		Api\Cache::unsetSession('schulmanager', 'klassen_asv_ids');

		foreach($rs as $row){
			//$rows[$id] = $row['klassenname'];
			$rows[$id] = array(
				'name' => $row['klassenname'],
				'asvid' => $row['klasse_asv_id']
			);
			$klassenasvids[$id] = $row['klasse_asv_id'];
			$id++;
		}
		Api\Cache::setSession('schulmanager', 'klassen_asv_ids', $klassenasvids);

	}

	function &getKlassenGruppen(array $lehrerStammIDs, &$rows, $showAllGroups = 0, $search = ''){
		$searchSQL = $this->db->quote('%'.$search.'%');

		//$csvIDs = $this->db->quote($lehrerStammIDs);
        foreach($lehrerStammIDs as &$lsid){
            $lsid = "'".$lsid."'";
        }
        $csvIDs = implode(',', $lehrerStammIDs); // empty for non teachers
        if(empty($csvIDs)){
            $csvIDs = "FALSE";
        }

        if($showAllGroups == 1){
            // alle Klassengruppen
            $sql = "SELECT egw_schulmanager_asv_klasse.kl_asv_id AS klasse_asv_id,
					egw_schulmanager_asv_klassengruppe.kg_asv_id AS kg_asv_id,
					egw_schulmanager_asv_klasse.kl_asv_klassenname AS klassenname,
					egw_schulmanager_asv_klassengruppe.kg_asv_kennung AS kennung
				FROM egw_schulmanager_asv_klassengruppe
				INNER JOIN egw_schulmanager_asv_klasse ON egw_schulmanager_asv_klasse.kl_asv_id = egw_schulmanager_asv_klassengruppe.kg_asv_klasse_id
				WHERE egw_schulmanager_asv_klasse.kl_asv_klassenname LIKE ".$searchSQL."
				ORDER BY egw_schulmanager_asv_klasse.kl_asv_klassenname, egw_schulmanager_asv_klassengruppe.kg_asv_kennung";
        }
		elseif ($showAllGroups == 0) {
			// nur Klassengruppen, bei denen der User unterrichtet
            $sql = "SELECT DISTINCT
						egw_schulmanager_asv_klasse.kl_asv_id AS klasse_asv_id,
									egw_schulmanager_asv_klassengruppe.kg_asv_id AS kg_asv_id,
									egw_schulmanager_asv_klasse.kl_asv_klassenname AS klassenname,
									egw_schulmanager_asv_klassengruppe.kg_asv_kennung AS kennung,
									egw_schulmanager_asv_unterrichtselement.ue_asv_koppel_id AS koppel
					FROM egw_schulmanager_asv_unterrichtselement
					INNER JOIN egw_schulmanager_asv_lehrer_schuljahr_schule ON egw_schulmanager_asv_lehrer_schuljahr_schule.lss_asv_id = egw_schulmanager_asv_unterrichtselement.ue_asv_lehrer_schuljahr_schule_id
					INNER JOIN egw_schulmanager_asv_lehrer_schuljahr ON egw_schulmanager_asv_lehrer_schuljahr.lsj_asv_id = egw_schulmanager_asv_lehrer_schuljahr_schule.lss_asv_lehrer_schuljahr_id
					INNER JOIN egw_schulmanager_asv_klassengruppe ON egw_schulmanager_asv_klassengruppe.kg_asv_id = egw_schulmanager_asv_unterrichtselement.ue_asv_klassengruppe_id
					INNER JOIN egw_schulmanager_asv_klasse ON egw_schulmanager_asv_klasse.kl_asv_id = egw_schulmanager_asv_klassengruppe.kg_asv_klasse_id
					WHERE egw_schulmanager_asv_lehrer_schuljahr.lsj_asv_lehrer_stamm_id IN (".$csvIDs.")
						AND egw_schulmanager_asv_klasse.kl_asv_klassenname LIKE ".$searchSQL."
					ORDER BY egw_schulmanager_asv_klasse.kl_asv_klassenname";

		}
		else{
			// nur Klassengruppen, bei denen der Lehrer Klassleiter ist.
            $sql = "SELECT DISTINCT egw_schulmanager_asv_klasse.kl_asv_id AS klasse_asv_id,
				egw_schulmanager_asv_klassengruppe.kg_asv_id AS kg_asv_id,
				egw_schulmanager_asv_klassenleitung.kl_lehrer_schuljahr_schule_id AS schuljahr_id,
				egw_schulmanager_asv_klassenleitung.kl_art AS art,
				egw_schulmanager_asv_klasse.kl_asv_klassenname AS klassenname,
				egw_schulmanager_asv_klassengruppe.kg_asv_kennung AS kennung

				FROM egw_schulmanager_asv_klassenleitung
				INNER JOIN egw_schulmanager_asv_klasse ON egw_schulmanager_asv_klasse.kl_asv_id = egw_schulmanager_asv_klassenleitung.kl_klasse_id
				INNER JOIN egw_schulmanager_asv_klassengruppe ON egw_schulmanager_asv_klassengruppe.kg_asv_klasse_id = egw_schulmanager_asv_klasse.kl_asv_id
				INNER JOIN egw_schulmanager_asv_lehrer_schuljahr_schule ON egw_schulmanager_asv_lehrer_schuljahr_schule.lss_asv_id = egw_schulmanager_asv_klassenleitung.kl_lehrer_schuljahr_schule_id
				INNER JOIN egw_schulmanager_asv_lehrer_schuljahr ON egw_schulmanager_asv_lehrer_schuljahr.lsj_asv_id = egw_schulmanager_asv_lehrer_schuljahr_schule.lss_asv_lehrer_schuljahr_id
				WHERE egw_schulmanager_asv_lehrer_schuljahr.lsj_asv_lehrer_stamm_id IN (".$csvIDs.")
					AND egw_schulmanager_asv_klasse.kl_asv_klassenname LIKE ".$searchSQL." ORDER BY egw_schulmanager_asv_klasse.kl_asv_klassenname, egw_schulmanager_asv_klassengruppe.kg_asv_kennung";
		}

		$rs = $this->db->query($sql, __LINE__, __FILE__, 0, -1);
		$id = 0;
		$klassenasvids = array();

		Api\Cache::unsetSession('schulmanager', 'klassengruppen_asv_ids');

		foreach($rs as $row){
			$rows[$id] = array(
				'asvid' => $row['klasse_asv_id'],
				'kgid' => $row['kg_asv_id'],
				'name' => $row['klassenname'],
				'kennung' => $row['kennung']
			);
			$klassenasvids[$id] = $row['klasse_asv_id'];
			$id++;
		}
		Api\Cache::setSession('schulmanager', 'klassengruppen_asv_ids', $klassenasvids);
	}


	/**
	 * Returns a list witd schueler in klassengruppe and schuelerfach with makrs (noten)
	 * @param type $kg_asv_is
	 * @param type $sf_asv_id
	 */
	function &loadSchuelerNotenList(&$query_in, $kg_asv_id, $sf_asv_id, &$rows, $gewichtungen){
	    $limit = '';
	    if(isset($query_in['start']) && isset($query_in['num_rows'])){
	        $limit = " LIMIT ".$query_in['start'].", ".$query_in['num_rows'];
        }

        $sql = "SELECT DISTINCT
					egw_schulmanager_asv_schueler_stamm.sch_asv_id AS st_asv_id,
					egw_schulmanager_asv_schueler_stamm.sch_asv_familienname AS st_asv_familienname,
					egw_schulmanager_asv_schueler_stamm.sch_asv_rufname AS st_asv_rufname,
					egw_schulmanager_asv_schueler_stamm.sch_asv_austrittsdatum AS st_asv_austrittsdatum,
					egw_schulmanager_asv_schueler_schuljahr.ss_asv_id AS asv_schueler_schuljahr_id,
					egw_schulmanager_asv_schuelerfach.sf_asv_id AS asv_schuelerfach_id
				FROM
					egw_schulmanager_asv_schueler_stamm
				INNER JOIN egw_schulmanager_asv_schueler_schuljahr ON egw_schulmanager_asv_schueler_schuljahr.ss_asv_schueler_stamm_id = egw_schulmanager_asv_schueler_stamm.sch_asv_id
				INNER JOIN egw_schulmanager_asv_besuchtes_fach	 ON egw_schulmanager_asv_besuchtes_fach.bf_asv_schueler_schuljahr_id = egw_schulmanager_asv_schueler_schuljahr.ss_asv_id
				INNER JOIN egw_schulmanager_asv_schuelerfach       ON egw_schulmanager_asv_schuelerfach.sf_asv_id = egw_schulmanager_asv_besuchtes_fach.bf_asv_schuelerfach_id

				WHERE egw_schulmanager_asv_schuelerfach.sf_asv_id = '".$sf_asv_id."'
					AND egw_schulmanager_asv_schueler_schuljahr.ss_asv_klassengruppe_id = '".$kg_asv_id."'
				ORDER BY st_asv_familienname, st_asv_rufname COLLATE 'utf8_general_ci'";

		if(!isset($query_in['total'])){
		    // first call, nextmatch will call again
            $rs = $this->db->query($sql, __LINE__, __FILE__, 0, -1);
            $query_in['total'] = $rs->_numOfRows;
            return $rs->_numOfRows;
        }

		$sql = $sql.$limit;

		$rs = $this->db->query($sql, __LINE__, __FILE__, 0, -1);
		$rowid = 0;
		$id = 1;

		/*$note_empty = array(
            'note'   => '',
            'note_id'=> '',
            'update_date' => '',
            'update_user' => '',
            'art' => '',
            'definition_date' => '',
            'description' => '',
        );*/

		foreach($rs as $row){
			//$rows[] = array(
			$schueler = array(
				'nm_id'		=> $id,
				'nm_st'		=> array(
					'st_asv_id'			  => $row['st_asv_id'],
					'sch_schuljahr_asv_id' => $row['asv_schueler_schuljahr_id'],
					'st_asv_familienname' => $row['st_asv_familienname'],
					'st_asv_rufname'	  => $row['st_asv_rufname'],
					'st_asv_austrittsdatum' => $row['st_asv_austrittsdatum'],
					'nm_st_class'		=> ''
				),
				/*'noten'		=> array(
					'alt_b' => array(
						-1 => array(
							'note'   => false,
							'note_id'=> '',
							'img' => '',
							'checked' => false
						),
					),
					'glnw_hj_1' => array(
						'avgclass' => '',
						-1 => array(
							'note'   => '',
							'note_id'=> '',
							'manuell' => '0',
						),
						0 => $note_empty,
						1 => $note_empty,
						2 => $note_empty,
					),
					'klnw_hj_1' => array(
						'avgclass' => '',
						-1 =>  array(
							'note'   => '',
							'note_id'=> '',
							'manuell' => '0'
						),
                        0 => $note_empty,
                        1 => $note_empty,
                        2 => $note_empty,
                        3 => $note_empty,
                        4 => $note_empty,
                        5 => $note_empty,
                        6 => $note_empty,
                        7 => $note_empty,
                        8 => $note_empty,
                        9 => $note_empty,
                        10 => $note_empty,
                        11 => $note_empty,
					),
					'schnitt_hj_1' =>  array(
						'avgclass' => '',
						-1 =>  array(
							'note'   => '',
							'note_id'=> '',
							'manuell' => '0'
						)
					),
					'note_hj_1' =>  array(
						'avgclass' => '',
						-1 =>  array(
							'note'   => '',
							'note_id'=> '',
							'manuell' => '0'
						)
					),
					'm_hj_1' =>  array(
						-1 =>  array(
							'note'   => '',
							'note_id'=> ''
						)
					),
					'v_hj_1' =>  array(
						-1 =>  array(
							'note'   => '',
							'note_id'=> ''
						)
					),
					'glnw_hj_2' => array(
						'avgclass' => '',
						-1 => array(
							'note'   => '',
							'note_id'=> '',
							'manuell' => '0',
						),
                        0 => $note_empty,
                        1 => $note_empty,
                        2 => $note_empty,
					),
					'klnw_hj_2' => array(
						'avgclass' => '',
						-1 =>  array(
							'note'   => '',
							'note_id'=> '',
							'manuell' => '0'
						),
                        0 => $note_empty,
                        1 => $note_empty,
                        2 => $note_empty,
                        3 => $note_empty,
                        4 => $note_empty,
                        5 => $note_empty,
                        6 => $note_empty,
                        7 => $note_empty,
                        8 => $note_empty,
                        9 => $note_empty,
                        10 => $note_empty,
                        11 => $note_empty,
					),
					'schnitt_hj_2' =>  array(
						'avgclass' => '',
						-1 =>  array(
							'note'   => '',
							'note_id'=> '',
							'manuell' => '0'
						)
					),
					'note_hj_2' =>  array(
						'avgclass' => '',
						-1 =>  array(
							'note'   => '',
							'note_id'=> '',
							'manuell' => false
						)
					),
					'm_hj_2' =>  array(
						-1 =>  array(
							'note'   => '',
							'note_id'=> ''
						)
					),
					'v_hj_2' =>  array(
						-1 =>  array(
							'note'   => '',
							'note_id'=> ''
						)
					)
				)*/
			);

            $schueler['noten'] = $this->getNotenTemplate();

			$this->loadNotenBySchuljahrFach($row['asv_schueler_schuljahr_id'], $row['asv_schuelerfach_id'], $schueler);
			self::beforeSendToClient($schueler, $gewichtungen);
			$rows[$rowid] = $schueler;
			$id++;
			$rowid++;
		}
		// writes calculated values to Database
		$this->writeAutoValues($rows, $kg_asv_id, $sf_asv_id);
		$total = count($rows);
		return $total;
	}

    function getNotenTemplate(){
        $note_empty = array(
            'note'   => '',
            'note_id'=> '',
            'update_date' => '',
            'update_user' => '',
            'art' => '',
            'definition_date' => '',
            'description' => '',
        );

        $tmpl = array(
            'alt_b' => array(
                -1 => array(
                    'note'   => false,
                    'note_id'=> '',
                    'img' => '',
                    'checked' => false
                ),
            ),
            'glnw_hj_1' => array(
                'avgclass' => '',
                -1 => array(
                    'note'   => '',
                    'note_id'=> '',
                    'manuell' => '0',
                ),
                0 => $note_empty,
                1 => $note_empty,
                2 => $note_empty,
            ),
            'klnw_hj_1' => array(
                'avgclass' => '',
                -1 =>  array(
                    'note'   => '',
                    'note_id'=> '',
                    'manuell' => '0'
                ),
                0 => $note_empty,
                1 => $note_empty,
                2 => $note_empty,
                3 => $note_empty,
                4 => $note_empty,
                5 => $note_empty,
                6 => $note_empty,
                7 => $note_empty,
                8 => $note_empty,
                9 => $note_empty,
                10 => $note_empty,
                11 => $note_empty,
            ),
            'schnitt_hj_1' =>  array(
                'avgclass' => '',
                -1 =>  array(
                    'note'   => '',
                    'note_id'=> '',
                    'manuell' => '0'
                )
            ),
            'note_hj_1' =>  array(
                'avgclass' => '',
                -1 =>  array(
                    'note'   => '',
                    'note_id'=> '',
                    'manuell' => '0'
                )
            ),
            'm_hj_1' =>  array(
                -1 =>  array(
                    'note'   => '',
                    'note_id'=> ''
                )
            ),
            'v_hj_1' =>  array(
                -1 =>  array(
                    'note'   => '',
                    'note_id'=> ''
                )
            ),
            'glnw_hj_2' => array(
                'avgclass' => '',
                -1 => array(
                    'note'   => '',
                    'note_id'=> '',
                    'manuell' => '0',
                ),
                0 => $note_empty,
                1 => $note_empty,
                2 => $note_empty,
            ),
            'klnw_hj_2' => array(
                'avgclass' => '',
                -1 =>  array(
                    'note'   => '',
                    'note_id'=> '',
                    'manuell' => '0'
                ),
                0 => $note_empty,
                1 => $note_empty,
                2 => $note_empty,
                3 => $note_empty,
                4 => $note_empty,
                5 => $note_empty,
                6 => $note_empty,
                7 => $note_empty,
                8 => $note_empty,
                9 => $note_empty,
                10 => $note_empty,
                11 => $note_empty,
            ),
            'schnitt_hj_2' =>  array(
                'avgclass' => '',
                -1 =>  array(
                    'note'   => '',
                    'note_id'=> '',
                    'manuell' => '0'
                )
            ),
            'note_hj_2' =>  array(
                'avgclass' => '',
                -1 =>  array(
                    'note'   => '',
                    'note_id'=> '',
                    'manuell' => false
                )
            ),
            'm_hj_2' =>  array(
                -1 =>  array(
                    'note'   => '',
                    'note_id'=> ''
                )
            ),
            'v_hj_2' =>  array(
                -1 =>  array(
                    'note'   => '',
                    'note_id'=> ''
                )
            )
        );
        return $tmpl;
    }

	/**
	 *
	 * @param type $klasseasvid
	 * @param type $rows
	 * @return int
	 */
	function &loadKlassenSchuelerList($klasseasvid, &$rows){
		$schueler_so = new schulmanager_schueler_so();

		$sql = "SELECT DISTINCT
					egw_schulmanager_asv_schueler_stamm.sch_asv_familienname AS st_asv_familienname,
					egw_schulmanager_asv_schueler_stamm.sch_asv_rufname AS st_asv_rufname,
					egw_schulmanager_asv_schueler_stamm.sch_asv_austrittsdatum AS st_asv_austrittsdatum,
					egw_schulmanager_asv_schueler_stamm.sch_asv_id AS st_asv_id,
					egw_schulmanager_asv_schueler_stamm.sch_asv_vornamen AS st_asv_vornamen,
					egw_schulmanager_asv_schueler_stamm.sch_asv_wl_geschlecht_id AS st_asv_wl_geschlecht_id,
					egw_schulmanager_asv_schueler_schuljahr.ss_asv_id AS asv_schueler_schuljahr_id
				FROM egw_schulmanager_asv_klasse
				INNER JOIN egw_schulmanager_asv_klassengruppe ON egw_schulmanager_asv_klassengruppe.kg_asv_klasse_id = egw_schulmanager_asv_klasse.kl_asv_id
				INNER JOIN egw_schulmanager_asv_schueler_schuljahr ON egw_schulmanager_asv_schueler_schuljahr.ss_asv_klassengruppe_id = egw_schulmanager_asv_klassengruppe.kg_asv_id
				INNER JOIN egw_schulmanager_asv_schueler_stamm ON egw_schulmanager_asv_schueler_stamm.sch_asv_id = egw_schulmanager_asv_schueler_schuljahr.ss_asv_schueler_stamm_id
				WHERE egw_schulmanager_asv_klasse.kl_asv_id = '".$klasseasvid."'
				ORDER BY egw_schulmanager_asv_schueler_stamm.sch_asv_familienname,
					egw_schulmanager_asv_schueler_stamm.sch_asv_rufname COLLATE 'utf8_general_ci'"; // WHERE egw_schulmanager_asv_klasse.kl_asv_id = 'v40o/ncke/q510/g8vx/ua0h'

		$rs = $this->db->query($sql, __LINE__, __FILE__, 0, -1);

		$id = 0;
		foreach($rs as $row){
            $geschlecht = schulmanager_werteliste_bo::getGeschlecht($row['st_asv_wl_geschlecht_id'], 'kurzform');
			$schueler = array(
				'rownr'		=> $id + 1,
				'nm_id'		=> $id,
				'nm_st'		=> array(
					'st_asv_id'			  => $row['st_asv_id'],
					'sch_schuljahr_asv_id' => $row['asv_schueler_schuljahr_id'],
					'st_asv_familienname' => $row['st_asv_familienname'],
                    'st_asv_vornamen' => $row['st_asv_vornamen'],
					'st_asv_rufname'	  => $row['st_asv_rufname'],
                    'st_asv_wl_geschlecht_id'	  => $row['st_asv_wl_geschlecht_id'],
					'st_asv_austrittsdatum' => $row['st_asv_austrittsdatum'],
					'nm_st_class'		=> '',
                    'geschlecht' => $geschlecht
				),
				'is_par' => 1
			);

			$schueler_so->getSchuelerAVG($row['asv_schueler_schuljahr_id'], $schueler);

			self::checkExitDate($schueler);

			$rows[$id] = $schueler;
			$id++;
		}

		return $rows;
	}
	/**
	 * Save auto calculated values to Database
	 * @param type $rows
	 */
	function writeAutoValues($rows, $kg_asv_id, $sf_asv_id){
		foreach($rows as $id => &$schueler){
			foreach($schueler['noten'] as $blockname => &$notenblock){
				if(array_key_exists('manuell', $notenblock[-1]) && $notenblock[-1]['manuell'] == 0 && array_key_exists('note', $notenblock[-1])){// && $notenblock[-1]['note'] > 0){
					$note = array(
						'note_asv_schueler_schuljahr_id' => $schueler['nm_st']['sch_schuljahr_asv_id'],
						'note_asv_schueler_schuelerfach_id' => $sf_asv_id,
						'note_blockbezeichner' => $blockname,
						'note_index_im_block' => -1,
						'note_note' => $notenblock[-1]['note'],
						//'note_asv_id' => $asv_id,
						'note_asv_note_manuell' => 0
					);
					if(array_key_exists('note_id', $notenblock[-1])){
						$note['note_id'] = $notenblock[-1]['note_id'];
					}
					$this->note_bo->save($note);
				}
				// TODO Löschen von nicht mehr benötigten Durchschnitten!!
			}
		}
		return 0;
	}




	/**
	 * rows[nm][nm_st][st_asv_id]
	 *     [nm][nm_st][st_asv_familienname]
     *     [nm][nm_st][st_asv_rufname]
	 *     [nm][nm_st][noten]
	 *     [nm][noten][glnw_hj][0] = 3
	 *     [nm][noten][glnw_hj][1] = 1
	 *     [nm][noten][klnw_hj][0] = 4
	 *     [nm][noten][klnw_hj][1] = 2
	 * @param string $asv_id
	 * @return array of noten
	 */
	function &loadNotenBySchuljahrFach($asv_schueler_schuljahr_id, $asv_schuelerfach_id, &$schueler){

		$noten = array();
		$sql = "SELECT
			egw_schulmanager_note.note_id AS note_id,
			egw_schulmanager_note.note_asv_id AS asv_id,
			egw_schulmanager_note.note_asv_schueler_schuljahr_id AS asv_schueler_schuljahr_id,
			egw_schulmanager_note.note_asv_schueler_schuelerfach_id AS asv_schueler_schuelerfach_id,
			egw_schulmanager_note.note_blockbezeichner AS blockbezeichner,
			egw_schulmanager_note.note_index_im_block AS index_im_block,
			egw_schulmanager_note.note_note AS note,
			egw_schulmanager_note.note_create_date AS create_date,
			egw_schulmanager_note.note_create_user AS create_user,
			egw_schulmanager_note.note_update_date AS update_date,
			egw_schulmanager_note.note_update_user AS update_user,
			egw_schulmanager_note.note_asv_note_manuell AS asv_note_manuell,
            egw_schulmanager_note.note_art AS art,
            egw_schulmanager_note.note_definition_date AS definition_date,
            egw_schulmanager_note.note_description AS description
		FROM egw_schulmanager_note
			WHERE egw_schulmanager_note.note_asv_schueler_schuljahr_id='".$asv_schueler_schuljahr_id."'
			AND egw_schulmanager_note.note_asv_schueler_schuelerfach_id='".$asv_schuelerfach_id."'";

		$rs = $this->db->query($sql, __LINE__, __FILE__, 0, -1);


		foreach($rs as $row) {
            $note_id = $row['note_id'];
            $asv_id = $row['asv_id'];
            $blockbezeichner = $row['blockbezeichner'];
            $index_im_block = $row['index_im_block'];
            $note = $row['note'];
            $manuell = $row['asv_note_manuell'];
            $schueler['noten'][$blockbezeichner][$index_im_block]['note'] = $note;
            $schueler['noten'][$blockbezeichner][$index_im_block]['note_id'] = $note_id;
            $schueler['noten'][$blockbezeichner][$index_im_block]['asv_id'] = $asv_id;
            $schueler['noten'][$blockbezeichner][$index_im_block]['update_date'] = substr($row['update_date'],  0,10);
            $schueler['noten'][$blockbezeichner][$index_im_block]['update_user'] = $row['update_user'];
            $schueler['noten'][$blockbezeichner][$index_im_block]['art'] = $row['art'];

            if(isset($row['definition_date'])){
                $schueler['noten'][$blockbezeichner][$index_im_block]['definition_date'] = date("Y-m-d", $row['definition_date']);
            }

            $schueler['noten'][$blockbezeichner][$index_im_block]['description'] = $row['description'];

			if($index_im_block == -1){
				if($manuell > 0){
					$schueler['noten'][$blockbezeichner][$index_im_block]['manuell'] = '1';
					$schueler['noten'][$blockbezeichner]['avgclass'] = 'nm_avg_manuell';
				}
				else{
					$schueler['noten'][$blockbezeichner][$index_im_block]['manuell'] = '0';
					$schueler['noten'][$blockbezeichner]['avgclass'] = 'nm_avg_auto';
				}
			}
		}
	}

    /**
     * Load the teacher of a given unterrichtselement
     * @param $gk_asv_id
     * @param $fs_asv_id
     * @return void
     */
    function getLehrerByUnterricht($kg_asv_id, $sf_asv_id, &$teacher){
        $result = array();
        $sql = "SELECT DISTINCT
                    egw_schulmanager_asv_lehrer_stamm.ls_asv_rufname,
                    egw_schulmanager_asv_lehrer_stamm.ls_asv_familienname,
                    egw_schulmanager_asv_lehrer_stamm.ls_asv_wl_geschlecht_id,
                    egw_schulmanager_asv_lehrer_stamm.ls_asv_zeugnisname1,
                    egw_schulmanager_asv_lehrer_stamm.ls_asv_zeugnisname2,
                    egw_schulmanager_asv_lehrer_stamm.ls_asv_amtsbezeichnung_id
                FROM egw_schulmanager_asv_unterrichtselement
                INNER JOIN egw_schulmanager_asv_lehrer_schuljahr_schule ON egw_schulmanager_asv_lehrer_schuljahr_schule.lss_asv_id = egw_schulmanager_asv_unterrichtselement.ue_asv_lehrer_schuljahr_schule_id
                INNER JOIN egw_schulmanager_asv_lehrer_schuljahr ON egw_schulmanager_asv_lehrer_schuljahr.lsj_asv_id = egw_schulmanager_asv_lehrer_schuljahr_schule.lss_asv_lehrer_schuljahr_id
                INNER JOIN egw_schulmanager_asv_lehrer_stamm ON egw_schulmanager_asv_lehrer_stamm.ls_asv_id = egw_schulmanager_asv_lehrer_schuljahr.lsj_asv_lehrer_stamm_id
                INNER JOIN egw_schulmanager_asv_fachgruppe ON egw_schulmanager_asv_fachgruppe.fg_asv_id = egw_schulmanager_asv_unterrichtselement.ue_asv_fachgruppe_id
                INNER JOIN egw_schulmanager_asv_schuelerfach ON egw_schulmanager_asv_schuelerfach.sf_asv_id = egw_schulmanager_asv_fachgruppe.fg_asv_schuelerfach_id
                WHERE egw_schulmanager_asv_unterrichtselement.ue_asv_klassengruppe_id = '".$kg_asv_id."'
                    AND egw_schulmanager_asv_schuelerfach.sf_asv_id = '".$sf_asv_id."'";
        $rs = $this->db->query($sql, __LINE__, __FILE__, 0, -1);

        foreach($rs as $row) {
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
    }

	/**
	 * Nur als aktuelle Notloesung
	 * @param type $gewichtungen
	 * @param type $blockname
	 * @param type $index_im_block
	 */
	static function getGewichtung($gewichtungen, $blockname, $index_im_block){
		$gew = 1;

		$gewKey = '';
		if($blockname == 'glnw_hj_1'){
			$gewKey = 'glnw_1_'.$index_im_block;
		}
		elseif($blockname == 'klnw_hj_1'){
			$gewKey = 'klnw_1_'.$index_im_block;
		}
		elseif($blockname == 'glnw_hj_2'){
			$gewKey = 'glnw_2_'.$index_im_block;
		}
		elseif($blockname == 'klnw_hj_2'){
			$gewKey = 'klnw_2_'.$index_im_block;
		}
		if(array_key_exists($gewKey, $gewichtungen)){
			$gew = $gewichtungen[$gewKey];
		}
		return $gew;
	}


	/**
	 * recalculates avg and formats them
	 * @param type $schueler
	 */
	static function beforeSendToClient(&$schueler, $gewichtungen){
		if(isset($schueler['noten'])){
			foreach($schueler['noten'] as $blockname => &$notenblock){
				if(is_array($notenblock)){
					$notenblock['##sum##'] = 0;
					$notenblock['##anz##'] = 0;
					foreach($notenblock as $index_im_block => &$note){
						if(is_integer($index_im_block) and $index_im_block >= 0 and is_numeric($note['note']) and ((int)$note['note']) > 0){
							$gew = self::getGewichtung($gewichtungen, $blockname, $index_im_block);
							$n = (int)$note['note'];
							$notenblock['##sum##'] += $n * $gew;
							$notenblock['##anz##'] += $gew;
						}
					}
					if((!isset($notenblock[-1]['note']) || empty($notenblock[-1]['note']) || $notenblock[-1]['manuell']==0) && isset($notenblock['##anz##']) && $notenblock['##anz##'] > 0){
						// calculate average, because it is emtpty, not edited manually
						//$notenblock[-1]['value'] = floor(floatval($notenblock['##sum##']) / $notenblock['##anz##'] * 100) / 100;
						$notenblock[-1]['value'] = self::getNotenBlockSchnitt($notenblock['##sum##'], $notenblock['##anz##']);
						$notenblock[-1]['note'] = number_format(floatval($notenblock[-1]['value']), 2, ',', '');
					}
					elseif(!empty($notenblock[-1]['note']) && $notenblock[-1]['manuell']==1){
						// manuelle Eingabe
						$notenblock[-1]['value'] = floatval(str_replace(',', '.',$notenblock[-1]['note']));
					}
					else{
						$notenblock[-1]['note'] = '';
						$notenblock[-1]['value'] = '';
					}
					//if(isset($notenblock[-1]['note']) and !empty($notenblock[-1]['note'])){
					//	$notenblock[-1]['note'] = number_format(floor($notenblock[-1]['note'] * 100)/100, 2, ',', '');
					//}
				}
			}
			// alternative Berechnung
			if($schueler['noten']['alt_b'][-1]['note'] == 1){
				$schueler['noten']['alt_b'][-1]['checked'] = true;
				$schueler['noten']['alt_b'][-1]['img'] = 'done';
			}
			else{
				$schueler['noten']['alt_b'][-1]['checked'] = false;
				$schueler['noten']['alt_b'][-1]['img'] = '';
			}
			// Gesamtschnitt 1. HJ
			$glnw = $schueler['noten']['glnw_hj_1'][-1]['value'];
			$klnw = $schueler['noten']['klnw_hj_1'][-1]['value'];
			//$glnw = new Decimal($schueler['noten']['glnw_hj_1'][-1]['value']);
			//$klnw = new Decimal($schueler['noten']['klnw_hj_1'][-1]['value']);
			if(empty($schueler['noten']['schnitt_hj_1'][-1]['note']) || $schueler['noten']['schnitt_hj_1'][-1]['manuell'] == 0){
				if(!empty($schueler['noten']['glnw_hj_1'][-1]['note']) && !empty($schueler['noten']['klnw_hj_1'][-1]['note'])){
					$gew = $schueler['noten']['alt_b'][-1]['note'] == 1 ? 0 : 1;
					// value in glnw AND klnw
					//$schueler['noten']['schnitt_hj_1'][-1]['value'] = floor(((1+$gew)*$glnw + $klnw) / (2+$gew) * 100) /100;
					$schueler['noten']['schnitt_hj_1'][-1]['value'] = self::getNotenSchnitt($klnw, $glnw, $gew);
				}
				elseif (!empty($schueler['noten']['glnw_hj_1'][-1]['note']) && empty($schueler['noten']['klnw_hj_1'][-1]['note'])) {
					// Schnitt = kleine lnw
					$schueler['noten']['schnitt_hj_1'][-1]['value'] = $glnw;
				}
				elseif (empty($schueler['noten']['glnw_hj_1'][-1]['note']) && !empty($schueler['noten']['klnw_hj_1'][-1]['note'])) {
					// Schnitt = GROSSE LNW
					$schueler['noten']['schnitt_hj_1'][-1]['value'] = $klnw;
				}
			}
			if($schueler['noten']['schnitt_hj_1'][-1]['value'] > 0){
				$schueler['noten']['schnitt_hj_1'][-1]['note'] = number_format(floatval($schueler['noten']['schnitt_hj_1'][-1]['value']), 2, ',', '');
			}
			if(empty($schueler['noten']['note_hj_1'][-1]['note']) || $schueler['noten']['note_hj_1'][-1]['manuell'] == 0){
				if($schueler['noten']['schnitt_hj_1'][-1]['value'] > 0){
					$schueler['noten']['note_hj_1'][-1]['note'] = round(floatval($schueler['noten']['schnitt_hj_1'][-1]['value']) - 0.01, 0, PHP_ROUND_HALF_UP);
				}
				else{
					$schueler['noten']['note_hj_1'][-1]['note'] = '';
				}
			}
			// 1. HJ ins 2. HJ bei Schnitten übernehmen
			// GLNW 2. HJ
			$schueler['noten']['glnw_hj_2']['##sum##'] = $schueler['noten']['glnw_hj_1']['##sum##'] + $schueler['noten']['glnw_hj_2']['##sum##'];
			$schueler['noten']['glnw_hj_2']['##anz##'] = $schueler['noten']['glnw_hj_1']['##anz##'] + $schueler['noten']['glnw_hj_2']['##anz##'];
			if($schueler['noten']['glnw_hj_2']['##anz##'] !== 0 && $schueler['noten']['glnw_hj_2'][-1]['manuell'] == 0){
				//$schueler['noten']['glnw_hj_2'][-1]['value'] = floor(floatval($schueler['noten']['glnw_hj_2']['##sum##']) / $schueler['noten']['glnw_hj_2']['##anz##'] * 100) / 100;
				$schueler['noten']['glnw_hj_2'][-1]['value'] = self::getNotenBlockSchnitt($schueler['noten']['glnw_hj_2']['##sum##'], $schueler['noten']['glnw_hj_2']['##anz##']);
				$schueler['noten']['glnw_hj_2'][-1]['note'] = number_format(floatval($schueler['noten']['glnw_hj_2'][-1]['value']), 2, ',', '');
			}
			// klnw 2. HJ
			$schueler['noten']['klnw_hj_2']['##sum##'] = $schueler['noten']['klnw_hj_1']['##sum##'] + $schueler['noten']['klnw_hj_2']['##sum##'];
			$schueler['noten']['klnw_hj_2']['##anz##'] = $schueler['noten']['klnw_hj_1']['##anz##'] + $schueler['noten']['klnw_hj_2']['##anz##'];
			if($schueler['noten']['klnw_hj_2']['##anz##'] !== 0 && $schueler['noten']['klnw_hj_2'][-1]['manuell'] == 0){
				//$schueler['noten']['klnw_hj_2'][-1]['value'] = floor(floatval($schueler['noten']['klnw_hj_2']['##sum##']) / $schueler['noten']['klnw_hj_2']['##anz##'] * 100) / 100;
				$schueler['noten']['klnw_hj_2'][-1]['value'] = self::getNotenBlockSchnitt($schueler['noten']['klnw_hj_2']['##sum##'], $schueler['noten']['klnw_hj_2']['##anz##']);
				$schueler['noten']['klnw_hj_2'][-1]['note'] = number_format(floatval($schueler['noten']['klnw_hj_2'][-1]['value']), 2, ',', '');
			}

			// Gesamtschnitt 2. HJ
			$glnw_2 = $schueler['noten']['glnw_hj_2'][-1]['value'];
			$klnw_2 = $schueler['noten']['klnw_hj_2'][-1]['value'];
			if(empty($schueler['noten']['schnitt_hj_2'][-1]['note']) || $schueler['noten']['schnitt_hj_2'][-1]['manuell'] == 0){
				if(!empty($schueler['noten']['glnw_hj_2'][-1]['note']) && !empty($schueler['noten']['klnw_hj_2'][-1]['note'])){
					$gew = $schueler['noten']['alt_b'][-1]['note'] == 1 ? 0 : 1;
					// value in glnw AND klnw
					//$schueler['noten']['schnitt_hj_2'][-1]['value'] = floor(((1+$gew)*$glnw_2 + $klnw_2) / (2+$gew) * 100) /100;
					$schueler['noten']['schnitt_hj_2'][-1]['value'] = self::getNotenSchnitt($klnw_2, $glnw_2, $gew);
				}
				elseif (!empty($schueler['noten']['glnw_hj_2'][-1]['note']) && empty($schueler['noten']['klnw_hj_2'][-1]['note'])) {
					// Schnitt = kleine lnw
					$schueler['noten']['schnitt_hj_2'][-1]['value'] = $glnw_2;
				}
				elseif (empty($schueler['noten']['glnw_hj_2'][-1]['note']) && !empty($schueler['noten']['klnw_hj_2'][-1]['note'])) {
					// Schnitt = GROSSE LNW
					$schueler['noten']['schnitt_hj_2'][-1]['value'] = $klnw_2;
				}
			}
			// value to note, value contains string like '1.23'
			if(floatval($schueler['noten']['schnitt_hj_2'][-1]['value']) > 0){
				$schueler['noten']['schnitt_hj_2'][-1]['note'] = number_format(floatval($schueler['noten']['schnitt_hj_2'][-1]['value']), 2, ',', '');
			}
			if(empty($schueler['noten']['note_hj_2'][-1]['note']) || $schueler['noten']['note_hj_2'][-1]['manuell'] == 0){
				if($schueler['noten']['schnitt_hj_2'][-1]['value'] > 0){
					$schueler['noten']['note_hj_2'][-1]['note'] = round(floatval($schueler['noten']['schnitt_hj_2'][-1]['value']) - 0.01, 0, PHP_ROUND_HALF_UP);
				}
				else{
					$schueler['noten']['note_hj_2'][-1]['note'] = '';
				}
			}
		}
		// Austrittsdatum
		self::checkExitDate($schueler);
	}

	/**
	 * Calculates the average value of grades-avgs
	 * @param string $klnw
	 * @param string $glnw
	 * @param string $gew
	 * @param int $scale
	 * @return string
	 */
	static function getNotenSchnitt(string $klnw, string $glnw, string $gew, int $scale = 2){
		bcscale($scale + 3);
		// floor(((1+$gew)*$glnw + $klnw) / (2+$gew) * 100) /100;
		$schnitt = bcdiv( bcadd( bcmul( bcadd(1, $gew), $glnw), $klnw) , bcadd(2, $gew));
		return bcadd($schnitt, '0.0', $scale);
	}

	/**
	 * Calculates the average value of a single grades block
	 * @param int $sum
	 * @param int $anz
	 * @param int $scale
	 * @return string
	 */
	static function getNotenBlockSchnitt(int $sum, int $anz, int $scale = 2){
		bcscale($scale + 3);
		// floor(((1+$gew)*$glnw + $klnw) / (2+$gew) * 100) /100;
		$schnitt = bcdiv(strval($sum), strval($anz));
		return bcadd($schnitt, '0.0', $scale);
	}


	static function checkExitDate(&$schueler){
		if(!empty($schueler['nm_st']['st_asv_austrittsdatum'])){
			$exitDate = DateTime::createFromFormat('Y-m-d', $schueler['nm_st']['st_asv_austrittsdatum']);
			if($exitDate <= new DateTime()){
				$schueler['nm_st']['nm_st_class'] = 'nm_st_left';
			}
			$schueler['nm_st']['hint']['img'] = 'dialog_info';
			$schueler['nm_st']['hint']['text'] = 'Austrittsdatum: '.$schueler['nm_st']['st_asv_austrittsdatum'];
		}
	}


    /**
     * queries all teachers
     * @param $filter
     * @param $rows
     */
	function getLehrerList(&$query_in, &$rows){
        $readonlys = array();
        $result = $this->get_rows($query_in, $rows, $readonlys);

        if($query_in['start'] == 0){
            $query_in['total'] = $result;//->NumRows();
        }
    }

    function getLehrerAccountList(&$query_in, &$rows){
	    $readonlys = array();
        $join = " LEFT JOIN egw_schulmanager_lehrer_account ON egw_schulmanager_lehrer_account.leac_lehrer = $this->sm_lehrer_table.ls_asv_id ";

        $extra_cols = 'leac_account';
        $rowsAppend = array();
        $result = $this->get_rows($query_in, $rowsAppend, $readonlys, $join, false, false, $extra_cols);

        $start = isset($query_in['start']) ? $query_in['start'] : 0;

        $nm_id = $start;
        foreach ($rowsAppend as &$rowA){
            if($rowA['leac_account']){
                $rowA['account_lid'] = API\Accounts::id2name($rowA['leac_account']);
            }
            $rowA['nm_id'] = $nm_id;
            $rowA['row_id'] = $nm_id + 1;

            $rows[$nm_id] = $rowA;
            $nm_id++;
        }

        if($query_in['start'] == 0){
            $query_in['total'] = $result;
        }
    }

    function getTeacherByEGWUserID($egw_uid){
        $teacher = null;
        $sql = "SELECT egw_schulmanager_asv_lehrer_stamm.ls_asv_rufname,
                    egw_schulmanager_asv_lehrer_stamm.ls_asv_familienname,
                    egw_schulmanager_asv_lehrer_stamm.ls_asv_wl_geschlecht_id,
                    egw_schulmanager_asv_lehrer_stamm.ls_asv_zeugnisname1,
                    egw_schulmanager_asv_lehrer_stamm.ls_asv_zeugnisname2,
                    egw_schulmanager_asv_lehrer_stamm.ls_asv_amtsbezeichnung_id                    
                    FROM egw_schulmanager_asv_lehrer_stamm
                    INNER JOIN egw_schulmanager_lehrer_account ON egw_schulmanager_lehrer_account.leac_lehrer = egw_schulmanager_asv_lehrer_stamm.ls_asv_id
                    WHERE egw_schulmanager_lehrer_account.leac_account = '".$egw_uid."'";
        $rs = $this->db->query($sql, __LINE__, __FILE__, 0, -1);

        foreach($rs as $row) {
            $teacher = array(
                'ls_asv_rufname' => $row['ls_asv_rufname'],
                'ls_asv_familienname' => $row['ls_asv_familienname'],
                'ls_asv_wl_geschlecht_id' => $row['ls_asv_wl_geschlecht_id'],
                'ls_asv_zeugnisname1' => $row['ls_asv_zeugnisname1'],
                'ls_asv_zeugnisname2' => $row['ls_asv_zeugnisname2'],
                'ls_asv_amtsbezeichnung_id' => $row['ls_asv_amtsbezeichnung_id'],
                'geschlecht' => schulmanager_werteliste_bo::getGeschlecht($row['ls_asv_wl_geschlecht_id'], 'kurzform')
            );
            break;
        }
        return $teacher;
    }


    /**
     * Return an array with class leaders
     * @param $klasse_id
     * @return void
     */
    function getKlassenleitungen($klasse_id, &$kls){
        $sql = "SELECT DISTINCT
                    egw_schulmanager_asv_lehrer_stamm.ls_asv_rufname,
                    egw_schulmanager_asv_lehrer_stamm.ls_asv_familienname,
                    egw_schulmanager_asv_lehrer_stamm.ls_asv_wl_geschlecht_id,
                    egw_schulmanager_asv_lehrer_stamm.ls_asv_zeugnisname1,
                    egw_schulmanager_asv_lehrer_stamm.ls_asv_zeugnisname2,
                    egw_schulmanager_asv_lehrer_stamm.ls_asv_amtsbezeichnung_id
                FROM egw_schulmanager_asv_klassenleitung
                INNER JOIN egw_schulmanager_asv_lehrer_schuljahr_schule ON egw_schulmanager_asv_lehrer_schuljahr_schule.lss_asv_id = egw_schulmanager_asv_klassenleitung.kl_lehrer_schuljahr_schule_id
                INNER JOIN egw_schulmanager_asv_lehrer_schuljahr ON egw_schulmanager_asv_lehrer_schuljahr.lsj_asv_id = egw_schulmanager_asv_lehrer_schuljahr_schule.lss_asv_lehrer_schuljahr_id
                INNER JOIN egw_schulmanager_asv_lehrer_stamm ON egw_schulmanager_asv_lehrer_stamm.ls_asv_id = egw_schulmanager_asv_lehrer_schuljahr.lsj_asv_lehrer_stamm_id                
                WHERE egw_schulmanager_asv_klassenleitung.kl_klasse_id = '".$klasse_id."' 
                ORDER BY egw_schulmanager_asv_klassenleitung.kl_art";
        $rs = $this->db->query($sql, __LINE__, __FILE__, 0, -1);

        foreach($rs as $row) {
            $kls[] = array(
                'ls_asv_rufname' => $row['ls_asv_rufname'],
                'ls_asv_familienname' => $row['ls_asv_familienname'],
                'ls_asv_wl_geschlecht_id' => $row['ls_asv_wl_geschlecht_id'],
                'ls_asv_zeugnisname1' => $row['ls_asv_zeugnisname1'],
                'ls_asv_zeugnisname2' => $row['ls_asv_zeugnisname2'],
                'ls_asv_amtsbezeichnung_id' => $row['ls_asv_amtsbezeichnung_id'],
                'geschlecht' => schulmanager_werteliste_bo::getGeschlecht($row['ls_asv_wl_geschlecht_id'], 'kurzform')
            );
        }
    }

    /**
     * Updates mapping teachers with EGroupware accounts
     */
    function updateEGWLinking(){
        $criteria = array();
        $extra_cols = 'leac_lehrer';

        $join = " LEFT JOIN egw_schulmanager_lehrer_account ON egw_schulmanager_lehrer_account.leac_lehrer = $this->sm_lehrer_table.ls_asv_id ";
	    $lehrerList = $this->search($criteria, false, '', $extra_cols, '', False, 'AND', false, null, $join);

	    // do mapping
        $options = array();
        $options['account_type'] = 'accounts';

        $lehrer_account_so =  new schulmanager_lehrer_account_so();

        foreach ($lehrerList as &$lehrer) {
            if(isset($lehrer['leac_lehrer'])){
                // already linked
                continue;
            }

            $pattern = Api\Accounts::email($lehrer['ls_asv_rufname'], $lehrer['ls_asv_familienname'], "");
            $ids = Api\Accounts::link_query($pattern, $options);

            if(count($ids) == 1){
                // unique egroupware user found
                $egw_uid = array_key_first($ids);
                $lehrer_account = array(
                    'leac_lehrer' => $lehrer['ls_asv_id'],
                    'leac_account' => $egw_uid,
                );
                $lehrer_account_so->saveItem($lehrer_account);
            }
        }
    }
}

