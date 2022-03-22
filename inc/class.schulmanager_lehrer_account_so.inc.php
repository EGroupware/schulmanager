<?php

/**
 * Schulmanager - lehrer - account
 *
 * @link http://www.egroupware.org
 * @package schulmanager
 * @author Axel Wild <info-AT-wild-solutions.de>
 * @copyright (c) 2022 by Axel Wild <info-AT-wild-solutions.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

use EGroupware\Api;

/**
 * This class represents a mapping between a teacher and egw user
 *
 * @author axel
 */
class schulmanager_lehrer_account_so extends Api\Storage
{
    var $schulmanager_lehrer_account_table = 'egw_schulmanager_lehrer_account';

    var $value_col = array();
    var $customfields = array();

    /**
     * constructor
     * @throws Api\Exception\WrongParameter
     */
    public function __construct()
    {
        $this->db = clone($GLOBALS['egw']->db);
        $this->db->set_app('schulmanager');
        $this->all_tables = array($this->schulmanager_lehrer_account_table);
        $this->setup_table('schulmanager', $this->schulmanager_lehrer_account_table);
        $this->debug = 0;
        foreach(array('lehrer', 'account', 'modified') as $name)
        {
            $this->value_col[$name] = 'leac_'.$name;
        }
    }

    /**
     * saves a linked lehrer user connection
     *
     * @param array $lehrer_account key => value
     * @return mixed id of resource if all right, false if fale
     */
    function saveItem($lehrer_account)
    {
        $this->data = $lehrer_account;
        if(parent::save() != 0) return false;

        return true;
    }

    /**
     * Loads lehrer-stamm-ids by given egw account id
     * @param $account_id EGW account id
     */
    function loadLehrerStammIDs($account_id){
        $ids = array();

        $key_col = '';
        $filter = array();
        $filter[] = "leac_account = " . $account_id;
        $result = $this->query_list($this->value_col, $key_col, $filter);

        foreach($result as $item){
            $ids[] = $item['lehrer'];
        }
        return $ids;
    }
}
