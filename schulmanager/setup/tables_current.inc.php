<?php
/**
 * eGroupWare - Setup
 * http://www.egroupware.org
 * Created by eTemplates DB-Tools written by ralfbecker@outdoor-training.de
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package stundenplan
 * @subpackage setup
 * @version $Id$
 */


$phpgw_baseline = array(
	// Benutzer aus ASV
	'egw_schulmanager_asv_benutzer' => array(
		'fd' => array(
			'ben_id' => array('type' => 'auto','nullable' => False,'comment' => 'benutzer id'),
			'ben_asv_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'benutzer asv id'),
			'ben_asv_kennung' => array('type' => 'varchar','precision' => '20','nullable' => False,'comment' => 'benutzer asv kennung'),
			'ben_asv_name' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'benutzer asv name'),
			'ben_asv_vorname' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'benutzer asv vorname'),
			'ben_asv_lehrer_stamm_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'benutzer asv lehrer stamm_id'),
		),
		'pk' => array('ben_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),

	// Schüler aus ASV
	'egw_schulmanager_asv_schueler_stamm' => array(
		'fd' => array(
			'sch_id' => array('type' => 'auto','nullable' => False,'comment' => 'schueler id'),
			'sch_asv_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'schueler asv id'),
			'sch_asv_familienname' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'schueler asv familiennname'),
			'sch_asv_rufname' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'schueler asv rufname)'),
			'sch_asv_lokales_dm' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'lokales Differentierungsmerkmal'),
			'sch_asv_eintrittsdatum' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'schueler asv eintrittsdatum'),
			'sch_asv_austrittsdatum' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'schueler asv austrittsdatum'),
		),
		'pk' => array('sch_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),

	// Klassengruppen aus ASV
	'egw_schulmanager_asv_klassengruppe' => array(
		'fd' => array(
			'kg_id' => array('type' => 'auto','nullable' => False,'comment' => 'klassengruppe id'),
			'kg_asv_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'klassengruppe asv id'),
			'kg_asv_klasse_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'klassengruppe asv klasse id'),
			'kg_asv_jahrgangsstufe_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'klassengruppe asv wl jahrgangsstufe id'),
			'kg_asv_bildungsgang_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'klassengruppe asv wl bildungsgang id'),
			'kg_asv_kennung' => array('type' => 'varchar','precision' => '32','nullable' => False,'comment' => 'klassengruppe asv kennung'),
		),
		'pk' => array('kg_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),

	// Klassen aus ASV
	'egw_schulmanager_asv_klasse' => array(
		'fd' => array(
			'kl_id' => array('type' => 'auto','nullable' => False,'comment' => 'klasse id'),
			'kl_asv_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'klasse asv id'),
			'kl_asv_klassenname' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'klasse asv klassenname'),
		),
		'pk' => array('kl_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),

	// Unterrichtselement aus ASV
	'egw_schulmanager_asv_unterrichtselement' => array(
		'fd' => array(
			'ue_id' => array('type' => 'auto','nullable' => False,'comment' => 'unterrichtselement id'),
			'ue_asv_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'unterrichtselement asv id'),
			'ue_asv_lehrer_schuljahr_schule_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'unterrichtselement asv lehrer_schuljahr_schule_id'),
			'ue_asv_klassengruppe_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'unterrichtselement asv klassengruppe_id'),
			'ue_asv_fachgruppe_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'unterrichtselement asv fachgruppe_id'),
			'ue_asv_koppel_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'unterrichtselement asv koppel_id'),
		),
		'pk' => array('ue_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),

	// Fachgruppe aus ASV
	'egw_schulmanager_asv_fachgruppe' => array(
		'fd' => array(
			'fg_id' => array('type' => 'auto','nullable' => False,'comment' => 'fachgruppe id'),
			'fg_asv_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'fachgruppe asv id'),
			'fg_asv_schuelerfach_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'fachgruppe asv schuelerfach_id'),
		),
		'pk' => array('fg_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),

	// Schueler_Schuljahr aus ASV
	'egw_schulmanager_asv_schueler_schuljahr' => array(
		'fd' => array(
			'ss_id' => array('type' => 'auto','nullable' => False,'comment' => 'schueler_schuljahr id'),
			'ss_asv_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'schueler_schuljahr asv id'),
			'ss_asv_schueler_stamm_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'schueler_schuljahr asv schueler_stamm_id'),
			'ss_asv_schuljahr_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'schueler_schuljahr asv schuljahr_id'),
			'ss_asv_klassengruppe_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'schueler_schuljahr asv klassengruppe_id'),

			'ss_asv_wl_gefaehrdung_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'schueler_schuljahr asv wl gefaehrdung_id'),
			'ss_asv_notenausgleich' => array('type' => 'int','precision' => '4','nullable' => False,'comment' => 'schueler_schuljahr asv notenausgleich'),
			'ss_asv_wl_abweisung_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'schueler_schuljahr asv wl abweisung_id'),
			'ss_asv_wl_ziel_jgst_vorjahr_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'schueler_schuljahr asv wl ziel jgst vorjahr_id'),
			'ss_asv_wl_vorruecken_probe_vorjahr_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'schueler_schuljahr asv wl vorrücken Probe vorjahr_id'),
			'ss_asv_notenausgleich_vorjahr' => array('type' => 'int','precision' => '4','nullable' => False,'comment' => 'schueler_schuljahr asv notenausgleich vorjahr'),
			'ss_asv_wl_wiederholungsart_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'schueler_schuljahr asv wl wiederholungsart_id'),
			'ss_asv_wl_sportbefreiung_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'schueler_schuljahr asv wl sportbefreiung_id'),
		),
		'pk' => array('ss_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),

	// lehrer_unterr_faecher aus ASV
	'egw_schulmanager_asv_lehrer_unterr_faecher' => array(
		'fd' => array(
			'luf_id' => array('type' => 'auto','nullable' => False,'comment' => 'lehrer_unterr_faecher id'),
			'luf_asv_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'lehrer_unterr_faecher asv id'),
			'luf_asv_lehrer_schuljahr_schule_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'lehrer_unterr_faecher asv lehrer_schuljahr_schule_id'),
			'luf_asv_schuelerfach_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'lehrer_unterr_faecher asv schuelerfach_id'),
		),
		'pk' => array('luf_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),

	// Schuelerfach aus ASV
	'egw_schulmanager_asv_schuelerfach' => array(
		'fd' => array(
			'sf_id' => array('type' => 'auto','nullable' => False,'comment' => 'schuelerfach id'),
			'sf_asv_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'schuelerfach asv id'),
			'sf_asv_kurzform' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'schuelerfach asv kurzform'),
			'sf_asv_anzeigeform' => array('type' => 'varchar','precision' => '50','nullable' => False,'comment' => 'schuelerfach asv anzeigeform'),
			'sf_asv_unterrichtsfach_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'schuelerfach asv unterrichtsfach_id'),
			'sf_asv_schule_schuljahr_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'schuelerfach asv schule_schuljahr_id'),
			'sf_asv_pflichtfach' => array('type' => 'int','precision' => '4','nullable' => False,'comment' => 'schuelerfach asl_pflichtfach'),
			'sf_asv_schule_fach_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'schuelerfach schule_fach_id'),
		),
		'pk' => array('sf_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),

	// besuchtes Fach aus ASV
	'egw_schulmanager_asv_besuchtes_fach' => array(
		'fd' => array(
			'bf_id' => array('type' => 'auto','nullable' => False,'comment' => 'besuchtes_fach id'),
			'bf_asv_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'besuchtes_fach asv id'),
			'bf_asv_schueler_schuljahr_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'besuchtes_fach asv schueler_schuljahr_id'),
			'bf_asv_schuelerfach_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'besuchtes_fach asv schuelerfach_id'),
			'bf_asv_wl_belegart_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'besuchtes_fach asv wl_belegart_id'),
			'bf_asv_unterrichtsart' => array('type' => 'varchar','precision' => '10','nullable' => False,'comment' => 'besuchtes_fach asv unterrichtsart'),
		),
		'pk' => array('bf_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),

	// Lehrer Stamm aus ASV
	'egw_schulmanager_asv_lehrer_stamm' => array(
		'fd' => array(
			'ls_id' => array('type' => 'auto','nullable' => False,'comment' => 'lehrer_stamm id'),
			'ls_asv_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'lehrer_stamm asv id'),
			'ls_asv_familienname' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'lehrer_stamm asv familienname'),
			'ls_asv_rufname' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'lehrer_stamm asv rufname'),
		),
		'pk' => array('ls_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),

	// Lehrer Schuljahr aus ASV
	'egw_schulmanager_asv_lehrer_schuljahr' => array(
		'fd' => array(
			'lsj_id' => array('type' => 'auto','nullable' => False,'comment' => 'lehrer_schuljahr id'),
			'lsj_asv_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'lehrer_schuljahr asv id'),
			'lsj_asv_lehrer_stamm_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'lehrer_schuljahr asv lehrer_stamm_id'),
			'lsj_asv_schuljahr_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'lehrer_schuljahr asv schuljahr_id'),
		),
		'pk' => array('lsj_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),

	// Lehrer Schuljahr aus ASV
	'egw_schulmanager_asv_lehrer_schuljahr_schule' => array(
		'fd' => array(
			'lss_id' => array('type' => 'auto','nullable' => False,'comment' => 'lehrer_schuljahr_schule id'),
			'lss_asv_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'lehrer_schuljahr_schule asv id'),
			'lss_asv_lehrer_schuljahr_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'lehrer_schuljahr_schule asv lehrer_schuljahr_id'),
			'lss_asv_schule_schuljahr_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'lehrer_schuljahr_schule asv schule_schuljahr_id'),
			'lss_asv_namenskuerzel' => array('type' => 'varchar','precision' => '10','nullable' => False,'comment' => 'lehrer_schuljahr_schule asv namenskuerzel'),
		),
		'pk' => array('lss_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),

	// Schueler Schuljahr alternative Berechnung aus ASV
	'egw_schulmanager_asv_schueler_altb' => array(
		'fd' => array(
			'altb_id' => array('type' => 'auto','nullable' => False,'comment' => 'lehrer_schuljahr_schule id'),
			'altb_asv_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'lehrer_schuljahr_schule asv id'),
			'altb_asv_schueler_schuljahr_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'schueler_schuljahr_schule asv schueler_schuljahr_id'),
			'altb_asv_schuelerfach_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'schuelerfach asv schuelerfach_id'),
			'altb_asv_altb' => array('type' => 'varchar','precision' => '10','nullable' => False,'comment' => 'schueler_schuljahr_alt_berechnung'),
			'altb_asv_update_date' => array('type' => 'varchar','precision' => '10','nullable' => False,'comment' => 'schueler_schuljahr_alt_berechnung update user'),
			'altb_asv_update_user' => array('type' => 'varchar','precision' => '10','nullable' => False,'comment' => 'schueler_schuljahr_alt_berechnung create user')
		),
		'pk' => array('altb_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),

	// Noten Import aus ASV
	'egw_schulmanager_asv_note_import' => array(
		'fd' => array(
			'asv_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'asv.svp_note.id'),
			'asv_schueler_schuljahr_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'asv.svp_note.schueler_schuljahr_id'),
			'asv_schueler_schuelerfach_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'asv.svp_note.schuelerfach_id'),
			'asv_blockbezeichner' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'asv.svp_note.blockbezeichner'),
			'asv_index_im_block' => array('type' => 'varchar','precision' => '10','nullable' => False,'comment' => 'lehrer_schuljahr_schule asv namenskuerzel'),
			'asv_note' => array('type' => 'varchar','precision' => '10','nullable' => False,'comment' => 'lehrer_schuljahr_schule asv namenskuerzel'),
			'asv_create_date' => array('type' => 'varchar','precision' => '10','nullable' => False,'comment' => 'lehrer_schuljahr_schule asv namenskuerzel'),
			'asv_create_user' => array('type' => 'varchar','precision' => '10','nullable' => False,'comment' => 'lehrer_schuljahr_schule asv namenskuerzel'),
			'asv_slice_key' => array('type' => 'varchar','precision' => '10','nullable' => False,'comment' => 'lehrer_schuljahr_schule asv namenskuerzel'),
			'asv_update_date' => array('type' => 'varchar','precision' => '10','nullable' => False,'comment' => 'lehrer_schuljahr_schule asv namenskuerzel'),
			'asv_update_user' => array('type' => 'varchar','precision' => '10','nullable' => False,'comment' => 'lehrer_schuljahr_schule asv namenskuerzel'),
			'asv_version' => array('type' => 'varchar','precision' => '10','nullable' => False,'comment' => 'lehrer_schuljahr_schule asv namenskuerzel'),
			'asv_note_manuell' => array('type' => 'varchar','precision' => '10','nullable' => False,'comment' => 'lehrer_schuljahr_schule asv namenskuerzel'),
		),
		'pk' => array('asv_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),

	// Blockbezeichner Konfiguration
	'egw_schulmanager_blockbezeichner' => array(
		'fd' => array(
			'bbz_id' => array('type' => 'auto','nullable' => False,'comment' => 'Blockbezeichner id'),
			'bbz_asv_blockbezeichner' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'asv Blockbezeichner'),
			'bbz_sm_blockbezeichner' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'asv.svp_note.schuelerfach_id'),
		),
		'pk' => array('bbz_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),

	// Schulamanger Note
	'egw_schulmanager_note' => array(
		'fd' => array(
			'note_id' => array('type' => 'auto','nullable' => False,'comment' => 'Note id'),
			'note_asv_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'asv.svp_note.id'),
			'note_asv_schueler_schuljahr_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'asv.svp_note.schueler_schuljahr_id'),
			'note_asv_schueler_schuelerfach_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'asv.svp_note.schuelerfach_id'),
			'note_blockbezeichner' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'asv.svp_note.blockbezeichner'),
			'note_index_im_block' => array('type' => 'int','precision' => '11','default' => '1', 'comment' => 'index im block'),
			'note_note' => array('type' => 'varchar','precision' => '10','nullable' => False,'comment' => 'notenwert'),
			'note_create_date' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'create date'),
			'note_create_user' => array('type' => 'varchar','precision' => '20','nullable' => False,'comment' => 'create user'),
			'note_update_date' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'update date'),
			'note_update_user' => array('type' => 'varchar','precision' => '20','nullable' => False,'comment' => 'update user'),
			'note_asv_note_manuell' => array('type' => 'int','precision' => '4','nullable' => False,'comment' => 'manuel update avg'),
		),
		'pk' => array('note_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),
	// Schulamanger Note Extras
	'egw_schulmanager_note_extra' => array(
		'fd' => array(
			'schulmanager_note_id' => array('type' => 'int','precision' => '4','nullable' => False),
			'schulmanager_note_name' => array('type' => 'varchar','meta' => 'cfname','precision' => '40','nullable' => False),
			'schulmanager_note_owner' => array('type' => 'int','meta' => 'account','precision' => '4','nullable' => False,'default' => '-1'),
			'schulmanager_note_value' => array('type' => 'varchar','meta' => 'cfvalue','precision' => '255','nullable' => False,'default' => '')
		),
		'pk' => array('schulmanager_note_id','schulmanager_note_name','schulmanager_note_owner'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),
	// Klassenleitung
	'egw_schulmanager_asv_klassenleitung' => array(
		'fd' => array(
			'kl_id' => array('type' => 'auto','nullable' => False,'comment' => 'klassenleitung id'),
			'kl_klasse_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'klassenleitung klasse id'),
			'kl_lehrer_schuljahr_schule_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'lehrer schule schuljahr id'),
			'kl_art' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'schueler asv rufname)'),
		),
		'pk' => array('kl_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),
	// Configuration
	'egw_schulmanager_config' => array(
		'fd' => array(
			'cnf_id' => array('type' => 'auto','nullable' => False,'comment' => 'config id'),
			'cnf_key' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'config key'),
			'cnf_val' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'config val'),
			'cnf_extra' => array('type' => 'varchar','precision' => '255','nullable' => False,'comment' => 'config extra)'),
		),
		'pk' => array('cnf_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),
	// Schulamanger Note-Gewichtung
	'egw_schulmanager_note_gew' => array(
		'fd' => array(
			'ngew_id' => array('type' => 'auto','nullable' => False,'comment' => 'Note id'),
			'ngew_asv_schueler_schuelerfach_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'asv.svp_note.schuelerfach_id'),
			'ngew_asv_klassengruppe_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'asv.svp_note.schuelerfach_id'),
			'ngew_blockbezeichner' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'asv.svp_note.blockbezeichner'),
			'ngew_index_im_block' => array('type' => 'int','precision' => '11','default' => '1', 'comment' => 'index im block'),
			'ngew_gew' => array('type' => 'varchar','precision' => '10','nullable' => False,'comment' => 'notenwert'),
			'ngew_create_date' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'create date'),
			'ngew_create_user' => array('type' => 'varchar','precision' => '20','nullable' => False,'comment' => 'create user'),
			'ngew_update_date' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'update date'),
			'ngew_update_user' => array('type' => 'varchar','precision' => '20','nullable' => False,'comment' => 'update user'),
		),
		'pk' => array('ngew_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),
	// Schulamanger Werteliste
	'egw_schulmanager_asv_werteliste' => array(
		'fd' => array(
			'wl_id' => array('type' => 'auto','nullable' => False,'comment' => 'Note id'),
			'wl_asv_wl_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'asv.svp_note.schuelerfach_id'),
			'wl_asv_wl_schluessel' => array('type' => 'varchar','precision' => '240','nullable' => False,'comment' => 'asv.svp_note.blockbezeichner'),
			'wl_asv_wl_gueltig_von' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'asv.svp_note.schuelerfach_id'),
			'wl_asv_wl_gueltig_bis' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'asv.svp_note.schuelerfach_id'),
			'wl_asv_wl_bezeichnung' => array('type' => 'varchar','precision' => '240','nullable' => False,'comment' => 'asv.svp_note.blockbezeichner'),
			'wl_asv_wl_schulartspezifisch' => array('type' => 'int','precision' => '11','default' => '1', 'comment' => 'index im block'),
			'wl_asv_wert_id' => array('type' => 'varchar','precision' => '10','nullable' => False,'comment' => 'notenwert'),
			'wl_asv_wert_schluessel' => array('type' => 'varchar','precision' => '240','nullable' => False,'comment' => 'asv.svp_note.blockbezeichner'),
			'wl_asv_wert_kurzform' => array('type' => 'varchar','precision' => '20','nullable' => False,'comment' => 'create date'),
			'wl_asv_wert_anzeigeform' => array('type' => 'varchar','precision' => '50','nullable' => False,'comment' => 'create user'),
			'wl_asv_wert_langform' => array('type' => 'varchar','precision' => '240','nullable' => False,'comment' => 'update date'),
		),
		'pk' => array('wl_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),
	// Schulmanager Schule_Fach
    'egw_schulmanager_asv_schule_fach' => array(
		'fd' => array(
			'sf_id' => array('type' => 'auto','nullable' => False,'comment' => 'schule_fach id'),
			'sf_asv_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'asv.svp_schule_fach.id'),
			'sf_asv_unterrichtsfach_id' => array('type' => 'varchar','precision' => '240','nullable' => False,'comment' => 'asv.schule_fach.unterrichtsfach_id'),
			'sf_asv_schluessel' => array('type' => 'varchar','precision' => '20','nullable' => False,'comment' => 'schluessel'),
			'sf_asv_kurzform' => array('type' => 'varchar','precision' => '20','nullable' => False,'comment' => 'kurzform'),
			'sf_asv_anzeigeform' => array('type' => 'varchar','precision' => '50','nullable' => False,'comment' => 'anzeigeform'),
			'sf_asv_langform' => array('type' => 'varchar','precision' => '240','nullable' => False,'comment' => 'langform'),
		),
		'pk' => array('sf_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),
	// Schulmanager Jahrgangsstufe
    'egw_schulmanager_asv_jahrgangsstufe' => array(
		'fd' => array(
			'jgs_id' => array('type' => 'auto','nullable' => False,'comment' => 'schule_fach id'),
			'jgs_asv_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'asv.svp_schule_fach.id'),
			'jgs_asv_schluessel' => array('type' => 'varchar','precision' => '20','nullable' => False,'comment' => 'schluessel'),
			'jgs_asv_kurzform' => array('type' => 'varchar','precision' => '20','nullable' => False,'comment' => 'kurzform'),
			'jgs_asv_anzeigeform' => array('type' => 'varchar','precision' => '50','nullable' => False,'comment' => 'anzeigeform'),
			'jgs_asv_langform' => array('type' => 'varchar','precision' => '240','nullable' => False,'comment' => 'langform'),
		),
		'pk' => array('jgs_id'),
		'fk' => array(),
		'ix' => array('jgs_asv_id'),
		'uc' => array()
	),
	// Schulmanager Vertretung
    'egw_schulmanager_substitution' => array(
        'fd' => array(
            'subs_id' => array('type' => 'auto','nullable' => False,'comment' => ''),
            'subs_asv_kennung' => array('type' => 'varchar','precision' => '20','nullable' => False,'comment' => ''),
            'subs_asv_kennung_orig' => array('type' => 'varchar','precision' => '20','nullable' => False,'comment' => ''),
            'subs_kg_asv_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => ''),
            'subs_kg_asv_kennung' => array('type' => 'varchar','precision' => '32','nullable' => False,'comment' => ''),
            'subs_kl_asv_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => ''),
            'subs_kl_asv_klassenname' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => ''),
            'subs_sf_asv_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => ''),
            'subs_sf_asv_kurzform' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => ''),
            'subs_sf_asv_anzeigeform' => array('type' => 'varchar','precision' => '50','nullable' => False,'comment' => ''),
        ),
        'pk' => array('subs_id'),
        'fk' => array(),
        'ix' => array(),
        'uc' => array()
    ),
);

