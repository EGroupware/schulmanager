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
			'wl_asv_wl_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'id'),
			'wl_asv_wl_schluessel' => array('type' => 'varchar','precision' => '240','nullable' => False,'comment' => ''),
			'wl_asv_wl_gueltig_von' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => ''),
			'wl_asv_wl_gueltig_bis' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => ''),
			'wl_asv_wl_bezeichnung' => array('type' => 'varchar','precision' => '240','nullable' => False,'comment' => ''),
			'wl_asv_wl_schulartspezifisch' => array('type' => 'int','precision' => '11','default' => '1', 'comment' => ''),
			'wl_asv_wert_id' => array('type' => 'varchar','precision' => '10','nullable' => False,'comment' => ''),
			'wl_asv_wert_schluessel' => array('type' => 'varchar','precision' => '240','nullable' => False,'comment' => ''),
			'wl_asv_wert_kurzform' => array('type' => 'varchar','precision' => '20','nullable' => False,'comment' => ''),
			'wl_asv_wert_anzeigeform' => array('type' => 'varchar','precision' => '50','nullable' => False,'comment' => ''),
			'wl_asv_wert_langform' => array('type' => 'varchar','precision' => '240','nullable' => False,'comment' => ''),
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
    $GLOBALS['egw_setup']->oProc->query('ALTER TABLE egw_schulmanager_asv_klassengruppe CHANGE COLUMN kg_asv_bildungsgang_id kg_asv_bildungsgang_id VARCHAR(40) NULL DEFAULT NULL AFTER kg_asv_kennung');
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

function schulmanager_upgrade0_0_014()
{
    $GLOBALS['egw_setup']->oProc->AddColumn('egw_schulmanager_note','note_art',array('type' => 'varchar','precision' => '25','comment' => 'type of grade'));
    $GLOBALS['egw_setup']->oProc->AddColumn('egw_schulmanager_note','note_definition_date',array('type' => 'int','meta' => 'timestamp','precision' => '8','comment' => 'date of grade
    '));
    $GLOBALS['egw_setup']->oProc->AddColumn('egw_schulmanager_note','note_description',array('type' => 'varchar','precision' => '150','comment' => 'description'));

    return $GLOBALS['setup_info']['schulmanager']['currentver'] = '0.0.015';
}

function schulmanager_upgrade0_0_015()
{
    $GLOBALS['egw_setup']->oProc->query('ALTER TABLE egw_schulmanager_asv_benutzer DROP COLUMN ben_id');
    $GLOBALS['egw_setup']->oProc->query('ALTER TABLE egw_schulmanager_asv_benutzer ADD PRIMARY KEY (ben_asv_id)');

    $GLOBALS['egw_setup']->oProc->query('ALTER TABLE egw_schulmanager_asv_schueler_stamm DROP COLUMN sch_id');
    $GLOBALS['egw_setup']->oProc->query('ALTER TABLE egw_schulmanager_asv_schueler_stamm ADD PRIMARY KEY (sch_asv_id)');

    $GLOBALS['egw_setup']->oProc->query('ALTER TABLE egw_schulmanager_asv_klassengruppe DROP COLUMN kg_id');
    $GLOBALS['egw_setup']->oProc->query('ALTER TABLE egw_schulmanager_asv_klassengruppe ADD PRIMARY KEY (kg_asv_id)');
    $GLOBALS['egw_setup']->oProc->CreateIndex('egw_schulmanager_asv_klassengruppe', array('kg_asv_klasse_id'),false);

    $GLOBALS['egw_setup']->oProc->query('ALTER TABLE egw_schulmanager_asv_klasse DROP COLUMN kl_id');
    $GLOBALS['egw_setup']->oProc->query('ALTER TABLE egw_schulmanager_asv_klasse ADD PRIMARY KEY (kl_asv_id)');

    $GLOBALS['egw_setup']->oProc->query('ALTER TABLE egw_schulmanager_asv_unterrichtselement DROP COLUMN ue_id');
    $GLOBALS['egw_setup']->oProc->query('ALTER TABLE egw_schulmanager_asv_unterrichtselement ADD PRIMARY KEY (ue_asv_id)');
    $GLOBALS['egw_setup']->oProc->CreateIndex('egw_schulmanager_asv_unterrichtselement', array('ue_asv_lehrer_schuljahr_schule_id','ue_asv_klassengruppe_id','ue_asv_fachgruppe_id','ue_asv_koppel_id'),false);

    $GLOBALS['egw_setup']->oProc->query('ALTER TABLE egw_schulmanager_asv_fachgruppe DROP COLUMN fg_id');
    $GLOBALS['egw_setup']->oProc->query('ALTER TABLE egw_schulmanager_asv_fachgruppe ADD PRIMARY KEY (fg_asv_id)');
    $GLOBALS['egw_setup']->oProc->CreateIndex('egw_schulmanager_asv_fachgruppe', array('fg_asv_schuelerfach_id'),false);

    $GLOBALS['egw_setup']->oProc->query('ALTER TABLE egw_schulmanager_asv_schueler_schuljahr DROP COLUMN ss_id');
    $GLOBALS['egw_setup']->oProc->query('ALTER TABLE egw_schulmanager_asv_schueler_schuljahr ADD PRIMARY KEY (ss_asv_id)');
    $GLOBALS['egw_setup']->oProc->CreateIndex('egw_schulmanager_asv_schueler_schuljahr', array('ss_asv_schueler_stamm_id','ss_asv_schuljahr_id','ss_asv_klassengruppe_id'),false);

    $GLOBALS['egw_setup']->oProc->query('ALTER TABLE egw_schulmanager_asv_lehrer_unterr_faecher DROP COLUMN luf_id');
    $GLOBALS['egw_setup']->oProc->query('ALTER TABLE egw_schulmanager_asv_lehrer_unterr_faecher ADD PRIMARY KEY (luf_asv_id)');

    $GLOBALS['egw_setup']->oProc->query('ALTER TABLE egw_schulmanager_asv_schuelerfach DROP COLUMN sf_id');
    $GLOBALS['egw_setup']->oProc->query('ALTER TABLE egw_schulmanager_asv_schuelerfach ADD PRIMARY KEY (sf_asv_id)');

    $GLOBALS['egw_setup']->oProc->query('ALTER TABLE egw_schulmanager_asv_besuchtes_fach DROP COLUMN bf_id');
    $GLOBALS['egw_setup']->oProc->query('ALTER TABLE egw_schulmanager_asv_besuchtes_fach ADD PRIMARY KEY (bf_asv_id)');
    $GLOBALS['egw_setup']->oProc->CreateIndex('egw_schulmanager_asv_besuchtes_fach', array('bf_asv_schueler_schuljahr_id','bf_asv_schuelerfach_id'),false);

    $GLOBALS['egw_setup']->oProc->query('ALTER TABLE egw_schulmanager_asv_lehrer_stamm DROP COLUMN ls_id');
    $GLOBALS['egw_setup']->oProc->query('ALTER TABLE egw_schulmanager_asv_lehrer_stamm ADD PRIMARY KEY (ls_asv_id)');

    $GLOBALS['egw_setup']->oProc->query('ALTER TABLE egw_schulmanager_asv_lehrer_schuljahr DROP COLUMN lsj_id');
    $GLOBALS['egw_setup']->oProc->query('ALTER TABLE egw_schulmanager_asv_lehrer_schuljahr ADD PRIMARY KEY (lsj_asv_id)');
    $GLOBALS['egw_setup']->oProc->CreateIndex('egw_schulmanager_asv_lehrer_schuljahr', array('lsj_asv_lehrer_stamm_id','lsj_asv_schuljahr_id'),false);

    $GLOBALS['egw_setup']->oProc->query('ALTER TABLE egw_schulmanager_asv_lehrer_schuljahr_schule DROP COLUMN lss_id');
    $GLOBALS['egw_setup']->oProc->query('ALTER TABLE egw_schulmanager_asv_lehrer_schuljahr_schule ADD PRIMARY KEY (lss_asv_id)');
    $GLOBALS['egw_setup']->oProc->CreateIndex('egw_schulmanager_asv_lehrer_schuljahr_schule', array('lss_asv_lehrer_schuljahr_id','lss_asv_schule_schuljahr_id'),false);

    $GLOBALS['egw_setup']->oProc->query('ALTER TABLE egw_schulmanager_asv_schueler_altb DROP COLUMN altb_id');
    $GLOBALS['egw_setup']->oProc->query('ALTER TABLE egw_schulmanager_asv_schueler_altb ADD PRIMARY KEY (altb_asv_id)');

    $GLOBALS['egw_setup']->oProc->CreateIndex('egw_schulmanager_note', array('note_asv_schueler_schuljahr_id','note_asv_schueler_schuelerfach_id'),false);

    $GLOBALS['egw_setup']->oProc->CreateIndex('egw_schulmanager_asv_klassenleitung', array('kl_klasse_id','kl_lehrer_schuljahr_schule_id'),false);

    $GLOBALS['egw_setup']->oProc->CreateIndex('egw_schulmanager_note_gew', array('ngew_asv_schueler_schuelerfach_id','ngew_asv_klassengruppe_id'),false);

    $GLOBALS['egw_setup']->oProc->CreateIndex('egw_schulmanager_asv_werteliste', array('wl_asv_wl_id','wl_asv_wl_schluessel'),false);

    $GLOBALS['egw_setup']->oProc->query('ALTER TABLE egw_schulmanager_asv_schule_fach DROP COLUMN sf_id');
    $GLOBALS['egw_setup']->oProc->query('ALTER TABLE egw_schulmanager_asv_schule_fach ADD PRIMARY KEY (sf_asv_id)');
    $GLOBALS['egw_setup']->oProc->CreateIndex('egw_schulmanager_asv_schule_fach', array('sf_asv_unterrichtsfach_id'),false);

    return $GLOBALS['setup_info']['schulmanager']['currentver'] = '0.0.016';
}

function schulmanager_upgrade0_0_016()
{
    $GLOBALS['egw_setup']->oProc->CreateTable('egw_schulmanager_asv_schullaufbahn', array(
        'fd' => array(
            'sla_asv_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => ''),
            'sla_asv_schueler_stamm_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => ''),
            'sla_asv_schulverzeichnis_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => ''),
            'sla_asv_datum' => array('type' => 'varchar','precision' => '10','nullable' => False,'comment' => ''),
            'sla_asv_schuljahr_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => ''),
            'sla_asv_jahrgangsstufe_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => ''),
            'sla_asv_schulbesuchsjahr' => array('type' => 'int','precision' => '11','nullable' => True,'comment' => ''),
            'sla_asv_bildungsgang_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => ''),
            'sla_asv_wl_vorgang_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => ''),
            'sla_asv_wl_vorgang_zusatz_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => ''),
            'sla_asv_vorgang_bemerkung' => array('type' => 'varchar','precision' => '240','nullable' => False,'comment' => ''),
            'sla_asv_klassenname' => array('type' => 'varchar','precision' => '32','nullable' => False,'comment' => ''),
        ),
        'pk' => array('sla_asv_id'),
        'fk' => array(),
        'ix' => array('sla_asv_schueler_stamm_id'),
        'uc' => array()
    ));

    return $GLOBALS['setup_info']['schulmanager']['currentver'] = '0.0.017';
}

function schulmanager_upgrade0_0_017()
{
    $GLOBALS['egw_setup']->oProc->CreateTable('egw_schulmanager_lehrer_account', array(
        'fd' => array(
            'leac_lehrer' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => 'ASV teacher id'),
            'leac_account' => array('type' => 'int','precision' => '11','nullable' => False,'comment' => 'EGroupware account id'),
            'leac_modified' => array('type' => 'timestamp','meta' => 'timestamp','default' => 'current_timestamp','comment' => 'timestamp of the last modificatione'),
        ),
        'pk' => array('leac_lehrer','leac_account'),
        'fk' => array(),
        'ix' => array(),
        'uc' => array()
    ));

    $GLOBALS['egw_setup']->oProc->DropTable('egw_schulmanager_asv_schueler_altb');

    return $GLOBALS['setup_info']['schulmanager']['currentver'] = '0.0.018';
}

function schulmanager_upgrade0_0_018()
{
    $GLOBALS['egw_setup']->oProc->DropTable('egw_schulmanager_asv_benutzer');
    $GLOBALS['egw_setup']->oProc->DropTable('egw_schulmanager_blockbezeichner');

    return $GLOBALS['setup_info']['schulmanager']['currentver'] = '0.0.019';
}

function schulmanager_upgrade0_0_019()
{
    $GLOBALS['egw_setup']->oProc->CreateTable('egw_schulmanager_asv_schuelerkommunikation', array(
        'fd' => array(
            'sko_asv_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => ''),
            'sko_asv_schueler_stamm_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => ''),
            'sko_asv_wl_kommunikationstyp_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => ''),
            'sko_asv_kommunikationsadresse' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => ''),
            'sko_asv_bemerkung' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => ''),
        ),
        'pk' => array('sko_asv_id'),
        'fk' => array(),
        'ix' => array('sko_asv_schueler_stamm_id'),
        'uc' => array()
    ));

    return $GLOBALS['setup_info']['schulmanager']['currentver'] = '0.0.020';
}

function schulmanager_upgrade0_0_020()
{
    $GLOBALS['egw_setup']->oProc->CreateTable('egw_schulmanager_asv_schueleranschrift', array(
        'fd' => array(
            'san_asv_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => ''),
            'san_asv_schueler_stamm_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => ''),
            'san_asv_wl_anschriftstyp_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => ''),
            'san_asv_auskunftsberechtigt' => array('type' => 'int','precision' => '4','default' => '0','comment' => ''),
            'san_asv_hauptansprechpartner' => array('type' => 'int','precision' => '4','default' => '0','comment' => ''),
            'san_asv_im_verteiler_schriftverkehr' => array('type' => 'int','precision' => '4','default' => '0','comment' => ''),
            'san_asv_strasse' => array('type' => 'varchar','precision' => '80','nullable' => False,'comment' => ''),
            'san_asv_nummer' => array('type' => 'varchar','precision' => '10','nullable' => False,'comment' => ''),
            'san_asv_postleitzahl' => array('type' => 'varchar','precision' => '10','nullable' => False,'comment' => ''),
            'san_asv_ortsbezeichnung' => array('type' => 'varchar','precision' => '80','nullable' => False,'comment' => ''),
            'san_asv_ortsteil' => array('type' => 'varchar','precision' => '80','nullable' => False,'comment' => ''),
            'san_asv_anredetext' => array('type' => 'varchar','precision' => '256','nullable' => False,'comment' => ''),
            'san_asv_anschrifttext' => array('type' => 'varchar','precision' => '256','nullable' => False,'comment' => ''),
            'san_asv_wl_staat_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => ''),
            'san_asv_wl_personentyp_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => ''),
            'san_asv_familienname' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => ''),
            'san_asv_vornamen' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => ''),
            'san_asv_wl_akademischer_grad_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => ''),
            'san_asv_wl_anrede_id' => array('type' => 'varchar','precision' => '40','nullable' => False,'comment' => ''),
        ),
        'pk' => array('san_asv_id'),
        'fk' => array(),
        'ix' => array('san_asv_schueler_stamm_id'),
        'uc' => array()
    ));

    return $GLOBALS['setup_info']['schulmanager']['currentver'] = '0.0.021';
}

function schulmanager_upgrade0_0_021()
{
    $GLOBALS['egw_setup']->oProc->query('ALTER TABLE egw_schulmanager_asv_klassengruppe CHANGE COLUMN kg_asv_bildungsgang_id kg_asv_bildungsgang_id VARCHAR(40) NULL DEFAULT NULL AFTER kg_asv_kennung');
    return $GLOBALS['setup_info']['schulmanager']['currentver'] = '0.0.022';
}