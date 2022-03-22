<?php
/**
 * EGroupware - Schulmanager - setup table updates
 *
 * @link http://www.egroupware.org
 * @author Wild Axel
 * @package schulmanager
 * @subpackage setup
 * @copyright (c) 2019 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 */

use EGroupware\Api;

function schulmanager_upgrade0_0_003()
{
	return $GLOBALS['setup_info']['schulmanager']['currentver'] = '0.0.004';
}

function schulmanager_upgrade0_0_004()
{
	$GLOBALS['egw_setup']->oProc->CreateTable('egw_schulmanager_asv_klassenleitung', array(
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
	));

	return $GLOBALS['setup_info']['schulmanager']['currentver'] = '0.0.005';
}

function schulmanager_upgrade0_0_005()
{
	$GLOBALS['egw_setup']->oProc->CreateTable('egw_schulmanager_config', array(
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
	));

	return $GLOBALS['setup_info']['schulmanager']['currentver'] = '0.0.006';
}

function schulmanager_upgrade0_0_006()
{
	$GLOBALS['egw_setup']->oProc->CreateTable('egw_schulmanager_note_gew', array(
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
	));

	return $GLOBALS['setup_info']['schulmanager']['currentver'] = '0.0.007';
}

function schulmanager_upgrade0_0_007()
{
	$GLOBALS['egw_setup']->oProc->AddColumn('egw_schulmanager_asv_unterrichtselement','ue_asv_koppel_id',array(
		'type' => 'varchar',
		'precision' => '40'
	));

	$GLOBALS['egw_setup']->oProc->AddColumn('egw_schulmanager_asv_schueler_schuljahr','ss_asv_wl_gefaehrdung_id',array('type' => 'varchar',	'precision' => '40'	));
	$GLOBALS['egw_setup']->oProc->AddColumn('egw_schulmanager_asv_schueler_schuljahr','ss_asv_notenausgleich',array('type' => 'int',	'precision' => '4'	));
	$GLOBALS['egw_setup']->oProc->AddColumn('egw_schulmanager_asv_schueler_schuljahr','ss_asv_wl_abweisung_id',array('type' => 'varchar',	'precision' => '40'	));
	$GLOBALS['egw_setup']->oProc->AddColumn('egw_schulmanager_asv_schueler_schuljahr','ss_asv_wl_ziel_jgst_vorjahr_id',array('type' => 'varchar',	'precision' => '40'	));
	$GLOBALS['egw_setup']->oProc->AddColumn('egw_schulmanager_asv_schueler_schuljahr','ss_asv_wl_vorruecken_probe_vorjahr_id',array('type' => 'varchar',	'precision' => '40'	));
	$GLOBALS['egw_setup']->oProc->AddColumn('egw_schulmanager_asv_schueler_schuljahr','ss_asv_notenausgleich_vorjahr',array('type' => 'int',	'precision' => '4'	));
	$GLOBALS['egw_setup']->oProc->AddColumn('egw_schulmanager_asv_schueler_schuljahr','ss_asv_wl_wiederholungsart_id',array('type' => 'varchar',	'precision' => '40'	));
	$GLOBALS['egw_setup']->oProc->AddColumn('egw_schulmanager_asv_schueler_schuljahr','ss_asv_wl_sportbefreiung_id',array('type' => 'varchar',	'precision' => '40'	));


	$GLOBALS['egw_setup']->oProc->CreateTable('egw_schulmanager_asv_werteliste', array(
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
	));

	return $GLOBALS['setup_info']['schulmanager']['currentver'] = '0.0.008';
}

function schulmanager_upgrade0_0_008()
{
	$GLOBALS['egw_setup']->oProc->AddColumn('egw_schulmanager_asv_schuelerfach','sf_asv_pflichtfach',array('type' => 'int','precision' => '4','nullable' => False,'comment' => 'schuelerfach asl_pflichtfach'));
	$GLOBALS['egw_setup']->oProc->AddColumn('egw_schulmanager_asv_schuelerfach','sf_asv_schule_fach_id',array('type' => 'varchar','precision' => '40','nullable' => False));

	$GLOBALS['egw_setup']->oProc->CreateTable('egw_schulmanager_asv_schule_fach', array(
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
		'fk' => array('sf_id'),
		'ix' => array(),
		'uc' => array()
	));

	return $GLOBALS['setup_info']['schulmanager']['currentver'] = '0.0.009';
}

function schulmanager_upgrade0_0_009()
{

	$GLOBALS['egw_setup']->oProc->AddColumn('egw_schulmanager_asv_klassengruppe','kg_asv_jahrgangsstufe_id',array('type' => 'varchar','precision' => '40','nullable' => False));
	$GLOBALS['egw_setup']->oProc->AddColumn('egw_schulmanager_asv_klassengruppe','kg_asv_bildungsgang_id',array('type' => 'varchar','precision' => '40','nullable' => False));

	$GLOBALS['egw_setup']->oProc->CreateTable('egw_schulmanager_asv_jahrgangsstufe', array(
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
	));

	return $GLOBALS['setup_info']['schulmanager']['currentver'] = '0.0.010';
}

function schulmanager_upgrade0_0_010()
{
	$GLOBALS['egw_setup']->oProc->AddColumn('egw_schulmanager_asv_klassengruppe','kg_asv_kennung',array('type' => 'varchar','precision' => '32','nullable' => False));
	return $GLOBALS['setup_info']['schulmanager']['currentver'] = '0.0.011';
}

function schulmanager_upgrade0_0_011()
{
	// DELETE FROM egw_customfields WHERE cf_name LIKE '#SCHULMANAGER%';
	// DELETE FROM egw_customfields WHERE cf_name = '#SCHULMANAGER_CAL_TYPE'
	// add user sys_schulmanager!!!!!!
	$GLOBALS['egw_setup']->oProc->query("INSERT INTO egw_customfields (cf_app,cf_name,cf_label,cf_type,cf_order) VALUES ('calendar', '#SCHULMANAGER_CAL', 'Eintrag im Schulmanager Terminkalender', 'checkbox',10)");
	$GLOBALS['egw_setup']->oProc->query("INSERT INTO egw_customfields (cf_app,cf_name,cf_label,cf_type,cf_order) VALUES ('calendar', '#SCHULMANAGER_CAL_KLASSE', 'Schulklasse des Termins', 'text',20)");
	$GLOBALS['egw_setup']->oProc->query("INSERT INTO egw_customfields (cf_app,cf_name,cf_label,cf_type,cf_order) VALUES ('calendar', '#SCHULMANAGER_CAL_KLASSENGRUPPE', 'Klassengruppe des Termins', 'text',30)");
	$GLOBALS['egw_setup']->oProc->query("INSERT INTO egw_customfields (cf_app,cf_name,cf_label,cf_type,cf_values,cf_order) VALUES ('calendar', '#SCHULMANAGER_CAL_TYPE', 'Typ des Termins', 'select','{\"sa\":\"Schulaufgabe\",\"ka\":\"Kurzarbeit\",\"ex\":\"Stegreifaufgabe\",\"flt\":\"fachlicher Leistungstest\",\"sonst\":\"Sonstiges\",\"block\":\"BLOCKIERT\"}',40)");
	$GLOBALS['egw_setup']->oProc->query("INSERT INTO egw_customfields (cf_app,cf_name,cf_label,cf_type,cf_len,cf_order) VALUES ('calendar', '#SCHULMANAGER_CAL_FACH', 'Unterrichtsfach des Termins', 'text',5,50)");
	$GLOBALS['egw_setup']->oProc->query("INSERT INTO egw_customfields (cf_app,cf_name,cf_label,cf_type,cf_order) VALUES ('calendar', '#SCHULMANAGER_CAL_USER', 'verantw. Lehrer des Termins', 'select-account',60)");
	$GLOBALS['egw_setup']->oProc->query("INSERT INTO egw_customfields (cf_app,cf_name,cf_label,cf_type,cf_order) VALUES ('calendar', '#SCHULMANAGER_CAL_INDEX', 'Index', 'int',70)");

	return $GLOBALS['setup_info']['schulmanager']['currentver'] = '0.0.012';
}

function schulmanager_upgrade0_0_012()
{
	$GLOBALS['egw_setup']->oProc->AddColumn('egw_schulmanager_asv_besuchtes_fach','bf_asv_wl_belegart_id',array('type' => 'varchar','precision' => '40','nullable' => False));
	$GLOBALS['egw_setup']->oProc->AddColumn('egw_schulmanager_asv_besuchtes_fach','bf_asv_unterrichtsart',array('type' => 'varchar','precision' => '40','nullable' => False));
	
	return $GLOBALS['setup_info']['schulmanager']['currentver'] = '0.0.013';
}

function schulmanager_upgrade0_0_013()
{
    $GLOBALS['egw_setup']->oProc->CreateTable('egw_schulmanager_substitution', array(
        'fd' => array(
            'subs_id' => array('type' => 'auto','nullable' => False,'comment' => 'schule_fach id'),
            'subs_asv_kennung' => array('type' => 'varchar','precision' => '20','nullable' => False,'comment' => ''),
            'subs_asv_kennung_orig' => array('type' => 'varchar','precision' => '20','nullable' => False,'comment' => ''),
            'subs_kg_asv_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => ''),
            'subs_kg_asv_kennung' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => ''),
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
    ));
    
    return $GLOBALS['setup_info']['schulmanager']['currentver'] = '0.0.014';
}
