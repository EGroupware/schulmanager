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
     * Constructor
     */
    function __construct()
    {

    }

    /**
     * @param $asv_wl_schluessel
     * @param $rows
     * @return int
     */
    public static function loadWerteliste($asv_wl_schluessel, &$rows, bool $onlyKeyVal = false){
        $wl = Api\Cache::getSession('schulmanager', 'wl_'.$asv_wl_schluessel);
        if(!isset($wl)) {
            $wl = array();
            $so = new schulmanager_werteliste_so();
            $so->loadWerteliste($asv_wl_schluessel, $wl, $onlyKeyVal);
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
     * Gefaehrdung
     * @param bool $onlyKeyVal
     * @return false|string[]
     */
    public static function getGefaehrdungList(bool $onlyKeyVal = false){
        $result = array();
        $wl = Api\Cache::getSession('schulmanager', 'wl_GEFAEHRD');
        if(!isset($wl)) {
            self::loadWerteliste('GEFAEHRD', $wl, false);
            Api\Cache::setSession('schulmanager', 'wl_GEFAEHRD', $wl);
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
        $lnw_glnw_json = "{\"S\": \"Schulaufgabe\"}";
        $lnw_klnw_json = "{
              \"KA\": \"Kurzarbeit\",
              \"T\": \"Test\",
              \"ksL\": [
                \"Stegreifaufgabe\",
                \"kleiner schiftlicher LNW\"
              ],
              \"M\": [
                \"Rechenschaftsablage\",
                \"Unterrichtsbeitrag\",
                \"Referat\",
                \"kleiner mündl. LNW\"
              ]
        }";

        if($glnw){
            //$lnw_glnw = json_decode($config['lnw_glnw_json'], true);
            $lnw_glnw = json_decode($lnw_glnw_json, true);
            $list = self::getRecursiveArrayValues($lnw_glnw);
        }
        else{
            //$lnw_klnw = json_decode($config['lnw_klnw_json'], true);
            $lnw_klnw = json_decode($lnw_klnw_json, true);
            $list = self::getRecursiveArrayValues($lnw_klnw);
        }
        return $list;
    }

    /**
     * Return an array with all values of the given multidimensional array
     * @param array $arr
     * @return void
     */
    private static function getRecursiveArrayValues(array $arr){
        $iter_object = new RecursiveIteratorIterator(new RecursiveArrayIterator($arr));
        $flatten_array= array();
        foreach($iter_object as $value) {
            array_push($flatten_array,$value);
        }
        return $flatten_array;
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

    /**
     * @param $wl_geschlecht_id wl id
     * @param $outputkey 'kurzform' | 'anzeigeform' | 'langform' | 'schluessel'
     * @return mixed|string
     */
    public static function getGeschlecht($wl_geschlecht_id, $outputkey){
        $result = '';
        $wertelisten = Api\Cache::getSession('schulmanager', 'wertelisten');
        if(!isset($wertelisten['GESCHLECHT'])){
            $wl_geschlecht = array();
            self::loadWerteliste('GESCHLECHT', $wl_geschlecht);
            $wertelisten['GESCHLECHT'] = $wl_geschlecht;
            Api\Cache::setSession('schulmanager', 'wertelisten', $wertelisten);
        }

        foreach($wertelisten['GESCHLECHT'] as $key => $value){
            if($value['asv_wert_id'] == $wl_geschlecht_id){
                $result = $value['asv_wert_'.$outputkey];
                break;
            }
        }
        return $result;
    }

    /**
     * @param $wl_belegart_id wl id
     * @param $outputkey 'kurzform' | 'anzeigeform' | 'langform' | 'schluessel'
     * @return mixed|string
     */
    public static function getBelegart($wl_belegart_id, $outputkey){
        $result = '';
        $wertelisten = Api\Cache::getSession('schulmanager', 'wertelisten');
        if(!isset($wertelisten['BELEGART'])){
            $wl_geschlecht = array();
            self::loadWerteliste('BELEGART', $wl_belegart);
            $wertelisten['BELEGART'] = $wl_belegart;
            Api\Cache::setSession('schulmanager', 'wertelisten', $wertelisten);
        }

        foreach($wertelisten['BELEGART'] as $key => $value){
            if($value['asv_wert_id'] == $wl_belegart_id){
                $result = $value['asv_wert_'.$outputkey];
                break;
            }
        }
        return $result;
    }
}