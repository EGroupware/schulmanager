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

    function loadSubjectsAndGrades(&$schueler){
        $lehrer_so = new schulmanager_lehrer_so();
        $gew_bo = new schulmanager_note_gew_bo();
        // load subjects
        $faecher = array();
        $this->so->getSchuelerFaecherData($schueler['nm_st']['st_asv_id'], $faecher);

        $kgs = array();
        $this->so->getKlassenGruppen($schueler['nm_st']['st_asv_id'], $kgs);

        // load grades
        $schueler['faecher'] = array();
        $fachIndex = 0;
        foreach($faecher as $key => $fach){
            $fach['noten'] = $lehrer_so->getNotenTemplate();
            $lehrer_so->loadNotenBySchuljahrFach($schueler['nm_st']['sch_schuljahr_asv_id'], $fach['sf_asv_id'], $fach);

            // load weights
            $gewichtungen = array();

            $gew_bo->loadGewichtungen($kgs[0], $fach['sf_asv_id'], $gewichtungen);
            $fach['gew'] = $gewichtungen;
            // get teacher
            $teacher = array();
            $lehrer_so->getLehrerByUnterricht($kgs[0], $fach['sf_asv_id'], $teacher);
            $fach['teacher'] = $teacher;

            $schueler['faecher'][$fachIndex] = $fach;
            $fachIndex++;
        }
    }


}
