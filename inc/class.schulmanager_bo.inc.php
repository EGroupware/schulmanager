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
 * Required (!) include, as we use the MCAL_* constants, BEFORE instanciating (and therefore autoloading) the class
 */
require_once(EGW_INCLUDE_ROOT.'/schulmanager/inc/class.schulmanager_lehrer_bo.inc.php');
require_once(EGW_INCLUDE_ROOT.'/schulmanager/inc/class.schulmanager_export_pdf.inc.php');

/**
 * This class is the BO-layer of InfoLog
 */
class schulmanager_bo
{

	/**
	 * lehrer instance of this user
	 * @var schulmanager_lehrer_bo
	 */
	var $myLehrer;

	/**
	 * lehrer bo object
	 * @var schulmanager_lehrer_bo
	 */
	var $klassenFachList = array();

    /**
     * lehrer stamm ids
     * @var array|NULL
     */
	var $lehrerStammIDs;

	/**
	 * Constructor Schulmanager BO
	 */
	function __construct()
	{
		$this->myLehrer = new schulmanager_lehrer_bo();
		$this->lehrerStammIDs = $this->loadMyLehrerStammIDs();
	}

    /**
     * Loads all linked lehrer stamm ids, a teacher can be linked to multiple ids
     * @return array
     */
	function loadMyLehrerStammIDs(){
	    $lehrer_account_so = new schulmanager_lehrer_account_so();
        return $lehrer_account_so->loadLehrerStammIDs($GLOBALS['egw_info']['user']['account_id']);
	}

    /**
     * This method returns all classes in which this teacher is the class teacher or has special rights.
     * @return array
     */
	function getClassLeaderClasses()
	{
		return $this->myLehrer->getClassLeaderClasses();
	}

    /**
     * Returns an array of classes taught
     * @return array
     */
	function getKlassenFachList()
	{
		return $this->myLehrer->getKlasseUnterrichtList();
	}

	/**
	 * This method loads all students in a class with their grades
	 * rows[nm][nm_st]{
	 * @param type $query_in
	 * @param type $rows
	 */
	function getSchuelerNotenList(&$query_in,&$rows)
	{
        $this->myLehrer->getSchuelerNotenList($query_in, $rows);
	}

    /**
     * This method loads all classes of this teacher. This teacher is the class leader, teaches the class or
     * has special access rights
     * @param $rows
     */
    function getSchuelerViewClassList(&$rows){
        $this->myLehrer->getSchuelerViewClassList($rows);
    }

	/**
     * THis method loads all relevant class lists
	 * rows[nm][nm_st]{
	 * @param type $query_in
	 * @param type $rows
	 * @param type $readonlys
	 * @param type $id_only
	 */
	function getKlassenSchuelerList(&$query_in,&$rows,&$readonlys,$id_only=false)
	{
		$this->myLehrer->getKlassenSchuelerList($query_in['filter'], $rows);
	}

    /**
     * Save modified grades
     * @param array $inputInfo
     * @return bool allways true
     */
	function write(array $inputInfo)
	{
		$modified_records = Api\Cache::getSession('schulmanager', 'notenmanager_modified_records');
		$modified_gewichtung = Api\Cache::getSession('schulmanager', 'notenmanager_modified_gewichtung');
		$session_rows = Api\Cache::getSession('schulmanager', 'notenmanager_rows');
		$klassengruppe_asv_id = Api\Cache::getSession('schulmanager', 'filter_klassengruppe_asv_id');
		$schuelerfach_asv_id = Api\Cache::getSession('schulmanager', 'filter_schuelerfach_asv_id');

		// grades eight has been modified
		if(isset($modified_gewichtung)){
			$note_gew_bo = new schulmanager_note_gew_bo();
			foreach ($modified_gewichtung as $key => $gew){
				$block = -1;
				$index = -1;

				$this->splitGewichtungKey($key, $block, $index);
				if(isset($schuelerfach_asv_id)){
					$note_gew_bo->save($gew, $klassengruppe_asv_id, $schuelerfach_asv_id, $block, $index);
				}
			}
		}

		// grade has been changed
		if(isset($modified_records)){
			$note_bo = new schulmanager_note_bo();

			foreach ($modified_records as $key => $value){
				$row_id = -1;
				$block = -1;
				$block_index = -1;

				$this->splitRecordRowKey($key, $row_id, $block, $block_index);

				$schueler_schuljahr_asv_id = $session_rows[$row_id]['nm_st']['sch_schuljahr_asv_id'];
				$note_id = $session_rows[$row_id]['noten'][$block][$block_index]['note_id'];
				$asv_id = $session_rows[$row_id]['noten'][$block][$block_index]['asv_id'];
				if(isset($schueler_schuljahr_asv_id)){
					$note = array(
						'note_asv_schueler_schuljahr_id' => $schueler_schuljahr_asv_id,
						'note_asv_schueler_schuelerfach_id' => $schuelerfach_asv_id,
						'note_blockbezeichner' => $block,
						'note_index_im_block' => $block_index,
						'note_note' => $value['val'],
						'note_asv_id' => $asv_id,
						'note_asv_note_manuell' => $block_index == -1,
					);

					// apply input info
                    $note['note_definition_date'] = $inputInfo['date'];
                    $note['note_description'] = $inputInfo['desc'];
                    $note['note_art'] = $inputInfo['art'];

					if(isset($note_id)){
						$note['note_id'] = $note_id;
					}
					$note_bo->save($note);
				}
			}
		}
		return true;
	}

	/**
	 * This methos creates import file for ASV-Import-Interface
     * https://www.asv.bayern.de/doku/_export/pdf/alle/schnittstellen/xml_sst/zeugnisnotenimport/anleitung_fuer_schulen/start?rev=0
	 * @param int $period 1 = zwischenzeugnis, 2 jahreszeugnis
	 * @return string
	 */
	function asvNotenExport($period){
		$note_so = new schulmanager_note_so();

		$in = 0;
		$xml = new XMLWriter();
		$xml->openMemory();
		//$xml->openURI('php://output');
		$xml->startDocument('1.0', 'UTF-8');
		// START ROOT
		$xml->startElement('zeugnisnoten-import');
		$xml->writeAttribute('xmlns', "http://www.asv.bayern.de/import");
		$xml->writeAttribute('version', "1.0");

		// START SCHULEN
		$xml->setIndent(++$in);
		$xml->startElement('schulen');

		// START SCHULE
		$xml->setIndent(++$in);
		$xml->startElement('schule');

		$xml->setIndent(++$in);
		$xml->startElement('schulnummer');
		$xml->writeCdata('0369');
		$xml->endElement();

		$xml->setIndent(++$in);
		$xml->startElement('schuelerinnen');

		// START SCHUELER
		$note_so->exportZeugnisnoten2XML($xml, $in, $period);
		// END SCHUELER

		$xml->endElement();
		$xml->setIndent(--$in);
		// END SCHULE
		$xml->endElement();
		$xml->setIndent(--$in);
		// END SCHULEN
		$xml->endElement();
		$xml->setIndent(--$in);
		// END ROOT
		$xml->endElement();
		// NED DOCUMENT
		$xml->endDocument();
		return $xml->outputMemory();
	}

	/**
	 * This method extracts key values from ui id-value
	 * @param type $notekey 2[note_xy]
	 * @param type $row_id
	 * @param type $note_id
     * @param type $block_index
	 */
	function splitRecordRowKey($notekey, &$row_id, &$note_id, &$block_index){
		list($row_id, $noten, $note_id, $block_index) = explode("[", $notekey);
		$note_id = substr($note_id, 0, -1);
		$block_index = substr($block_index, 0, -1);
	}

	/**
	 * This method extracts key values from ui id-value
	 * @param type $notekey 2[note_xy]
	 * @param type $row_id
	 * @param type $note_id
	 */
	function splitGewichtungKey($gewkey, &$block, &$index){
		$block = explode('_', $gewkey);
		$index = array_pop($block);
		$block = implode('_', $block);
	}
}