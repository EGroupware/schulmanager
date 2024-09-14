<?php

/**
 * EGroupware Schulmanager - teacher - storage object
 *
 * @link http://www.egroupware.org
 * @package schulmanager
 * @author Axel Wild <info-AT-wild-solutions.de>
 * @copyright (c) 2022 by info-AT-wild-solutions.de
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

use EGroupware\Api;


class schulmanager_schueler_so {

	/**
	 * name of the main lehrer table and prefix for all other calendar tables
	 */
    var $sm_schueler_table = 'egw_schulmanager_asv_schueler';
	var $stamm_table,$schuljahr_table,$all_tables;

	/**
	 * reference to global db-object
	 * @var Api\Db
	 */
	var $db;

    var $customfields = array();

	/**
	 * instance of the async object
	 *
	 * @var Api\Asyncservice
	 */
	var $async;

    function __construct() {
        $this->db = clone($GLOBALS['egw']->db);
        $this->db->set_app('stundenplan');
        $this->all_tables = array($this->sm_schueler_table);
		foreach(array('stamm','schuljahr') as $name)
		{
			$vname = $name.'_table';
			$this->all_tables[] = $this->$vname = $this->sm_schueler_table.'_'.$name;
		}
    }

    /**
     * returns an array with all class groups
     * @param $st_asv_id
     * @return void
     */
    function getKlassenGruppen($st_asv_id, &$rows){
        $tables = $this->sm_schueler_table."_stamm";

        $cols =  'ss_asv_klassengruppe_id';

        $where = array(
            "sch_asv_id = ".$this->db->quote($st_asv_id),
        );

        $join = "INNER JOIN egw_schulmanager_asv_schueler_schuljahr ON egw_schulmanager_asv_schueler_schuljahr.ss_asv_schueler_stamm_id = egw_schulmanager_asv_schueler_stamm.sch_asv_id";

        $append = "";

        $result = $this->db->select($tables, $cols, $where, '', '', False, $append, False, 0, $join);

        $rowIndex = 0;
        foreach($result as $item){
            $rows[$rowIndex] = $item['ss_asv_klassengruppe_id'];
            $rowIndex++;
        }
    }
}