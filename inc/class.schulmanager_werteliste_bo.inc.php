<?php

/**
 * EGroupware Schulmanager - wertelisten - bussiness object
 *
 * @link http://www.egroupware.org
 * @package schulmanager
 * @author Axel Wild <info-AT-wild-solutions.de>
 * @copyright (c) 2022 by info-AT-wild-solutions.de
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

use EGroupware\Api;

/**
 * werteliste bo
 * @author axel
 */
class schulmanager_werteliste_bo {

    /**
     * Instance of  so object
     * @var schulmanager_so
     */
    var $so;

    /**
     * Constructor
     */
    function __construct()
    {
        $this->so = new schulmanager_werteliste_so();
    }

    /**
     * @param $asv_wl_schluessel
     * @param $rows
     * @return int
     */
    public function loadWerteliste($asv_wl_schluessel, &$rows, bool $onlyKeyVal = false){
        $wl = Api\Cache::getSession('schulmanager', 'wl_'.$asv_wl_schluessel);
        if(!isset($wl)) {
            $wl = array();
            $this->so->loadWerteliste($asv_wl_schluessel, $wl, $onlyKeyVal);
            Api\Cache::setSession('schulmanager', 'wl_'.$asv_wl_schluessel, $wl);
        }

        foreach($wl as $w) {
            if($onlyKeyVal){
                // for select control
                $rows[$w['asv_wert_id']] = $w['asv_wert_anzeigeform'];
            }
            else{
                $rows[] = $w;
            }
        }
        return count($wl);
    }

    /**
     * @return array|mixed|null
     * @deprecated
     */
    public static function loadWLNotGebArt(bool $onlyKeyVal = false){
        $result = array();
        $wl = Api\Cache::getSession('schulmanager', 'wl_NOTGEBART');
        if(!isset($wl)) {
            $bo = new schulmanager_werteliste_bo();
            $wl = array();
            $bo->loadWerteliste('NOTGEBART', $wl, true);
        }

        foreach($wl as $w) {
            if($onlyKeyVal){
                // for select control
                $result[$w['asv_wert_id']] = $w['asv_wert_anzeigeform'];
            }
            else{
                $result[] = $w;
            }
        }
        return $result;
    }

    /**
     * determines grades type
     * @param bool $glnw
     * @return false|string[]
     */
    public static function getNotenArtList(bool $glnw = true){
        $config = Api\Config::read('schulmanager');

        if($glnw){
            $list = explode(';', $config['typlist_glnw']);
        }
        else{
            $list = explode(';', $config['typlist_klnw']);
        }
        return $list;
    }

    /**
     * creates a combined list
     * @return false|string[]
     */
    public static function getNotenArtListCombi(){
        $glnwList = self::getNotenArtList(True);
        $klnwList = self::getNotenArtList(False);
        return array_merge($glnwList, $klnwList);;
    }

    /**
     * return string value by id
     * @param string $blockname
     * @param $index
     * @return mixed|string
     */
    public static function getNotenArtByListIndex(string $blockname, $index){
        //php 8:$list = self::getNotenArt(str_starts_with($blockname, 'glnw'));
        $list = self::getNotenArtList(substr( $blockname, 0, 4 ) === "glnw");
        return $list[$index];
    }
}