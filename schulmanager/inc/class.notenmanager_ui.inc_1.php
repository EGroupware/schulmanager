<?php

/**
 * SchulManager - User interface
 */
use EGroupware\Api;
use EGroupware\Api\Link;
use EGroupware\Api\Framework;
use EGroupware\Api\Egw;
use EGroupware\Api\Acl;
use EGroupware\Api\Etemplate;

/**
 * This class is the UI-layer (user interface) of InfoLog
 */
class notenmanager_ui
{
	var $public_functions = array(
		'index'       => true,
		'edit'		  => true,
		'apply'		  => true,
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
	 * @var notenmanager_bo
	 */
	var $bo;
	/**
	 * instance of the etemplate class
	 *
	 * @var Etemplate
	 */
	var $template;





	/**
	 * Constructor
	 *
	 * @return notenmanager_ui
	 */
	function __construct(Etemplate $etemplate = null)
	{
		if($etemplate === null)
		{
			$etemplate = new Etemplate();
		}
		$this->template = $etemplate;
		//$this->template	= new Etemplate('schulmanager.notenmanager.index');
		$this->bo = new schulmanager_bo();

	}

	/**
	 * Get actions / context menu for index
	 *
	 * Changes here, require to log out, as $content['nm'] get stored in session!
	 *
	 * @return array see nextmatch_widget::egw_actions()
	 */
//	public function get_actions(Array $query)
//	{
//		$actions = array();
		//	'onExecute' => 'javaScript:app.schulmanager.action'
		//);
//	}

	/*public function _do_action($action_id, $selected){
		$success = $failed = 0;
	}*/

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
//	function action($action,$checked,$use_all,&$success,&$failed,&$action_msg,$session_name,&$msg)
//	{
//		$success = $failed = 0;
//	}



	/**
	 * List Schulmanager entries
	 *
	 * @param array $content
	 * @param string $msg
	 */
	function index(array $content = null,$msg='')
	{
		$readonlys = array();
		$sel_options = array();
		$preserv = array();
		$content = array();
		$content['msg'] = $msg ? $msg : $_GET['msg'];

		$query_in = array(
			'filter' => 0,
		);
		$rows = array();
		$content['lst']['len'] = $this->get_rows($query_in, $rows, $readonlys);
		$content['lst'] = $rows;

		$content['cnf']['filter'] = $this->bo->getKlassenFachList();


		$content['notenlist'] = array(
				'1' => array(
					'nm_id'		=> $id,
					'nm_st'		=> array(
						'st_asv_id'			  => $row['st_asv_id'],
						'st_asv_familienname' => $row['st_asv_familienname'],
						'st_asv_rufname'	  => $row['st_asv_rufname']
					),
					'noten'		=> array(
						'alt_b' => false,
						'glnw_hj_1' => array(
							-1 => '3,22',
							0 => '2',
							1 => '3'
						),
						'klnw_hj_1' => array(
							-1 => '1,22',
							0 => '1',
							1 => '1',
							2 => '2',
							3 => '3',
							4 => '4',
						),
						'schnitt_hj_1' => '',
						'note_hj_1' => '',
						'm_hj_1' => '',
						'v_hj_1' => ''
					),
				),
		);


		$preserv = $sel_options;
		$this->template->read('schulmanager.notenmanager.index');
		return $this->template->exec('schulmanager.notenmanager_ui.index',$content,$sel_options,$readonlys,$preserv);
	}


	/**
	 * List Schulmanager entries
	 *
	 * @param array $content
	 * @param string $msg
	 */
	function edit($content = null)
	{
		if (is_array($content))
		{
			list($button) = @each($content['button']);

			unset($content['button']);
			if ($button)
			{
				if (($button == 'save' || $button == 'apply') && (!$info_id || $edit_acl || $status_only || $undelete))
				{
					if (!($info_id = $this->bo->write($content)))
					{
						$content['msg'] = $info_id !== 0 || !$content['info_id'] ? lang('Error: saving the entry') :
							lang('Error: the entry has been updated since you opened it for editing!').'<br />'.
							lang('Copy your changes to the clipboard, %1reload the entry%2 and merge them.','<a href="'.
								htmlspecialchars(Egw::link('/index.php',array(
									'menuaction' => 'schulmanager.notenmanager_ui.edit',
									'no_popup'   => $no_popup,
									'referer'    => $referer,
								))).'">','</a>');
						$button = $action = '';	// not exiting edit
					}
					else
					{
						$content['msg'] = lang('InfoLog entry saved');
					}

				}

				if ($button == 'save' || $button == 'cancel')
				{
					if ($no_popup)
					{
						Egw::redirect_link($referer,array('msg' => $content['msg']));
					}
					Framework::window_close();
				}
			}
		}
		else{


			$content = array();
			$content['msg'] = $msg ? $msg : $_GET['msg'];

			$content['nm']['get_rows']		= 'schulmanager.notenmanager_ui.get_rows';//'schulmanager.notenmanager_ui.get_rows';//'resources.resources_bo.get_rows';
			//$content['nm']['get_rows']		= $this->bo->getSchuelerNotenList;//'schulmanager.notenmanager_ui.get_rows';//'resources.resources_bo.get_rows';
			$content['nm']['no_filter'] 	= true;
			$content['nm']['filter_no_lang'] = true;
			$content['nm']['no_cat']	= true;
			$content['nm']['no_search']	= true;
			$content['nm']['no_filter2']	= true;
			$content['nm']['bottom_too']	= true;
			$content['nm']['order']		= 'ts_id';
			$content['nm']['sort']		= 'ASC';
		//	$content['nm']['store_state']	= 'get_rows';
			$content['nm']['row_id']	= 'nm_id';
			$content['nm']['favorites'] = false;
		//	$content['nm']['filter'] = 0;
		//	$content['nm']['onExecute'] = 0;
		//	$content['nm']['num_rows'] = true;
			$content['nm']['lettersearch'] = false;
			$content['nm']['header_title'] = "Notenübersicht: 10A Mathematik";
		//	$content['nm']['sel_options'] = true;
			$content['nm']['row_modified'] = 'modified';


			$content['dummy_test'] = 'hello';
			$content['notenlist'] = array(
				'1' => array(
					'nm_id'		=> $id,
					'nm_st'		=> array(
						'st_asv_id'			  => $row['st_asv_id'],
						'st_asv_familienname' => $row['st_asv_familienname'],
						'st_asv_rufname'	  => $row['st_asv_rufname']
					),
					'noten'		=> array(
						'alt_b' => false,
						'glnw_hj_1' => array(
							-1 => '3,22',
							0 => '2',
							1 => '3'
						),
						'klnw_hj_1' => array(
							-1 => '',
							0 => '1',
							1 => '1',
							2 => '2',
							3 => '3',
							4 => '',
						),
						'schnitt_hj_1' => '',
						'note_hj_1' => '',
						'm_hj_1' => '',
						'v_hj_1' => ''
					),
				),
			);

		/*	$nm_session_data = Api\Cache::getSession('resources', 'index_nm');
			if($nm_session_data)
			{
				$content['nm'] = $nm_session_data;
			}*/
			//$content['nm']['options-filter']= array('5A M','5B Inf', '10A Sm');//array(''=>lang('all categories'))+(array)$this->bo->acl->get_cats(Acl::READ);
			$content['nm']['options-filter'] = $this->bo->getKlassenFachList();
			//$content['nm']['options-filter2'] = resources_bo::$filter_options;

			$sel_options = array();
			$preserv = $content;
			$readonlys = array(
				'button[save]'     => false,
				'button[apply]'    => false,
				'button[cancel]'    => false,
			);



			// TODO 1. oder 2. Halbjahr eingeben
			//$this->template->read('schulmanager.notenmanager.edit');
			$this->template->read('schulmanager.notenmanager.edit');
			return $this->template->exec('schulmanager.notenmanager_ui.edit',$content,$sel_options,$readonlys,$preserv);

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
		if(isset($query_in['filter'])){
			Api\Cache::setSession('schulmanager', $values['query_in']['filter'], $query_in['filter']);
		}
		else{
			$query_in['filter'] = Api\Cache::getSession('schulmanager', $values['query_in']['filter']);
		}

		$this->bo->getSchuelerNotenList($query_in,$rows,$readonlys,$id_only);
		//$query_in['options-selectcols'] = array('sm_nm_note_hj'=>false,'sm_nm_sa1'=>false,'sm_nm_sa2'=>false);
		//$query_in['colfilter'] = array('sm_nm_note_hj'=>false,'sm_nm_sa1'=>false,'sm_nm_sa2'=>false);
		//$query_in['selectcols'] = array('sm_nm_note_hj','sm_nm_sa1','sm_nm_sa2');

		return count($rows);
	}





}