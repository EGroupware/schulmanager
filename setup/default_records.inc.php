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

$schulmanager_table_blockbezeichner = 'egw_schulmanager_blockbezeichner';

// Add two rooms to give user an idea of what resources is...
$oProc->query("INSERT INTO {$schulmanager_table_blockbezeichner} (bbz_id,bbz_asv_blockbezeichner,bbz_sm_blockbezeichner) VALUES ( 1,'Große Leistungsnachweise 1.HJ','glnw_hj_1')");
$oProc->query("INSERT INTO {$schulmanager_table_blockbezeichner} (bbz_id,bbz_asv_blockbezeichner,bbz_sm_blockbezeichner) VALUES ( 2,'Große Leistungsnachweise 2.HJ','glnw_hj_2')");
$oProc->query("INSERT INTO {$schulmanager_table_blockbezeichner} (bbz_id,bbz_asv_blockbezeichner,bbz_sm_blockbezeichner) VALUES ( 3,'Durchschnitt GLNW 1.HJ','glnw_schnitt_hj1')");
$oProc->query("INSERT INTO {$schulmanager_table_blockbezeichner} (bbz_id,bbz_asv_blockbezeichner,bbz_sm_blockbezeichner) VALUES ( 4,'Durchschnitt GLNW','glnw_schnitt')");
$oProc->query("INSERT INTO {$schulmanager_table_blockbezeichner} (bbz_id,bbz_asv_blockbezeichner,bbz_sm_blockbezeichner) VALUES ( 5,'Kleine Leistungsnachweise 1.HJ','klnw_hj_1')");
$oProc->query("INSERT INTO {$schulmanager_table_blockbezeichner} (bbz_id,bbz_asv_blockbezeichner,bbz_sm_blockbezeichner) VALUES ( 6,'Kleine Leistungsnachweise 2.HJ','klnw_hj_2')");
$oProc->query("INSERT INTO {$schulmanager_table_blockbezeichner} (bbz_id,bbz_asv_blockbezeichner,bbz_sm_blockbezeichner) VALUES ( 7,'Durchschnitt KLNW 1.HJ','klnw_schnitt_hj_1')");
$oProc->query("INSERT INTO {$schulmanager_table_blockbezeichner} (bbz_id,bbz_asv_blockbezeichner,bbz_sm_blockbezeichner) VALUES ( 8,'Durchschnitt KLNW','klnw_schnitt')");

$schulmanager_table_note = 'egw_schulmanager_note';

/*$oProc->query("INSERT INTO {$schulmanager_table_note} (note_id,note_asv_id,note_asv_schueler_schuljahr_id,note_asv_schueler_schuelerfach_id,note_blockbezeichner,note_index_im_block,note_note,note_create_date,note_create_user,note_update_date,note_update_user,note_asv_note_manuell) VALUES ('1', 'note/asv/1234', 'schueler/schuljahr/abcd71234', 'schueler/schuelerfach/abcde', 'glnw_hj_1', '0', '2', '2018-11-13 10:24:45.278', 'wild', '2018-11-13 10:24:45.278', 'wild', '0')");
$oProc->query("INSERT INTO {$schulmanager_table_note} (note_id,note_asv_id,note_asv_schueler_schuljahr_id,note_asv_schueler_schuelerfach_id,note_blockbezeichner,note_index_im_block,note_note,note_create_date,note_create_user,note_update_date,note_update_user,note_asv_note_manuell) VALUES ('3', 'note/asv/2345', 'i0rj/ww9z/0pnh/65n0/o5ox', 'wwyw/qbqs/zi7z/ehyk/auk1', 'glnw_hj_1', '1', '2', '2018-11-13 10:24:45.278', 'wild', '2018-11-13 10:24:45.278', 'wild', '0')");
$oProc->query("INSERT INTO {$schulmanager_table_note} (note_id,note_asv_id,note_asv_schueler_schuljahr_id,note_asv_schueler_schuelerfach_id,note_blockbezeichner,note_index_im_block,note_note,note_create_date,note_create_user,note_update_date,note_update_user,note_asv_note_manuell) VALUES ('4', 'note/asv/2345', 'i0rj/ww9z/0pnh/65n0/o5ox', 'wwyw/qbqs/zi7z/ehyk/auk1', 'glnw_hj_1', '-1', '2.50', '2018-11-13 10:24:45.278', 'wild', '2018-11-13 10:24:45.278', 'wild', '0')");
$oProc->query("INSERT INTO {$schulmanager_table_note} (note_id,note_asv_id,note_asv_schueler_schuljahr_id,note_asv_schueler_schuelerfach_id,note_blockbezeichner,note_index_im_block,note_note,note_create_date,note_create_user,note_update_date,note_update_user,note_asv_note_manuell) VALUES ('5', 'note/asv/2345/1', 'i0rj/ww9z/0pnh/65n0/o5ox', 'wwyw/qbqs/zi7z/ehyk/auk1', 'klnw_hj_1', '0', '6', '2018-11-13 10:24:45.278', 'wild', '2018-11-13 10:24:45.278', 'wild', '0')");
$oProc->query("INSERT INTO {$schulmanager_table_note} (note_id,note_asv_id,note_asv_schueler_schuljahr_id,note_asv_schueler_schuelerfach_id,note_blockbezeichner,note_index_im_block,note_note,note_create_date,note_create_user,note_update_date,note_update_user,note_asv_note_manuell) VALUES ('6', 'note/asv/2345/2', 'i0rj/ww9z/0pnh/65n0/o5ox', 'wwyw/qbqs/zi7z/ehyk/auk1', 'klnw_hj_1', '1', '2', '2018-11-13 10:24:45.278', 'wild', '2018-11-13 10:24:45.278', 'wild', '0')");
$oProc->query("INSERT INTO {$schulmanager_table_note} (note_id,note_asv_id,note_asv_schueler_schuljahr_id,note_asv_schueler_schuelerfach_id,note_blockbezeichner,note_index_im_block,note_note,note_create_date,note_create_user,note_update_date,note_update_user,note_asv_note_manuell) VALUES ('7', 'note/asv/2345/3', 'i0rj/ww9z/0pnh/65n0/o5ox', 'wwyw/qbqs/zi7z/ehyk/auk1', 'klnw_hj_1', '2', '3', '2018-11-13 10:24:45.278', 'wild', '2018-11-13 10:24:45.278', 'wild', '0')");
*/

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
$oProc->query("INSERT INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'Ph',  '042')");
$oProc->query("INSERT INTO {$egw_config} (cnf_key, cnf_val, cnf_extra) VALUES ('#FACH_ORDER#', 'Ch',  '043')");
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