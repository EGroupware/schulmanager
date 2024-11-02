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

    var $sreport_so;

	/**
	 * Constructor
	 */
	function __construct()
    {
        $this->so = new schulmanager_schueler_so();
        $this->sreport_so = new schulmanager_sreportcontent_so();
    }

	/**
	 * Liefert eine Notenübersicht über alle belegten Fächer
	 * @param type $schueler_id
	 * @param type $rows
	 */
	function getNotenAbstract($schueler, &$rows, $rowid, $short = false){
        $note_so = new schulmanager_note_so();
		$schueler_id = $schueler['nm_st']['st_asv_id'];
        if($short){
            $note_so->getNotenAbstractShort($schueler_id, $rows, $rowid);
        }
        else{
            $note_so->getNotenAbstract($schueler_id, $rows, $rowid);
        }
	}

    /**
     * Übersicht über alle Fächer und die Noten eines Schülers
     * @param $schueler
     * @return void
     */
    function loadSubjectsAndGrades(&$schueler){
        $gew_bo = new schulmanager_note_gew_bo();
        $unterricht_so = new schulmanager_unterricht_so();
        $note_bo = new schulmanager_note_bo();
        $note_so = new schulmanager_note_so();
        // load subjects

        $kgs = array();
        $this->so->getKlassenGruppen($schueler['nm_st']['st_asv_id'], $kgs);

        $faecher = $unterricht_so->loadSchuelerUnterricht($schueler['nm_st']['st_asv_id']);

        // load grades
        $schueler['faecher'] = array();
        $fachIndex = 0;
        foreach($faecher as $key => $fach){
            $schueler['noten'] = $note_bo->getNotenTemplate();
            $fach['noten'] = $note_bo->getNotenTemplate();

            // noten stehen aktuell nach Aufruf beim Schueler UND beim Fach!
            $note_so->loadNotenBySchueler($schueler['nm_st']['st_asv_id'], $schueler, $fach);

            // load weights
            $gewichtungen = array();
            $gew_bo->loadGewichtungen($fach['koppel_id'], $gewichtungen);
            $fach['gew'] = $gewichtungen;
            // get teacher
            $teacher = $unterricht_so->loadUnterrichtLehrer($schueler['nm_st']['st_asv_id'], $fach['untart'], $fach['belegart_id'], $fach['fachid']);
            $fach['teacher'] = $teacher;

            $fach['noten'] = $schueler['noten'];

            $schueler['faecher'][$fachIndex] = $fach;
            $fachIndex++;
        }
    }

    /**
     * Informationen für Zeugnisse und Notenberichte
     * @param $schueler
     * @param $result
     * @return void
     */
    function getEvaluationInfo($schueler, &$result){
        // report data
        $result['zz_gefaehrdung'] ='';
        $result['zz_gefaehrdung_value'] ='';
        $result['zz_modified'] = '';

        $gefaehrdungArr = $this->sreport_so->load($schueler['nm_st']['st_asv_id'], 'key_zz_gefaehrdung');
        if($gefaehrdungArr){
            $gefaehrdung = reset($gefaehrdungArr);

            $wl_gefaehrdung = schulmanager_werteliste_bo::getGefaehrdungList(false);

            if(is_null($gefaehrdung['sr_asv_wert_id'])){
                $result['zz_gefaehrdung_id'] = 0;
            }
            else{
                $result['zz_gefaehrdung_id'] = array_search($gefaehrdung['sr_asv_wert_id'], array_column($wl_gefaehrdung, 'asv_wert_id')) + 1;
            }

            $result['zz_gefaehrdung'] = $gefaehrdung['sr_asv_wert_anzeigeform'];
            $result['zz_gefaehrdung_value'] = $gefaehrdung['sr_value'];
            $result['zz_modified'] = $gefaehrdung['sr_update_date'].' '.$gefaehrdung['sr_update_user'];
        }

        $result['zz_abweisung'] = '';
        $result['zz_abweisung_value'] = '';
        $abweisung = $this->sreport_so->load($schueler['nm_st']['st_asv_id'], 'key_zz_abweisung');
        if($abweisung){
            $result['zz_abweisung'] = reset($abweisung)['sr_value'];
            $result['zz_abweisung_value'] = reset($abweisung)['sr_value'];
        }
    }
}