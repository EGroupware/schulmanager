<?php

/**
 * Schulmanager Cal - teacher bo
 *
 * @link http://www.egroupware.org
 * @package schulmanager
 * @author Axel Wild <info-AT-wild-solutions.de>
 * @copyright (c) 2022 by Axel Wild <info-AT-wild-solutions.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

use EGroupware\Api;

require_once(EGW_INCLUDE_ROOT.'/schulmanager/inc/class.schulmanager_lehrer_so.inc.php');

/**
 * This class is the BO-layer of a teacher
 */
class schulmanager_lehrer_bo
{
	/**
	 * @var int $debug name of method to debug or level of debug-messages:
	 *	False=Off as higher as more messages you get ;-)
	 *	1 = function-calls incl. parameters to general functions like search, read, write, delete
	 *	2 = function-calls to exported helper-functions like check_perms
	 *	4 = function-calls to exported conversation-functions like date2ts, date2array, ...
	 *	5 = function-calls to private functions
	 */
	var $debug=false;

	/**
	 * Instance of the so lehrer class
	 *
	 * @var schulmanager_lehrer_so
	 */
	var $so;

    var $unterricht_so;

	/**
	 * @var int $user nummerical id of the current user-id
	 */
	var $user=0;

	/**
	 * @var string $user nummerical id of the current user-id
	 */
	var $username=0;

	/**
	 * @var schulmanager_klassengr_schuelerfa $lessons array mit klassengruppen schuelerfach Kombinationen
	 */
	var $lessons = null;

    /**
     * instance of the bo-class
     * @var wl_bo
     */
    var $wl_bo;

    var $lehrerStammIDs;


	/**
	 * Constructor
	 */
	function __construct()
	{
		if ($this->debug > 0) $this->debug_message('schulmanager_lehrer_bo::bocal() started',True);
		$this->so = new schulmanager_lehrer_so();
        $this->unterricht_so = new schulmanager_unterricht_so();
		$this->username = $GLOBALS['egw_info']['user']['account_lid'];
        $lehrer_account_so = new schulmanager_lehrer_account_so();
        $this->lehrerStammIDs = $lehrer_account_so->loadLehrerStammIDs($GLOBALS['egw_info']['user']['account_id']);
        $this->lessons = $this->unterricht_so->loadLehrerUnterricht($this->lehrerStammIDs);
        $this->wl_bo = new schulmanager_werteliste_bo();
	}

    /**
     * Creates an array of lessons, without combined lessen elements
     * @return array
     */
	function getLessonList(){
		$result = array();
		foreach($this->lessons as $item){
			$result[] = $item['bezeichnung'];
		}
		return $result;
	}

	/**
	 * Returns TRUE, if active user hast read access to all classes
	 * @return boolean
	 */
	function showAllKlassen(){
		$config = Api\Config::read('schulmanager');
		$this->user = $GLOBALS['egw_info']['user'];

		$view_all_accounts = $config['view_all_accounts'];
		$view_all_groups = $config['view_all_groups'];

		if(in_array($this->user['account_id'], $view_all_accounts)){
			return TRUE;
		}

		foreach($view_all_groups as $groupid){
			if(array_key_exists($groupid, $this->user['memberships'])){
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
     * Search all grades
	 * Liefert alle Note der Schüler zu einem Fach
	 * @param type $filter
	 * @param type $rows
	 * @return type|void
	 */
	function getSchuelerNotenList(&$query_in, &$rows){
	    $filter = $query_in['filter'];
		$total = 0;
		if(is_null($filter) or $filter < 0){
			Api\Cache::unsetSession('schulmanager', 'filter_klassengruppe_asv_id');
			Api\Cache::unsetSession('schulmanager', 'filter_schuelerfach_asv_id');
            $query_in['total'] = 0;
			return;
		}
		else{
			if(is_null($this->lessons)){
				$this->getLessonList();
			}
			if(empty($this->lessons)){
                $query_in['total'] = 0;
				return;
			}
            $koppel_id = $this->lessons[$filter]['koppel_id'];

			// Save in Session
            Api\Cache::setSession('schulmanager', 'actual_lesson', $this->lessons[$filter]);
            Api\Cache::setSession('schulmanager', 'filter_koppel_id', $koppel_id);

			// Gewichtungen
			$gewichtungen = array();
			$gew_bo = new schulmanager_note_gew_bo();
			$gew_bo->loadGewichtungen($koppel_id, $gewichtungen);
			Api\Cache::setSession('schulmanager', 'notenmanager_gewichtungen', $gewichtungen);

            //$total = $this->unterricht_so->loadSchuelerNotenList($query_in, $koppel_id, $rows, $gewichtungen);
            $this->unterricht_so->loadSchuelerNotenList($query_in, $koppel_id, $rows, $gewichtungen);

			// Gewichtungen in rows schreiben
			foreach($gewichtungen as $gewKey => $gewVal){
				$rows[$gewKey] = $gewVal;
			}
            //$query_in['total'] = $total;
		}
	}


    /**
     * Return all classes as an array of this class leader or if this user has special access rights
     * array(0 => '5A', 1 => '5B', ...
     * @param bool $plain if playin = true, the result only contains the names of classes, if plain is false, the complete data is returned
     * @return array|mixed|null
     */
	function getClassLeaderClasses($plain = true){
        $classLeaderClasses = Api\Cache::getSession('schulmanager', 'classLeaderClasses');

        if(!is_array($classLeaderClasses)){
            $this->so->loadClassleaderClasses($this->lehrerStammIDs, $classLeaderClasses, $this->showAllKlassen());
            Api\Cache::setSession('schulmanager', 'classLeaderClasses', $classLeaderClasses);
        }
		$result = array();
		if($plain){
            foreach($classLeaderClasses as $key => $value){
                $result[$key] = $value['name'];
            }
		}
		else{
			$result = $classLeaderClasses;
		}
		return $result;
	}

	/**
	 * Delivers all students of a class
	 * @param type $filter index in class list
	 * @param type $rows
	 * @return type
	 */
	function getKlassenSchuelerList($filter, &$rows){
		$klassenasvids = Api\Cache::getSession('schulmanager', 'klassen_asv_ids');
		$asvid = $klassenasvids[$filter];
		$this->so->loadKlassenSchuelerList($asvid, $rows);
	}

    function getKlassenleitungen($klasse_id, &$kls){
        return $this->so->getKlassenleitungen($klasse_id, $kls);
    }

    function getTeacherByEGWUserID($egw_uid){
        return $this->so->getTeacherByEGWUserID($egw_uid);
    }
}
