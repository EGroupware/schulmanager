<?php
/**
 * EGroupware Schulmanager
 *
 * @link http://www.egroupware.org
 * @package schulmanager
 * @author Axel Wild <info-AT-wild-solutions.de>
 * @copyright (c) 2022 by info-AT-wild-solutions.de
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */
use EGroupware\Api\Framework;

include_once('./setup/setup.inc.php');
$ts_version = $setup_info['schulmanager']['version'];
unset($setup_info);

$GLOBALS['egw_info'] = array(
	'flags' => array(
		'currentapp'	=> 'schulmanager',
		'noheader'		=> True,
		'nonavbar'		=> True
));
include('../header.inc.php');

if ($ts_version != $GLOBALS['egw_info']['apps']['schulmanager']['version'])
{
    $GLOBALS['egw']->framework->render('<p style="text-align: center; color:red; font-weight: bold;">'.
		lang('Your database is NOT up to date (%1 vs. %2), please run %3setup%4 to update your database.',
		$ts_version,$GLOBALS['egw_info']['apps']['schulmanager']['version'],
		'<a href="../setup/">','</a>')."</p>\n", null, true);
	//Framework::render('<p style="text-align: center; color:red; font-weight: bold;">'.
	//	lang('Your database is NOT up to date (%1 vs. %2), please run %3setup%4 to update your database.',
	//	$ts_version,$GLOBALS['egw_info']['apps']['schulmanager']['version'],
	//	'<a href="../setup/">','</a>')."</p>\n", null, true);
	exit();
}

Framework::redirect_link('/index.php',array('menuaction'=>'schulmanager.schulmanager_ui.index'));
