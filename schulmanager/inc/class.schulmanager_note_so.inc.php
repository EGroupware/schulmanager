<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use EGroupware\Api;
/**
 * Description of class
 *
 * @author axel
 */
class schulmanager_note_so extends Api\Storage {

	public function __construct(){
		//$app, $table, $extra_table, $column_prefix = '', $extra_key = '_name', $extra_value = '_value', $extra_id = '_id', \EGroupware\Api\Db $db = null, $no_clone = true, $allow_multiple_values = false, $timestamp_type = null) {

		parent::__construct('schulmanager', 'egw_schulmanager_note', 'egw_schulmanager_note_extra', 'schulmanager_note');
		$this->set_times('string');

		/*
		 * parent::__construct('resources','egw_resources', 'egw_resources_extra', '',
			'extra_name', 'extra_value', 'extra_id' );

		$this->columns_to_search = array('name','short_description','inventory_number','long_description','location');
		 */
	}


	/**
	 * saves a resource including extra fields
	 *
	 * @param array $note key => value
	 * @return mixed id of resource if all right, false if fale
	 */
	function save($note)
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

		//return 'egw-'.$egwid.'-'.date("Y-m-d").'-334ac8339bf21';
		return 'egw-'.$egwid.'-34ac-8339bf21-1234';
	}

	function exportJahresZeugnisnotenCheck(){
		$isFirst = true;

		$sqlNotenEGW = "SELECT egw_schulmanager_note.note_id AS id,
							'1198_25' AS wl_zeugnisart,
							egw_schulmanager_note.note_note AS note,
							egw_schulmanager_asv_klassengruppe.kg_asv_jahrgangsstufe_id AS jahrgangsstufe_id,
							egw_schulmanager_asv_schueler_schuljahr.ss_asv_schueler_stamm_id AS schueler_stamm_id,
							egw_schulmanager_asv_schuelerfach.sf_asv_schule_fach_id AS schule_fach_id,
							egw_schulmanager_asv_schueler_schuljahr.ss_asv_schuljahr_id AS schuljahr_id,
							-- debug
							egw_schulmanager_asv_schueler_stamm.sch_asv_familienname AS debug_schueler_familienname,
							egw_schulmanager_asv_schueler_stamm.sch_asv_rufname AS debug_schueler_rufname,
							egw_schulmanager_asv_klasse.kl_asv_klassenname AS debug_klassenname,
							egw_schulmanager_asv_schule_fach.sf_asv_kurzform AS debug_schule_fach
						FROM egw_schulmanager_note
						INNER JOIN egw_schulmanager_asv_schuelerfach ON egw_schulmanager_asv_schuelerfach.sf_asv_id = egw_schulmanager_note.note_asv_schueler_schuelerfach_id
						INNER JOIN egw_schulmanager_asv_schueler_schuljahr ON egw_schulmanager_asv_schueler_schuljahr.ss_asv_id = egw_schulmanager_note.note_asv_schueler_schuljahr_id
						INNER JOIN egw_schulmanager_asv_klassengruppe ON egw_schulmanager_asv_klassengruppe.kg_asv_id = egw_schulmanager_asv_schueler_schuljahr.ss_asv_klassengruppe_id
						-- debug
						INNER JOIN egw_schulmanager_asv_schueler_stamm ON egw_schulmanager_asv_schueler_stamm.sch_asv_id = egw_schulmanager_asv_schueler_schuljahr.ss_asv_schueler_stamm_id
						INNER JOIN egw_schulmanager_asv_klasse ON egw_schulmanager_asv_klasse.kl_asv_id = egw_schulmanager_asv_klassengruppe.kg_asv_klasse_id
						INNER JOIN egw_schulmanager_asv_schule_fach ON egw_schulmanager_asv_schule_fach.sf_asv_id = egw_schulmanager_asv_schuelerfach.sf_asv_schule_fach_id
						WHERE egw_schulmanager_note.note_blockbezeichner = 'note_hj_2' AND egw_schulmanager_note.note_index_im_block = '-1'
							AND egw_schulmanager_asv_schueler_schuljahr.ss_asv_schuljahr_id = 'SCHULJAHR_2018'";


		$rsExists = $this->db->query($sqlNotenEGW, __LINE__, __FILE__, 0, -1);

		$content =		 "SELECT \n"
						."  asv.svp_zeugnis_note.id,\n"
						."  asv.svp_zeugnis_note.note,\n"
						."  asv.svp_zeugnis_note.update_date,\n"
						."  asv.svp_zeugnis_note.update_user,\n"
						."  asv.svp_schueler_stamm.familienname,\n"
						."  asv.svp_schueler_stamm.rufname,\n"
						."  asv.svp_wl_unterrichtsfach.kurzform\n"
						." FROM "
						."  asv.svp_zeugnis_note \n"
						." INNER JOIN asv.svp_schueler_stamm ON asv.svp_schueler_stamm.id = asv.svp_zeugnis_note.schueler_stamm_id \n"
						." INNER JOIN asv.svp_schule_fach ON asv.svp_schule_fach.id = asv.svp_zeugnis_note.schule_fach_id \n"
						." INNER JOIN asv.svp_wl_unterrichtsfach ON asv.svp_wl_unterrichtsfach.id = asv.svp_schule_fach.unterrichtsfach_id \n"
						." WHERE \n";

		// zuerst alle entspr. Datensätze löschen
		foreach($rsExists as $row){
			$rs_wl_zeugnisart = $row['wl_zeugnisart'];
			$rs_note = $row['note'];
			$rs_jahrgangsstufe_id = $row['jahrgangsstufe_id'];
			$rs_schueler_stamm_id = $row['schueler_stamm_id'];
			$rs_schule_fach_id = $row['schule_fach_id'];
			$rs_schuljahr_id = $row['schuljahr_id'];
			$rs_name = $row['debug_schueler_familienname'];
			$rs_rufname = $row['debug_schueler_rufname'];
			$rs_klassenname = $row['debug_klassenname'];
			$rs_fach = $row['debug_schule_fach'];

			if($isFirst){
				//$content = $content. "WHERE ";
				$isFirst = false;
			}
			else{
				$content = $content. " OR ";
			}

			$content = $content
					. " ("
					. " note <> '".$rs_note."' "
					. " AND wl_zeugnisart_id='".$rs_wl_zeugnisart."' AND jahrgangsstufe_id='".$rs_jahrgangsstufe_id."' "
					. " AND schueler_stamm_id='".$rs_schueler_stamm_id."'"
					. " AND schule_fach_id = '".$rs_schule_fach_id."' "
					. " AND schuljahr_id='".$rs_schuljahr_id."')\n";
		}


		$content = $content."\n\n\n";


		return $content;
	}


	function exportJahresZeugnisnoten2SQL(){
		//$content = "-- 2019-07-07\n ASV-Version 2.8.266\n upsert noten aus EGroupware\n\n";
		//$content = "ALTER TABLE asv.svp_zeugnis_note ADD CONSTRAINT unique_egw_upsert UNIQUE (wl_zeugnisart_id, note, note_berechnet, schueler_stamm_id, schule_fach_id, schuljahr_id);\n\n";
		$isFirst = true;


		$sqlNotenEGW = "SELECT egw_schulmanager_note.note_id AS id,
							'1198_25' AS wl_zeugnisart,
							IF(note_blockbezeichner = 'note_hj_2', egw_schulmanager_note.note_note, NULL) AS note,
							IF(note_blockbezeichner = 'schnitt_hj_2', egw_schulmanager_note.note_note, NULL) AS note_berechnet,
							1 AS note_manuell,
							'Importiert am 07.07.2019 aus EGroupware' AS leistungsgegenstand,
							'40289d7a/3fe24cf0/013f/e252323b/0002' AS client_key,
							'2019-07-07 00:00:00.000000' AS create_date,
							'schul0369GY' AS create_user,
							'2019' AS slice_key,
							'2019-07-07 00:00:00.000000' AS update_date,
							'import-egroupware' AS update_user,
							1 AS version,
							'07.07.2019' AS datum,
							egw_schulmanager_asv_klassengruppe.kg_asv_jahrgangsstufe_id AS jahrgangsstufe_id,
							egw_schulmanager_asv_schueler_schuljahr.ss_asv_schueler_stamm_id AS schueler_stamm_id,
							egw_schulmanager_asv_schuelerfach.sf_asv_schule_fach_id AS schule_fach_id,
							egw_schulmanager_asv_schueler_schuljahr.ss_asv_schuljahr_id AS schuljahr_id
						FROM egw_schulmanager_note
						INNER JOIN egw_schulmanager_asv_schuelerfach ON egw_schulmanager_asv_schuelerfach.sf_asv_id = egw_schulmanager_note.note_asv_schueler_schuelerfach_id
						INNER JOIN egw_schulmanager_asv_schueler_schuljahr ON egw_schulmanager_asv_schueler_schuljahr.ss_asv_id = egw_schulmanager_note.note_asv_schueler_schuljahr_id
						INNER JOIN egw_schulmanager_asv_klassengruppe ON egw_schulmanager_asv_klassengruppe.kg_asv_id = egw_schulmanager_asv_schueler_schuljahr.ss_asv_klassengruppe_id
						WHERE egw_schulmanager_note.note_blockbezeichner IN ('schnitt_hj_2','note_hj_2') AND egw_schulmanager_note.note_index_im_block = '-1'
							AND egw_schulmanager_asv_schueler_schuljahr.ss_asv_schuljahr_id = 'SCHULJAHR_2018'";

		$sqlNotenMissing = "SELECT note_1.note_id AS id,
								'1198_25' AS wl_zeugnisart,
								NULL AS note,
								CONCAT(note_1.note_note, ',00') AS note_berechnet,
								1 AS note_manuell,
								'Importiert am 07.07.2019 aus EGroupware (generated from note)' AS leistungsgegenstand,
								'40289d7a/3fe24cf0/013f/e252323b/0002' AS client_key,
								'2019-07-07 00:00:00.000000' AS create_date,
								'schul0369GY' AS create_user,
								'2019' AS slice_key,
								'2019-07-07 00:00:00.000000' AS update_date,
								'import-egroupware' AS update_user,
								1 AS version,
								'07.07.2019' AS datum,
								egw_schulmanager_asv_klassengruppe.kg_asv_jahrgangsstufe_id AS jahrgangsstufe_id,
								egw_schulmanager_asv_schueler_schuljahr.ss_asv_schueler_stamm_id AS schueler_stamm_id,
								egw_schulmanager_asv_schuelerfach.sf_asv_schule_fach_id AS schule_fach_id,
								egw_schulmanager_asv_schueler_schuljahr.ss_asv_schuljahr_id AS schuljahr_id
							FROM egw_schulmanager_note note_1
							INNER JOIN egw_schulmanager_asv_schuelerfach ON egw_schulmanager_asv_schuelerfach.sf_asv_id = note_1.note_asv_schueler_schuelerfach_id
							INNER JOIN egw_schulmanager_asv_schueler_schuljahr ON egw_schulmanager_asv_schueler_schuljahr.ss_asv_id = note_1.note_asv_schueler_schuljahr_id
							INNER JOIN egw_schulmanager_asv_klassengruppe ON egw_schulmanager_asv_klassengruppe.kg_asv_id = egw_schulmanager_asv_schueler_schuljahr.ss_asv_klassengruppe_id
							WHERE
								note_1.note_blockbezeichner = 'note_hj_2' AND note_1.note_index_im_block = '-1'
								AND NOT EXISTS ( SELECT * FROM egw_schulmanager_note note_2
														WHERE note_1.note_asv_schueler_schuljahr_id = note_2.note_asv_schueler_schuljahr_id
															AND note_1.note_asv_schueler_schuelerfach_id = note_2.note_asv_schueler_schuelerfach_id
															AND note_2.note_blockbezeichner = 'schnitt_hj_2')
							ORDER BY note_1.note_id";

		$rsExists = $this->db->query($sqlNotenEGW, __LINE__, __FILE__, 0, -1);

		// zuerst alle entspr. Datensätze löschen
		foreach($rsExists as $row){
			$rs_wl_zeugnisart = $row['wl_zeugnisart'];
			$rs_jahrgangsstufe_id = $row['jahrgangsstufe_id'];
			$rs_schueler_stamm_id = $row['schueler_stamm_id'];
			$rs_schule_fach_id = $row['schule_fach_id'];
			$rs_schuljahr_id = $row['schuljahr_id'];

			$content = $content."DELETE FROM asv.svp_zeugnis_note "
					. "WHERE wl_zeugnisart_id='".$rs_wl_zeugnisart."' AND jahrgangsstufe_id='".$rs_jahrgangsstufe_id."' "
					. "AND schueler_stamm_id='".$rs_schueler_stamm_id."' AND schule_fach_id = '".$rs_schule_fach_id."' "
					. "AND schuljahr_id='".$rs_schuljahr_id."';\n";
		}

		$content = $content."\n\n\n";

		$content = $content."INSERT INTO asv.svp_zeugnis_note\n"
				."  (id,wl_zeugnisart_id,note,note_berechnet,note_manuell,leistungsgegenstand,client_key,"
						."create_date,create_user,slice_key,update_date,update_user,version,datum,jahrgangsstufe_id,schueler_stamm_id,schule_fach_id,schuljahr_id)\n";
		$content = $content."VALUES\n";


		foreach($rsExists as $row){
			$rs_id = $this->createASVID($row['id']);
			$rs_wl_zeugnisart = $row['wl_zeugnisart'];
			$rs_note = empty($row['note']) ? "null" : "'".$row['note']."'";
			$rs_note_berechnet = empty($row['note_berechnet']) ? "null" : "'".$row['note_berechnet']."'";
			$rs_note_manuell = $row['note_manuell'];
			$rs_leistungsgegenstand = $row['leistungsgegenstand'];
			$rs_client_key = $row['client_key'];
			$rs_create_date = $row['create_date'];
			$rs_create_user = $row['create_user'];
			$rs_slice_key = $row['slice_key'];
			$rs_update_date = $row['update_date'];
			$rs_update_user = $row['update_user'];
			$rs_version = $row['version'];
			$rs_datum = $row['datum'];
			$rs_jahrgangsstufe_id = $row['jahrgangsstufe_id'];
			$rs_schueler_stamm_id = $row['schueler_stamm_id'];
			$rs_schule_fach_id = $row['schule_fach_id'];
			$rs_schuljahr_id = $row['schuljahr_id'];

			if($isFirst){
				$isFirst = false;
			}
			else{
				$content = $content.",\n";
			}

			$content = $content."  ('".$rs_id."','".$rs_wl_zeugnisart."',".$rs_note.",".$rs_note_berechnet.",".$rs_note_manuell.",'".$rs_leistungsgegenstand."','".$rs_client_key
										."','".$rs_create_date."','".$rs_create_user."','".$rs_slice_key."','".$rs_update_date."','".$rs_update_user."',".$rs_version.",'".$rs_datum
										."','".$rs_jahrgangsstufe_id."','".$rs_schueler_stamm_id."','".$rs_schule_fach_id."','".$rs_schuljahr_id."')";
		}

		$rsMissing = $this->db->query($sqlNotenMissing, __LINE__, __FILE__, 0, -1);

		foreach($rsMissing as $row){
			$rs_id = $this->createASVID('gen'.$row['id']);
			$rs_wl_zeugnisart = $row['wl_zeugnisart'];
			$rs_note = empty($row['note']) ? "null" : "'".$row['note']."'";
			$rs_note_berechnet = empty($row['note_berechnet']) ? "null" : "'".$row['note_berechnet']."'";
			$rs_note_manuell = $row['note_manuell'];
			$rs_leistungsgegenstand = $row['leistungsgegenstand'];
			$rs_client_key = $row['client_key'];
			$rs_create_date = $row['create_date'];
			$rs_create_user = $row['create_user'];
			$rs_slice_key = $row['slice_key'];
			$rs_update_date = $row['update_date'];
			$rs_update_user = $row['update_user'];
			$rs_version = $row['version'];
			$rs_datum = $row['datum'];
			$rs_jahrgangsstufe_id = $row['jahrgangsstufe_id'];
			$rs_schueler_stamm_id = $row['schueler_stamm_id'];
			$rs_schule_fach_id = $row['schule_fach_id'];
			$rs_schuljahr_id = $row['schuljahr_id'];

			if($isFirst){
				$isFirst = false;
			}
			else{
				$content = $content.",\n";
			}

			$content = $content."  ('".$rs_id."','".$rs_wl_zeugnisart."',".$rs_note.",".$rs_note_berechnet.",".$rs_note_manuell.",'".$rs_leistungsgegenstand."','".$rs_client_key
										."','".$rs_create_date."','".$rs_create_user."','".$rs_slice_key."','".$rs_update_date."','".$rs_update_user."',".$rs_version.",'".$rs_datum
										."','".$rs_jahrgangsstufe_id."','".$rs_schueler_stamm_id."','".$rs_schule_fach_id."','".$rs_schuljahr_id."')";
		}

		/*$content = $content."ON CONFLICT (unique_egw_upsert) DO UPDATE\n"
				."  SET note = EXCLUDED.note,\n"
				."      note_berechnet = EXCLUDED.note_berechnet,\n"
				."      note_manuell = EXCLUDED.note_manuell,\n"
				."      leistungsgegenstand = EXCLUDED.leistungsgegenstand,\n"
				."      update_date = EXCLUDED.update_date,\n"
				."      update_user = EXCLUDED.update_user,\n"
				."      leistungsgegenstand = EXCLUDED.leistungsgegenstand,\n"
				."      version = asv.svp_zeugnis_note.version + 1,\n"
				."      datum = EXCLUDED.datum;\n";

		$content = $content."\n\n-- drop constraint\n";
		$content = $content."ALTER TABLE asv.svp_zeugnis_note DROP CONSTRAINT unique_egw_upsert;";
		 */

		return $content;
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

			//AND egw_schulmanager_note.note_create_user = 'wild'
			//	AND egw_schulmanager_note.note_create_date > '2019-07-02 00:00:00.000'";

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
	//		$xml->endElement();
	//		$xml->endElement();

			// END ZEUGNISSE
	//		$xml->endElement();
	//		$xml->setIndent(--$in);

			// END SCHUELER
	//		$xml->endElement();
	//		$xml->setIndent(--$in);

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
//put your code here
}
