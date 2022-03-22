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
class notenmanager_ui
{
	var $public_functions = array(
		'index'       => True,
		'edit'		  => True,
		'klassenview' => True,
		'exportasv_jz'	  => True,
	    'exportasv_zz'	  => True,
	//	'exportpdf'	  => True,
	//	'exportpdf_kv' => True,
		'calendar_week' => True,
		'exportnotensql' => True,
		'exportnotensqlcheck' => True,
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
	var $bo;

	/**
	 * instance of the bo-class
	 *
	 * @var schueler_bo
	 */
	var $schueler_bo;

	var $calendar_ui;





	/**
	 * Constructor
	 *
	 * @return notenmanager_ui
	 */
	function __construct(Etemplate $etemplate = null)
	{
		//parent::__construct();
		/*if($etemplate === null)
		{
			$etemplate = new Etemplate();
		}*/
		//$this->template	= new Etemplate('schulmanager.notenmanager.index');
		$this->bo = new schulmanager_bo();

		$this->schueler_bo = new schulmanager_schueler_bo();

		$this->calendar_ui = new schulmanager_cal_ui();
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
		//	'editgew' => array(
		//		'caption' => 'Bearbeiten...',
				//'group' => $group,
		//		'allowOnMultiple' => false,
		//		'icon' => 'edit',
		//		'postSubmit' => true,
				//'shortcut' => array('ctrl' => true, 'shift' => true, 'keyCode' => 90, 'caption' => 'Ctrl + Shift + Z'),
				//'onExecute' => 'javaScript:app.schulmanager.test123',
		//	),			
			/*'block' => array(
				'caption' => 'Blockieren',
				//'group' => $group,
				'allowOnMultiple' => true,
				'icon' => 'save_zip',
				'postSubmit' => true,
				//'shortcut' => array('ctrl' => true, 'shift' => true, 'keyCode' => 90, 'caption' => 'Ctrl + Shift + Z'),
			)*/
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

			case 'editgew':
	//			$this->exportpdf();
				exit();
			case 'add':
	//			$this->exportpdf();
				exit();
			default:

		}
		return "Unknown action '$action'!";
	}



	/**
	 * List Schulmanager entries
	 *
	 * @param array $content
	 * @param string $msg
	 */
	function index(array $content = null,$msg='')
	{
	    $config = Api\Config::read('schulmanager');
		$etpl = new Etemplate('schulmanager.notenmanager.index');

		if ($_GET['msg']) $msg = $_GET['msg'];

		$sel_options = array();
		$preserv = array();

		$filter = Api\Cache::getSession('schulmanager', 'filter');
		if(empty($filter)){
			$filter = 0;
		}

		if (is_array($content))
		{
			list($button) = @each($content['nm']['button']);

			unset($content['nm']['button']);
			if ($button)
			{
				if ($button == 'exportpdf')
				{

					//header("Content-Type: application/zip");
					//header("Content-Length: " . filesize($zip_file));
					//header("Content-Disposition: attachment; filename=\"$filename\"");
		//			$this->bo->pdfNotenExport();
		//			exit;

		//			Framework::redirect_link(Egw::link('/index.php',array('menuaction' => 'schulmanager.notenmanager_ui.index','ajax' => 'true')));
				}
				elseif($button == 'edit'){
					Framework::redirect_link(Egw::link('/index.php',array('menuaction' => 'schulmanager.notenmanager_ui.edit','ajax' => 'true')));
				}
			}
		}

		$content = array(
			'nm' => Api\Cache::getSession('schulmanager', 'index'),
			'msg' => $msg,
		);


	//	else{
		if (!is_array($content['nm']))
		{
			//$content = array();
			$content['nm'] = array();
			$content['msg'] = $msg;

			$content['nm']['get_rows']		= 'schulmanager.notenmanager_ui.get_rows';//'schulmanager.notenmanager_ui.get_rows';//'resources.resources_bo.get_rows';
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
		//	$content['nm']['default_cols']  = '!legacy_actions';
			$content['nm']['no_columnselection'] = false;

			$content['nm']['options-filter'] = $this->bo->getKlassenFachList();

			$readonlys = array(
				'button[export_pdf]'     => false,
			);
		//	$content['nm']['test'] = "";
		}

		$edit_credits_enabled = (bool) $config['edit_credits_enabled'];
		
		$content['nm']['edit_credits_disabled'] = !$edit_credits_enabled;
		
		$readonlys = array(
			'button[edit]'     => false,
		);

		$preserv = $sel_options;
		//$this->template->read('schulmanager.notenmanager.index');
		//return $this->template->exec('schulmanager.notenmanager_ui.index',$content,$sel_options,$readonlys,$preserv);
		return $etpl->exec('schulmanager.notenmanager_ui.index',$content,$sel_options,$readonlys,$preserv);
	}


	/**
	 * List Schulmanager entries
	 *
	 * @param array $content
	 * @param string $msg
	 */
	function edit($content = null, $view = false)
	{
	    $config = Api\Config::read('schulmanager');
	    $edit_credits_enabled = (bool) $config['edit_credits_enabled'];
	    if(!$edit_credits_enabled){
	        return;
	    }
		$etpl = new Etemplate('schulmanager.notenmanager.edit');
		$preserv = array();
		$sel_options = array();
		if (is_array($content))
		{
			list($button) = @each($content['nm']['button']);

			unset($content['nm']['button']);
			if ($button)
			{
				if ($button == 'save' || $button == 'apply')
				{
					// if ($etpl->validation_errors()) break;
					$modified_records = Api\Cache::getSession('schulmanager', 'notenmanager_modified_records');
					$modified_gewichtung = Api\Cache::getSession('schulmanager', 'notenmanager_modified_gewichtung');
					$msg = '';
					if(isset($modified_records) || isset($modified_gewichtung)){
						if (!($info_id = $this->bo->write()))
						{
	/*						$content['msg'] = $info_id !== 0 || !$content['info_id'] ? lang('Error: saving the entry') :
								lang('Error: the entry has been updated since you opened it for editing!').'<br />'.
								lang('Copy your changes to the clipboard, %1reload the entry%2 and merge them.','<a href="'.
									htmlspecialchars(Egw::link('/index.php',array(
										'menuaction' => 'schulmanager.notenmanager_ui.edit',
										'no_popup'   => $no_popup,
										'referer'    => $referer,
									))).'">','</a>');
							$button = $action = '';	// not exiting edit
	 */
						}
						else
						{
							$content['msg'] = lang('InfoLog entry saved');
						}
					}
					else{
						$content['msg'] = lang('Nothing to save!');
					}
				}

				if ($button == 'save' || $button == 'apply' || $button == 'cancel')
				{
					Api\Cache::unsetSession('schulmanager', 'notenmanager_temp_rows');
					Api\Cache::unsetSession('schulmanager', 'notenmanager_modified_records');
					Api\Cache::unsetSession('schulmanager', 'notenmanager_modified_gewichtung');
				}

				//Framework::refresh_opener($msg, 'schulmanager');
				if ($button == 'save' || $button == 'cancel')
				{
					//Framework::window_close();
					//Framework::redirect_link('/index.php',array('menuaction'=>'schulmanager.notenmanager_ui.index'));
					Framework::redirect_link(Egw::link('/index.php',array('menuaction' => 'schulmanager.notenmanager_ui.index','ajax' => 'true')));
					//return;
				}
				/*else if( $button == 'apply'){
					Framework::refresh_opener($msg, 'schulmanager');
					return;
				}*/
			}
		}
		elseif(isset($_POST["action"]) && $_POST['action'] === 'buffer-modified'){
			if(isset($_POST['notekey'])){
				// Note wurde geändert
				$notekey = $_POST['notekey'];
				$noteval = $_POST['noteval'];

				$modified_records = Api\Cache::getSession('schulmanager', 'notenmanager_modified_records');
				if(!is_array($modified_records)){
					// maybe first modified record
					$modified_records = array();
				}
				$modified_records[$notekey] = $noteval;
				Api\Cache::setSession('schulmanager', 'notenmanager_modified_records', $modified_records);
			}
			elseif(isset($_POST['gewkey'])){
				// Gewichtung wurde geändert
				$gewkey = $_POST['gewkey'];
				$gewval = $_POST['gewval'];

				$modified_gewichtung = Api\Cache::getSession('schulmanager', 'notenmanager_modified_gewichtung');
				if(!is_array($modified_gewichtung)){
					// maybe first modified record
					$modified_gewichtung = array();
				}
				$modified_gewichtung[$gewkey] = $gewval;
				Api\Cache::setSession('schulmanager', 'notenmanager_modified_gewichtung', $modified_gewichtung);
			}

		}
	//	else{

			Api\Cache::unsetSession('schulmanager', 'notenmanager_modified_records');
			$content = array();
			$content['msg'] = $msg ? $msg : $_GET['msg'];

			$content['nm']['get_rows']		= 'schulmanager.notenmanager_ui.get_rows_edit';
			$content['nm']['no_filter'] 	= true;
			$content['nm']['filter_no_lang'] = true;
			$content['nm']['no_cat']	= true;
			$content['nm']['no_search']	= true;
			$content['nm']['no_filter2']	= true;
			$content['nm']['bottom_too']	= true;
			$content['nm']['order']		= 'nm_id';
			$content['nm']['sort']		= 'ASC';
		//	$content['nm']['store_state']	= 'get_rows';
			$content['nm']['row_id']	= 'nm_id';
		// nicht setzen!	$content['nm']['filter'] = 0;			
		//	$content['nm']['onExecute'] = 0;
		//	$content['nm']['num_rows'] = true;
			$content['nm']['lettersearch'] = false;
		//	$content['nm']['default_cols']  = '!legacy_actions';
			$content['nm']['no_columnselection'] = false;
			$content['nm']['favorites'] = false;


			$content['nm']['options-filter'] = $this->bo->getKlassenFachList();

			$readonlys = array(
				'button[save]'     => false,
				'button[apply]'    => false,
				'button[cancel]'   => false,
			);


			$preserv = $content;/* + array(
				'view'    => $view,
				'referer' => $referer,
				'nm_title_blur' => $content['nm_title_blur'],
			);
			 */
			// TODO 1. oder 2. Halbjahr eingeben
			//$this->template->read('schulmanager.notenmanager.edit');
			//$this->template->read('schulmanager.notenmanager.edit');
			return $etpl->exec('schulmanager.notenmanager_ui.edit',$content,$sel_options,$readonlys,$preserv);

//		}
	}


	/**
	 * List Schulmanager entries
	 *
	 * @param array $content
	 * @param string $msg
	 */
	function klassenview(array $content = null,$msg='')
	{
		$etpl = new Etemplate('schulmanager.notenmanager.klassenview');
		$readonlys = array();
		$sel_options = array();
		$preserv = array();

		if (is_array($content))
		{
			list($button) = @each($content['nm']['button']);

			unset($content['nm']['button']);
			if ($button)
			{
				if ($button == 'export_pdf')
				{
// not used anymore					$this->exportpdf($content);
				}
			}
		}
		elseif(isset($_POST["action"]) && $_POST['action'] === 'export_pdf_klassenview'){
// not used anymore			return $this->exportpdf();
		}
		else{
			if (!is_array($content['nm']))
			{

				$content = array();
				$content['msg'] = $msg ? $msg : $_GET['msg'];
				$content['nm'] = array();
				$content['nm']['get_rows']		= 'schulmanager.notenmanager_ui.get_klassen_rows';//'schulmanager.notenmanager_ui.get_rows';//'resources.resources_bo.get_rows';

				$content['nm']['no_filter2']	= True;
				$content['nm']['no_cat']	= True;
				// hide it, but how??
				//$content['nm']['lettersearch']	= false;
				//$content['nm']['searchletter']   =	false;


				$content['nm']['bottom_too']	= true;
				$content['nm']['order']		= 'nm_id';
				$content['nm']['sort']		= 'ASC';
				$content['nm']['is_parent']		= 'is_par';
				$content['nm']['parent_id']		= 'schueler_id';
				$content['nm']['row_id']	= 'nm_id';
				$content['nm']['filter'] = 0;

				$content['nm']['options-filter'] = $this->bo->getKlassen();

				$readonlys = array(
					'button[export_pdf]'     => false,
				);
			}
		}
		$preserv = $sel_options;
		//$this->template->read('schulmanager.notenmanager.klassenview');
		return $etpl->exec('schulmanager.notenmanager_ui.klassenview',$content,$sel_options,$readonlys,$preserv);
	}

	function exportnotensqlcheck(array $content = null, $msg = ''){
		if($GLOBALS['egw_info']['user']['apps']['admin'])
		{
			$sqlData = array(
				'type'	=> 'text/sql',
				'charset' => 'utf8',
				'filename'	=> '2019_Jahreszeugnis_SQLCHECK.sql',
			);

			$path = '2019_Jahreszeugnis_SQLCHECK.sql';
			$mime = '';

			$length = 0;
			// public static function safe(&$content, $path, &$mime='', &$length=0, $nocache=true, $force_download=true, $no_content_type=false)
			Api\Header\Content::safe($sqlData, $path, $mime, $length, True, True);
			echo $this->bo->exportJahresZeugnisnotenCheck();
		}
		exit();
	}

	function exportnotensql(array $content = null, $msg = ''){
		if($GLOBALS['egw_info']['user']['apps']['admin'])
		{
			$sqlData = array(
				'type'	=> 'text/sql',
				'charset' => 'utf8',
				'filename'	=> '2012_Jahreszeugnis_Noten2ASV.sql',
			);

			$path = '2018-19_Jahreszeugnis_Noten2ASV.sql';
			$mime = '';

			$length = 0;
			// public static function safe(&$content, $path, &$mime='', &$length=0, $nocache=true, $force_download=true, $no_content_type=false)
			Api\Header\Content::safe($sqlData, $path, $mime, $length, True, True);
			echo $this->bo->exportJahresZeugnisnoten2SQL();
		}
		exit();
	}

	/**
	 * List Schulmanager entries
	 *
	 * @param array $content
	 * @param string $msg
	 */
	function exportasv_jz(array $content = null,$msg='')
	{
		if($GLOBALS['egw_info']['user']['apps']['admin'])
		{
			$xmlData = array(
				'type'	=> 'text/xml',
				'charset' => 'utf8',
				'filename'	=> 'noten-export-jz.xml',
			);

			$path = 'asvexport_jz.xml';
			$mime = '';

			$length = 0;
			// public static function safe(&$content, $path, &$mime='', &$length=0, $nocache=true, $force_download=true, $no_content_type=false)
			Api\Header\Content::safe($xmlData, $path, $mime, $length, True, True);
			echo $this->bo->asvNotenExport(2);
		}

		exit();
	}
	
	/**
	 * List Schulmanager entries
	 *
	 * @param array $content
	 * @param string $msg
	 */
	function exportasv_zz(array $content = null,$msg='')
	{
	    if($GLOBALS['egw_info']['user']['apps']['admin'])
	    {
	        $xmlData = array(
	            'type'	=> 'text/xml',
	            'charset' => 'utf8',
	            'filename'	=> 'noten-export-zz.xml',
	        );
	        
	        $path = 'asvexport_zz.xml';
	        $mime = '';
	        
	        $length = 0;
	        // public static function safe(&$content, $path, &$mime='', &$length=0, $nocache=true, $force_download=true, $no_content_type=false)
	        Api\Header\Content::safe($xmlData, $path, $mime, $length, True, True);
	        echo $this->bo->asvNotenExport(1);
	    }
	    
	    exit();
	}
	


	/**
	 * List Schulmanager entries
	 *
	 * @param array $content
	 * @param string $msg
	 */
	function exportpdf(array $content = null,$msg='')
	{
		$this->bo->pdfNotenExport();
		//Api\Json\Response::get()->data('');
		//exit();
	}
	/**
	 * Creates PDF-Klassenview
	 * @param array $content
	 * @param type $msg
	 */
	function exportpdf_kv(array $content = null,$msg='')
	{
		$this->bo->pdfKlassenNotenExport();
		//exit();
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
			Api\Cache::setSession('schulmanager', 'filter', $query_in['filter']);
		}
		else{
			// edit records
			Api\Cache::unsetSession('schulmanager', 'notenmanager_modified_records');
			$query_in['filter'] = Api\Cache::getSession('schulmanager', 'filter');
		}

		$total = $this->bo->getSchuelerNotenList($query_in,$rows);

		// only for temporäry calculation with ajax
		Api\Cache::setSession('schulmanager', 'notenmanager_temp_rows', $rows);
		Api\Cache::setSession('schulmanager', 'notenmanager_rows', $rows);

		return $total;
	}

	/**
	 * get rows for editing
	 *
	 * @param array &$query
	 * @param array &$rows returned rows/cups
	 * @param array &$readonlys eg. to disable buttons based on Acl
	 * @param boolean $id_only if true only return (via $rows) an array of contact-ids, dont save state to session
	 * @return int total number of contacts matching the selection
	 */
	function get_rows_edit(&$query_in,&$rows,&$readonlys,$id_only=false)
	{
		// todo if edit, dann rows aus session holen!
		//$total = 0;
		if(isset($query_in['filter'])){
			$setSession = Api\Cache::setSession('schulmanager', 'filter', $query_in['filter']);
		}
		else{
			// edit records
			Api\Cache::unsetSession('schulmanager', 'notenmanager_modified_records');
			$query_in['filter'] = Api\Cache::getSession('schulmanager', 'filter');
		}

		// only check number of lines
		$total = $this->bo->getSchuelerNotenList($query_in,$rows);
		if(is_null(Api\Cache::getSession('schulmanager', 'notenmanager_temp_rows'))){
			// situation after apply
			Api\Cache::setSession('schulmanager', 'notenmanager_temp_rows', $rows);
			Api\Cache::setSession('schulmanager', 'notenmanager_rows', $rows);
		}
		else{
			// load rows from session if exists, needed for reload when columns has been resized
			$rows = Api\Cache::getSession('schulmanager', 'notenmanager_temp_rows');
		}

		return $total;
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
	function get_klassen_rows(&$query_in,&$rows)
	{
		$klassen_filter_id = Api\Cache::getSession('schulmanager', 'klassen_filter_id');
		if(!array_key_exists('col_filter', $query_in) || ($query_in['filter'] != $klassen_filter_id) || !isset( $query_in['col_filter']['schueler_id'])){
			Api\Cache::setSession('schulmanager', 'klassen_filter_id', $query_in['filter']);
			$this->bo->getKlassenSchuelerList($query_in,$rows,$readonlys,$id_only);
			Api\Cache::setSession('schulmanager', 'klassen_schueler_list', $rows);
		}
		elseif(array_key_exists('col_filter', $query_in) && isset( $query_in['col_filter']['schueler_id'])){ //array_key_exists('schueler_id', $query_in['col_filter'])){
			//$query_in['test'] = $query_in['col_filter'];
			$klassen_schueler_list = Api\Cache::getSession('schulmanager', 'klassen_schueler_list');
			$schueler = $klassen_schueler_list[$query_in['col_filter']['schueler_id']];
			$this->schueler_bo->getNotenAbstract($schueler, $rows, $query_in['col_filter']['schueler_id']);
		}
		/*else{
			 //= $query_in['filter'];
			Api\Cache::setSession('schulmanager', 'klassen_filter_id', $query_in['filter']);
			$this->bo->getKlassenSchuelerList($query_in,$rows,$readonlys,$id_only);
			Api\Cache::setSession('schulmanager', 'klassen_schueler_list', $rows);
		}*/

		return count($rows);
	}


	function ajax_getGewichtungen($query) {
		$kg_asv_id = Api\Cache::getSession('schulmanager', 'filter_klassengruppe_asv_id');
		$sf_asv_id = Api\Cache::getSession('schulmanager', 'filter_schuelerfach_asv_id');

		// Gewichtungen
		$gewichtungen = array();
		$gew_bo = new schulmanager_note_gew_bo();
		$gew_bo->loadGewichtungen($kg_asv_id, $sf_asv_id, $gewichtungen);

		$result = array();
		foreach($gewichtungen AS $key => $gew){
			$result[$key] = $gew;
		}

		Api\Json\Response::get()->data($result);
	}

	/**
	 * Ajax call, wenn credit was modified, before beeing saved
	 * Revalidate avg-values with new credits.
	 * @param type $noteKey
	 * @param type $noteVal
	 */
	function ajax_noteModified($noteKey, $noteVal) {
		$result = array();
		// Key/val in session speichern
		$modified_records = Api\Cache::getSession('schulmanager', 'notenmanager_modified_records');
		if(!is_array($modified_records)){
			// maybe first modified record
			$modified_records = array();
		}
		$modified_records[$noteKey] = $noteVal;
		Api\Cache::setSession('schulmanager', 'notenmanager_modified_records', $modified_records);

		// neue Schnitte berechnen
		$rows = Api\Cache::getSession('schulmanager', 'notenmanager_temp_rows');
		$gewichtungen = Api\Cache::getSession('schulmanager', 'notenmanager_gewichtungen');

		$keys = preg_split('/\\]\\[|\\[|\\]/', $noteKey, -1, PREG_SPLIT_NO_EMPTY);

		$schueler = $rows[$keys[0]];

		// clean up
		$this->resetAVGNoten($schueler);
		// ende clean up

		$schueler[$keys[1]][$keys[2]][$keys[3]][$keys[4]] = $noteVal;

		// manuelle Einträge
		if($keys[3] == -1 && !empty($noteVal)){
			// schnitt oder note wurde geändert und ist nicht nur gelöscht
			$schueler[$keys[1]][$keys[2]][$keys[3]]['manuell'] = true;
//			$schueler[$keys[1]][$keys[2]]['avgclass'] = 'nm_avg_manuell';
		}
		elseif($keys[3] == -1 && empty($noteVal)){
			// schnitt oder note wurde geändert und ist nicht nur gelöscht
			$schueler[$keys[1]][$keys[2]][$keys[3]]['manuell'] = false;
//			$schueler[$keys[1]][$keys[2]]['avgclass'] = 'nm_avg_auto';
		}

		// alternative Berechnung
		if($keys[2] === 'alt_b' && $keys[3] == -1){
			$schueler[$keys[1]][$keys[2]][$keys[3]]['note'] = $noteVal;
		}


		//foreach($rows AS $key => $schueler){
		schulmanager_lehrer_so::beforeSendToClient($schueler, $gewichtungen);		// Schnite und noten neu berechnen
		$rows[$keys[0]] = $schueler;
		Api\Cache::setSession('schulmanager', 'notenmanager_temp_rows', $rows);

		$this->setAVGNoten($result, $keys[0], $schueler);

		Api\Json\Response::get()->data($result);
	}

	/*function ajax_exportpdf($noteKey, $noteVal) {
		$result = "OK";

		Api\Cache::setSession('schulmanager', 'pdfexprt', time());

		Api\Json\Response::get()->data($result);

	}
	 */
	/*
	function ajax_exportpdf_kv(){
		$result = array();

		$downloadUI = new schulmanager_download_ui();
		$data = $downloadUI->exportpdf_kv();

		$base64 = base64_encode(substr($data, 0, 20));

	//	$result['data'] = $this->bo->pdfKlassenNotenExport();
		$result['filename'] = 'test.pdf';
		$result['state'] = "OK";
		$result['data'] = $base64;
		$result['data2'] = $downloadUI->exportpdf_kv();
		//Api\Json\Response::get()->apply('data', array($data, 'data'));
		Api\Json\Response::get()->data($result);
	}
	*/

	/**
	 * Ajax call, wenn gew was modified, before beeing saved
	 * Revalidate avg-values with new credits.
	 * @param type $noteKey
	 * @param type $noteVal
	 */
	function ajax_gewModified($gewKey, $gewVal) {
		$result = array();
		// Key/val in session speichern
		$modified_gewichtung = Api\Cache::getSession('schulmanager', 'notenmanager_modified_gewichtung');
		if(!is_array($modified_gewichtung)){
			// maybe first modified record
			$modified_gewichtung = array();
		}

		if(empty($gewVal)){
			$gewVal = 1;
		}

		$modified_gewichtung[$gewKey] = $gewVal;


		Api\Cache::setSession('schulmanager', 'notenmanager_modified_gewichtung', $modified_gewichtung);

		$rows = Api\Cache::getSession('schulmanager', 'notenmanager_temp_rows');
	//	if(isset($rows)){

	//	}
		$gewichtungen = Api\Cache::getSession('schulmanager', 'notenmanager_gewichtungen');

		// commit gewichtung
		foreach($modified_gewichtung as $key => $val) {
            $gewichtungen[$key] = $val;
        }
		// reset avg credits
		foreach($rows as $key => $schueler){
			if(is_array($schueler) && array_key_exists('noten', $schueler)){
				$this->resetAVGNoten($schueler);
				schulmanager_lehrer_so::beforeSendToClient($schueler, $gewichtungen);
				// to result
				$this->setAVGNoten($result, $key, $schueler);
			}
		}
		Api\Cache::setSession('schulmanager', 'notenmanager_gewichtungen', $gewichtungen);
		Api\Cache::setSession('schulmanager', 'notenmanager_temp_rows', $rows);
		Api\Json\Response::get()->data($result);
	}

	/**
	 * Ajax call, wenn gew was modified in header checkbox, before beeing saved
	 * Revalidate avg-values with new credits.
	 * @param type $noteKey
	 * @param type $noteVal
	 */
	function ajax_gewAllModified(string $gewKey, int $altb_Val) {
		$result = array();

		// Key/val in session speichern
		$modified_gewichtung = Api\Cache::getSession('schulmanager', 'notenmanager_modified_gewichtung');
		$modified_records = Api\Cache::getSession('schulmanager', 'notenmanager_modified_records');
		if(!is_array($modified_records)){
			// maybe first modified record
			$modified_records = array();
		}

		$rows = Api\Cache::getSession('schulmanager', 'notenmanager_temp_rows');
		foreach($rows as $key => &$schueler){
			$dummy = 0;
			if(is_array($schueler) && array_key_exists('noten', $schueler)){
				$modified_records[$key.'[noten][alt_b][-1][checked]'] = $altb_Val;
				$schueler['noten']['alt_b']['-1']['note'] = $altb_Val;
				$schueler['noten']['alt_b']['-1']['checked'] = $altb_Val;
				if($altb_Val == 1){
					// alt_b wurde geändert und ist nicht nur gelöscht
					$schueler['noten']['alt_b']['-1']['manuell'] = true;
				}
				else{
					// alt_b wurde geändert und ist nicht nur gelöscht
					$schueler['noten']['alt_b']['-1']['manuell'] = false;
				}
			}
			$dummy++;
		}
		Api\Cache::setSession('schulmanager', 'notenmanager_modified_records', $modified_records);

		// commit modified gewichtung
		$gewichtungen = Api\Cache::getSession('schulmanager', 'notenmanager_gewichtungen');
		foreach($modified_gewichtung as $key => $gewval) {
            $gewichtungen[$key] = $gewval;
        }


		//$rows = Api\Cache::getSession('schulmanager', 'notenmanager_temp_rows');
		foreach($rows as $key => &$schueler){
			if(is_array($schueler) && array_key_exists('noten', $schueler)){
				$this->resetAVGNoten($schueler);
				/*$schueler['noten']['alt_b'][-1]['note'] = $altb_Val;

				if(!empty($altb_Val)){
					// alt_b wurde geändert und ist nicht nur gelöscht
					$schueler['noten']['alt_b'][-1]['manuell'] = true;
				}
				else{
					// alt_b wurde geändert und ist nicht nur gelöscht
					$schueler['noten']['alt_b'][-1]['manuell'] = false;
				}*/


				schulmanager_lehrer_so::beforeSendToClient($schueler, $gewichtungen);
				// to result
				$this->setAVGNoten($result, $key, $schueler);
				//TODO: test ob geändert wurde
				//$modified_records[$key]['noten']['alt_b']['-1']['note'] = $altb_Val;
				//$modified_records[$key.'[noten][alt_b][-1][checked]'] = $altb_Val;
				$result[$key.'[noten][alt_b]']['[-1][checked]'] = $altb_Val == 1;
			}
		}

		//Api\Cache::setSession('schulmanager', 'notenmanager_modified_records', $modified_records);
		Api\Cache::setSession('schulmanager', 'notenmanager_temp_rows', $rows);
		Api\Json\Response::get()->data($result);
	}


	/**
	 * set all avg credits to result array
	 * @param type $schueler
	 */
	private function setAVGNoten(array &$result, $key, array $schueler){
		$result[$key.'[noten][glnw_hj_1]'] = array(
			'[-1][note]' => $schueler['noten']['glnw_hj_1'][-1]['note'],
			'avgclass' => $schueler['noten']['glnw_hj_1'][-1]['manuell'] == true ? 'nm_avg_manuell' : 'nm_avg_auto',
		);
		$result[$key.'[noten][klnw_hj_1]'] = array(
			'[-1][note]' => $schueler['noten']['klnw_hj_1'][-1]['note'],
			'avgclass' => $schueler['noten']['klnw_hj_1'][-1]['manuell'] == true ? 'nm_avg_manuell' : 'nm_avg_auto',
		);
		$result[$key.'[noten][schnitt_hj_1]'] = array(
			'[-1][note]' => $schueler['noten']['schnitt_hj_1'][-1]['note'],
			'avgclass' => $schueler['noten']['schnitt_hj_1'][-1]['manuell'] == true ? 'nm_avg_manuell' : 'nm_avg_auto',
		);

		$result[$key.'[noten][note_hj_1]'] = array(
			'[-1][note]' => $schueler['noten']['note_hj_1'][-1]['note'],
			'avgclass' => $schueler['noten']['note_hj_1'][-1]['manuell'] == true ? 'nm_avg_manuell' : 'nm_avg_auto',
		);
		// 2. HJ
		$result[$key.'[noten][glnw_hj_2]'] = array(
			'[-1][note]' => $schueler['noten']['glnw_hj_2'][-1]['note'],
			'avgclass' => $schueler['noten']['glnw_hj_2'][-1]['manuell'] == true ? 'nm_avg_manuell' : 'nm_avg_auto',
		);

		$result[$key.'[noten][klnw_hj_2]'] = array(
			'[-1][note]' => $schueler['noten']['klnw_hj_2'][-1]['note'],
			'avgclass' => $schueler['noten']['klnw_hj_2'][-1]['manuell'] == true ? 'nm_avg_manuell' : 'nm_avg_auto',
		);

		$result[$key.'[noten][schnitt_hj_2]'] = array(
			'[-1][note]' => $schueler['noten']['schnitt_hj_2'][-1]['note'],
			'avgclass' => $schueler['noten']['schnitt_hj_2'][-1]['manuell'] == true ? 'nm_avg_manuell' : 'nm_avg_auto',
		);

		$result[$key.'[noten][note_hj_2]'] = array(
			'[-1][note]' => $schueler['noten']['note_hj_2'][-1]['note'],
			'avgclass' => $schueler['noten']['note_hj_2'][-1]['manuell'] == true ? 'nm_avg_manuell' : 'nm_avg_auto',
		);
	}

	/**
	 * reset all avg credits to empty string
	 * @param type $schueler
	 */
	private function resetAVGNoten(&$schueler){
		// clean up
		if($schueler['noten']['glnw_hj_1']['-1']['manuell']==0){
			$schueler['noten']['glnw_hj_1'][-1]['note'] = '';
		}
		if($schueler['noten']['klnw_hj_1']['-1']['manuell']==0){
			$schueler['noten']['klnw_hj_1'][-1]['note'] = '';
		}
		if($schueler['noten']['schnitt_hj_1']['-1']['manuell']==0){
			$schueler['noten']['schnitt_hj_1'][-1]['note'] = '';
		}
		if($schueler['noten']['note_hj_1']['-1']['manuell']==0){
			$schueler['noten']['note_hj_1'][-1]['note'] = '';
		}
			// 2. HJ
		if($schueler['noten']['glnw_hj_2']['-1']['manuell']==0){
			$schueler['noten']['glnw_hj_2']['-1']['note'] = '';
		}
		if($schueler['noten']['klnw_hj_2']['-1']['manuell']==0){
			$schueler['noten']['klnw_hj_2']['-1']['note'] = '';
		}
		if($schueler['noten']['schnitt_hj_2']['-1']['manuell']==0){
			$schueler['noten']['schnitt_hj_2']['-1']['note'] = '';
		}
		if($schueler['noten']['note_hj_2']['-1']['manuell']==0){
			$schueler['noten']['note_hj_2']['-1']['note'] = '';
		}
		// ende clean up
	}

	function calendar_week(array $content = null,$msg='')
	{
		return $this->calendar_ui->index($content, $msg);

	}



}