<?php
/**
 * EGroupware - Schulmanager Lehrer buisness-object - access only
 *
 @link http://www.egroupware.org
 * @package schulmanager
 * @author Axel Wild <info-AT-wild-solutions.de>
 * @copyright (c) 2018 by info-AT-wild-solutions.de
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

use EGroupware\Api;
use EGroupware\Api\Link;
use EGroupware\Api\Acl;

/**
 * Required (!) include, as we use the MCAL_* constants, BEFORE instanciating (and therefore autoloading) the class
 */
require_once(EGW_INCLUDE_ROOT.'/schulmanager/inc/class.schulmanager_lehrer_so.inc.php');


/**
 * This class is the BO-layer of InfoLog
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

	/**
	 * @var int $user nummerical id of the current user-id
	 */
	var $user=0;

	/**
	 * @var string $user nummerical id of the current user-id
	 */
	var $username=0;

	/**
	 * @var schulmanager_klassengr_schuelerfa $klassengr_schuelerfa array mit klassengruppen schuelerfach Kombinationen
	 */
	var $klassengr_schuelerfa = null;



	/**
	 * Constructor
	 */
	function __construct($kennung)
	{
		if ($this->debug > 0) $this->debug_message('schulmanager_lehrer_bo::bocal() started',True);

		$this->so = new schulmanager_lehrer_so();

		$this->username = $kennung;

		//$result = $this->so->loadUnterricht($kennung);
		$this->klassengr_schuelerfa = $this->so->loadUnterricht($kennung);
		//$this->klassengr_schuelerfa = $this->so->loadUnterricht('lehrer9310GY');
		//$this->user = $GLOBALS['egw_info']['user']['account_id'];
	}

	function getKlasseUnterrichtList(){
		$result = array();
		foreach($this->klassengr_schuelerfa as $item){
			$result[] = $item->getFormatKgSf();
		}
		return $result;
	}

	/**
	 * Liefert TRUE, wenn User alle Klassen einsehen darf
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
	 * Liefert alle Note der Schüler zu einem Fach
	 * @param type $filter
	 * @param type $rows
	 * @return type
	 */
	function getSchuelerNotenList($filter, &$rows){
		$total = 0;
		if(is_null($filter) or $filter < 0){
			Api\Cache::unsetSession('schulmanager', 'filter_klassengruppe_asv_id');
			Api\Cache::unsetSession('schulmanager', 'filter_schuelerfach_asv_id');
			return 0;
		}
		else{
			if(is_null($this->klassengr_schuelerfa)){
				$this->getKlasseUnterrichtList();
			}
			elseif(empty($this->klassengr_schuelerfa)){
				return 0;
			}
			$kg_asv_id = $this->klassengr_schuelerfa[$filter]->getKlassengruppe_asv_id();
			$sf_asv_id = $this->klassengr_schuelerfa[$filter]->getSchuelerfach_asv_id();

			// Save in Session
			Api\Cache::setSession('schulmanager', 'filter_klassengruppe_asv_id', $kg_asv_id);
			Api\Cache::setSession('schulmanager', 'filter_schuelerfach_asv_id', $sf_asv_id);

			// Gewichtungen
			$gewichtungen = array();
			$gew_bo = new schulmanager_note_gew_bo();
			$gew_bo->loadGewichtungen($kg_asv_id, $sf_asv_id, $gewichtungen);
			Api\Cache::setSession('schulmanager', 'notenmanager_gewichtungen', $gewichtungen);

			$total = $this->so->loadSchuelerNotenList($kg_asv_id, $sf_asv_id, $rows, $gewichtungen);

			// Gewichtungen in rows schreiben
			// foreach($rows as $id => &$schueler){
			foreach($gewichtungen as $gewKey => $gewVal){
				$rows[$gewKey] = $gewVal;
			}

			//$rows['gew_glnw_hj1_0'] = '4';
		    //$rows['gew_glnw_hj1_1'] = '3';
			//$rows['test'] = rand(1, 7);
			//$rows['test_1'] = rand(1, 7);

			return $total;
		}
	}



	/**
	 * plain list of klassen
	 * @return array
	 */
	function getKlassen($plain = true){
		// TODO Cache?
		$result = array();
		$klassen = array();
		$this->so->getKlassen($this->username, $klassen, $this->showAllKlassen());

		if($plain){
			foreach($klassen as $key => $value){
				$result[$key] = $value['name'];
			}
		}
		else{
			$result = $klassen;
		}

		return $result;
	}

	/**
	 * Liefert die Schueler einer Klasse
	 * @param type $filter
	 * @param type $rows
	 * @return type
	 */
	function getKlassenSchuelerList($filter, &$rows){
		// TODO
		//$klassenasvid = 'rbdu/faqs/55d4/a216/fmwu';

		$klassenasvids = Api\Cache::getSession('schulmanager', 'klassen_asv_ids');
		$asvid = $klassenasvids[$filter];
		return $this->so->loadKlassenSchuelerList($asvid, $rows);


	}







}
