<?php

/**
 * Schulmanager Cal - User interface
 *
 * @link http://www.egroupware.org
 * @package schulmanager
 * @author Axel Wild <info-AT-wild-solutions.de>
 * @copyright (c) 2022 by Axel Wild <info-AT-wild-solutions.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */
use EGroupware\Api;
use EGroupware\Api\Egw;
use EGroupware\Api\Framework;
use EGroupware\Api\Etemplate;

/**
 * This class is the UI-layer (user interface) of InfoLog
 */
class schulmanager_cal_ui
{
	/**
	 * instance of the bo-class
	 *
	 * @var schulmanager_cal_bo
	 */
	var $bo;

    /**
     * public functions
     * @var bool[]
     */
	var $public_functions = array(
		'index'		  => True,
		'editevent'		  => True,
		'help'		=> True,
		'access'	=> True,
	);
	/**
	 * reference to the infolog preferences of the user
	 *
	 * @var array
	 */
	var $prefs;
	/**
	 * instance of the bo-class
	 *
	 * @var schulmanager_bo
	 */
	//var $bo;

	/**
	 * instance of the bo-class
	 *
	 * @var schueler_bo
	 */

	/**
	 * Constructor
	 *
	 * @return notenmanager_ui
	 */
	function __construct(Etemplate $etemplate = null)
	{
		$this->bo = new schulmanager_cal_bo();
	}

	/**
	 * Context menu
	 *
	 * @return array
	 */
	public static function get_eventListActions(Array $content)
	{
		$actions = array(
			'deleteEvent' => array(
				'caption' => lang('Löschen'),
				//'group' => $group,
				'allowOnMultiple' => true,
				'icon' => 'delete',
				'postSubmit' => true,
				//'url' => 'menuaction=schulmanager.schulmanager_cal_ui.editevent&action=delevent&id=$row_id',
				'onExecute' => 'javaScript:app.schulmanager.calDeleteEvent',
			),
			'showAddEvent' => array(
				'caption' => lang('Einfügen'),
				'no_lang' => true,
				//'icon' => 'add',
				'onExecute' => 'javaScript:app.schulmanager.calShowAddEvent',
			)
		);
		return $actions;
	}

	/**
	 * returns nextmatch eventlist content
	 *
	 * @param array &$query
	 * @param array &$rows returned rows/cups
	 * @param array &$readonlys eg. to disable buttons based on Acl
	 * @param boolean $id_only if true only return (via $rows) an array of contact-ids, dont save state to session
	 * @return int total number of contacts matching the selection
	 */
	function get_event_list_rows(&$query_in,&$rows,&$readonlys = null,$id_only=false)
	{
		$total = $this->bo->searchCalEventList($query_in, $rows, $query_in['filter']);
		Api\Cache::setSession('schulmanager', 'schulmanager_cal_eventlist', $rows);
		return $total;
	}

	/**
	 * query
	 *
	 * @param array &$query
	 * @param array &$rows returned rows/cups
	 * @param array &$readonlys eg. to disable buttons based on Acl
	 * @param boolean $id_only if true only return (via $rows) an array of contact-ids, dont save state to session
	 * @return int total number of contacts matching the selection
	 */
	function get_rows(&$query_in,&$rows,&$readonlys = null,$id_only=false)
	{
		if(isset($query_in['filter'])){
			Api\Cache::setSession('schulmanager', 'cal_filter', $query_in['filter']);
		}
		else{
			// edit records
			$query_in['filter'] = Api\Cache::getSession('schulmanager', 'cal_filter');
		}

		if(isset($query_in['search'])){
			Api\Cache::setSession('schulmanager', 'cal_search', $query_in['search']);
		}
		else{
			// edit records
			$query_in['search'] = Api\Cache::getSession('schulmanager', 'cal_search');
		}

		$total = $this->bo->getCalKlassenGruppen($query_in, $rows, $query_in['filter']);
        $this->bo->searchCalDates($query_in, $rows);
		Api\Cache::setSession('schulmanager', 'schulmanager_cal_rows', $rows);
		return $total;
	}

    /**
     * List cal events
     * @param int $content
     * @param string $msg
     * @return Etemplate\Request|string
     * @throws Api\Exception\AssertionFailed
     */
	public function index($content = 0,$msg='')
	{
		$dateSelected = new DateTime();
		$dateSelected->modify('first day of this month');

		$sel_options = array();

		$etpl = new Etemplate('schulmanager.calendar.index');

		if (is_array($content))
		{
			$button = @key($content['nm']['button']);

			unset($content['nm']['button']);
			if ($button)
			{
				setlocale(LC_TIME, "de_DE.UTF-8");
				// Key/val in session speichern
				$dateSelected = new DateTime(Api\Cache::getSession('schulmanager', 'cal_sel_month_date'));

				if ($button == 'previous')
				{
					$dateSelected->modify('-1 month');
				}
				elseif($button == 'forward'){
					$dateSelected->modify('+1 month');
				}
				elseif($button == 'help'){
					Framework::popup(Egw::link('/index.php',array('menuaction' => 'schulmanager.schulmanager_cal_ui.help','ajax' => 'true')));
					//return;
				}
				elseif($button == 'access'){
					Framework::popup(Egw::link('/index.php',array('menuaction' => 'schulmanager.schulmanager_cal_ui.access','ajax' => 'true')));
					//return;
				}
				Api\Cache::setSession('schulmanager', 'cal_sel_month_date', $dateSelected->format('Y-m-d'));
			}
		}

		$content = array(
			'nm' => Api\Cache::getSession('schulmanager', 'index'),
			'msg' => $msg,
		);

		if (!is_array($content['nm']))
		{
			$search = Api\Cache::getSession('schulmanager', 'cal_search');

			$content = array();
			$content['msg'] = $msg ? $msg : $_GET['msg'];

			$content['nm']['get_rows']		= 'schulmanager.schulmanager_cal_ui.get_rows';
			$content['nm']['no_filter'] 	= False;
			$content['nm']['filter_no_lang'] = true;
			$content['nm']['no_cat']	= true;
			$content['nm']['no_search']	= true;
			$content['nm']['no_filter2']	= true;
			$content['nm']['bottom_too']	= true;
			$content['nm']['order']		= 'nm_id';
			$content['nm']['sort']		= 'ASC';
			$content['nm']['search']		= $search;
			$content['nm']['row_id']	= 'nm_id';
			$content['nm']['favorites'] = false;
			$content['nm']['no_columnselection'] = false;
			$content['nm']['options-filter'] = schulmanager_cal_bo::$filter_options;

			setlocale(LC_TIME, "de_DE.UTF-8");
			$content['nm']['sm_coordinator'] = $this->bo->activeUserIsCalCoordinator();

			$content['cal_sel_month'] = strftime('%B %Y', $dateSelected->getTimestamp());
		}

		if(!$this->bo->activeUserHasWriteAccess()){
			$content['write_access_msg'] = 'Schreibgeschützt!';
		}
		else{
			$content['write_access_msg'] = '';
		}

		$readonlys = array(
			'button[previous]'     => false,
			'button[access]'     => false,
		);

		Api\Cache::setSession('schulmanager', 'cal_sel_month_date', $dateSelected->format('Y-m-d'));

		$preserv = $sel_options;
		return $etpl->exec('schulmanager.schulmanager_cal_ui.index',$content,$sel_options,$readonlys,$preserv);
	}

    /**
     * help function
     * @param int $content
     * @param string $msg
     * @return Etemplate\Request|string
     * @throws Api\Exception\AssertionFailed
     */
	public function help($content = 0,$msg='')
	{
		$sel_options = array();
		$content = array();

		$etpl = new Etemplate('schulmanager.calendar.help');
		$readonlys = array();

		$preserv = $sel_options;
		return $etpl->exec('schulmanager.schulmanager_cal_ui.help',$content,$sel_options,$readonlys,$preserv);
	}

	/**
	 * configurates special access rules for users or group
	 * @param array $content
	 * @param type $msg
	 * @return type
	 */
	public function access($content = 0,$msg='')
	{
		$sel_options = array();
		$etpl = new Etemplate('schulmanager.calendar.access');

		if (is_array($content))
		{
			$button = @key($content['nm']['button']);

			//unset($content['button']);
			if ($button)
			{
				if ($button == 'save' || $button == 'apply')
				{
					$this->bo->saveAccessConfig($content);
				}
				if ($button == 'save' || $button == 'cancel')
				{
					Api\Cache::unsetSession('schulmanager', 'cal_session_copy');
					Framework::window_close();
				}
			}
			unset($content['button']);
		}

		$content = array();
		$this->bo->loadAccessConfig($content);
		$readonlys = array(
			'button[save]'     => false,
			'button[apply]'     => false,
			'button[cancel]'     => false,
		);

		$preserv = $sel_options;
		return $etpl->exec('schulmanager.schulmanager_cal_ui.access',$content,$sel_options,$readonlys,$preserv);
	}

    /**
     * Ajax call - edit event
     * @param $_senderId
     * @param false $multiple
     * @throws Api\Json\Exception
     */
	function ajax_editCalEvent($_senderId, $multiple = false) {
		$result = array();

		Framework::popup(Egw::link('/index.php',array('menuaction' => 'schulmanager.schulmanager_cal_ui.editevent','ajax' => 'true','sender' => $_senderId, 'multiple' => $multiple)));
		Api\Json\Response::get()->data($result);
	}

    /**
     * Ajax call - delete event
     * @param $widgetId
     * @throws Api\Json\Exception
     */
	function ajax_deleteEvent($widgetId) {
		$result = array();
		$msg = '';
		if(!$this->bo->deleteEvent($widgetId, $msg)){
			$msg = $msg." Eintrag konnte nicht entfernt werden!";
		}
		$result['msg'] = $msg;
		Api\Json\Response::get()->data($result);
	}

    /**
     * Ajax call - add event to list
     * @param $type
     * @param $fach
     * @param $user
     * @throws Api\Json\Exception
     */
	function ajax_addEventToList($type, $fach, $user) {
		$result = array();
		if(!$this->bo->addEventToList($type, $fach, $user, $msg)){
			// event not saved
		}
		
		$result['msg'] = $msg;
		Api\Json\Response::get()->data($result);
	}

    /**
     * Edit event
     * @param int $content
     * @param string $msg
     * @return Etemplate\Request|string
     * @throws Api\Exception\AssertionFailed
     */
	public function editevent($content = 0,$msg='')
	{
		$senderID = null;
		$multiple = false;
		$id = null; // has to be null, if adding multiple events!
		$colID = -1;
		$rowID = -1;
		$klasse = 0;
		$klassengruppe = 0;
		$klassengruppeID = null; // has to be null, if adding multiple events!


		setlocale(LC_TIME, "de_DE.UTF-8");
		// get sender col and row
		if (isset($_GET['sender'])){
			$senderID = $_GET['sender'];
		}
		if (isset($_GET['multiple'])){
			$multiple = boolval($_GET['multiple']);
		}
		if(isset($senderID)){
			$this->bo->parseSenderID($senderID, $rowID, $colID, $multiple);
		}

		$date = $this->bo->getDateByColID($colID);
		$tag = $date->format('w');
		$dateFormatted = $this->bo->tage[$tag].$date->format(' d.m.Y');

		// TODO load events from DB, by date and klasse
		if($rowID >= 0 && $colID >= 0){
			$id = $this->bo->getCalendarEventID($colID, $rowID);
			$klasse = $this->bo->getKlasseByRowID($rowID);
			$klassengruppe = $this->bo->getKlassengruppeByRowID($rowID);
			$klassengruppeID = $this->bo->getKlassengruppeASVIDByRowID($rowID);
			$type = $this->bo->getCalendarEventType($colID, $rowID);
		}

		if (is_array($content))
		{
			$button = @key($content['button']);

			if ($button)
			{
				if ($button == 'save' || $button == 'apply')
				{
					$msg = '';
					if ($this->bo->write($content, $msg))
					{
						$msg = lang('Eintrag erfolgreich gespeichert!');
					}
					$content['msg'] = $msg;
				}
				elseif($button == 'delete'){
					$this->bo->deleteItems($content);
				}
				if ($button == 'save' || $button == 'cancel' || $button == 'delete' || $button == 'close')
				{
					Api\Cache::unsetSession('schulmanager', 'cal_session_copy');
					Framework::refresh_opener($msg, 'schulmanager');
					Framework::window_close();
				}
			}
		}

		$sel_options = array();
		$content = array();
		$sel_options['sm_type_options'] = schulmanager_cal_bo::$cal_item_options;
		// remove block, if user is no supervisor
		if(!$this->bo->activeUserIsCalCoordinator()){
			unset($sel_options['sm_type_options']['block']);
		}
		$sel_options['sm_type_options_list'] = $this->bo->getNonBlockingEventTypes();

		$sel_options['sm_index'] = schulmanager_cal_bo::$cal_index_options;

		$start = $date->getTimestamp();
		$date->modify('+1 day');
		$end = $date->getTimestamp();
		// new search!! maybe someone added an event?
		$events = $this->bo->searchCalDatesInRange($start, $end, $klasse, $klassengruppe);

		if(sizeof($events) == 0){
			$editEventList = false; // no event exists
		}
		elseif(array_key_exists($events[0]['sm_type'], $this->bo->getBlockingEventTypes())){
			$editEventList = false; // only single event is possible
		}
		else{
			$editEventList = true;
		}

		if(sizeof($events) <= 1){
			$event = $events[0];
		}

		$unterricht = $this->bo->getUnterrichtByKlassengruppe($klassengruppeID);
		foreach($unterricht as $key => $value){
			$sel_options['sm_fach_options'][$key] = $value['kurzform'];
			$sel_options['sm_fach_options_list'][$key] = $value['kurzform'];
		}

		$content['msg'] = $msg;
		$content['sm_date'] = $dateFormatted;
		$content['sm_klasse'] = $klasse;
		$content['sm_klassengruppe'] = $klassengruppe;
		$content['sm_col'] = $senderID;

		$content['sm_coordinator'] = $this->bo->activeUserIsCalCoordinator();

		$content['sm_type_options'] = $event['sm_type'];
		$content['sm_type_name'] = $sel_options['sm_type_options'][$event['sm_type']];

		$content['sm_fach_options'] = array_search($event['sm_fach'], $sel_options['sm_fach_options']);
		$content['sm_fach_name'] = $event['sm_fach'];

		$account_lid = Api\Accounts::id2name($event['sm_user']);
		$content['sm_user_name'] = $account_lid;
		$content['sm_user'] = $event['sm_user'];
		if($event['sm_index'] > 0){
			$content['sm_index_val'] = $event['sm_index'].'. ';
		}
		$content['sm_index'] = 'sm_index_'.$event['sm_index'];
		$content['description'] = $event['description'];

		if(!isset($content['sm_user'])){
			$content['sm_user'] = $GLOBALS['egw_info']['user']['account_id'];
		}
		$content['sm_activeuser'] = $GLOBALS['egw_info']['user']['account_lid'];
		$content['sm_activeuserID'] = $GLOBALS['egw_info']['user']['account_id'];

		$session_copy = array(
			'id' => $id,
			'colid' => $colID,
			'date' => $content['sm_date'],
			'startTS' => $start,
			'endTS' => $end,
			'klasse' => $klasse,
			'type' => $event['sm_type'],
			'klassengruppe' => $klassengruppe,
			'modified' => $event['modified'],
			'select_fach_options' => $sel_options['sm_fach_options'],
		);
		// edit single event or list
		if($editEventList){
			$content['editmode'] = 'list';

			$content['sm_type_options_list'] = $content['sm_type_options'];
			$content['sm_fach_options_list'] = $content['sm_fach_options'];


			$content['nm']['get_rows']		= 'schulmanager.schulmanager_cal_ui.get_event_list_rows';
			$content['nm']['actions'] = self::get_eventListActions($content);
			$content['nm']['no_filter'] 	= true;
			$content['nm']['filter_no_lang'] = true;
			$content['nm']['no_cat']	= true;
			$content['nm']['no_search']	= true;
			$content['nm']['no_filter2']	= true;
			$content['nm']['bottom_too']	= true;
			$content['nm']['order']		= 'id';
			$content['nm']['sort']		= 'ASC';
			$content['nm']['row_id']	= 'row_id';
			$content['nm']['favorites'] = false;
			$content['nm']['no_columnselection'] = false;

			$readonlys = array(
				'button[close]'     => false,
			);

			// to avoid doing injection
			$session_copy['select_type_options'] = $sel_options['sm_type_options_list'];
		}
		else{
			// create new event or edit single event
			$content['editmode'] = 'single';
			$readonly = $this->bo->isReadonly($event);
			$content['sm_readonly'] = $readonly;

			if(isset($event['modified'])){
				$content['sm_modified'] =  strftime('%e.%b.%Y %H:%M', $event['modified']);
			}

			$readonlys = array(
				'button[save]'     => false,
				'button[apply]'    => false,
				'button[cancel]'   => false,
				'button[delete]'    => false,
			);

			$session_copy['select_type_options'] = $sel_options['sm_type_options'];
			$session_copy['select_fach_options'] = $sel_options['sm_fach_options'];
			$session_copy['multiple'] = $multiple;
		}
        $id = $this->bo->getCalendarEventID($colID, $rowID);
        $klasse = $this->bo->getKlasseByRowID($rowID);
        $klassengruppe = $this->bo->getKlassengruppeByRowID($rowID);
        $type = $this->bo->getCalendarEventType($colID, $rowID);

		Api\Cache::setSession('schulmanager', 'cal_session_copy', $session_copy);
        $preserv = array();
		$etpl = new Etemplate('schulmanager.calendar.editevent');
		return $etpl->exec('schulmanager.schulmanager_cal_ui.editevent',$content,$sel_options,$readonlys,$preserv);
	}

    /**
     * Return weekdays of amonth
     * @param $result
     * @throws Exception
     */
	function getWeekDays(&$result) {
		setlocale(LC_TIME, "de_DE.UTF-8");
		$dateSelected = new DateTime(Api\Cache::getSession('schulmanager', 'cal_sel_month_date'));
		$dateSelected->modify('first day of this month');

		for($i = 1; $i < 32; $i++){
			$ts = $dateSelected->getTimestamp();
			if($dateSelected->format('d') == 1){
				$result['nm_header']['hday_'.$i]['nr'] = strftime('%e.%b', $ts);
			}
			else{
				$result['nm_header']['hday_'.$i]['nr'] = strftime('%e.', $ts);
			}
			$result['nm_header']['hday_'.$i]['name'] = strftime('%a', $dateSelected->getTimestamp());
			if(strftime('%u', $dateSelected->getTimestamp()) > 5){
				$result['nm_header']['hday_'.$i]['class'] = 'sm_cal_saso';
			}
			else{
				$result['nm_header']['hday_'.$i]['class'] = 'sm_cal_mofr';
			}
			$dateSelected->modify('+1 day');
		}
	}

    /**
     * Ajax call - retrun weekdays of a month
     * @param $result
     * @throws Api\Json\Exception
     */
	function ajax_getWeekDays($result) {
		$result = array();
		$this->getWeekDays($result);
                Api\Cache::setSession('schulmanager', 'cal_weekdays', $result);
		Api\Json\Response::get()->data($result);
	}

	/**
	 * Context menu
	 *
	 * @return array
	 */
	public function get_actions()
	{
		$actions = array(
		);
		return $actions;
	}

	/**
	 * apply an action to multiple timesheets
	 *
	 * @param string/int $action 'status_to',set status to timeshhets
	 * @param array $checked timesheet id's to use if !$use_all
	 * @param boolean $use_all if true use all timesheets of the current selection (in the session)
	 * @param int &$success number of succeded actions
	 * @param int &$failed number of failed actions (not enought permissions)
	 * @param string &$action_msg translated verb for the actions, to be used in a message like %1 timesheets 'deleted'
	 * @param string/array $session_name 'index' or 'email', or array with session-data depending if we are in the main list or the popup
	 * @return boolean true if all actions succeded, false otherwise
	 */
	function action($action,$checked,$use_all,&$success,&$failed,&$action_msg,$session_name,&$msg)
	{
		$success = $failed = 0;
		return "Unknown action '$action'!";
	}
}