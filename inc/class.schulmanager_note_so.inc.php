<?php

/**
 * EGroupware Schulmanager - grade - bussiness object
 *
 * @link http://www.egroupware.org
 * @package schulmanager
 * @author Axel Wild <info-AT-wild-solutions.de>
 * @copyright (c) 2022 by info-AT-wild-solutions.de
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

use EGroupware\Api;
/**
 * grade
 * @author axel
 */
class schulmanager_note_so extends Api\Storage {

	public function __construct(){
		parent::__construct('schulmanager', 'egw_schulmanager_note', 'egw_schulmanager_note_extra', 'schulmanager_note');
		$this->set_times('string');
	}

    var $customfields = array();

	/**
	 * saves a resource including extra fields
	 *
	 * @param array $note key => value
	 * @return mixed id of resource if all right, false if failed
	 */
	function saveItem($note)
	{
		$time = date("Y-m-d H:i:s").'.000';//time();
		$kennung = $GLOBALS['egw_info']['user']['account_lid'];

		if(isset($note['note_id']) && !empty($note['note_id'])){
			if(empty($note['note_note'])){
				$this->data = $note;
				if(parent::delete() != 0) return false;
			}
			else{
				$note['note_update_date'] = $time;
				$note['note_update_user'] = $kennung;
				$this->data = $note;
				if(parent::update($note, true) != 0) return false;
			}
		}elseif(!empty($note['note_note'])){
			$note['note_create_date'] = $time;
			$note['note_create_user'] = $kennung;
			$note['note_update_date'] = $time;
			$note['note_update_user'] = $kennung;
			$this->data = $note;
			if(parent::save() != 0) return false;
			$note['note_id'] = $this->data['note_id'];
			$note['note_asv_id'] = $this->createASVID($this->data['note_id']);
			$this->data = $note;
			if(parent::update($note) != 0) return false;


		}
		$id = $this->data['note_id'];

		return $id;
	}

	function createASVID($egwid){
        return 'egw-'.$egwid.'-34ac-8339bf21-1234';
	}

	/**
	 * export ...
	 * @param unknown $xml
	 * @param unknown $in
	 * @param unknown $period 1 => zwischenzeugnis, 2 jahreszeugnis
	 */
	function exportZeugnisnoten2XML(&$xml, &$in, $period = 1){
		$lokales_dm_before = '';
		
		$block = 'note_hj_1';
        if($period == 2){
            $block = 'note_hj_2';
        }

		$noten = array();
		// TODO variable nach Zeugnisart
		$sql = "SELECT
				egw_schulmanager_asv_schueler_stamm.sch_asv_lokales_dm AS lokales_dm,
				egw_schulmanager_asv_schueler_stamm.sch_asv_familienname AS familienname,
				egw_schulmanager_asv_schueler_stamm.sch_asv_rufname AS rufname,
				egw_schulmanager_asv_schueler_schuljahr.ss_asv_schuljahr_id AS schuljahr_id,
				egw_schulmanager_note.note_note AS note,
				egw_schulmanager_note.note_blockbezeichner AS blockbezeichner,
				egw_schulmanager_note.note_index_im_block AS index_im_block,
				egw_schulmanager_note.note_asv_note_manuell AS manuell,
				egw_schulmanager_note.note_update_date AS update_date,
				egw_schulmanager_asv_schuelerfach.sf_asv_unterrichtsfach_id AS fachid,
				egw_schulmanager_asv_schuelerfach.sf_asv_kurzform AS kurzform,
				egw_schulmanager_asv_schule_fach.sf_asv_schluessel AS schluessel
			FROM egw_schulmanager_asv_schueler_stamm
			INNER JOIN egw_schulmanager_asv_schueler_schuljahr ON egw_schulmanager_asv_schueler_schuljahr.ss_asv_schueler_stamm_id = egw_schulmanager_asv_schueler_stamm.sch_asv_id
			INNER JOIN egw_schulmanager_note ON egw_schulmanager_note.note_asv_schueler_schuljahr_id = egw_schulmanager_asv_schueler_schuljahr.ss_asv_id
			INNER JOIN egw_schulmanager_asv_schuelerfach ON egw_schulmanager_asv_schuelerfach.sf_asv_id = egw_schulmanager_note.note_asv_schueler_schuelerfach_id
			INNER JOIN egw_schulmanager_asv_schule_fach ON egw_schulmanager_asv_schule_fach.sf_asv_id = egw_schulmanager_asv_schuelerfach.sf_asv_schule_fach_id
			WHERE egw_schulmanager_note.note_blockbezeichner IN ('$block') AND
				egw_schulmanager_note.note_index_im_block = '-1'
			ORDER BY lokales_dm";
		$rs = $this->db->query($sql, __LINE__, __FILE__, 0, -1);


		foreach($rs as $row){
			$lokales_dm = $row['lokales_dm'];
			$familienname = $row['familienname'];
			$rufname = $row['rufname'];
			$schuljahr_id = $row['schuljahr_id'];
			$note = $row['note'];

			$blockbezeichner = $row['blockbezeichner'];
			$index_im_block = $row['index_im_block'];
			$manuell = $row['manuell'];
			$update_date = DateTime::createFromFormat('Y-m-d H:i:s.000', $row['update_date']);
			$update_date = date_format($update_date, 'd.m.Y');


			$schluessel = $row['schluessel']; // UFACH_0300400500
			$kurzform = $row['kurzform'];

			//$zeugnisart = 'Zwischenzeugnis';
			$zeugnisart = '01'; // 01 Schluessel Zwischenzeugnis

			if($blockbezeichner === 'note_hj_2'){
				//$zeugnisart = 'Jahreszeugnis';;
				$zeugnisart = '25'; // 25 = Schluessel Jahreszeugnis
			}

			if($lokales_dm != $lokales_dm_before){
				// end xml noten/schueler
				if(!empty($lokales_dm_before)){
					$xml->endElement(); // end note
					$xml->endElement(); // end zeugnis
					$xml->endElement(); // end zeugnisse
					$xml->setIndent(--$in);
					$xml->endElement(); // end schuelerin
					$xml->setIndent(--$in);

				}


				$xml->setIndent(++$in);
				$xml->startElement('schuelerin');
				// START IDENTIFIZIERBARE MERKMALE
				$xml->setIndent(++$in);
				$xml->startElement('identifizierende_merkmale');

				$xml->setIndent(++$in);
				$xml->startElement('lokales_differenzierungsmerkmal');
				$xml->writeCdata($lokales_dm);
				$xml->endElement();
				$xml->setIndent($in);
				$xml->startElement('familienname');
				$xml->writeCdata($familienname);
				$xml->endElement();
				$xml->setIndent($in);
				$xml->startElement('rufname');
				$xml->writeCdata($rufname);
				$xml->endElement();
				$xml->setIndent(--$in);

				// END IDENTIFIZIERBARE MERKMALE
				$xml->endElement();
				$xml->setIndent($in);

				// START ZEUGNISSE
				$xml->startElement('zeugnisse');
				$xml->setIndent(++$in);
				$xml->startElement('zeugnis');
				$xml->setIndent(++$in);
				$xml->startElement('zeugnisart');
				$xml->writeCdata($zeugnisart);
				$xml->endElement();
				$xml->setIndent(--$in);

				$xml->startElement('noten');
			}

			$xml->startElement('note');
			$xml->startElement('fach');
			$xml->setIndent(++$in);
			$xml->startElement('schluessel');
			$xml->writeCdata($schluessel);
			$xml->endElement();
			$xml->startElement('kurzform');
			$xml->writeCdata($kurzform);
			$xml->endElement();
			$xml->setIndent(--$in);
			$xml->endElement();

			$xml->startElement('notenwert');
			$xml->writeCdata($note);
			$xml->endElement();

			$xml->startElement('datum');
			$xml->writeCdata($update_date);
			$xml->endElement();

			$xml->endElement();

			$lokales_dm_before = $lokales_dm;
		}

		// end last element
		if(!empty($lokales_dm_before)){
			$xml->endElement(); // end note
			$xml->endElement(); // end zeugnis
			$xml->endElement(); // end zeugnisse
			$xml->setIndent(--$in);
			$xml->endElement(); // end schuelerin
			$xml->setIndent(--$in);
		}
	}

    function truncate(){
        $sql = "TRUNCATE egw_schulmanager_note";
        return $this->db->query($sql, __LINE__, __FILE__);
    }
}
