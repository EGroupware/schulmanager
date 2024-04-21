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
$oProc->query("REPLACE INTO {$egw_customfields} (cf_app,cf_name,cf_label,cf_type) VALUES ('calendar', '#SCHULMANAGER_CAL', 'Eintrag im Schulmanager Terminkalender', 'checkbox')");
$oProc->query("REPLACE INTO {$egw_customfields} (cf_app,cf_name,cf_label,cf_type) VALUES ('calendar', '#SCHULMANAGER_CAL_KLASSE', 'Schulklasse des Termins', 'text')");
$oProc->query("REPLACE INTO {$egw_customfields} (cf_app,cf_name,cf_label,cf_type) VALUES ('calendar', '#SCHULMANAGER_CAL_KLASSENGRUPPE', 'Klassengruppe des Termins', 'text')");
$oProc->query("REPLACE INTO {$egw_customfields} (cf_app,cf_name,cf_label,cf_type,cf_values) VALUES ('calendar', '#SCHULMANAGER_CAL_TYPE', 'Typ des Termins', 'select','{\"sa\":\"Schulaufgabe\",\"ka\":\"Kurzarbeit\",\"ex\":\"Stegreifaufgabe\",\"flt\":\"fachlicher Leistungstest\",\"sonst\":\"Sonstiges\",\"block\":\"BLOCKIERT\"}')");
$oProc->query("REPLACE INTO {$egw_customfields} (cf_app,cf_name,cf_label,cf_type,cf_len) VALUES ('calendar', '#SCHULMANAGER_CAL_FACH', 'Unterrichtsfach des Termins', 'text',5)");
$oProc->query("REPLACE INTO {$egw_customfields} (cf_app,cf_name,cf_label,cf_type) VALUES ('calendar', '#SCHULMANAGER_CAL_USER', 'verantw. Lehrer des Termins', 'select-account')");

$egw_config = 'egw_schulmanager_config';
$oProc->query("REPLACE INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'K',   '010')");
$oProc->query("REPLACE INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'Ev',  '011')");
$oProc->query("REPLACE INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'Eth', '012')");
$oProc->query("REPLACE INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'D',   '020')");
$oProc->query("REPLACE INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'L',   '030')");
$oProc->query("REPLACE INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'Gr',  '031')");
$oProc->query("REPLACE INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'E',   '032')");
$oProc->query("REPLACE INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'F',   '033')");
$oProc->query("REPLACE INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'Sp',  '034')");
$oProc->query("REPLACE INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'M',   '040')");
$oProc->query("REPLACE INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'Inf', '041')");
$oProc->query("REPLACE INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'Ph',  '042')");
$oProc->query("REPLACE INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'Ch',  '043')");
$oProc->query("REPLACE INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'B',   '044')");
$oProc->query("REPLACE INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'NuT', '045')");
$oProc->query("REPLACE INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'G',   '050')");
$oProc->query("REPLACE INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'Geo', '051')");
$oProc->query("REPLACE INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'WR',  '052')");
$oProc->query("REPLACE INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'PuG', '060')");
$oProc->query("REPLACE INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'Sk',  '070')");
$oProc->query("REPLACE INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'Ku',  '080')");
$oProc->query("REPLACE INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'Mu',  '090')");
$oProc->query("REPLACE INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'Sm',  '100')");
$oProc->query("REPLACE INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'Sw',  '101')");
