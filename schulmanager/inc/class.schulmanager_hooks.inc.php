<?php
/**
 * Schulmanager -  diverse hooks: Admin-, Preferences- and SideboxMenu-Hooks
 *
 * @link http://www.egroupware.org
 * @author Axel Wild
 * @package schulmanager
 * @copyright (c) 2018
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

use EGroupware\Api;
use EGroupware\Api\Link;
use EGroupware\Api\Framework;
use EGroupware\Api\Egw;
use EGroupware\Api\Acl;

if (!defined('SCHULMANAGER_APP'))
{
	define('SCHULMANAGER_APP','schulmanager');
}

/**
 * diverse hooks as static methods
 *
 */
class schulmanager_hooks
{
	/**
	 * Instance of timesheet_bo class
	 *
	 * @var timesheet_bo
	 */
	static $schulmanager_bo;

	function search_link($args)
	{
		return array(
			'add_popup'  => '800x600',
		);
	}

	/**
	 * hooks to build projectmanager's sidebox-menu plus the admin and Api\Preferences sections
	 *
	 * @param string/array $args hook args
	 */
	static function all_hooks($args)
	{
		$appname = SCHULMANAGER_APP;
		$location = is_array($args) ? $args['location'] : $args;

		$config = Api\Config::read('schulmanager');
		$appmode = $config['app_mode'];

//		$this->acl = new resources_acl_bo();

		//echo "<p>ts_admin_prefs_sidebox_hooks::all_hooks(".print_r($args,True).") appname='$appname', location='$location'</p>\n";

		if ($location == 'sidebox_menu')
		{
			// Magic etemplate2 favorites menu (from nextmatch widget)
		//	display_sidebox($appname, lang('Favorites'), Framework\Favorites::list_favorites($appname));

			$title = $GLOBALS['egw_info']['apps']['schulmanager']['title'].' '.lang('Menu');

			$file = Array();

			$file[] = array(
				'text' => 'Notenbuch',
				'icon' => Api\Image::find('schulmanager', 'book'),
				'app'  => 'schulmanager',
				'link' =>  Egw::link('/index.php',array(
					'menuaction' => 'schulmanager.notenmanager_ui.index',
					'ajax' => 'true',
				))
			);

			$file[] = array(
				'text' => 'Klassenübersicht',
				'icon' => Api\Image::find('schulmanager', 'group'),
				'app'  => 'schulmanager',
				'link' =>  Egw::link('/index.php',array(
					'menuaction' => 'schulmanager.notenmanager_ui.klassenview',
					'ajax' => 'true',
				))
			);

			$file[] = array(
				'text' => 'Schulaufgabenplan',
				'icon' => Api\Image::find('schulmanager', 'calendar'),
				'app'  => 'schulmanager',
				'link' =>  Egw::link('/index.php',array(
					'menuaction' => 'schulmanager.schulmanager_cal_ui.index',
					'ajax' => 'true',
				))
			);

			if (self::showModulesWhileDeveloping()) {

			/*	$file[] = array(
					'text' => 'Zeugnisse',
					'icon' => Api\Image::find('schulmanager', 'zeugnis'),
					'app'  => 'schulmanager',
					'link' =>  Egw::link('/index.php',array(
						'menuaction' => 'schulmanager.notenmanager_ui.klassenview',
						'ajax' => 'true',
					))
				);
			*/
			}

			display_sidebox($appname,$title,$file);

			$title = 'Export';

			$file = Array();
			/*$file = Array(
				'Export als PDF-Datei' => Egw::link('/index.php', 'menuaction=schulmanager.notenmanager_ui.exportpdf&appname=' . $appname, '&ajax=true'),
			);*/
			//if($this->acl->get_cats(Acl::ADD))
			if($GLOBALS['egw_info']['user']['apps']['admin'])
			{
				//$file['Export: 2018 JZ SQL'] = Egw::link('/index.php', 'menuaction=schulmanager.notenmanager_ui.exportnotensql&appname=' . $appname, '&ajax=true');
				//$file['Export: 2018 JZ SQL-Check'] = Egw::link('/index.php', 'menuaction=schulmanager.notenmanager_ui.exportnotensqlcheck&appname=' . $appname, '&ajax=true');
				$file['Export: ZZ-Noten nach ASV'] = Egw::link('/index.php', 'menuaction=schulmanager.notenmanager_ui.exportasv_zz&appname=' . $appname, '&ajax=true');
							//$file['Export: ZZ-Noten nach ASV'] = Egw::link('/index.php', 'menuaction=schulmanager.schulmanager_download_ui.exportasv&appname=' . $appname, '&ajax=true');
				$file['Export: JZ-Noten nach ASV'] = Egw::link('/index.php', 'menuaction=schulmanager.notenmanager_ui.exportasv_jz&appname=' . $appname, '&ajax=true');
							//$file['Export: JZ-Noten nach ASV'] = Egw::link('/index.php', 'menuaction=schulmanager.schulmanager_download_ui.exportasv&appname=' . $appname, '&ajax=true');
			}
			display_sidebox($appname,$title,$file);
			
			$title = 'Zuordnungen';			
			$file = Array();
			
			if($GLOBALS['egw_info']['user']['apps']['admin'])
			{
			    $file['Vertretungen'] = Egw::link('/index.php', 'menuaction=schulmanager.schulmanager_substitution_ui.index&appname=' . $appname, '&ajax=true');			    
			}
			display_sidebox($appname,$title,$file);


		}


		if ($GLOBALS['egw_info']['user']['apps']['admin'])
		{
			$file = Array(
				'Site Configuration' => Egw::link('/index.php','menuaction=admin.admin_config.index&appname=' . $appname,'&ajax=true'),
				//'Site Configuration' => Egw::link('/index.php','menuaction=schulmanager.schulmanager_admin.config&appname=' . $appname,'&ajax=true'),
				//'Stundenraster' => Egw::link('/index.php','menuaction=schulmanager.schulmanager_admin.stundenraster&appname=' . $appname,'&ajax=true'),
				//'Schulferien/Feiertage' => Egw::link('/index.php','menuaction=schulmanager.schulmanager_admin.ferien&appname=' . $appname,'&ajax=true'),
				//'Export: Noten nach ASV' => Egw::link('/index.php', 'menuaction=schulmanager.notenmanager_ui.asvexport&appname=' . $appname, '&ajax=true'),
			);
			if ($location == 'admin')
			{
				display_section($appname,$file);
			}
			else
			{
				display_sidebox($appname,lang('Admin'),$file);
			}
		}
	}

	static function showModulesWhileDeveloping(){
		$config = Api\Config::read('schulmanager');
		$developer = $config['schulmanager_developer'];
		$user = $GLOBALS['egw_info']['user'];

		$appmode = $config['app_mode'];

		return in_array($user['account_id'], $developer) && $appmode == 'develop';
	}

	/**
	 * populates $GLOBALS['settings'] for the Api\Preferences
	 */
	static function settings()
	{
		$settings = array();

		/*if ($GLOBALS['egw_info']['user']['apps']['importexport'])
		{
			$definitions = new importexport_definitions_bo(array(
				'type' => 'export',
				'application' => 'resources'
			));
			$options = array(
				'~nextmatch~'	=>	lang('Old fixed definition')
			);
			$default_def = 'export-resources';
			foreach ((array)$definitions->get_definitions() as $identifier)
			{
				try
				{
					$definition = new importexport_definition($identifier);
				}
				catch (Exception $e)
				{
					// permission error
					continue;
				}
				if ($title = $definition->get_title())
				{
					$options[$title] = $title;
				}
				unset($definition);
			}
			$settings['nextmatch-export-definition'] = array(
				'type'   => 'select',
				'values' => $options,
				'label'  => 'Export definition to use for nextmatch export',
				'name'   => 'nextmatch-export-definition',
				'help'   => lang('If you specify an export definition, it will be used when you export'),
				'run_lang' => false,
				'xmlrpc' => True,
				'admin'  => False,
				'default'=> isset($options[$default_def]) ? $default_def : false,
			);
		}*/
		return $settings;
	}

	/**
	 * ACL rights and labels used by Calendar
	 *
	 * @param string|array string with location or array with parameters incl. "location", specially "owner" for selected acl owner
	 */
	public static function acl_rights($params)
	{
		unset($params);	// not used, but required by function signature

		return array(
			Acl::READ    => 'read',
			Acl::EDIT    => 'edit',
			Acl::DELETE  => 'delete',
		);
	}

	/**
	 * Hook to tell framework we use standard categories method
	 *
	 * @param string|array $data hook-data or location
	 * @return boolean
	 */
	public static function categories($data)
	{
		unset($data);	// not used, but required by function signature

		return true;
	}
}
