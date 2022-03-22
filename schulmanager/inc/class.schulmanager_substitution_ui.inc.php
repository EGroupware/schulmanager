<?php

/**
 * SchulManager - User interface
 */
use EGroupware\Api;
use EGroupware\Api\Egw;
use EGroupware\Api\Link;
use EGroupware\Api\Framework;
use EGroupware\Api\Acl;
use EGroupware\Api\Etemplate;

/**
 * This class is the UI-layer (user interface)
 */
class schulmanager_substitution_ui
{
	var $public_functions = array(
		'index'       => True,			
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
	 * @var schulmanager_substitution_bo
	 */
	var $bo;
	
	
	/**
	 * @var chulmanager_lehrer_so
	 */
	var $schulmanager_lehrer_so;


	/**
	 * Constructor
	 *
	 * @return notenmanager_ui
	 */
	function __construct(Etemplate $etemplate = null)
	{		
		$this->bo = new schulmanager_substitution_bo();
		
		$this->schulmanager_lehrer_so = new schulmanager_lehrer_so();
	}

	/**
	 * Get actions / context menu for index
	 *
	 * Changes here, require to log out, as $content['nm'] get stored in session!
	 *
	 * @return array see nextmatch_widget::egw_actions()
	 */
	/**
	 * Context menu
	 *
	 * @return array
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
			             $msg = $error . "\n";
			             $failed++;
			         }
			    }
				break;
		}
		return $failed == 0;
	}



	/**
	 * List Schulmanager entries
	 *
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

		if (is_array($content))
		{
		    
		    list($button) = @each($content['button']);
		    
		    unset($content['button']);
		    if ($button)
		    {
		        if ($button == 'add')
		        {
		            $this->bo->add($content['add_kennung'], $content['add_kennung_orig'], $content['add_lesson_list']);		            
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


	//	else{
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
		//	$content['nm']['store_state']	= 'get_rows';
			$content['nm']['row_id']	= 'nm_id';
			$content['nm']['favorites'] = false;
			$content['nm']['filter'] = $filter;
			$content['nm']['actions'] = self::get_actions($content);
		//	$content['nm']['filter_onchange'] = "app.schulmanager.filter_change();";
			$content['nm']['default_cols']  = '!legacy_actions';
			$content['nm']['no_columnselection'] = false;

		//	$content['nm']['options-filter'] = $this->bo->getKlassenFachList();
			
		//	$content['nm']['test'] = "";
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
	    
	    //$res_id = is_numeric($content) ? (int)$content : $content['res_id'];
	    
	    if ($row_id > 0)
	    {
	        
	       $this->bo->delete($row_id);
	    }
	    
	}


	/**
	 * query projects for nextmatch in the projects-list
	 *
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
	    $teacher = Api\Accounts::read($teacher_id);
	    $kennung = $teacher['account_lid'];
	    
	    //$result = $this->so->loadUnterricht($kennung);
	    $lessonList = $this->schulmanager_lehrer_so->loadUnterricht($kennung);
	   	   
	    Api\Cache::setSession('schulmanager', 'substitution_lesson_list', $lessonList);
	    $result = array();
	    foreach($lessonList AS $key => $lesson){
	        $result[$key] = $lesson->getFormatKgSf();
	    }
	    
	    	    
	    Api\Json\Response::get()->data($result);
	}
}