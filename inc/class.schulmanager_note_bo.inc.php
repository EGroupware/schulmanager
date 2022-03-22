<?php

/**
 * EGroupware Schulmanager - grade bussiness object
 *
 * @link http://www.egroupware.org
 * @package schulmanager
 * @author Axel Wild <info-AT-wild-solutions.de>
 * @copyright (c) 2022 by info-AT-wild-solutions.de
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * Combination of Klassengruppe ans Schuelerfach
 *
 * @author axel
 */
class schulmanager_note_bo {

	/**
	 * Instance of  so object
	 *
	 * @var schulmanager_so
	 */
	var $so;

	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->so = new schulmanager_note_so();
	}

	/**
	 * saves a note
	 *
	 * @param array $schulmanager_noge array with key => value of all needed datas
	 * @return string msg if somthing went wrong; nothing if all right
	 */
	function save($note)
	{
		$this->so->saveItem($note);
	}
}
