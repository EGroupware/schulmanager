<?php

/**
 * EGroupware Schulmanager - susbtitution of a teacher - ui object
 *
 * @link http://www.egroupware.org
 * @package schulmanager
 * @author Axel Wild <info-AT-wild-solutions.de>
 * @copyright (c) 2022 by info-AT-wild-solutions.de
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

use EGroupware\Api;
use EGroupware\Api\Etemplate;

/**
 * This class is the UI-layer (user interface)
 */
class schulmanager_substitution_ui
{
	var $public_functions = array(
		'index'       => True,
        'accounts'       => True,
    );
	/**
	 * preference
	 * @var array
	 */
	var $prefs;
	/**
	 * instance of the bo-class
	 * @var schulmanager_substitution_bo
	 */
	var $bo;

	/**
	 * @var schulmanager_lehrer_so
	 */
	var $schulmanager_lehrer_so;

    /**
     * @var unterricht_so
     */
    var $unterricht_so;

	/**
	 * Constructor
	 * @return
	 */
	function __construct(Etemplate $etemplate = null)
	{		
		$this->bo = new schulmanager_substitution_bo();
		$this->schulmanager_lehrer_so = new schulmanager_lehrer_so();
        $this->unterricht_so = new schulmanager_unterricht_so();
	}

	/**
	 * Get actions / context menu for index
	 * Changes here, require to log out, as $content['nm'] get stored in session!
	 * @return array see nextmatch_widget::egw_actions()
	 */
	public static function get_actions(array $content)
	{
		$actions = array(
			'delete' => array(
				'caption' => 'Delete',						   
			),			
		);
		return $actions;
	}

    public static function get_actions_account(array $content)
    {
        $actions = array(
            'edit' => array(
                'caption' => 'Bearbeiten',
                'group' => $group = 1,
                'allowOnMultiple' => false,
                'icon' => 'edit',
                'postSubmit' => true,
                'shortcut' => array('ctrl' => true, 'shift' => true, 'keyCode' => 78, 'caption' => 'Ctrl + Shift + N'),
                'onExecute' => 'javaScript:app.schulmanager.onTeacherAccountLinkEdit',
            ),
            'reset' => array(
                'caption' => 'Zurücksetzen',
                'group' => $group = 1,
                'allowOnMultiple' => true,
                'icon' => 'reset',
                'postSubmit' => true,
                'shortcut' => array('ctrl' => true, 'shift' => true, 'keyCode' => 78, 'caption' => 'Ctrl + Shift + N'),
                'onExecute' => 'javaScript:app.schulmanager.onTeacherAccountLinkReset',
            ),
        );
        return $actions;
    }

	/**
	 * apply an action
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
		switch($action)
		{
			case 'delete':
			    $action_msg = lang('deleted');
			    $promoted_accessories = 0;
			    $rows = Api\Cache::getSession('schulmanager', 'substitution_rows');
			    
			    foreach($checked as $n => &$id)
			    {
			        $ret =  $this->bo->delete($rows[$id]);
			         			        
			         if ($ret)
			         {
			             $success++;
			         }
			         else
			         {
                         // TODO
			             $msg = "error \n";
			             $failed++;
			         }
			    }
				break;
		}
		return $failed == 0;
	}

	/**
	 * List Schulmanager entries
	 * @param array $content
	 * @param string $msg
	 */
	function index(array $content = null,$msg='')
	{
		$etpl = new Etemplate('schulmanager.substitution');

		if ($_GET['msg']) $msg = $_GET['msg'];

		$sel_options = array();
		$preserv = array();

		$filter = Api\Cache::getSession('schulmanager', 'filter');
		if(empty($filter)){
			$filter = 0;
		}

		if (is_array($content)) {
            if (is_array($content['button'])) {
                $button = @key($content['button']);
                unset($content['button']);
                if ($button) {
                    if ($button == 'add') {
                        $this->bo->add($content['add_kennung'], $content['add_kennung_orig'], $content['add_lesson_list']);
                    }
                }
            }
		        
			// action
		    if ($content['nm']['action'])
		    {
		        if (!count($content['nm']['selected']) && !$content['nm']['select_all'])
		        {
		            $msg = lang('You need to select some entries first!');
		        }
		        else
		        {
		            if ($this->action($content['nm']['action'],$content['nm']['selected'],$content['nm']['select_all'],
		                $success,$failed,$action_msg,'substitution_index_nm',$msg))
		            {
		                $msg .= lang('%1 Vertretung %2',$success,$action_msg);
		            }
		            elseif(empty($msg))
		            {
		                $msg .= lang('%1 Vertretung(en) %2, %3 failed because of insufficent rights !!!',$success,$action_msg,$failed);
		            }
		            else
		            {
		                $msg .= lang('%1 Vertretung(en) %2, %3 failed',$success,$action_msg,$failed);
		            }
		        }
		    }
		}

		$content = array(			
			'msg' => $msg,
		);

		if (!is_array($content['nm']))
		{
		    Api\Cache::unsetSession('schulmanager', 'substitution_filter');
		    Api\Cache::unsetSession('schulmanager', 'substitution_rows');
		    Api\Cache::unsetSession('schulmanager', 'substitution_lesson_list');
		    
			//$content = array();
			$content['nm'] = array();
			$content['msg'] = $msg;

			$content['nm']['get_rows']		= 'schulmanager.schulmanager_substitution_ui.get_rows';//'schulmanager.notenmanager_ui.get_rows';//'resources.resources_bo.get_rows';
			$content['nm']['no_filter'] 	= False;
			$content['nm']['filter_no_lang'] = true;
			$content['nm']['no_cat']	= true;
			$content['nm']['no_search']	= true;
			$content['nm']['no_filter2']	= true;
			$content['nm']['bottom_too']	= true;
			$content['nm']['order']		= 'nm_id';
			$content['nm']['sort']		= 'ASC';
			$content['nm']['row_id']	= 'nm_id';
			$content['nm']['favorites'] = false;
			$content['nm']['filter'] = $filter;
			$content['nm']['actions'] = self::get_actions($content);
			$content['nm']['default_cols']  = '!legacy_actions';
			$content['nm']['no_columnselection'] = false;
		}
		
		$sel_options['add_lesson_list'] = array(		    
		);
		// todo this is only for possitive validation check
		for ($i = 0; $i <= 30; $i++) {
		    $sel_options['add_lesson_list'][$i] = $i;
		}

		$readonlys = array(
			'button[add]'     => false,
		);

		$preserv = $sel_options;
		$ignore_validation = true;
		return $etpl->exec('schulmanager.schulmanager_substitution_ui.index',$content,$sel_options,$readonlys, $preserv, 0, $ignore_validation);
	}
	
	
	function delete($content = null){
	    $row_id = 0;
	    if (isset($_GET['row_id'])) $row_id = $_GET['row_id'];
	    
	    if ($row_id > 0)
	    {
	       $this->bo->delete($row_id);
	    }
	}

	/**
	 * query projects for nextmatch in the substitution-list
	 * reimplemented from Api\Storage\Base to disable action-buttons based on the Acl and make some modification on the data
	 *
	 * @param array &$query
	 * @param array &$rows returned rows/cups
	 * @param array &$readonlys eg. to disable buttons based on Acl
	 * @param boolean $id_only if true only return (via $rows) an array of contact-ids, dont save state to session
	 * @return int total number of contacts matching the selection
	 */
	function get_rows(&$query_in,&$rows,&$readonlys,$id_only=false)
	{
		// todo if edit, dann rows aus session holen!
		$total = 0;
		if(isset($query_in['filter'])){
			Api\Cache::setSession('schulmanager', 'substitution_filter', $query_in['filter']);
		}
		else{
			// edit records			
			$query_in['filter'] = Api\Cache::getSession('schulmanager', 'substitution_filter');
		}
		
		$total = $this->bo->getSubstitutionList($query_in,$rows);		
		Api\Cache::setSession('schulmanager', 'substitution_rows', $rows);
		return $total;
	}
	
	/**
	 * ajax load teachers lessons
	 * @param unknown $query
	 */
	function ajax_getTeacherLessonList($teacher_id) {
        $lehrer_account_so = new schulmanager_lehrer_account_so();
        $lehrerStammIDs = $lehrer_account_so->loadLehrerStammIDs($teacher_id);

        $lessonList = $this->unterricht_so->loadLehrerUnterricht($lehrerStammIDs);
	   	   
	    Api\Cache::setSession('schulmanager', 'substitution_lesson_list', $lessonList);
	    $result = array();
	    foreach($lessonList AS $key => $lesson){
	        $result[$key] = $lesson['bezeichnung'];
	    }
	    Api\Json\Response::get()->data($result);
	}

    function accounts(array $content = null,$msg='')
    {
        $etpl = new Etemplate('schulmanager.accounts');

        if ($_GET['msg']) $msg = $_GET['msg'];

        $sel_options = array();
        $preserv = array();

        $content = array(
            'msg' => $msg,
        );

        if (!is_array($content['nm']))
        {
            Api\Cache::unsetSession('schulmanager', 'substitution_filter');
            Api\Cache::unsetSession('schulmanager', 'substitution_rows');
            Api\Cache::unsetSession('schulmanager', 'substitution_lesson_list');

            $content['nm'] = array();
            $content['msg'] = $msg;

            $content['nm']['get_rows']		= 'schulmanager.schulmanager_substitution_ui.get_rows_lehrer';
            $content['nm']['no_filter'] 	= True;
            $content['nm']['filter_no_lang'] = true;
            $content['nm']['no_cat']	= true;
            $content['nm']['no_search']	= true;
            $content['nm']['no_filter2']	= true;
            $content['nm']['bottom_too']	= true;
            $content['nm']['order']		= 'ls_asv_familienname, ls_asv_rufname';
            $content['nm']['sort']		= 'ASC';
            $content['nm']['row_id']	= 'nm_id';
            $content['nm']['favorites'] = false;
            $content['nm']['actions'] = self::get_actions_account($content);
            $content['nm']['default_cols']  = '!legacy_actions';
            $content['nm']['no_columnselection'] = false;
        }

        $xrsf_token = bin2hex(random_bytes(32));
        Api\Cache::setSession('schulmanager', 'token_teacher_account_link', $xrsf_token);
        $content['token'] = $xrsf_token;

        $readonlys = array();

        $preserv = $sel_options;
        $ignore_validation = true;
        return $etpl->exec('schulmanager.schulmanager_substitution_ui.accounts',$content,$sel_options,$readonlys, $preserv, 0, $ignore_validation);
    }

    /**
     * query projects for nextmatch in the teacher-mapping-list
     * reimplemented from Api\Storage\Base to disable action-buttons based on the Acl and make some modification on the data
     * @param array &$query
     * @param array &$rows returned rows/cups
     * @param array &$readonlys eg. to disable buttons based on Acl
     * @param boolean $id_only if true only return (via $rows) an array of contact-ids, dont save state to session
     * @return int total number of contacts matching the selection
     */
    function get_rows_lehrer(&$query_in,&$rows,&$readonlys,$id_only=false)
    {
        $lehrer_so = new schulmanager_lehrer_so();
        $lehrer_so->getLehrerAccountList($query_in, $rows);

        $querySearchCached = Api\Cache::getSession('schulmanager', 'teacher_account_list_search');
        if($query_in['search'] != $querySearchCached){
            Api\Cache::setSession('schulmanager', 'teacher_account_list_search', $query_in['search']);
            Api\Cache::unsetSession('schulmanager', 'teacher_account_list');
        }
        else{
            $rowsCache = Api\Cache::getSession('schulmanager', 'teacher_account_list');
        }

        if(!is_array($rowsCache)){
            $rowsCache = array();
        }
        //$rowsOld = array_merge($rowsOld, $rows);
        foreach($rows AS $key => $val){
            //$sKey = strval($key);
            if(!array_key_exists($key, $rowsCache)){
                $rowsCache[$key] = $val;
            }
        }

        Api\Cache::setSession('schulmanager', 'teacher_account_list', $rowsCache);
        return $query_in['total'];
    }

    /**
     * checks if token and saved token are equal
     * @param $key
     * @param $token
     */
    function checkToken($key, $token){
        $sessToken = Api\Cache::getSession('schulmanager', $key);
        return !empty($sessToken) && !empty($token) && $sessToken == $token;
    }

    function ajax_onTeacherAutoLinking(){
        $result = array();
        $lehrer_so = new schulmanager_lehrer_so();
        $lehrer_so->updateEGWLinking();
        Api\Json\Response::get()->data($result);
    }

    function ajax_onTeacherResetLinking(){
        $result = array();
        $lehrer_account_so =  new schulmanager_lehrer_account_so();
        $lehrer_account_so->truncateEGWLinking();
        Api\Json\Response::get()->data($result);
    }

    /**
     * Edit teacher - EGWUser link
     * @param $row_index
     * @throws Api\Json\Exception
     */
    function ajax_onTeacherAccountLinkEdit($row_index){
        $result = array();
        $rows = Api\Cache::getSession('schulmanager', 'teacher_account_list');

        if($rows[$row_index]){
            Api\Cache::setSession('schulmanager', 'substitution_linkedit_lehrer', $rows[$row_index]);
            $result['row_index'] = $row_index;
            $result['ls_asv_familienname'] = $rows[$row_index]['ls_asv_familienname'];
            $result['ls_asv_rufname'] = $rows[$row_index]['ls_asv_rufname'];
            $result['link_account_id'] = $rows[$row_index]['leac_account'];
        }
        else{
            Api\Cache::unsetSession('schulmanager', 'substitution_linkedit_lehrer');
        }
        Api\Json\Response::get()->data($result);
    }

    /**
     * commit modified teacher user link
     * @param $account_id
     * @param $token
     * @throws Api\Json\Exception
     */
    function ajax_onTeacherAccountLinkCommit($account_id, $token){
        if(!$this->checkToken('token_teacher_account_link', $token)){
            $result['error_msg'] = 'ERROR: could not submit date!';
            Api\Json\Response::get()->data($result);
            return;
        }

        $result = array();
        $selRowLehrer = Api\Cache::getSession('schulmanager', 'substitution_linkedit_lehrer');

        if($selRowLehrer) {
            $lehrer_mapping_old = array(
                'leac_lehrer' => $selRowLehrer['ls_asv_id']
            );
            $lehrer_account_so = new schulmanager_lehrer_account_so();
            if($selRowLehrer['ls_asv_id']){
                $lehrer_account_so->delete($lehrer_mapping_old);
            }
            $lehrer_mapping_new = array(
                'leac_lehrer' => $selRowLehrer['ls_asv_id'],
                'leac_account' => $account_id,
            );
            $lehrer_account_so->saveItem($lehrer_mapping_new);
            $result['row_index'] = $selRowLehrer['nm_id'];
            Api\Cache::unsetSession('schulmanager', 'teacher_account_list');
        }
        Api\Json\Response::get()->data($result);
    }

    /**
     * delete modified teacher user link
     * @param $row_ids array of row ids
     * @throws Api\Json\Exception
     */
    function ajax_onTeacherAccountLinkReset($row_ids, $token){
        if(!$this->checkToken('token_teacher_account_link', $token)){
            $result['error_msg'] = 'ERROR: could not submit date!';
            Api\Json\Response::get()->data($result);
            return;
        }
        $result = array();
        $rows = Api\Cache::getSession('schulmanager', 'teacher_account_list');

        foreach($row_ids as $row_index){
            if($rows[$row_index]){
                $lehrer_account_so = new schulmanager_lehrer_account_so();
                $pkeys = array(
                    'leac_lehrer' => $rows[$row_index]['ls_asv_id'],
                    'leac_account' => $rows[$row_index]['leac_account']
                );
                $lehrer_account_so->delete($pkeys);
            }
        }
        Api\Cache::unsetSession('schulmanager', 'teacher_account_list');
        Api\Json\Response::get()->data($result);//$result);
    }
}