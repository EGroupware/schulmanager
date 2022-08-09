<?php
/**
 * EGroupware - Schulmanager - business object
 *
 * @link http://www.egroupware.org
 * @package schulmanager
 * @author Axel Wild <info-AT-wild-solutions.de>
 * @copyright (c) 2022 by Axel Wild <info-AT-wild-solutions.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

use EGroupware\Api;



/**
 * This class is the BO-layer of InfoLog
 */
class schulmanager_mntc_bo
{


    /**
     * Constructor Schulmanager BO
     */
    function __construct()
    {

    }

    /**
     * delete all grades
     * @return void
     */
    function resetAllGrades(){
        $note_so = new schulmanager_note_so();
        $note_so->truncate();

        $note_gew_so = new schulmanager_note_gew_so();
        $note_gew_so->truncate();
    }


}