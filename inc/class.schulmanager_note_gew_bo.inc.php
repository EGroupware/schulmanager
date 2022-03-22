<?php
/**
 * EGroupware Schulmanager - weight of a grade - bussiness object
 *
 * @link http://www.egroupware.org
 * @package schulmanager
 * @author Axel Wild <info-AT-wild-solutions.de>
 * @copyright (c) 2022 by info-AT-wild-solutions.de
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

require_once(EGW_INCLUDE_ROOT.'/schulmanager/inc/class.schulmanager_note_gew_so.inc.php');

/**
 * This class is the BO-layer of weight of a grade
 */
class schulmanager_note_gew_bo
{
	/**
	 * @var int $debug name of method to debug or level of debug-messages:
	 *	False=Off as higher as more messages you get ;-)
	 *	1 = function-calls incl. parameters to general functions like search, read, write, delete
	 *	2 = function-calls to exported helper-functions like check_perms
	 *	4 = function-calls to exported conversation-functions like date2ts, date2array, ...
	 *	5 = function-calls to private functions
	 */
	var $debug=false;

	/**
	 * Instance of the so lehrer class
	 *
	 * @var schulmanager_note_gew_so
	 */
	var $so;

	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->so = new schulmanager_note_gew_so();
	}

    /**
     * Load all weights by subject and class group
	 * @param type $asv_schueler_schuljahr_id
	 * @param type $asv_schuelerfach_id
	 * @param type $rows
	 */
	function &loadGewichtungen($kg_asv_id, $sf_asv_id, &$gewichtungen){
		$this->so->load($kg_asv_id, $sf_asv_id, $gewichtungen);
	}

	/**
	 * saves a weight
	 *
	 * @param array note
	 * @return string msg if somthing went wrong; nothing if all right
	 */
	function save($gew, $kg_id, $sf_id, $blockbezeichner, $index_im_block)
	{
		$this->so->saveItem($gew, $kg_id, $sf_id, $blockbezeichner, $index_im_block);
	}
}