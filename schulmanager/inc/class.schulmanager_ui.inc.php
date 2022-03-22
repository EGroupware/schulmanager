<?php
/**
 * eGroupWare - resources
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package resources
 * @link http://www.egroupware.org
 * @author Cornelius Weiss <egw@von-und-zu-weiss.de>
 * @author Lukas Weiss <wnz_gh05t@users.sourceforge.net>
 * @version $Id$
 */

use EGroupware\Api;
use EGroupware\Api\Link;
use EGroupware\Api\Framework;
use EGroupware\Api\Acl;
use EGroupware\Api\Etemplate;

/**
 * General userinterface object for resources
 *
 * @package resources
 */
class schulmanager_ui
{
	var $public_functions = array(
		'index'		=> True,
		'edit'		=> True,
		'save'		=> True,
	);

	/**
	 * Constructor
	 *
	 */
	function __construct()
	{
// 		print_r($GLOBALS['egw_info']); die();
		$this->tmpl	= new Etemplate('schulmanager.notenmanager.index');
		$this->bo = new schulmanager_bo();
// 		$this->calui	= CreateObject('resources.ui_calviews');
	}

		/**
	 * List Schulmanager entries
	 *
	 * @param array $content
	 * @param string $msg
	 */
	function index($content='')
	{
		/*if (is_array($content))
		{
			$sessiondata = $content['nm'];
			unset($sessiondata['rows']);
			Api\Cache::setSession('resources', 'index_nm', $sessiondata);

			if (isset($content['btn_delete_selected']))
			{
				foreach($content['nm']['rows'] as $row)
				{
					if($res_id = $row['checkbox'][0])
					{
						$msg .= '<p>'. $this->bo->delete($res_id). '</p><br>';
					}
				}
				return $this->index($msg);
			}
			foreach($content['nm']['rows'] as $row)
			{
				if(isset($row['delete']))
				{
					$res_id = array_search('pressed',$row['delete']);
					return $this->index($this->bo->delete($res_id));
				}
				if(isset($row['view_acc']))
				{
					$sessiondata['filter2'] = array_search('pressed',$row['view_acc']);
					Api\Cache::setSession('resources', 'index_nm', $sessiondata);
					return $this->index();
				}
			}
			if ($content['nm']['action'])
			{
				if (!count($content['nm']['selected']) && !$content['nm']['select_all'])
				{
					$msg = lang('You need to select some entries first!');
				}
				else
				{
					if ($this->action($content['nm']['action'],$content['nm']['selected'],$content['nm']['select_all'],
						$success,$failed,$action_msg,'resources_index_nm',$msg))
					{
						$msg .= lang('%1 resource(s) %2',$success,$action_msg);
					}
					elseif(empty($msg))
					{
						$msg .= lang('%1 resource(s) %2, %3 failed because of insufficent rights !!!',$success,$action_msg,$failed);
					}
					else
					{
						$msg .= lang('%1 resource(s) %2, %3 failed',$success,$action_msg,$failed);
					}
				}
			}
		} else {
			$msg = $content;
		}*/
		$content = array();
		$content['msg'] = $msg ? $msg : $_GET['msg'];

		$content['nm']['get_rows']		= 'schulmanager.notenmanager_ui.get_rows';//'resources.resources_bo.get_rows';
		$content['nm']['no_filter'] 	= False;
		$content['nm']['filter_no_lang'] = true;
		$content['nm']['no_cat']	= true;
	//	$content['nm']['bottom_too']	= true;
		$content['nm']['order']		= 'name';
		$content['nm']['sort']		= 'ASC';
	//	$content['nm']['store_state']	= 'get_rows';
		$content['nm']['row_id']	= 'res_id';
		$content['nm']['favorites'] = true;

	/*	$nm_session_data = Api\Cache::getSession('resources', 'index_nm');
		if($nm_session_data)
		{
			$content['nm'] = $nm_session_data;
		}*/
		$content['nm']['options-filter']= array('5A M','5B Inf', '10A Sm');//array(''=>lang('all categories'))+(array)$this->bo->acl->get_cats(Acl::READ);
		$content['nm']['options-filter2'] = resources_bo::$filter_options;
	/*	if(!$content['nm']['filter2'])
		{
			$content['nm']['filter2'] = key(resources_bo::$filter_options);
		}

		$config = Api\Config::read('resources');
		if($config['history'])
		{
			$content['nm']['options-filter2'][resources_bo::DELETED] = lang('Deleted');
		}

		if($_GET['search']) {
			$content['nm']['search'] = $_GET['search'];
		}
		if($_GET['view_accs_of'])
		{
			$content['nm']['filter2'] = (int)$_GET['view_accs_of'];
		}*/
//		$content['nm']['actions']	= $this->get_actions();
//		$content['nm']['placeholder_actions'] = array('add');

		// check if user is permitted to add resources
		// If they can't read any categories, they won't be able to save it
/*		if(!$this->bo->acl->get_cats(Acl::ADD) || !$this->bo->acl->get_cats(Acl::READ))
		{
			$no_button['add'] = $no_button['nm']['add'] = true;
		}*/
//		$no_button['back'] = true;
//		$GLOBALS['egw_info']['flags']['app_header'] = lang('resources');

//		Framework::includeJS('.','resources','resources');

/*		if($content['nm']['filter2'] > 0)
		{
			$master = $this->bo->so->read(array('res_id' => $content['nm']['filter2']));
			$content['nm']['options-filter2'] = resources_bo::$filter_options + array(
				$master['res_id'] => lang('accessories of') . ' ' . $master['name']
			);
			$content['nm']['get_rows'] 	= SCHULMANAGER_APP.'.notenmanager_ui.get_rows';//'resources.resources_bo.get_rows';
			$GLOBALS['egw_info']['flags']['app_header'] = lang('resources') . ' - ' . lang('accessories of '). ' '. $master['name'] .
				($master['short_description'] ? ' [' . $master['short_description'] . ']' : '');
		}*/
//		$preserv = $content;

//		$options = array();

//		Api\Cache::setSession('resources', 'index_nm', $content['nm']);
		$this->tmpl->read('schulmanager.notenmanager.index');
		return $this->tmpl->exec('schulmanager.notenmanager_ui.index',$content,$sel_options,$no_button,$preserv);
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
		$this->bo->getSchuelerNotenListe($rows, '9A', 'M');
				/*array();
		$rows[0] = array(
			'ts_id'	=> '1',
			'ts_title'	=> 'name',
			'ts_owner'	=> 'short description'
		);
		$rows[1] = array(
			'ts_id'	=> '2',
			'ts_title'	=> 'name2',
			'ts_owner'	=> 'short description'
		);
		$rows[2] = array(
			'ts_id'	=> '3',
			'ts_title'	=> 'name3',
			'ts_owner'	=> 'short description'
		);*/

		return count($rows);
	}

	public static function getSchuljahrXXXX(){
		$config = Api\Config::read('schulmanager');
		return $config['schuljahr'];
	}

	public static function getSchuljahrXXXXYY(){
		$schuljahr = self::getSchuljahrXXXX();
		$schuljahrPlus = $schuljahr + 1;
		return $schuljahr.'/'.substr($schuljahrPlus, -2);
	}

	public static function getSchulname(){
		$config = Api\Config::read('schulmanager');
		return $config['schulname'];
	}
	public static function getSchulnameSub(){
		$config = Api\Config::read('schulmanager');
		return $config['schulname_sub'];
	}
	public static function getHeaderColorRGB(){
		// 0, 112, 192
		$config = Api\Config::read('schulmanager');
		$rgb = $config['color_header'];
		return preg_split('/;|,/', $rgb, 3);
	}
	public static function getLogoImageURL(){
		$config = Api\Config::read('schulmanager');
		return $config['logo_img_url'];
	}
}

