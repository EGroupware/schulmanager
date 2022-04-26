<?php
/**
 * Schulmanager Cal - business object
 * Kalendereinträge im Schulmanager werden als normale EGroupware Kalendereinträge gespeichert.
 * Solch ein Eintrag muss als benutzerdefiniertes Feld (Checkbox) den Wert #SCHULMANAGER_CAL=1 verfügen
 * Es muss ein Benutzer angelegt sein, der die Kalendereinträge als user besitzt. Alle Lehrer benötigen Lese-rechte an dessen Kalender
 *
 * @link http://www.egroupware.org
 * @package schulmanager
 * @author Axel Wild <info-AT-wild-solutions.de>
 * @copyright (c) 2022 by Axel Wild <info-AT-wild-solutions.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

use EGroupware\Api;

if (!defined('SCHULMANAGER_APP'))
{
	define('SCHULMANAGER_APP','schulmanager');
}
// item in schulmanager calendar
if (!defined('SCHULMANAGER_CAL'))
{
	define('SCHULMANAGER_CAL','##SCHULMANAGER_CAL');
}
// Klasse
if (!defined('SCHULMANAGER_CAL_KLASSE'))
{
	define('SCHULMANAGER_CAL_KLASSE','##SCHULMANAGER_CAL_KLASSE');
}
// Klassengruppe
if (!defined('SCHULMANAGER_CAL_KLASSENGRUPPE'))
{
	define('SCHULMANAGER_CAL_KLASSENGRUPPE','##SCHULMANAGER_CAL_KLASSENGRUPPE');
}
// Typ
if (!defined('SCHULMANAGER_CAL_TYPE'))
{
	define('SCHULMANAGER_CAL_TYPE','##SCHULMANAGER_CAL_TYPE');
}
// Typ
if (!defined('SCHULMANAGER_CAL_FACH'))
{
	define('SCHULMANAGER_CAL_FACH','##SCHULMANAGER_CAL_FACH');
}
// User
if (!defined('SCHULMANAGER_CAL_USER'))
{
	define('SCHULMANAGER_CAL_USER','##SCHULMANAGER_CAL_USER');
}
// Index
if (!defined('SCHULMANAGER_CAL_INDEX'))
{
	define('SCHULMANAGER_CAL_INDEX','##SCHULMANAGER_CAL_INDEX');
}
/**
 * Business object of the Schulmanager calendar
 * Uses eTemplate's Api\Storage as storage object .
 */
class schulmanager_cal_bo extends Api\Storage
{
	/**
	 * Schulmanager Api\Config data
	 * @var array
	 */
	var $config_data = array();

	var $tage = array("Sonntag","Montag","Dienstag","Mittwoch","Donnerstag","Freitag","Samstag");


	/**
	 * List of filter options
	 */
	public static $filter_options = array(
		0 => 'eigene Klassen',
		1 => 'alle Klassen',
	);

    /**
     * Options of items
     * @var string[]
     */
	public static $cal_item_options = array(
		'sa' => 'Schulaufgabe',
		'ka' => 'Kurzarbeit',
		'ex' => 'Stegreifaufgabe',
		'flt' => 'fachlicher Leistungstest',
		'sonst' => 'Sonstige',
		'block' => 'BLOCKIERT',
	);

    /**
     * Indeces of items
     * @var string[]
     */
	public static $cal_index_options = array(
		'sm_index_0' => '(leer)',
		'sm_index_1' => '1.',
		'sm_index_2' => '2.',
		'sm_index_3' => '3.',
		'sm_index_4' => '4.',
	);

	/**
	 * Instance of the so lehrer class
	 * @var schulmanager_lehrer_so
	 */
	var $so_lehrer;

    /**
     * Current user
     * @var mixed
     */
	var $user;

    /**
     * Constructor
     */
	function __construct()
	{
		$this->so_lehrer = new schulmanager_lehrer_so();
		$this->user = $GLOBALS['egw_info']['user'];
		$this->config_data = Api\Config::read(SCHULMANAGER_APP);

		$this->today = mktime(0,0,0,date('m',$this->now),date('d',$this->now),date('Y',$this->now));
	}

    /**
     * Search calendar events
     * @param $query_in
     * @param $rows
     * @throws Exception
     */
	function searchCalDates(&$query_in, &$rows){
		$calbo = new calendar_boupdate();
		$schulmanager_user =  $this->config_data['schulmanager_user'];

		$dateSelected = new DateTime(Api\Cache::getSession('schulmanager', 'cal_sel_month_date'));
		$start = $dateSelected->getTimestamp();
		$dateSelected->modify('+31 day');
		$end = $dateSelected->getTimestamp();

		$this->resetCalDates($rows);

		$params = array(
			'start' => $start,
			'end'   => $end,
			'users' => $schulmanager_user,
			'cfs'	=> array(),
			'ignore_acl' => true,
		);

		$items = $calbo->search($params);
		foreach($items as $key => $calItem){
			$klasse = $calItem[SCHULMANAGER_CAL_KLASSE];
			$klassengruppe = $calItem[SCHULMANAGER_CAL_KLASSENGRUPPE];
			$rowIndex = $this->getRowIndex($klasse, $klassengruppe, $rows);
			if($rowIndex >= 0){
				// maybe row is not displayed
				$this->getCalViewData($calItem, $start, $end, $rows[$rowIndex]['cal']);
			}
		}
	}

	/**
	 * returns all blocking event types. max a single event should be possible per day, class and classgroup!
	 * @return type
	 */
	function getBlockingEventTypes(){
		$result = array_merge(array(), self::$cal_item_options);
		unset($result['ex']);
		unset($result['sonst']);
		return $result;
	}

    /**
     * Returns all non blocking calendar types
     * @return array
     */
	function getNonBlockingEventTypes(){
		$result = array_merge(array(), self::$cal_item_options);
		unset($result['sa']);
		unset($result['ka']);
		unset($result['flt']);
		unset($result['sonst']);
		unset($result['block']);
		return $result;
	}

	/**
	 * checks type of event
	 * @param type $event
	 * @return boolean true if $event is a blocking event
	 */
	function isBlockingEvent($event){
		return array_key_exists($event['sm_type'], $this->getBlockingEventTypes());
	}
	function isBlockingEventType($eventType){
		return array_key_exists($eventType, $this->getBlockingEventTypes());
	}

	/**
	 * searches all dates between $start an $end for class with name = $klassenname
	 * @param type $start
	 * @param type $end
	 * @param type $klassennamen
	 */
	function searchCalDatesInRange($start, $end, $klassenname, $klassengruppe){
		$result = array();
		$calbo = new calendar_boupdate();
		$schulmanager_user =  $this->config_data['schulmanager_user'];

		$params = array(
			'start' => $start,
			'end'   => $end,
			'users' => $schulmanager_user,
			'cfs'	=> array(),
			'ignore_acl' => true,
		);

		$items = $calbo->search($params);
		$row_id = 0;
		foreach($items as $key => $calItem){
			if($klassenname == $calItem[SCHULMANAGER_CAL_KLASSE] && $klassengruppe == $calItem[SCHULMANAGER_CAL_KLASSENGRUPPE]){
				//$result[] = $calItem;
				$row_id++;
				$event = array();
				$calItem['row_id'] = $row_id;
				$this->modifyBeforeSend2Client($event, $calItem);
				$result[] = $event;
			}
		}
		return $result;
	}

    /**
     * This method tests, if active user has coordinative rights
     * @return bool
     */
	function activeUserIsCalCoordinator(){
		$result = false;
		$config = Api\Config::read('schulmanager');
		$cal_Coo = $config['cal_coordinator'];
		if(isset($cal_Coo)){
			$result = in_array($this->user['account_id'], $cal_Coo);
		}
		return $result;
	}

    /**
     * This method tests, if active user has write permissions
     * @return bool
     */
	function activeUserHasWriteAccess(){
		$appname = 'schulmanager';
		$config = Api\Config::read($appname);

		$access_accounts = $config['sm_cal_access_accounts'];
		$access_groups = $config['sm_cal_access_groups'];

		if(in_array($this->user['account_id'], $access_accounts)){
			return TRUE;
		}

		foreach($access_groups as $groupid){
			if(array_key_exists($groupid, $this->user['memberships'])){
				return TRUE;
			}
		}
		if($this->activeUserIsCalCoordinator()){
			return TRUE;
		}

		return FALSE;
	}

    /**
     * This methos tests, if the event is writeable
     * @param $event
     * @return bool
     */
	function isReadonly($event){
		$readonly = true;

		$writeAccess = $this->activeUserHasWriteAccess();
		// event does not exixts and user has write access
		if(!isset($event['id']) && $writeAccess){
			$readonly = false;
		}
		// readonly for all users, who are not committed as SCHULMANAGER_CAL_USER
		// allusers who are set as 'cal_coordinator' can edit events!
		if(isset($event['owner'])){
			if($event['owner'] == $GLOBALS['egw_info']['user']['account_id']){
				$readonly = false; // system-user is owner has allways write access
			}
		}
		if($event['sm_user'] == $this->user['account_id'] && $writeAccess){
			$readonly = false;	// user is responsible for this event and has write access
		}

		if($this->activeUserIsCalCoordinator()){
			$readonly = false; // coordinator has allways access
		}
		return $readonly;
	}

	/**
	 * This method adds a single event to list
	 * @param type $type
	 * @param type $fach
	 * @param type $user
	 */
	function addEventToList($type, $fach, $user, &$msg){
		$session_copy = Api\Cache::getSession('schulmanager', 'cal_session_copy');

		$date = new DateTime('@'.$session_copy['startTS']);
		//$date->setTimezone(Api\DateTime::$server_timezone); // TODO
		//$date->setTimezone(new DateTimeZone('Europe/Berlin')); // TODO
		$klasse = $session_copy['klasse'];
		$klassengruppe = $session_copy['klassengruppe'];

		$content = array(
			'sm_user' => $user,
			'sm_type_options' => $type,
			'sm_fach_options' => $fach,
		);

		$calid = $this->writeEvent($content, null, $date, $klasse, $klassengruppe, $msg);
		return $calid;
	}

	/**
	 * This method deletes event if timestamp has not been updates allowed
	 * @param type $id
	 */
	function deleteEvent($idKey, &$msg){
		$success = false;
		$keyPrefix = 'schulmanager::';
		$calbo = new calendar_boupdate();
		$id = substr($idKey, strlen($keyPrefix), strlen($idKey)-1);
		$rows = Api\Cache::getSession('schulmanager', 'schulmanager_cal_eventlist');

		$event = $rows[$id -1];

		$eventDB = $calbo->read($event['id']);
		if(isset($eventDB) && $event['modified'] == $eventDB['modified']){
			// event hat not been updated
			if($this->isReadonly($event)){
				$msg = "Die haben keine Rechte um diesen Eintrag zu löschen!";
			}
			else{
				$success = $calbo->delete($event['id'], 0, true);
				if($success){
					unset($rows[$id -1]);
					$rows = Api\Cache::setSession('schulmanager', 'schulmanager_cal_eventlist', $rows);
					$msg = "Eintrag wurde entfernt!";
				}
				else{
					$msg = "Eintrag konnte nicht entfernt werden!";
				}
			}
		}
		else{
			$msg = 'Eintrag wurde nicht gespeichert. Der Eintrag wurde von '.$event[SCHULMANAGER_CAL_USER].' vor kurzem bearbeitet.';
		}
        return $success;
	}

	/**
	 * Saves events in calendar
	 * @param type $content
	 */
	function write($content, &$msg){
		$preserv_session = Api\Cache::getSession('schulmanager', 'cal_session_copy');

		$multiple = $preserv_session['multiple'];
		$id = $preserv_session['id'];
		$colid = $preserv_session['colid'];

		$date = new DateTime(Api\Cache::getSession('schulmanager', 'cal_sel_month_date'));
		$addDays = $colid -1;
		$date->modify('+'.$addDays.' day');

		if($multiple){
			$rows = Api\Cache::getSession('schulmanager', 'schulmanager_cal_rows');
			foreach($rows as $key => $value){
				$klasse = $rows[$key]['kg']['klasse'];
				$klassengruppe = $rows[$key]['kg']['kennung'];
				$event = $rows[$key]['cal'][$colid];
				$id = $event['id'];

				if(!$this->isReadonly($event)){
					// only change editable events
					$calid = $this->writeEvent($content, $id, $date, $klasse, $klassengruppe, $msg);
				}
			}
		}
		else{
			$klasse = $preserv_session['klasse'];
			$klassengruppe = $preserv_session['klassengruppe'];
			$calid = $this->writeEvent($content, $id, $date, $klasse, $klassengruppe, $msg);
		}

		return $calid;
	}

	/**
	 * saves a single event, if there were not an other event before
	 * @param type $content
	 * @param type $id
	 * @param type $date
	 * @param type $klasse
	 * @param type $klassengruppe
	 * @return type
	 */
	function writeEvent($content, $id, $date, $klasse, $klassengruppe, &$msg){
		$calbo = new calendar_boupdate();
		$preserv_session = Api\Cache::getSession('schulmanager', 'cal_session_copy');


		$start = $date->format('Y-m-d').' 0800';
		$end = $date->format('Y-m-d').' 0900';
		$owner =  $this->config_data['schulmanager_user'];
		$modifierID = $GLOBALS['egw_info']['user']['account_id'];
		//$modifierName = $GLOBALS['egw_info']['user']['account_lid'];
		$userID = $content['sm_user'];//$GLOBALS['egw_info']['user']['account_id'];
		$typeID = $content['sm_type_options'];
		//$typeName = $preserv_session['select_type_options'][$typeID];
		$fachID = $content['sm_fach_options'];
		$fachName = $preserv_session['select_fach_options'][$fachID];

		$modified = $preserv_session['modified'];

		$sm_index_key = $content['sm_index'];
		$sm_index = explode('_', $sm_index_key)[2];

		$description = $content['description'];

		if(!isset($userID)){
			$userID = $GLOBALS['egw_info']['user']['account_id']; // new event
		}

		if($typeID == 'block'){
			$fachName = 'X';
			$userID = null;
		}
		if($sm_index > 0){
			$title = $fachName.$sm_index;
		}
		else{
			$title = $fachName;
		}

		$event = array(
			'title' => $title,
			'id' => $id,
			'start' => $start,
			'end' => $end,
			'owner' => $owner,
			'description' => $description,
			'tzid' => Api\DateTime::$user_timezone->getName(),
			'cal_modifier' => $modifierID,
			'modified' => $modified,
			SCHULMANAGER_CAL => TRUE,
			SCHULMANAGER_CAL_KLASSE => $klasse,
			SCHULMANAGER_CAL_KLASSENGRUPPE => $klassengruppe,
			SCHULMANAGER_CAL_TYPE => $typeID, //array_search($type, self::$cal_item_options),
			SCHULMANAGER_CAL_FACH => $fachName,
			SCHULMANAGER_CAL_INDEX => $sm_index);

		if(isset($userID)){
			$event[SCHULMANAGER_CAL_USER] = $userID; // no user if blocked!
		}

		if(!$this->checkEventModified($event, $klasse, $klassengruppe, $msg)){
			return false;
		}

		unset($event['modified']);
        // add participant
        $this->createParticipantsInfo($content, $event);
        $calid = $calbo->update($event, true, true, true); // true for ignore_conflicts, update modifier, ignore acl
		if($calid > 0){
			$msg = "Eintrag wurde hinzugefügt!";
		}
		else{
			$msg = "Eintrag konnte nicht hinzugefügt werden!";
		}
        return $calid;
	}

	/**
	 * Check, if an event for $klasse, $klassengruppe at $date has been modified or added and one event is a blocking event,
	 * If two events collides, return false.
	 * @param type $content
	 * @param type $id
	 * @param type $date
	 * @param type $klasse
	 * @param type $klassengruppe
	 * @param type $msg
	 */
	function checkEventModified($newEvent, $klasse, $klassengruppe, &$msg){
		$calbo = new calendar_boupdate();
		$eventDB = $calbo->read($newEvent['id']);
		// 1. Check: same event has been updated
		if(!empty($eventDB) && $newEvent['modified'] != $eventDB['modified']){
			$msg = 'Eintrag wurde nicht gespeichert. Der Eintrag wurde vor Kurzem bearbeitet.';
			return false;
		}

		$sessionCopy = Api\Cache::getSession('schulmanager', 'cal_session_copy');
		if(isset($sessionCopy)){
			$events = $this->searchCalDatesInRange($sessionCopy['startTS'], $sessionCopy['endTS'], $klasse, $klassengruppe);

			/*if(isset($events) && count($events) > 0 && $this->isBlockingEventType($newEvent[SCHULMANAGER_CAL_TYPE])){
				$msg = 'Eintrag wurde nicht gespeichert. Ein anderer Eintrag wurde vor Kurzem bearbeitet und kollidiert mit dem neuen Eintrag!';
				return false;
			}*/
			// 2. now there is one ore some more events and this event to save is a blocking event
			// 3. Check for klasse and klassengruppe on this date an event has been created and one event is a blocking event
			foreach($events as $key => $value){
				if($value['id'] != $newEvent['id'] &&
						($this->isBlockingEvent($value) || $this->isBlockingEventType($newEvent[SCHULMANAGER_CAL_TYPE]))){
					$msg = 'Eintrag wurde nicht gespeichert. Es wurde eine Kollision mit einem weitern Eintrag festgestellt!';
					return false;
				}
			}
		}
		return true;
	}

    /**
     * Delete event
     * @param $content
     * @return bool
     */
	function deleteItems($content){
		$calbo = new calendar_boupdate();
		$preserv_session = Api\Cache::getSession('schulmanager', 'cal_session_copy');
		$multiple = $preserv_session['multiple'];

		if($multiple){
			$colid = $preserv_session['colid'];
			$rows = Api\Cache::getSession('schulmanager', 'schulmanager_cal_rows');
			foreach($rows as $key => $value){
				$event = $rows[$key]['cal'][$colid];
				$id = $event['id'];

				if(!$this->isReadonly($event)){
					// only change editable events
					$calid = $calbo->delete($id, 0, true);
				}
			}
		}
		else{
			$id = $preserv_session['id'];
			$calid = $calbo->delete($id, 0, true);
		}
        return $calid;
	}

	/**
     * Complements the array $eventarray with the keys 'participants' and
     * 'participant_types'
     * participants = array(12=>'ACHAIR', r32=>'A') // 1. UserID 12, 2. ResourceID 32
     * participant_types = array('u'=>array(12 => 'ACHAIR'), 'r'=> array(32=>'A'))
     * @param type $content
     * @param type $eventarray
     */
    function createParticipantsInfo($content, &$eventarray){
        // TODO
        /*$uid = $content['stunde']['uid'];
        $ruid = $this->getResourceID($content['stunde']['room']);
        $klassenIDs = str_getcsv($content['stunde']['klasse']);
        $klassenUserIDs = array();
        for($i = 0; $i < count($klassenIDs); $i++){
            $klassenUserIDs[] = $this->reorganizer->getKlassenUserID($klassenIDs[$i]);
        }
        $participantIDs = array();
        // Teilnehmer
        $participantIDs[$uid] = 'ACHAIR';
        for($i = 0; $i < count($klassenUserIDs); $i++){
            $participantIDs[$klassenUserIDs[$i]] = 'ACHAIR';
        }
        if(isset($ruid)){
            // Präsenz und Sprechstunde haben keinen Raum!
            $participantIDs['r'.$ruid] = 'A';
        }
        $eventarray['participants'] = $participantIDs;
        // Teilnehmerrollen
        $participantIDs_types = array();
        $participantIDs_types['u'] = array($uid => 'ACHAIR');
        for($i = 0; $i < count($klassenUserIDs); $i++){
            $participantIDs_types['u'] = array($uid => 'ACHAIR');
        }
        if(isset($ruid)){
            $participantIDs_types['r'] = array($ruid => 'A');
        }
        $eventarray['participant_types'] = $participantIDs_types;
		 */
    }

	/**
	 * Resets all 31 days
	 * @param type $rows
	 */
	function resetCalDates(&$rows){
		$dateSelected = new DateTime(Api\Cache::getSession('schulmanager', 'cal_sel_month_date'));
		$dateSelected->modify('first day of this month');

		foreach($rows as $key => $klasse){
			$rows[$key]['cal'] = array();
			for($i = 1; $i <= 31; $i++){
				$firstDayInMonth = clone $dateSelected;
				$rows[$key]['cal'][$i]['teaser'] = '';
				$rows[$key]['cal'][$i]['name'] = '';
				$rows[$key]['cal'][$i]['desc'] = '';
				if($this->getWorkDay($i, $firstDayInMonth)){
					$rows[$key]['cal'][$i]['class'] = 'sm_cal_mo_it';
				}
				else{
					$rows[$key]['cal'][$i]['class'] = 'sm_cal_hidden';
				}
			}
		}
	}

	/**
	 * Parse parameter like 0[cal][1][name]; 0 => klasse, represented by first row; 1 => first day of selected month;
	 * @param type $senderID
	 * @param type $rowID
	 * @param type $colID
	 */
	public function parseSenderID($senderID, &$rowID, &$colID, $multiple = false){
		if($multiple){
			$rowID = -1;
			$exploded = explode("_", $senderID);
			$colID = $exploded[1];
		}
		else{
			$ready = str_replace(array("][", "["), "#", $senderID);
			$exploded = explode("#", $ready);

			$rowID = $exploded[0];
			$colID = $exploded[2];
		}
	}

    /**
     * This method returns the date of the selected column
     * @param $colID
     * @return DateTime
     * @throws Exception
     */
	public function getDateByColID($colID){
		$dateSelected = new DateTime(Api\Cache::getSession('schulmanager', 'cal_sel_month_date'));
		$addDays = $colID -1;
		$dateSelected->modify('+'.$addDays.' day');

		return $dateSelected;

	}

    /**
     * This method returns the classgroup of the selected row
     * @param $rowID
     * @return mixed
     */
	public function getKlasseByRowID($rowID){
		$rows = Api\Cache::getSession('schulmanager', 'schulmanager_cal_rows');
		return $rows[$rowID]['kg']['klasse'];
	}

    /**
     * This method returns the classgroup by rowID
     * @param $rowID
     * @return mixed
     */
	public function getKlassenGruppeByRowID($rowID){
		$rows = Api\Cache::getSession('schulmanager', 'schulmanager_cal_rows');
		return $rows[$rowID]['kg']['kennung'];
	}

    /**
     * This method returns the classgroup asv ID by rowID
     * @param $rowID
     * @return mixed
     */
	public function getKlassenGruppeASVIDByRowID($rowID){
		$rows = Api\Cache::getSession('schulmanager', 'schulmanager_cal_rows');
		return $rows[$rowID]['kg']['kgid'];
	}

    /**
     * This method returns the event ID by column and row
     * @param $colID
     * @param $rowID
     * @return mixed
     */
	public function getCalendarEventID($colID, $rowID){
		$rows = Api\Cache::getSession('schulmanager', 'schulmanager_cal_rows');
		return $rows[$rowID]['cal'][$colID]['id'];
	}

    /**
     * This method returns the event type by column and row
     * @param $colID
     * @param $rowID
     * @return mixed
     */
	public function getCalendarEventType($colID, $rowID){
		$rows = Api\Cache::getSession('schulmanager', 'schulmanager_cal_rows');
		return $rows[$rowID]['cal'][$colID][SCHULMANAGER_CAL_TYPE];
	}

	/**
	 * checks, if dayInMonth in month is a workday
	 * @param int $dayInMonth
	 * @param DateTime $firstDayOfMonth
	 */
	function getWorkDay(int $dayInMonth, DateTime $firstDayInMonth) {
		setlocale(LC_TIME, "de_DE.UTF-8");
		$diffDays = $dayInMonth - 1;
		$firstDayInMonth->add(new DateInterval('P'.$diffDays.'D'));

		return strftime('%u', $firstDayInMonth->getTimestamp()) <= 5;
	}

	/**
	 * This method collects calendar event in given range
	 * @param type $calItem calender item
	 * @param type $start start date of view
	 * @param type $end date of view
	 * @return array
	 */
	function getCalViewData($calItem, $rangeStart, $rangeEnd,array &$calItems){
		$result = 0;
		$calStart = $calItem['start'];
		$calEnd = $calItem['end'];

		$firstIndex = date("j", $calStart);
		$lastIndex = date("j", $calEnd);
		$this->getColIndizes($calStart, $calEnd, $rangeStart, $rangeEnd, $firstIndex, $lastIndex);

		if($firstIndex != -1 && $lastIndex != -1){
			for($i = $firstIndex; $i <= $lastIndex; $i++){
				if(isset($calItems[$i]['teaser']) && !empty($calItems[$i]['teaser']) > 0){
					$calItems[$i]['teaser'] = $calItems[$i]['teaser'].'/'.$this->getEventTitle($calItem);
				}
				else{
					$calItems[$i]['teaser'] = $this->getEventTitle($calItem);
				}
				$this->modifyBeforeSend2Client($calItems[$i], $calItem);
				$result++;
			}
		}
		return $result;
	}

	/**
	 * modifies event titel for the display in the teaser
	 */
	function getEventTitle($event){
		if($event[SCHULMANAGER_CAL_TYPE] != 'sa'){
			return strtolower($event['title']);
		}
		return $event['title'];
	}

    /**
     * Do some modifications before sending to client
     * @param array $itemDesc
     * @param $itemSrc
     */
	function modifyBeforeSend2Client(array &$itemDesc, $itemSrc){
		$itemDesc['row_id'] = $itemSrc['row_id'];
		$itemDesc['id'] = $itemSrc['id'];
		$itemDesc['name'] = $itemSrc['title'];
		$itemDesc['owner'] = $itemSrc['owner'];
		$itemDesc['modified'] = $itemSrc['modified'];
		$itemDesc['description'] = $itemSrc['description'];
		if(isset($itemSrc['modified'])){
			$itemDesc['sm_modified'] =  strftime('%e.%b.%Y %H:%M', $itemSrc['modified']);
		}

		$account_lid = Api\Accounts::id2name($itemSrc[SCHULMANAGER_CAL_USER]);
		$itemDesc['sm_user'] = $itemSrc[SCHULMANAGER_CAL_USER];
		$itemDesc['sm_user_name'] = $account_lid;
		$itemDesc['sm_fach'] = $itemSrc[SCHULMANAGER_CAL_FACH];
		$itemDesc['sm_type'] = $itemSrc[SCHULMANAGER_CAL_TYPE];
		$itemDesc['sm_type_name'] = self::$cal_item_options[$itemSrc[SCHULMANAGER_CAL_TYPE]];
		$itemDesc['sm_index'] = $itemSrc[SCHULMANAGER_CAL_INDEX];

		if($this->isReadonly($itemDesc)){
			$itemDesc['cssmode'] = 'sm_cal_readonly';
		}
		else{
			$itemDesc['cssmode'] = 'sm_cal_editable';
		}
		if($itemDesc['sm_type'] == 'block'){
			$itemDesc['cssmode'] .= ' sm_cal_block';
		}
		else if($itemDesc['sm_type'] == 'sa'){
			$itemDesc['cssmode'] .= ' sm_cal_glnw';
		}
		else if($itemDesc['sm_type'] == 'ka' || $itemDesc['sm_type'] == 'ex' || $itemDesc['sm_type'] == 'flt'){
			$itemDesc['cssmode'] .= ' sm_cal_klnw';
		}

		// resposible user
		if($itemSrc[SCHULMANAGER_CAL_USER] == $GLOBALS['egw_info']['user']['account_id']){
			$itemDesc['cssmode'] .= ' sm_cal_user';
		}
	}

	/**
	 * defines column indices for display
	 * @param type $start
	 * @param type $end
	 * @param type $rangeStart
	 * @param type $rangeEnd
	 * @param type $firstIndex
	 * @param type $lastIndex
	 */
	function getColIndizes(int $start, int $end, int $rangeStart, int $rangeEnd, &$firstIndex, &$lastIndex){
		// set first index
		if($start <= $rangeStart && $end >= $rangeStart){
			$firstIndex = 1; // starts before rangestart, ends after rangestart
		}
		elseif ($start >= $rangeStart && $start <= $rangeEnd) {
            $firstIndex = ceil(($start - $rangeStart) / 86400); // sec per day, 60*60*24, starts inside
		}
		else{
			$firstIndex = -1;
		}
		// set last index
		if($end >= $rangeStart && $end <= $rangeEnd){
            $lastIndex = ceil(($end - $rangeStart) / 86400);
		}
		elseif ($start <= $rangeEnd && $end >= $rangeEnd) {
			$lastIndex = 31;		// starts before reangeEnd, ends after rangeEnd
		}
		else{
			$lastIndex = -1;
		}
	}



	/**
	 * Return the row index fro display calender item
	 * @param type $klasse
	 * @param type $rows
	 * @return type
	 */
	function getRowIndex($klasse, $klassengruppe, $rows){
		$result = -1;
		foreach($rows as $key => $value){
			if($value['kg']['klasse'] == $klasse && $value['kg']['kennung'] == $klassengruppe){
				$result = $key;
			}
		}
		return $result;
	}

    /**
     * Saves config
     * @param $content
     * @throws Api\Exception\WrongParameter
     */
	function saveAccessConfig($content){
		if ($content['button']['save'] || $content['button']['apply'])
		{
			$accessAccounts = $content['sm_cal_access_accounts'];
			$accessGroups = $content['sm_cal_access_groups'];

			Api\Config::save_value('sm_cal_access_accounts', $accessAccounts, 'schulmanager');
			Api\Config::save_value('sm_cal_access_groups', $accessGroups, 'schulmanager');

			if(!$content['apply'])
			{
				Api\Framework::message(lang('Configuration saved.'), 'success');
			}
		}

		if ($content['apply'])
		{
			Api\Framework::message(lang('Configuration saved.'), 'success');
		}
	}

    /**
     * Loads config
     * @param $content
     */
	function loadAccessConfig(&$content){
		$config = Api\Config::read('schulmanager');

		$content['sm_cal_access_accounts'] = $config['sm_cal_access_accounts'];
		$content['sm_cal_access_groups'] = $config['sm_cal_access_groups'];
	}

    /**
     * Load events
     * @param $query_in
     * @param $rows
     * @param int $filter
     * @return int
     */
	function searchCalEventList(&$query_in,&$rows,$filter = 0)
	{
		$result = 0;
		$sessionCopy = Api\Cache::getSession('schulmanager', 'cal_session_copy');
		if(isset($sessionCopy)){
			$events = $this->searchCalDatesInRange($sessionCopy['startTS'], $sessionCopy['endTS'], $sessionCopy['klasse'], $sessionCopy['klassengruppe']);
			$result = sizeof($events);
			$rows = $events;
		}
		return $result;
	}

    /**
     * get klassengruppen, f.e. calendar
     * @param $query_in
     * @param $rows
     * @param int $showAllGroups
     * @return int
     */
	function getCalKlassenGruppen(&$query_in,&$rows,$showAllGroups = 0)
	{
		$klassen = array();
        $lehrer_account_so = new schulmanager_lehrer_account_so();
        $lehrerStammIDs = $lehrer_account_so->loadLehrerStammIDs($GLOBALS['egw_info']['user']['account_id']);

		$this->so_lehrer->getKlassenGruppen($lehrerStammIDs, $klassen, $showAllGroups, $query_in['search']);

		foreach($klassen as $key => $value){
			$rows[$key] = array(
				'kg' => array(
					'asvid' => $value['asvid'],
					'klasse' => $value['name'],
					'kgid' => $value['kgid'],
					'kennung' => $value['kennung'],
					),
			);
		}

		return sizeof($rows);
	}

	/**
	 * This method returns a list of subjects of class group
	 * @param type $kgasvid
	 * @return typereturn alls Faehcer zur Klassengruppe
	 */
	function getUnterrichtByKlassengruppe($kgasvid){
		$unterricht = null;
		if(isset($kgasvid)){
			$unterricht = $this->so_lehrer->loadUnterrichtByKlassengruppe($kgasvid);
		}
		else{
			$rows = Api\Cache::getSession('schulmanager', 'schulmanager_cal_rows');
			$i = 0;
			foreach($rows as $rowID => $value){
				$kgasvid = $value['kg']['kgid'];
				if ($i == 0){
					$unterricht = $this->so_lehrer->loadUnterrichtByKlassengruppe($kgasvid);
				}
				else{
					$unterrichtNeu = $this->so_lehrer->loadUnterrichtByKlassengruppe($kgasvid);
					$unterricht = array_intersect($unterricht, $unterrichtNeu);
				}
				$i++;
			}
		}
		return $unterricht;
	}
}