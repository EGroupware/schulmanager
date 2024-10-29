<?php

/**
 * EGroupware Schulmanager - susbtitution of a teacher - bussiness object
 *
 * @link http://www.egroupware.org
 * @package schulmanager
 * @author Axel Wild <info-AT-wild-solutions.de>
 * @copyright (c) 2022 by info-AT-wild-solutions.de
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

use EGroupware\Api;
use EGroupware\Api\Etemplate;

/**
 * This class is the storage-layer (user interface)
 */
class schulmanager_substitution_bo
{
	/**
	 * instance of the bo-class
	 * @var schulmanager_bo
	 */
	var $so;

	/**
	 * Constructor
	 * @return notenmanager_ui
	 */
	function __construct(Etemplate $etemplate = null)
	{		
		$this->so = new schulmanager_substitution_so();
	}

    /**
     * Get list of substitutions
     * @param $query_in
     * @param $rows
     * @return unknown
     */
	function getSubstitutionList(&$query_in,&$rows)
	{
	    return $this->so->getNextmatchRows($query_in['filter'], $rows);
	    
	}

    /**
     * Delete substitution
     * @param $substitution
     * @return array|false|int
     */
	function delete($substitution){
	    if(is_array($substitution)){
	        return $this->so->delete($substitution['id']);
	    }
	    return false;
	}

    /**
     * add substitution
     * @param $asv_kennung_id
     * @param $asv_kennung_orig_id
     * @param $lesson_index
     * @return false
     */
	function add($asv_kennung_id, $asv_kennung_orig_id, $lesson_index){
	    $lesson_list = Api\Cache::getSession('schulmanager', 'substitution_lesson_list');
	    
	    if($lesson_index >= 0 && $lesson_index < count($lesson_list)){
	        $lesson = $lesson_list[$lesson_index];
	        
	        $teacher = $GLOBALS['egw']->accounts->read($asv_kennung_id);
	        $teacher_orig = $GLOBALS['egw']->accounts->read($asv_kennung_orig_id);

            $koppel_id = $lesson['koppel_id'];
            $bezeichnung = $lesson['bezeichnung'];

            $classes = $lesson['klassen'];
            $bezeichnung = $lesson['fach_name'].' ('.implode(',', $classes).') ['.$teacher_orig['account_lastname'].']';
	        return $this->so->saveItem($teacher['account_lid'], $teacher_orig['account_lid'], $koppel_id, $bezeichnung);
	    }
	    return false;
	}
}