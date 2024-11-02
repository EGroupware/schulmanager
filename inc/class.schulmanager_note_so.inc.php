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
			$this->data = $note;
			if(parent::update($note) != 0) return false;
		}
		$id = $this->data['note_id'];

		return $id;
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

    /**
     * @param $schueler_id
     * @param $schueler
     * @param $fach
     * @return void
     */
    function loadNotenBySchueler($schueler_id, &$schueler, $fach){
        $fach_id = $fach['fach_id'];
        $belegart_id = $fach['belegart_id'];
        $jahrgangsstufe_id = $schueler['klasse']['jahrgangsstufe_id'];
        $sql = "SELECT
			egw_schulmanager_note.note_id AS note_id,
			egw_schulmanager_note.note_blockbezeichner AS blockbezeichner,
			egw_schulmanager_note.note_index_im_block AS index_im_block,
			egw_schulmanager_note.note_note AS note,
			egw_schulmanager_note.note_create_date AS create_date,
			egw_schulmanager_note.note_create_user AS create_user,
			egw_schulmanager_note.note_update_date AS update_date,
			egw_schulmanager_note.note_update_user AS update_user,
			egw_schulmanager_note.note_asv_note_manuell AS asv_note_manuell,
            egw_schulmanager_note.note_art AS art,
            egw_schulmanager_note.note_definition_date AS definition_date,
            egw_schulmanager_note.note_description AS description,
            egw_schulmanager_note.koppel_id AS koppel_id,
            egw_schulmanager_note.schueler_id AS schueler_id,
            egw_schulmanager_note.fach_id AS fach_id,
            egw_schulmanager_note.belegart_id AS belegart_id,
            egw_schulmanager_note.jahrgangsstufe_id AS jahrgangsstufe_id
		FROM egw_schulmanager_note
			WHERE egw_schulmanager_note.schueler_id='".$schueler_id."'
			AND egw_schulmanager_note.fach_id='".$fach_id."'
			AND egw_schulmanager_note.belegart_id='".$belegart_id."'
			AND egw_schulmanager_note.jahrgangsstufe_id='".$jahrgangsstufe_id."'";

        $rs = $this->db->query($sql, __LINE__, __FILE__, 0, -1);

        foreach($rs as $row) {
            $note_id = $row['note_id'];
            $blockbezeichner = $row['blockbezeichner'];
            $index_im_block = $row['index_im_block'];
            $note = $row['note'];
            $manuell = $row['asv_note_manuell'];
            $schueler['noten'][$blockbezeichner][$index_im_block]['note'] = str_replace(",", ".", $note);
            $schueler['noten'][$blockbezeichner][$index_im_block]['note_id'] = $note_id;
            $schueler['noten'][$blockbezeichner][$index_im_block]['update_date'] = substr($row['update_date'],  0,10);
            $schueler['noten'][$blockbezeichner][$index_im_block]['update_user'] = $row['update_user'];
            $schueler['noten'][$blockbezeichner][$index_im_block]['art'] = $row['art'];

            if(isset($row['definition_date'])){
                $schueler['noten'][$blockbezeichner][$index_im_block]['definition_date'] = date("Y-m-d", $row['definition_date']);
            }

            $schueler['noten'][$blockbezeichner][$index_im_block]['description'] = $row['description'];

            if($index_im_block == -1){
                if($manuell > 0){
                    $schueler['noten'][$blockbezeichner][$index_im_block]['manuell'] = '1';
                    $schueler['noten'][$blockbezeichner]['avgclass'] = 'nm_avg_manuell';
                }
                else{
                    $schueler['noten'][$blockbezeichner][$index_im_block]['manuell'] = '0';
                    $schueler['noten'][$blockbezeichner]['avgclass'] = 'nm_avg_auto';
                }
            }
        }
    }


    /**
     * Creates abstract of grades
     * @param $schueler_id
     * @param $rows
     * @param $rowid
     * @return mixed
     * @throws Api\Db\Exception\InvalidSql
     */
    function &getNotenAbstract($schueler_id, &$rows, $rowid){

        $sql = "select
			egw_schulmanager_asv_schuelerfach.sf_asv_id AS asv_id,
			egw_schulmanager_asv_schuelerfach.sf_asv_kurzform AS asv_kurzform,
			egw_schulmanager_asv_schuelerfach.sf_asv_anzeigeform AS asv_anzeigeform,
            egw_schulmanager_unterrichtselement2_schueler.belegart_id AS belegart_id,
			egw_schulmanager_unterrichtselement2.bezeichnung AS unt_bezeichnung,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'alt_b' THEN egw_schulmanager_note.note_note END) AS alt_b,
			-- 1. HJ
			GROUP_CONCAT(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'glnw_hj_1' AND egw_schulmanager_note.note_index_im_block >= 0 THEN egw_schulmanager_note.note_note END ORDER BY egw_schulmanager_note.note_index_im_block SEPARATOR ' | ') AS glnw_hj_1,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'glnw_hj_1' AND egw_schulmanager_note.note_index_im_block = -1 THEN egw_schulmanager_note.note_note END) AS glnw_hj_1_avg,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'glnw_hj_1' AND egw_schulmanager_note.note_index_im_block = -1 AND egw_schulmanager_note.note_asv_note_manuell = '1' THEN '1' END) AS glnw_hj_1_avg_manuell,

		    GROUP_CONCAT(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'klnw_hj_1' AND egw_schulmanager_note.note_index_im_block >= 0 THEN egw_schulmanager_note.note_note END ORDER BY egw_schulmanager_note.note_index_im_block SEPARATOR ' | ') AS klnw_hj_1,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'klnw_hj_1' AND egw_schulmanager_note.note_index_im_block = -1 THEN egw_schulmanager_note.note_note END) AS klnw_hj_1_avg,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'klnw_hj_1' AND egw_schulmanager_note.note_index_im_block = -1 AND egw_schulmanager_note.note_asv_note_manuell = '1' THEN '1' END) AS klnw_hj_1_avg_manuell,

			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'schnitt_hj_1' THEN egw_schulmanager_note.note_note END) AS schnitt_hj_1,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'schnitt_hj_1' AND egw_schulmanager_note.note_asv_note_manuell = '1' THEN '1' END) AS schnitt_hj_1_manuell,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'note_hj_1' THEN egw_schulmanager_note.note_note END) AS note_hj_1,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'note_hj_1' AND egw_schulmanager_note.note_asv_note_manuell = '1' THEN '1' END) AS note_hj_1_manuell,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'm_hj_1' THEN egw_schulmanager_note.note_note END) AS m_hj_1,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'v_hj_1' THEN egw_schulmanager_note.note_note END) AS v_hj_1,
			-- 2. HJ
			GROUP_CONCAT(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'glnw_hj_2' AND egw_schulmanager_note.note_index_im_block >= 0 THEN egw_schulmanager_note.note_note END ORDER BY egw_schulmanager_note.note_index_im_block SEPARATOR ' | ') AS glnw_hj_2,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'glnw_hj_2' AND egw_schulmanager_note.note_index_im_block = -1 THEN egw_schulmanager_note.note_note END) AS glnw_hj_2_avg,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'glnw_hj_2' AND egw_schulmanager_note.note_index_im_block = -1 AND egw_schulmanager_note.note_asv_note_manuell = '1' THEN '1' END) AS glnw_hj_2_avg_manuell,

			GROUP_CONCAT(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'klnw_hj_2' AND egw_schulmanager_note.note_index_im_block >= 0 THEN egw_schulmanager_note.note_note END ORDER BY egw_schulmanager_note.note_index_im_block SEPARATOR ' | ') AS klnw_hj_2,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'klnw_hj_2' AND egw_schulmanager_note.note_index_im_block = -1 THEN egw_schulmanager_note.note_note END) AS klnw_hj_2_avg,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'klnw_hj_2' AND egw_schulmanager_note.note_index_im_block = -1 AND egw_schulmanager_note.note_asv_note_manuell = '1' THEN '1' END) AS klnw_hj_2_avg_manuell,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'schnitt_hj_2' THEN egw_schulmanager_note.note_note END) AS schnitt_hj_2,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'schnitt_hj_2' AND egw_schulmanager_note.note_asv_note_manuell = '1' THEN '1' END) AS schnitt_hj_2_manuell,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'note_hj_2' THEN egw_schulmanager_note.note_note END) AS note_hj_2,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'note_hj_2' AND egw_schulmanager_note.note_asv_note_manuell = '1' THEN '1' END) AS note_hj_2_manuell,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'm_hj_2' THEN egw_schulmanager_note.note_note END) AS m_hj_2,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'v_hj_2' THEN egw_schulmanager_note.note_note END) AS v_hj_2

			FROM egw_schulmanager_unterrichtselement2_schueler

			INNER JOIN egw_schulmanager_asv_schueler_schuljahr ON egw_schulmanager_asv_schueler_schuljahr.ss_asv_schueler_stamm_id = egw_schulmanager_unterrichtselement2_schueler.schueler_id
			INNER JOIN egw_schulmanager_asv_klassengruppe ON egw_schulmanager_asv_klassengruppe.kg_asv_id = egw_schulmanager_asv_schueler_schuljahr.ss_asv_klassengruppe_id
			INNER JOIN egw_schulmanager_unterrichtselement2 ON egw_schulmanager_unterrichtselement2.koppel_id = egw_schulmanager_unterrichtselement2_schueler.koppel_id
		        AND egw_schulmanager_unterrichtselement2.unt_id = egw_schulmanager_unterrichtselement2_schueler.unt_id
			    AND ((egw_schulmanager_unterrichtselement2.kg_id = egw_schulmanager_asv_klassengruppe.kg_asv_id)
			              XOR (egw_schulmanager_unterrichtselement2.kg_id = '' AND egw_schulmanager_unterrichtselement2_schueler.belegart_id <> ''))
			INNER JOIN egw_schulmanager_asv_schuelerfach ON egw_schulmanager_asv_schuelerfach.sf_asv_id = egw_schulmanager_unterrichtselement2.fach_id

            LEFT JOIN egw_schulmanager_config ON egw_schulmanager_asv_schuelerfach.sf_asv_kurzform = egw_schulmanager_config.cnf_val
			LEFT JOIN egw_schulmanager_note ON egw_schulmanager_note.koppel_id = egw_schulmanager_unterrichtselement2_schueler.koppel_id 
			    AND egw_schulmanager_note.schueler_id = egw_schulmanager_unterrichtselement2_schueler.schueler_id
			    AND egw_schulmanager_note.fach_id = egw_schulmanager_unterrichtselement2.fach_id

			WHERE egw_schulmanager_unterrichtselement2_schueler.schueler_id = '".$schueler_id."'
				AND (egw_schulmanager_unterrichtselement2_schueler.untart = 'P' OR egw_schulmanager_unterrichtselement2_schueler.untart = 'B')

			GROUP BY egw_schulmanager_asv_schuelerfach.sf_asv_id, egw_schulmanager_unterrichtselement2_schueler.belegart_id, egw_schulmanager_asv_schuelerfach.sf_asv_kurzform, egw_schulmanager_asv_schuelerfach.sf_asv_anzeigeform, egw_schulmanager_config.cnf_extra

			ORDER BY egw_schulmanager_config.cnf_extra, egw_schulmanager_asv_schuelerfach.sf_asv_kurzform";

        $rs = $this->db->query($sql, __LINE__, __FILE__, 0, -1);

        $id = ($rowid +1) * 100;
        foreach($rs as $row){
            $schueler = array(
                'rownr'	    => '',
                'nm_id'		=> $rowid * 100 + $id,
                'nm_st'		=> array(
                    'st_asv_id'			  => '',
                    'sch_schuljahr_asv_id' => '',
                    'st_asv_familienname' => '',
                    'st_asv_rufname'	  => $row['asv_anzeigeform']
                ),
                'fachname' => $row['asv_anzeigeform'],
                'noten'		=> array(
                    'alt_b'				  => array(
                        -1 => array(
                            'note'   => $row['alt_b'],
                            'note_id'=> '',
                            'img' => '',
                            'checked' => false
                        ),
                    ),
                    'glnw_hj_1'			  => array(
                        'concat' => $row['glnw_hj_1'],
                        -1 => array(
                            'note'   =>$row['glnw_hj_1_avg'],
                            'manuell'=>$row['glnw_hj_1_avg_manuell'],
                        ),
                    ),
                    'klnw_hj_1'			  => array(
                        'concat' => $row['klnw_hj_1'],
                        -1 => array(
                            'note'   =>$row['klnw_hj_1_avg'],
                            'manuell'=>$row['klnw_hj_1_avg_manuell'],
                        ),
                    ),
                    'schnitt_hj_1'		  => array(
                        -1 => array(
                            'note'   =>$row['schnitt_hj_1'],
                            'manuell'=>$row['schnitt_hj_1_manuell'],
                        ),
                    ),
                    'note_hj_1'			  => array(
                        -1 => array(
                            'note'   =>$row['note_hj_1'],
                            'manuell'=>$row['note_hj_1_manuell'],
                        ),
                    ),
                    'm_hj_1'			  => array(
                        -1 => array(
                            'note'   =>$row['m_hj_1'],
                        ),
                    ),
                    'v_hj_1'			  => array(
                        -1 => array(
                            'note'   =>$row['v_hj_1'],
                        ),
                    ),
                    // 2. HJ
                    'glnw_hj_2'			  => array(
                        'concat' => $row['glnw_hj_2'],
                        -1 => array(
                            'note'   =>$row['glnw_hj_2_avg'],
                            'manuell'=>$row['glnw_hj_2_avg_manuell'],
                        ),
                    ),
                    'klnw_hj_2'			  => array(
                        'concat' => $row['klnw_hj_2'],
                        -1 => array(
                            'note'   =>$row['klnw_hj_2_avg'],
                            'manuell'=>$row['klnw_hj_2_avg_manuell'],
                        ),
                    ),
                    'schnitt_hj_2'		  => array(
                        -1 => array(
                            'note'   =>$row['schnitt_hj_2'],
                            'manuell'=>$row['schnitt_hj_2_manuell'],
                        ),
                    ),
                    'note_hj_2'			  => array(
                        -1 => array(
                            'note'   =>$row['note_hj_2'],
                            'manuell'=>$row['note_hj_2_manuell'],
                        ),
                    ),
                    'm_hj_2'			  => array(
                        -1 => array(
                            'note'   =>$row['m_hj_2'],
                        ),
                    ),
                    'v_hj_2'			  => array(
                        -1 => array(
                            'note'   =>$row['v_hj_2'],
                        ),
                    ),
                ),
                'is_par' => 0
            );

            if(isset($row['belegart_id']) && strlen($row['belegart_id']) > 0){
                $belegart = schulmanager_werteliste_bo::getBelegart($row['belegart_id'], 'kurzform');
                $schueler['fachname'] .= ' / '.$belegart;
                $schueler['nm_st']['st_asv_rufname'] = $schueler['fachname'];
            }

            $this->beforeSendToClient($schueler);
            $rows[$id] = $schueler;
            $id++;
        }
        return $rows;
    }

    /**
     * set altb values
     * @param type $schueler
     */
    function beforeSendToClient(&$schueler){
        if(isset($schueler['noten'])){

            // alternative Berechnung
            if($schueler['noten']['alt_b'][-1]['note'] === '1'){
                $schueler['noten']['alt_b'][-1]['checked'] = true;
                $schueler['noten']['alt_b'][-1]['img'] = 'check.svg';
            }
            else{
                $schueler['noten']['alt_b'][-1]['checked'] = false;
                $schueler['noten']['alt_b'][-1]['img'] = '';
                $schueler['noten']['alt_b'][-1]['label'] = '';
            }
        }
    }

    /**
     * Creates a short abstract of grades
     * @param $schueler_schuljahr_id
     * @param $rows
     * @param $rowid
     * @return mixed
     * @throws Api\Db\Exception\InvalidSql
     */
    function &getNotenAbstractShort($schueler_id, &$rows, $rowid){
        $sql = "select
			egw_schulmanager_asv_schuelerfach.sf_asv_id AS asv_id,
			egw_schulmanager_asv_schuelerfach.sf_asv_kurzform AS asv_kurzform,
			egw_schulmanager_asv_schuelerfach.sf_asv_anzeigeform AS asv_anzeigeform,
			egw_schulmanager_unterrichtselement2_schueler.belegart_id AS belegart_id,
			egw_schulmanager_unterrichtselement2.bezeichnung AS unt_bezeichnung,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'alt_b' THEN egw_schulmanager_note.note_note END) AS alt_b,
			GROUP_CONCAT(CASE WHEN (egw_schulmanager_note.note_blockbezeichner = 'klnw_hj_1' OR egw_schulmanager_note.note_blockbezeichner = 'klnw_hj_2') AND egw_schulmanager_note.note_index_im_block >= 0 THEN egw_schulmanager_note.note_note END ORDER BY egw_schulmanager_note.note_index_im_block SEPARATOR ' | ') AS klnw,
			GROUP_CONCAT(CASE WHEN (egw_schulmanager_note.note_blockbezeichner = 'glnw_hj_1' OR egw_schulmanager_note.note_blockbezeichner = 'glnw_hj_2') AND egw_schulmanager_note.note_index_im_block >= 0 THEN egw_schulmanager_note.note_note END ORDER BY egw_schulmanager_note.note_index_im_block SEPARATOR ' | ') AS glnw,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'klnw_hj_2' AND egw_schulmanager_note.note_index_im_block = -1 THEN egw_schulmanager_note.note_note END) AS klnw_avg,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'glnw_hj_2' AND egw_schulmanager_note.note_index_im_block = -1 THEN egw_schulmanager_note.note_note END) AS glnw_avg,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'schnitt_hj_2' THEN egw_schulmanager_note.note_note END) AS schnitt,
			MAX(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'note_hj_2' THEN egw_schulmanager_note.note_note END) AS note
			FROM egw_schulmanager_unterrichtselement2_schueler
			INNER JOIN egw_schulmanager_asv_schueler_schuljahr ON egw_schulmanager_asv_schueler_schuljahr.ss_asv_schueler_stamm_id = egw_schulmanager_unterrichtselement2_schueler.schueler_id
			INNER JOIN egw_schulmanager_asv_klassengruppe ON egw_schulmanager_asv_klassengruppe.kg_asv_id = egw_schulmanager_asv_schueler_schuljahr.ss_asv_klassengruppe_id
			INNER JOIN egw_schulmanager_unterrichtselement2 ON egw_schulmanager_unterrichtselement2.koppel_id = egw_schulmanager_unterrichtselement2_schueler.koppel_id
			    AND egw_schulmanager_unterrichtselement2.unt_id = egw_schulmanager_unterrichtselement2_schueler.unt_id
			    AND ((egw_schulmanager_unterrichtselement2.kg_id = egw_schulmanager_asv_klassengruppe.kg_asv_id)
			              XOR (egw_schulmanager_unterrichtselement2.kg_id = '' AND egw_schulmanager_unterrichtselement2_schueler.belegart_id <> ''))
			INNER JOIN egw_schulmanager_asv_schuelerfach ON egw_schulmanager_asv_schuelerfach.sf_asv_id = egw_schulmanager_unterrichtselement2.fach_id
            LEFT JOIN egw_schulmanager_config ON egw_schulmanager_asv_schuelerfach.sf_asv_kurzform = egw_schulmanager_config.cnf_val
			LEFT JOIN egw_schulmanager_note ON egw_schulmanager_note.koppel_id = egw_schulmanager_unterrichtselement2_schueler.koppel_id
			    AND egw_schulmanager_note.schueler_id = egw_schulmanager_unterrichtselement2_schueler.schueler_id
			    AND egw_schulmanager_note.fach_id = egw_schulmanager_unterrichtselement2.fach_id
			WHERE egw_schulmanager_unterrichtselement2_schueler.schueler_id = '".$schueler_id."'
				AND (egw_schulmanager_unterrichtselement2_schueler.untart = 'P' OR egw_schulmanager_unterrichtselement2_schueler.untart = 'B')
			GROUP BY egw_schulmanager_asv_schuelerfach.sf_asv_id, egw_schulmanager_unterrichtselement2_schueler.belegart_id, egw_schulmanager_asv_schuelerfach.sf_asv_kurzform, egw_schulmanager_asv_schuelerfach.sf_asv_anzeigeform, egw_schulmanager_config.cnf_extra
			ORDER BY egw_schulmanager_config.cnf_extra, egw_schulmanager_asv_schuelerfach.sf_asv_kurzform";

        $rs = $this->db->query($sql, __LINE__, __FILE__, 0, -1);

        $id = ($rowid +1) * 100;
        foreach($rs as $row){
            //$rows[] = array(
            $schueler = array(
                'rownr'	    => '',
                'nm_id'		=> $rowid * 100 + $id,
                'nm_st'		=> array(
                    'st_asv_id'			  => '',
                    'sch_schuljahr_asv_id' => '',
                    'st_asv_familienname' => '',
                    'st_asv_rufname'	  => $row['asv_anzeigeform']
                ),
                'fachname' => $row['asv_anzeigeform'],
                'noten'		=> array(
                    'alt_b'	=> $row['alt_b'] ? '1:1' : '2:1',
                    'glnw'	=> $row['glnw'],
                    'klnw'	=> $row['klnw'],
                    'glnw_avg'	=> str_replace('.', ',', $row['glnw_avg']),
                    'klnw_avg'	=> str_replace('.', ',', $row['klnw_avg']),
                    'schnitt'	=> str_replace('.', ',', $row['schnitt']),
                    'note'	=> $row['note'],
                ),
                'is_par' => 0
            );

            if(isset($row['belegart_id']) && strlen($row['belegart_id']) > 0){
                $belegart = schulmanager_werteliste_bo::getBelegart($row['belegart_id'], 'kurzform');
                $schueler['fachname'] .= ' / '.$belegart;
            }

            $rows[$id] = $schueler;
            $id++;
        }
        return $rows;
    }


    /**
     * return average values from schnit_hj1, note_hj_1, m_hj1, v_hj1 and all rom  hj2
     * @param type $schueler_id
     * @param type $rows
     * @return type array
     */
    function getSchuelerAVG($schueler_id, &$schueler){
        $sql = "select
			-- 1. HJ
			AVG(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'schnitt_hj_1' THEN REPLACE(egw_schulmanager_note.note_note, ',', '.') END) AS schnitt_hj_1_avg,
			AVG(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'note_hj_1' THEN REPLACE(egw_schulmanager_note.note_note, ',', '.') END) AS note_hj_1_avg,
			AVG(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'm_hj_1' THEN REPLACE(egw_schulmanager_note.note_note, ',', '.') END) AS m_hj_1_avg,
			AVG(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'v_hj_1' THEN REPLACE(egw_schulmanager_note.note_note, ',', '.') END) AS v_hj_1_avg,
			-- 2. HJ
			AVG(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'schnitt_hj_2' THEN REPLACE(egw_schulmanager_note.note_note, ',', '.') END) AS schnitt_hj_2_avg,
			AVG(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'note_hj_2' THEN REPLACE(egw_schulmanager_note.note_note, ',', '.') END) AS note_hj_2_avg,
			AVG(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'm_hj_2' THEN REPLACE(egw_schulmanager_note.note_note, ',', '.') END) AS m_hj_2_avg,
			AVG(CASE WHEN egw_schulmanager_note.note_blockbezeichner = 'v_hj_2' THEN REPLACE(egw_schulmanager_note.note_note, ',', '.') END) AS v_hj_2_avg

			FROM egw_schulmanager_note
			WHERE egw_schulmanager_note.note_index_im_block = -1  AND egw_schulmanager_note.schueler_id= '".$schueler_id."'";

        $rs = $this->db->query($sql, __LINE__, __FILE__, 0, -1);
        //$rowid = 0;
        $id = 0;
        foreach($rs as $row){
            //$rows[] = array(
            $noten		= array(
                'schnitt_hj_1'		  => array(
                    -1 => array(
                        'note'   => $this->formatDecimal($row['schnitt_hj_1_avg']),
                    ),
                ),
                'note_hj_1'			  => array(
                    -1 => array(
                        'note'   => $this->formatDecimal($row['note_hj_1_avg']),
                    ),
                ),
                'm_hj_1'			  => array(
                    -1 => array(
                        'note'   => $this->formatDecimal($row['m_hj_1_avg'], 1),
                    ),
                ),
                'v_hj_1'			  => array(
                    -1 => array(
                        'note'   => $this->formatDecimal($row['v_hj_1_avg'], 1),
                    ),
                ),
                'schnitt_hj_2'		  => array(
                    -1 => array(
                        'note'   => $this->formatDecimal($row['schnitt_hj_2_avg']),
                    ),
                ),
                'note_hj_2'			  => array(
                    -1 => array(
                        'note'   => $this->formatDecimal($row['note_hj_2_avg']),
                    ),
                ),
                'm_hj_2'			  => array(
                    -1 => array(
                        'note'   => $this->formatDecimal($row['m_hj_2_avg'], 1),
                    ),
                ),
                'v_hj_2'			  => array(
                    -1 => array(
                        'note'   => $this->formatDecimal($row['v_hj_2_avg'], 1),
                    ),
                ),
            );
        }
        $schueler['noten'] = $noten;
    }

    /**
     * Formats a decimal number
     * @param $noteDec
     * @param $dec
     * @return string
     */
    function formatDecimal($noteDec, $dec = 2){
        if(!empty($noteDec)){
            return number_format(floatval(str_replace(',', '.', $noteDec)), $dec, ',', '');
        }
        else{
            return '-,--';
        }
    }

    /**
     * delete grades in period a and/or b
     * @param $schueler
     * @param $PerA
     * @param $perB
     * @return void
     */
    function delLnwPer($schueler, $perA = false, $perB = false){
        $schueler_id = $schueler['nm_st']['st_asv_id'];

        if(!isset($schueler_id) OR strlen($schueler_id) == 0){
            return;
        }
        if($perA) {
            $where = "schueler_id = '" . $schueler_id . "'
                AND note_blockbezeichner IN ('klnw_hj_1', 'glnw_hj_1', 'schnitt_hj_1', 'note_hj_1', 'm_hj_1', 'v_hj_1')";
            $rs = $this->db->delete('egw_schulmanager_note', $where, __LINE__, __FILE__, 0, -1);
        }
        elseif ($perB){
            $where = "schueler_id = '" . $schueler_id . "'
                AND note_blockbezeichner IN ('klnw_hj_2', 'glnw_hj_2', 'schnitt_hj_2', 'note_hj_2', 'm_hj_2', 'v_hj_2', 'schnitt_hj_2', 'note_hj_2', 'm_hj_2', 'v_hj_2')";
            $rs = $this->db->delete('egw_schulmanager_note', $where, __LINE__, __FILE__, 0, -1);
        }
    }

    function truncate(){
        $sql = "TRUNCATE egw_schulmanager_note";
        return $this->db->query($sql, __LINE__, __FILE__);
    }
}