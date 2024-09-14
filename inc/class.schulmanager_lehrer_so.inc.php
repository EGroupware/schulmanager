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

		$searchSQL = $this->db->quote('%'.$search.'%');

		$sql = "SELECT DISTINCT 
                        egw_schulmanager_asv_klasse.kl_asv_klassenname AS klassenname,
                        egw_schulmanager_asv_klasse.kl_asv_id AS klasse_asv_id,
                        egw_schulmanager_asv_lehrer_stamm.ls_asv_familienname AS lehrer_sn,
	                    egw_schulmanager_asv_lehrer_stamm.ls_asv_rufname AS lehrer_givenname,
	                    egw_schulmanager_asv_lehrer_stamm.ls_asv_zeugnisname1 AS lehrer_zeugnisname1,
	                    egw_schulmanager_asv_lehrer_stamm.ls_asv_zeugnisname2 AS lehrer_zeugnisname2,
	                    egw_schulmanager_asv_lehrer_stamm.ls_asv_id AS lehrer_asv_id,
                        egw_schulmanager_asv_klassenleitung.kl_art AS art                    
                    FROM egw_schulmanager_asv_klasse
                    INNER JOIN egw_schulmanager_asv_klassenleitung ON egw_schulmanager_asv_klassenleitung.kl_klasse_id = egw_schulmanager_asv_klasse.kl_asv_id
                    INNER JOIN egw_schulmanager_asv_lehrer_schuljahr_schule ON egw_schulmanager_asv_lehrer_schuljahr_schule.lss_asv_id = egw_schulmanager_asv_klassenleitung.kl_lehrer_schuljahr_schule_id
                    INNER JOIN egw_schulmanager_asv_lehrer_schuljahr ON egw_schulmanager_asv_lehrer_schuljahr.lsj_asv_id = egw_schulmanager_asv_lehrer_schuljahr_schule.lss_asv_lehrer_schuljahr_id
                    INNER JOIN egw_schulmanager_asv_lehrer_stamm ON egw_schulmanager_asv_lehrer_schuljahr.lsj_asv_lehrer_stamm_id = egw_schulmanager_asv_lehrer_stamm.ls_asv_id
                    WHERE egw_schulmanager_asv_klasse.kl_asv_klassenname LIKE ".$searchSQL."
                    ORDER BY egw_schulmanager_asv_klasse.kl_asv_klassenname";

		$rs = $this->db->query($sql, __LINE__, __FILE__, 0, -1);
		$id = 0;
		$klassenasvids = array();

		Api\Cache::unsetSession('schulmanager', 'klassen_asv_ids');

        $tmpRows = array();
        foreach($rs as $row){
            if(!array_key_exists($row['klasse_asv_id'], $tmpRows)){
                $tmpRows[$row['klasse_asv_id']] = array(
                    'name' => $row['klassenname'],
                    'asvid' => $row['klasse_asv_id']
                );
            }
            $tmpRows[$row['klasse_asv_id']][$row['art'].'_givenname'] = $row['lehrer_givenname'];
            $tmpRows[$row['klasse_asv_id']][$row['art'].'_sn'] = $row['lehrer_sn'];
            $tmpRows[$row['klasse_asv_id']][$row['art'].'_zeugnisname'] = $row['lehrer_zeugnisname'];
            $tmpRows[$row['klasse_asv_id']][$row['art'].'_asv_id'] = $row['lehrer_asv_id'];
        }

        foreach($tmpRows as $tmpRow){
            // exclude classes, where user is not class leader
            if(!$showAllKlassen && !in_array($tmpRow['1111_K_asv_id'], $lehrerStammIDs) && !in_array($tmpRow['1111_S_asv_id'], $lehrerStammIDs)){
                continue;
            }

            $rows[$id] = $tmpRow;
            $klassenasvids[$id] = $tmpRow['asvid'];
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
	 *
	 * @param type $klasseasvid
	 * @param type $rows
	 * @return int
	 */
	function &loadKlassenSchuelerList($klasseasvid, &$rows){
		$schueler_so = new schulmanager_schueler_so();
        $note_so = new schulmanager_note_so();


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

			$note_so->getSchuelerAVG($row['st_asv_id'], $schueler);

			self::checkExitDate($schueler);

			$rows[$id] = $schueler;
			$id++;
		}

		return $rows;
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