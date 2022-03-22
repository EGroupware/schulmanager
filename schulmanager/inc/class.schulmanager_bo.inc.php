<?php
/**
 * EGroupware - Schulmanager - Business object
 *

 */

use EGroupware\Api;
use EGroupware\Api\Link;
use EGroupware\Api\Acl;
use EGroupware\Api\Vfs;

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
	//var $lehrer_bo;

	var $klassenFachList = array();


	/**
	 * Constructor Schulmanager BO
	 *
	 * @param int $info_id
	 */
	function __construct()
	{
		$this->myLehrer = new schulmanager_lehrer_bo($GLOBALS['egw_info']['user']['account_lid']);
	}



	function getKlassen()
	{
		return $this->myLehrer->getKlassen();
	}

	function getKlassenFachList()
	{
		//return $this->klassenFachList;
		return $this->myLehrer->getKlasseUnterrichtList();
	}

	/**
	 *
	 * rows[nm][nm_st]{
	 * @param type $query_in
	 * @param type $rows
	 * @param type $readonlys
	 * @param type $id_only
	 * @return type
	 */
	function getSchuelerNotenList(&$query_in,&$rows)
	{
		return $this->myLehrer->getSchuelerNotenList($query_in['filter'], $rows);

	}

	/**
	 * rows[nm][nm_st]{
	 * @param type $query_in
	 * @param type $rows
	 * @param type $readonlys
	 * @param type $id_only
	 * @return type
	 */
	function getKlassenSchuelerList(&$query_in,&$rows,&$readonlys,$id_only=false)
	{
		return $this->myLehrer->getKlassenSchuelerList($query_in['filter'], $rows);

	}



	/**
	 *
	 * @param type $modified_records 0[noten][klnw_hj_1][0] = 2
	 * @param type $ignore_acl
	 * @return boolean
	 */
	function write()
	{
		$modified_records = Api\Cache::getSession('schulmanager', 'notenmanager_modified_records');
		$modified_gewichtung = Api\Cache::getSession('schulmanager', 'notenmanager_modified_gewichtung');
		$session_rows = Api\Cache::getSession('schulmanager', 'notenmanager_rows');
		$klassengruppe_asv_id = Api\Cache::getSession('schulmanager', 'filter_klassengruppe_asv_id');
		$schuelerfach_asv_id = Api\Cache::getSession('schulmanager', 'filter_schuelerfach_asv_id');

		// Gewichtungen wurden geändert
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

		// Noten wurden geändert
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
						'note_note' => $value,
						'note_asv_id' => $asv_id,
						'note_asv_note_manuell' => $block_index == -1
					);
					if(isset($note_id)){
						$note['note_id'] = $note_id;
					}
					//$note_bo = new schulmanager_note_bo($value, $schueler_schuljahr_asv_id, $schuelerfach_asv_id, $klassengruppe_asv_id, $block, $block_index, 1);
					$note_bo->save($note);
				}
			}
		}

		return true;
	}

	function exportJahresZeugnisnotenCheck(){
		$note_so = new schulmanager_note_so();
		return $note_so->exportJahresZeugnisnotenCheck();
	}

	function exportJahresZeugnisnoten2SQL(){
		$note_so = new schulmanager_note_so();
		return $note_so->exportJahresZeugnisnoten2SQL();
	}

	/**
	 * 
	 * @param unknown $period 1 = zwischenzeugnis, 2 jahreszeugnis
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
		//$xml->setIndent(++$in);
		//$xml->startElement('schuelerin');

		// START IDENTIFIZIERENDE MERMALE
		//$xml->startElement('identifizierende_merkmale');

		$note_so->exportZeugnisnoten2XML($xml, $in, $period);

		// END IDENTIFIZIERENDE MERKMALE
		//$xml->endElement();


		// END SCHUELER
		//$xml->endElement();
		//$xml->setIndent(--$in);

		// END SCHUELERINNEN
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
		//return xmlwriter_output_memory($xw);
	}

	/**
	 *
	 * @param type $notekey 2[note_xy]
	 * @param type $row_id
	 * @param type $note_id
	 */
	function splitRecordRowKey($notekey, &$row_id, &$note_id, &$block_index){
		list($row_id, $noten, $note_id, $block_index) = explode("[", $notekey);
		$note_id = substr($note_id, 0, -1);
		$block_index = substr($block_index, 0, -1);
	}

	/**
	 *
	 * @param type $notekey 2[note_xy]
	 * @param type $row_id
	 * @param type $note_id
	 */
	function splitGewichtungKey($gewkey, &$block, &$index){
		$block = explode('_', $gewkey);
		$index = array_pop($block);
		$block = implode('_', $block);
	}


	/**
	 *
	 * @param type $modified_records 0[noten][klnw_hj_1][0] = 2
	 * @param type $ignore_acl
	 * @return boolean
	 */
	function pdfNotenExport()
	{
		$meta = array();
		$session_rows = Api\Cache::getSession('schulmanager', 'notenmanager_rows');
		$filter = Api\Cache::getSession('schulmanager', 'filter');

		$klasse_fach = $this->myLehrer->getKlasseUnterrichtList()[$filter];

		$exportpdf = new schulmanager_export_pdf($session_rows, $klasse_fach, $meta);
		return $exportpdf->createPDFFachNotenListe();
		//$exportpdf->createPDFFachNotenListe();
	}

	function pdfKlassenNotenExport()
	{
		//Api\Cache::setSession('schulmanager', 'klassen_schueler_list', $rows);
		$session_rows = Api\Cache::getSession('schulmanager', 'klassen_schueler_list');
		$filter = Api\Cache::getSession('schulmanager', 'filter');

		$schueler_bo = new schulmanager_schueler_bo();
		$rowid = 0;
		$klasse_id = Api\Cache::getSession('schulmanager', 'klassen_filter_id');

		$klassenArray = $this->getKlassen();
		$klassenname = '';
		if(array_key_exists($klasse_id, $klassenArray)){
			$klassenname = $klassenArray[$klasse_id];
		}

		foreach($session_rows as &$schueler)
		{
			$rowid++;
			$rows = array();
			$schueler_bo->getNotenAbstract($schueler, $rows, $rowid);
			$schueler['faecher'] = $rows;
		}

		$exportpdf = new schulmanager_export_kv_pdf($session_rows, $klassenname);
		return $exportpdf->createPDFklassenNotenListe();
	}
}
