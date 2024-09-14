<?php
/**
 * eGroupWare - schulmanager
 * http://www.egroupware.org
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package schulmanager
 * @author
 * @version $Id$
 */

use EGroupware\Api;

$oProc = $GLOBALS['egw_setup']->oProc;

$egw_customfields = 'egw_customfields';
$oProc->query("INSERT INTO {$egw_customfields} (cf_app,cf_name,cf_label,cf_type) VALUES ('calendar', '#SCHULMANAGER_CAL', 'Eintrag im Schulmanager Terminkalender', 'checkbox')");
$oProc->query("INSERT INTO {$egw_customfields} (cf_app,cf_name,cf_label,cf_type) VALUES ('calendar', '#SCHULMANAGER_CAL_KLASSE', 'Schulklasse des Termins', 'text')");
$oProc->query("INSERT INTO {$egw_customfields} (cf_app,cf_name,cf_label,cf_type) VALUES ('calendar', '#SCHULMANAGER_CAL_KLASSENGRUPPE', 'Klassengruppe des Termins', 'text')");
$oProc->query("INSERT INTO {$egw_customfields} (cf_app,cf_name,cf_label,cf_type,cf_values) VALUES ('calendar', '#SCHULMANAGER_CAL_TYPE', 'Typ des Termins', 'select','{\"sa\":\"Schulaufgabe\",\"ka\":\"Kurzarbeit\",\"ex\":\"Stegreifaufgabe\",\"flt\":\"fachlicher Leistungstest\",\"sonst\":\"Sonstiges\",\"block\":\"BLOCKIERT\"}')");
$oProc->query("INSERT INTO {$egw_customfields} (cf_app,cf_name,cf_label,cf_len) VALUES ('calendar', '#SCHULMANAGER_CAL_FACH', 'Unterrichtsfach des Termins', 'text',5)");
$oProc->query("INSERT INTO {$egw_customfields} (cf_app,cf_name,cf_label) VALUES ('calendar', '#SCHULMANAGER_CAL_USER', 'verantw. Lehrer des Termins', 'select-account')");

$egw_config = 'egw_schulmanager_config';
$oProc->query("INSERT INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'K',   '010')");
$oProc->query("INSERT INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'Ev',  '011')");
$oProc->query("INSERT INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'Eth', '012')");
$oProc->query("INSERT INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'D',   '020')");
$oProc->query("INSERT INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'L',   '030')");
$oProc->query("INSERT INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'Gr',  '031')");
$oProc->query("INSERT INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'E',   '032')");
$oProc->query("INSERT INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'F',   '033')");
$oProc->query("INSERT INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'Sp',  '034')");
$oProc->query("INSERT INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'M',   '040')");
$oProc->query("INSERT INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'Inf', '041')");
$oProc->query("INSERT INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'SpInf', '041')");
$oProc->query("INSERT INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'Ph',  '042')");
$oProc->query("INSERT INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'C',  '043')");
$oProc->query("INSERT INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'B',   '044')");
$oProc->query("INSERT INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'NuT', '045')");
$oProc->query("INSERT INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'G',   '050')");
$oProc->query("INSERT INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'Geo', '051')");
$oProc->query("INSERT INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'WR',  '052')");
$oProc->query("INSERT INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'PuG', '060')");
$oProc->query("INSERT INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'Sk',  '070')");
$oProc->query("INSERT INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'Ku',  '080')");
$oProc->query("INSERT INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'Mu',  '090')");
$oProc->query("INSERT INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'Sm',  '100')");
$oProc->query("INSERT INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'Sw',  '101')");
$oProc->query("INSERT INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'Smw',  '102')");