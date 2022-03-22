<?php
/**
 * EGroupware Filemanager: mounting GUI
 *
 * @link http://www.egroupware.org/
 * @package filemanager
 * @author Ralf Becker <rb-AT-stylite.de>
 * @copyright (c) 2010-16 by Ralf Becker <rb-AT-stylite.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

use EGroupware\Api;
use EGroupware\Api\Framework;
use EGroupware\Api\Etemplate;
use EGroupware\Stylite\Vfs\Versioning;
use EGroupware\Api\Vfs;

/**
 * Filemanager: mounting GUI
 */
class schulmanager_admin extends schulmanager_ui
{
	/**
	 * Functions callable via menuaction
	 *
	 * @var array
	 */
	public $public_functions = array(
		'config' => true,
		'fsck' => true,
		'asvexport' => true,
		'addUStunde' => true,
		'addCustomFields' => true,
	);

	/**
	 * Autheticated user is setup config user
	 *
	 * @var boolean
	 */
	static protected $is_setup = false;

	/**
	 * Do we have versioning (Versioning\StreamWrapper class) available and with which schema
	 *
	 * @var string
	 */
	protected $versioning;

	/**
	 * Do not allow to (un)mount these
	 *
	 * @var array
	 */
	protected static $protected_path = array('/apps', '/templates');

	/**
	 * Constructor
	 */
	function __construct()
	{
		// make sure user has admin rights
		if (!isset($GLOBALS['egw_info']['user']['apps']['admin']))
		{
			throw new Api\Exception\NoPermission\Admin();
		}
		// sudo handling
		parent::__construct();
		self::$is_setup = Api\Cache::getSession('schulmanager', 'is_setup');

		if (class_exists('EGroupware\Stylite\Vfs\Versioning\StreamWrapper'))
		{
			$this->versioning = Versioning\StreamWrapper::SCHEME;
		}
	}

	function addUStunde($content = null){
		$test = 0;

	}

	/**
	 * Mount GUI
	 *
	 * @param array $content=null
	 * @param string $msg=''
	 */
	public function config(array $content=null, $msg='', $msg_type=null)
	{
		$test = 3;
	}

	public function adminAddCustomFields()
	{
		$test = 3;
	}


}