<?php

/**
 * EGroupware Schulmanager - susbtitution of a teacher - storage object
 *
 * @link http://www.egroupware.org
 * @package schulmanager
 * @author Axel Wild <info-AT-wild-solutions.de>
 * @copyright (c) 2022 by info-AT-wild-solutions.de
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

use EGroupware\Api;
use EGroupware\Api\Storage;

/**
 * substitutions in EGroupware
 * @author axel
 *
 */
class schulmanager_substitution_so extends Api\Storage {
    
    var $schulmanager_substitution_table = 'egw_schulmanager_substitution';
    
    var $value_col = array();

    var $customfields = array();
    
    public function __construct(){
        $this->db = clone($GLOBALS['egw']->db);
        $this->db->set_app('schulmanager');
        $this->all_tables = array($this->schulmanager_substitution_table);
        
        
        $this->setup_table('schulmanager', $this->schulmanager_substitution_table);
        
        $this->debug = 0;
        
        $this->value_col['id'] = 'subs_id';
        $this->value_col['asv_kennung'] = 'subs_asv_kennung';
        $this->value_col['asv_kennung_orig'] = 'subs_asv_kennung_orig';
        $this->value_col['kg_asv_id'] = 'subs_kg_asv_id';
        $this->value_col['kg_asv_kennung'] = 'subs_kg_asv_kennung';
        $this->value_col['kl_asv_id'] = 'subs_kl_asv_id';
        $this->value_col['kl_asv_klassenname'] = 'subs_kl_asv_klassenname';
        $this->value_col['sf_asv_id'] = 'subs_sf_asv_id';
        $this->value_col['sf_asv_kurzform'] = 'subs_sf_asv_kurzform';
        $this->value_col['sf_asv_anzeigeform'] = 'subs_sf_asv_anzeigeform';
        
        $this->customfields = Storage\Customfields::get($app, false, null, $db);
    }
    
    /**
     * loads all substitutions
     * @param array $unterricht
     * @return unknown
     */
    function load(array &$unterricht, $kennung){
        $filter = array();
        $filter[] = "subs_asv_kennung = '$kennung'";
        $result = $this->query_list($this->value_col, '', $filter);
        
        return $result;            
    }

    /**
     * nextmatch get rows
     * @param unknown $query_in
     * @param unknown $rows
     * @return unknown
     */
    function getNextmatchRows(&$query_in,&$rows){
        $filter = array();
        $filter[] = "subs_id >= -1";
        
        $result = $this->query_list($this->value_col, '', $filter);
        $index = 0;
        foreach($result as $ro){
            $rows[$index] = $ro;
            $rows[$index]['nm_id'] = $index;
            $rows[$index]['nr'] = $index + 1;
            $index++;
        }
        return count($result);
    }
    
    
    function saveItem($asv_kennung, $asv_kennung_orig, $kg_asv_id, $kg_asv_kennung, $kl_asv_id, $kl_asv_klassenname, $sf_asv_id, $sf_asv_kurzform, $sf_asv_anzeigeform){
        $time = time();
        $key_col = "";
        
        $subs = array(
            'subs_asv_kennung' => $asv_kennung,
            'subs_asv_kennung_orig' => $asv_kennung_orig,
            'subs_kg_asv_id' => $kg_asv_id,
            'subs_kg_asv_kennung' => $kg_asv_kennung,
            'subs_kl_asv_id' => $kl_asv_id,
            'subs_kl_asv_klassenname' => $kl_asv_klassenname,
            'subs_sf_asv_id' => $sf_asv_id,
            'subs_sf_asv_kurzform' => $sf_asv_kurzform,
            'subs_sf_asv_anzeigeform' => $sf_asv_anzeigeform,
        );
        
        $this->data = $subs;
        if(parent::save() != 0) return false;
        return false;
    }

    function truncate(){
        $sql = "TRUNCATE $this->untissync_substitution_table";
        return $this->db->query($sql, __LINE__, __FILE__);
    }
}