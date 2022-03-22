<?php

/**
 * EGroupware Schulmanager - student - bussiness object
 *
 * @link http://www.egroupware.org
 * @package schulmanager
 * @author Axel Wild <info-AT-wild-solutions.de>
 * @copyright (c) 2022 by info-AT-wild-solutions.de
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */


/**
 * Student
 *
 * @author axel
 */
class schulmanager_schueler_bo{

	/**
	 * Instance of the so schueler
	 *
	 * @var schulmanager_schueler_so
	 */
	var $so;

	/**
	 * Constructor
	 */
	function __construct()
    {
        $this->so = new schulmanager_schueler_so();
    }

	/**
	 * Liefert eine Notenübersicht über alle belegten Fächer
	 * @param type $schueler_id
	 * @param type $rows
	 */
	function getNotenAbstract($schueler, &$rows, $rowid){
		$schueler_schuljahr_id = $schueler['nm_st']['sch_schuljahr_asv_id'];
		$this->so->getNotenAbstract($schueler_schuljahr_id, $rows, $rowid);
	}
}
