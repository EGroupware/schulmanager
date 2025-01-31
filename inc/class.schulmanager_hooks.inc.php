<?php

/**
 * Schulmanager -  diverse hooks: Admin-, Preferences- and SideboxMenu-Hooks
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

		if ($location == 'sidebox_menu')
		{
			$title = $GLOBALS['egw_info']['apps']['schulmanager']['title'].' '.lang('Menu');

			$file = Array();
			$file[] = array(
				'text' => 'Notenbuch - Klassenansicht',
				'icon' => Api\Image::find('schulmanager', 'book'),
				'app'  => 'schulmanager',
				'link' =>  Egw::link('/index.php',array(
					'menuaction' => 'schulmanager.schulmanager_ui.index',
					'ajax' => 'true',
				))
			);

            //if (self::showModulesWhileDeveloping()) {
            $file[] = array(
                'text' => 'Notenbuch - Detailansicht',
                'icon' => Api\Image::find('schulmanager', 'book'),
                'app' => 'schulmanager',
                'link' => Egw::link('/index.php', array(
                    'menuaction' => 'schulmanager.schulmanager_ui.notenDetails',
                    'ajax' => 'true',
                ))
            );
            //}
            if (self::showModulesWhileDeveloping()) {
                $file[] = array(
                    'text' => 'Klassenübersicht',
                    'icon' => Api\Image::find('schulmanager', 'group'),
                    'app' => 'schulmanager',
                    'link' => Egw::link('/index.php', array(
                        'menuaction' => 'schulmanager.schulmanager_ui.klassenview',
                        'ajax' => 'true',
                    ))
                );
            }

            $file[] = array(
                'text' => 'Schüler',
                'icon' => Api\Image::find('schulmanager', 'student'),
                'app'  => 'schulmanager',
                'link' =>  Egw::link('/index.php',array(
                    'menuaction' => 'schulmanager.schulmanager_ui.schuelerview',
                    'ajax' => 'true',
                ))
            );

            $file[] = array(
                'text' => 'Placeholders',
                'app'  => 'schulmanager',
                'link' =>  Egw::link('/index.php','menuaction=schulmanager.schulmanager_merge.show_replacements'),
            );


            if (self::showModulesWhileDeveloping()) {
                $file[] = array(
                    'text' => 'devtest',
                    'app'  => 'schulmanager',
                    'link' =>  Egw::link('/index.php',array(
                        'menuaction' => 'schulmanager.schulmanager_ui.devtest',
                        'ajax' => 'true',
                    ))
                );
            /*    $file[] = array(
                    'text' => 'Anwesenheit',
                    'icon' => Api\Image::find('schulmanager', 'presence'),
                    'app'  => 'schulmanager',
                    'link' =>  Egw::link('/index.php',array(
                        'menuaction' => 'schulmanager.schulmanager_ui.presence',
                        'ajax' => 'true',
                    ))
                );

                $file[] = array(
                    'text' => 'Frühwarn-Radar',
                    'icon' => Api\Image::find('schulmanager', 'radar'),
                    'app'  => 'schulmanager',
                    'link' =>  Egw::link('/index.php',array(
                        'menuaction' => 'schulmanager.schulmanager_ui.radar',
                        'ajax' => 'true',
                    ))
                );
            */
			}


            if($config['show_exam_calendar']){
                $file[] = array(
                    'text' => 'Schulaufgabenplan',
                    'icon' => Api\Image::find('schulmanager', 'calendar'),
                    'app'  => 'schulmanager',
                    'link' =>  Egw::link('/index.php','menuaction=schulmanager.schulmanager_cal_ui.index', '&ajax=true'),
                );
            }

			display_sidebox($appname,$title,$file);


			$title = 'Export';
			$file = Array();

			if($GLOBALS['egw_info']['user']['apps']['admin'])
			{
				$file['Export: ZZ-Noten nach ASV'] = Egw::link('/index.php', 'menuaction=schulmanager.schulmanager_ui.exportasv_zz&appname=' . $appname, '&ajax=true');
				$file['Export: JZ-Noten nach ASV'] = Egw::link('/index.php', 'menuaction=schulmanager.schulmanager_ui.exportasv_jz&appname=' . $appname, '&ajax=true');
			}
			display_sidebox($appname,$title,$file);
			
			$title = 'Zuordnungen';			
			$file = Array();
			
			if($GLOBALS['egw_info']['user']['apps']['admin'])
			{
			    $file['Vertretungen'] = Egw::link('/index.php', 'menuaction=schulmanager.schulmanager_substitution_ui.index&appname=' . $appname, '&ajax=true');
                $file['Lehrer'] = Egw::link('/index.php', 'menuaction=schulmanager.schulmanager_substitution_ui.accounts&appname=' . $appname, '&ajax=true');
            }
			display_sidebox($appname,$title,$file);
		}

		if ($GLOBALS['egw_info']['user']['apps']['admin'])
		{
            $file = Array();
            $file['Wartungsaufgaben'] = Egw::link('/index.php',
                    'menuaction=schulmanager.schulmanager_mntc_ui.index',
                    '&ajax=true',
                );
			$file['Site Configuration'] = Egw::link('/index.php','menuaction=admin.admin_config.index&appname=' . $appname,'&ajax=true');


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

    /**
     * Show modules under developing only for special users
     * @return bool
     */
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
		return $settings;
	}

	/**
	 * ACL rights and labels used by Calendar
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
