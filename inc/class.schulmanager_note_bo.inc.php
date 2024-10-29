<?php

/**
 * EGroupware Schulmanager - grade bussiness object
 *
 * @link http://www.egroupware.org
 * @package schulmanager
 * @author Axel Wild <info-AT-wild-solutions.de>
 * @copyright (c) 2022 by info-AT-wild-solutions.de
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * Combination of Klassengruppe ans Schuelerfach
 *
 * @author axel
 */
class schulmanager_note_bo {

	/**
	 * Instance of  so object
	 *
	 * @var schulmanager_so
	 */
	var $so;

	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->so = new schulmanager_note_so();
	}

	/**
	 * saves a note
	 *
	 * @param array $schulmanager_noge array with key => value of all needed datas
	 * @return string msg if somthing went wrong; nothing if all right
	 */
	function save($note)
	{
		$this->so->saveItem($note);
	}

    function loadNotenBySchueler($schueler_id, &$schueler, array $fach)
    {
        $this->so->loadNotenBySchueler($schueler_id, $schueler, $fach);
    }

    function beforeSendToClient(&$schueler, $gewichtungen){
        $decimal_separator = '.';
        if(isset($schueler['noten'])){
            foreach($schueler['noten'] as $blockname => &$notenblock){
                if(is_array($notenblock)){
                    $notenblock['##sum##'] = 0;
                    $notenblock['##anz##'] = 0;
                    foreach($notenblock as $index_im_block => &$note){
                        if(is_integer($index_im_block) and $index_im_block >= 0 and is_numeric($note['note']) and ((int)$note['note']) > 0){
                            $gew = self::getGewichtung($gewichtungen, $blockname, $index_im_block);
                            $n = (int)$note['note'];
                            $notenblock['##sum##'] += $n * $gew;
                            $notenblock['##anz##'] += $gew;
                        }
                    }
                    if((!isset($notenblock[-1]['note']) || empty($notenblock[-1]['note']) || $notenblock[-1]['manuell']==0) && isset($notenblock['##anz##']) && $notenblock['##anz##'] > 0){
                        // calculate average, because it is empty, not edited manually
                        //$notenblock[-1]['value'] = floor(floatval($notenblock['##sum##']) / $notenblock['##anz##'] * 100) / 100;
                        $notenblock[-1]['value'] = self::getNotenBlockSchnitt($notenblock['##sum##'], $notenblock['##anz##']);
                        $notenblock[-1]['note'] = number_format(floatval($notenblock[-1]['value']), 2, $decimal_separator, '');
                        $notenblock[-1]['label'] = number_format(floatval($notenblock[-1]['value']), 2, ',', '');
                    }
                    elseif(!empty($notenblock[-1]['note']) && $notenblock[-1]['manuell'] == 1){
                        // manuelle Eingabe
                        $notenblock[-1]['value'] = floatval(str_replace(',', '.',$notenblock[-1]['note']));
                        $notenblock[-1]['label'] = $notenblock[-1]['note'];
                    }
                    else{
                        $notenblock[-1]['note'] = '';
                        $notenblock[-1]['value'] = '';
                        $notenblock[-1]['label'] = '';
                    }
                }
            }
            // alternative Berechnung
            if($schueler['noten']['alt_b'][-1]['note'] == 1){
                $schueler['noten']['alt_b'][-1]['checked'] = true;
                $schueler['noten']['alt_b'][-1]['img'] = 'check.svg';
                $schueler['noten']['alt_b'][-1]['label'] = '(1:1)';
            }
            else{
                $schueler['noten']['alt_b'][-1]['checked'] = false;
                $schueler['noten']['alt_b'][-1]['img'] = '';
                $schueler['noten']['alt_b'][-1]['label'] = '';
            }
            // Gesamtschnitt 1. HJ
            $glnw = $schueler['noten']['glnw_hj_1'][-1]['value'];
            $klnw = $schueler['noten']['klnw_hj_1'][-1]['value'];
            if(empty($schueler['noten']['schnitt_hj_1'][-1]['note']) || $schueler['noten']['schnitt_hj_1'][-1]['manuell'] == 0){
                if(!empty($schueler['noten']['glnw_hj_1'][-1]['note']) && !empty($schueler['noten']['klnw_hj_1'][-1]['note'])){
                    $gew = $schueler['noten']['alt_b'][-1]['note'] == 1 ? 0 : 1;
                    // value in glnw AND klnw
                    $schueler['noten']['schnitt_hj_1'][-1]['value'] = self::getNotenSchnitt($klnw, $glnw, $gew);
                }
                elseif (!empty($schueler['noten']['glnw_hj_1'][-1]['note']) && empty($schueler['noten']['klnw_hj_1'][-1]['note'])) {
                    // Schnitt = kleine lnw
                    $schueler['noten']['schnitt_hj_1'][-1]['value'] = $glnw;
                }
                elseif (empty($schueler['noten']['glnw_hj_1'][-1]['note']) && !empty($schueler['noten']['klnw_hj_1'][-1]['note'])) {
                    // Schnitt = GROSSE LNW
                    $schueler['noten']['schnitt_hj_1'][-1]['value'] = $klnw;
                }
            }
            if($schueler['noten']['schnitt_hj_1'][-1]['value'] > 0){
                $schueler['noten']['schnitt_hj_1'][-1]['note'] = number_format(floatval($schueler['noten']['schnitt_hj_1'][-1]['value']), 2, $decimal_separator, '');
                $schueler['noten']['schnitt_hj_1'][-1]['label'] = number_format(floatval($schueler['noten']['schnitt_hj_1'][-1]['value']), 2, ',', '');
            }
            if(empty($schueler['noten']['note_hj_1'][-1]['note']) || $schueler['noten']['note_hj_1'][-1]['manuell'] == 0){
                if($schueler['noten']['schnitt_hj_1'][-1]['value'] > 0){
                    $schueler['noten']['note_hj_1'][-1]['note'] = round(floatval($schueler['noten']['schnitt_hj_1'][-1]['value']) - 0.01, 0, PHP_ROUND_HALF_UP);
                }
                else{
                    $schueler['noten']['note_hj_1'][-1]['note'] = '';
                }
            }
            // 1. HJ ins 2. HJ bei Schnitten übernehmen
            // GLNW 2. HJ
            $schueler['noten']['glnw_hj_2']['##sum##'] = $schueler['noten']['glnw_hj_1']['##sum##'] + $schueler['noten']['glnw_hj_2']['##sum##'];
            $schueler['noten']['glnw_hj_2']['##anz##'] = $schueler['noten']['glnw_hj_1']['##anz##'] + $schueler['noten']['glnw_hj_2']['##anz##'];
            if($schueler['noten']['glnw_hj_2']['##anz##'] !== 0 && $schueler['noten']['glnw_hj_2'][-1]['manuell'] == 0){
                //$schueler['noten']['glnw_hj_2'][-1]['value'] = floor(floatval($schueler['noten']['glnw_hj_2']['##sum##']) / $schueler['noten']['glnw_hj_2']['##anz##'] * 100) / 100;
                $schueler['noten']['glnw_hj_2'][-1]['value'] = self::getNotenBlockSchnitt($schueler['noten']['glnw_hj_2']['##sum##'], $schueler['noten']['glnw_hj_2']['##anz##']);
                $schueler['noten']['glnw_hj_2'][-1]['note'] = number_format(floatval($schueler['noten']['glnw_hj_2'][-1]['value']), 2, $decimal_separator, '');
                $schueler['noten']['glnw_hj_2'][-1]['label'] = number_format(floatval($schueler['noten']['glnw_hj_2'][-1]['value']), 2, ',', '');
            }
            // klnw 2. HJ
            $schueler['noten']['klnw_hj_2']['##sum##'] = $schueler['noten']['klnw_hj_1']['##sum##'] + $schueler['noten']['klnw_hj_2']['##sum##'];
            $schueler['noten']['klnw_hj_2']['##anz##'] = $schueler['noten']['klnw_hj_1']['##anz##'] + $schueler['noten']['klnw_hj_2']['##anz##'];
            if($schueler['noten']['klnw_hj_2']['##anz##'] !== 0 && $schueler['noten']['klnw_hj_2'][-1]['manuell'] == 0){
                //$schueler['noten']['klnw_hj_2'][-1]['value'] = floor(floatval($schueler['noten']['klnw_hj_2']['##sum##']) / $schueler['noten']['klnw_hj_2']['##anz##'] * 100) / 100;
                $schueler['noten']['klnw_hj_2'][-1]['value'] = self::getNotenBlockSchnitt($schueler['noten']['klnw_hj_2']['##sum##'], $schueler['noten']['klnw_hj_2']['##anz##']);
                $schueler['noten']['klnw_hj_2'][-1]['note'] = number_format(floatval($schueler['noten']['klnw_hj_2'][-1]['value']), 2, $decimal_separator, '');
                $schueler['noten']['klnw_hj_2'][-1]['label'] = number_format(floatval($schueler['noten']['klnw_hj_2'][-1]['value']), 2, ',', '');
            }

            // Gesamtschnitt 2. HJ
            $glnw_2 = $schueler['noten']['glnw_hj_2'][-1]['value'];
            $klnw_2 = $schueler['noten']['klnw_hj_2'][-1]['value'];
            if(empty($schueler['noten']['schnitt_hj_2'][-1]['note']) || $schueler['noten']['schnitt_hj_2'][-1]['manuell'] == 0){
                if(!empty($schueler['noten']['glnw_hj_2'][-1]['note']) && !empty($schueler['noten']['klnw_hj_2'][-1]['note'])){
                    $gew = $schueler['noten']['alt_b'][-1]['note'] == 1 ? 0 : 1;
                    // value in glnw AND klnw
                    $schueler['noten']['schnitt_hj_2'][-1]['value'] = self::getNotenSchnitt($klnw_2, $glnw_2, $gew);
                }
                elseif (!empty($schueler['noten']['glnw_hj_2'][-1]['note']) && empty($schueler['noten']['klnw_hj_2'][-1]['note'])) {
                    // Schnitt = kleine lnw
                    $schueler['noten']['schnitt_hj_2'][-1]['value'] = $glnw_2;
                }
                elseif (empty($schueler['noten']['glnw_hj_2'][-1]['note']) && !empty($schueler['noten']['klnw_hj_2'][-1]['note'])) {
                    // Schnitt = GROSSE LNW
                    $schueler['noten']['schnitt_hj_2'][-1]['value'] = $klnw_2;
                }
            }
            // value to note, value contains string like '1.23'
            if(floatval($schueler['noten']['schnitt_hj_2'][-1]['value']) > 0){
                $schueler['noten']['schnitt_hj_2'][-1]['note'] = number_format(floatval($schueler['noten']['schnitt_hj_2'][-1]['value']), 2, $decimal_separator, '');
                $schueler['noten']['schnitt_hj_2'][-1]['label'] = number_format(floatval($schueler['noten']['schnitt_hj_2'][-1]['value']), 2, ',', '');
            }
            if(empty($schueler['noten']['note_hj_2'][-1]['note']) || $schueler['noten']['note_hj_2'][-1]['manuell'] == 0){
                if($schueler['noten']['schnitt_hj_2'][-1]['value'] > 0){
                    $schueler['noten']['note_hj_2'][-1]['note'] = round(floatval($schueler['noten']['schnitt_hj_2'][-1]['value']) - 0.01, 0, PHP_ROUND_HALF_UP);
                }
                else{
                    $schueler['noten']['note_hj_2'][-1]['note'] = '';
                }
            }
        }
        // Austrittsdatum
        self::checkExitDate($schueler);
    }

    /**
     * get gewichtung from array
     * @param type $gewichtungen
     * @param type $blockname
     * @param type $index_im_block
     */
    static function getGewichtung($gewichtungen, $blockname, $index_im_block){
        $gew = 1;

        $gewKey = '';
        if($blockname == 'glnw_hj_1'){
            $gewKey = 'glnw_1_'.$index_im_block;
        }
        elseif($blockname == 'klnw_hj_1'){
            $gewKey = 'klnw_1_'.$index_im_block;
        }
        elseif($blockname == 'glnw_hj_2'){
            $gewKey = 'glnw_2_'.$index_im_block;
        }
        elseif($blockname == 'klnw_hj_2'){
            $gewKey = 'klnw_2_'.$index_im_block;
        }
        if(array_key_exists($gewKey, $gewichtungen)){
            $gew = $gewichtungen[$gewKey];
        }
        return $gew;
    }

    /**
     * Calculates the average value of grades-avgs
     * @param string $klnw
     * @param string $glnw
     * @param string $gew
     * @param int $scale
     * @return string
     */
    function getNotenSchnitt(string $klnw, string $glnw, string $gew, int $scale = 2){
        bcscale($scale + 3);
        // floor(((1+$gew)*$glnw + $klnw) / (2+$gew) * 100) /100;
        $schnitt = bcdiv( bcadd( bcmul( bcadd(1, $gew), $glnw), $klnw) , bcadd(2, $gew));
        return bcadd($schnitt, '0.0', $scale);
    }

    /**
     * Calculates the average value of a single grades block
     * @param int $sum
     * @param int $anz
     * @param int $scale
     * @return string
     */
    function getNotenBlockSchnitt(int $sum, int $anz, int $scale = 2){
        bcscale($scale + 3);
        // floor(((1+$gew)*$glnw + $klnw) / (2+$gew) * 100) /100;
        $schnitt = bcdiv(strval($sum), strval($anz));
        return bcadd($schnitt, '0.0', $scale);
    }


    function checkExitDate(&$schueler){
        if(!empty($schueler['nm_st']['st_asv_austrittsdatum'])){
            $exitDate = DateTime::createFromFormat('Y-m-d', $schueler['nm_st']['st_asv_austrittsdatum']);
            if($exitDate <= new DateTime()){
                $schueler['nm_st']['nm_st_class'] = 'nm_st_left';
            }
            $schueler['nm_st']['hint']['img'] = 'dialog_info';
            $schueler['nm_st']['hint']['text'] = 'Austrittsdatum: '.$schueler['nm_st']['st_asv_austrittsdatum'];
        }
    }

    function getNotenTemplate(){
        $note_empty = array(
            'note'   => '',
            'note_id'=> '',
            'update_date' => '',
            'update_user' => '',
            'art' => '',
            'definition_date' => '',
            'description' => '',
        );

        $tmpl = array(
            'alt_b' => array(
                -1 => array(
                    'note'   => false,
                    'note_id'=> '',
                    'img' => '',
                    'checked' => false
                ),
            ),
            'glnw_hj_1' => array(
                'avgclass' => '',
                -1 => array(
                    'note'   => '',
                    'note_id'=> '',
                    'manuell' => '0',
                ),
                0 => $note_empty,
                1 => $note_empty,
                2 => $note_empty,
            ),
            'klnw_hj_1' => array(
                'avgclass' => '',
                -1 =>  array(
                    'note'   => '',
                    'note_id'=> '',
                    'manuell' => '0'
                ),
                0 => $note_empty,
                1 => $note_empty,
                2 => $note_empty,
                3 => $note_empty,
                4 => $note_empty,
                5 => $note_empty,
                6 => $note_empty,
                7 => $note_empty,
                8 => $note_empty,
                9 => $note_empty,
                10 => $note_empty,
                11 => $note_empty,
            ),
            'schnitt_hj_1' =>  array(
                'avgclass' => '',
                -1 =>  array(
                    'note'   => '',
                    'note_id'=> '',
                    'manuell' => '0'
                )
            ),
            'note_hj_1' =>  array(
                'avgclass' => '',
                -1 =>  array(
                    'note'   => '',
                    'note_id'=> '',
                    'manuell' => '0'
                )
            ),
            'm_hj_1' =>  array(
                -1 =>  array(
                    'note'   => '',
                    'note_id'=> ''
                )
            ),
            'v_hj_1' =>  array(
                -1 =>  array(
                    'note'   => '',
                    'note_id'=> ''
                )
            ),
            'glnw_hj_2' => array(
                'avgclass' => '',
                -1 => array(
                    'note'   => '',
                    'note_id'=> '',
                    'manuell' => '0',
                ),
                0 => $note_empty,
                1 => $note_empty,
                2 => $note_empty,
            ),
            'klnw_hj_2' => array(
                'avgclass' => '',
                -1 =>  array(
                    'note'   => '',
                    'note_id'=> '',
                    'manuell' => '0'
                ),
                0 => $note_empty,
                1 => $note_empty,
                2 => $note_empty,
                3 => $note_empty,
                4 => $note_empty,
                5 => $note_empty,
                6 => $note_empty,
                7 => $note_empty,
                8 => $note_empty,
                9 => $note_empty,
                10 => $note_empty,
                11 => $note_empty,
            ),
            'schnitt_hj_2' =>  array(
                'avgclass' => '',
                -1 =>  array(
                    'note'   => '',
                    'note_id'=> '',
                    'manuell' => '0'
                )
            ),
            'note_hj_2' =>  array(
                'avgclass' => '',
                -1 =>  array(
                    'note'   => '',
                    'note_id'=> '',
                    'manuell' => false
                )
            ),
            'm_hj_2' =>  array(
                -1 =>  array(
                    'note'   => '',
                    'note_id'=> ''
                )
            ),
            'v_hj_2' =>  array(
                -1 =>  array(
                    'note'   => '',
                    'note_id'=> ''
                )
            )
        );
        return $tmpl;
    }


    /**
     * Save auto calculated values to Database
     * @param type $rows
     */
    function writeAutoValues($rows, $koppel_id){
        foreach($rows as $id => &$schueler){
            foreach($schueler['noten'] as $blockname => &$notenblock){
                if(array_key_exists('manuell', $notenblock[-1]) && $notenblock[-1]['manuell'] == 0 && array_key_exists('note', $notenblock[-1])){// && $notenblock[-1]['note'] > 0){
                    $note = array(
                        'schueler_id' => $schueler['nm_st']['st_asv_id'],
                        'koppel_id' => $koppel_id,
                        'fach_id' => $schueler['fach']['fach_id'],
                        'belegart_id' => $schueler['fach']['belegart_id'],
                        'jahrgangsstufe_id' => $schueler['klasse']['jahrgangsstufe_id'],
                        'note_blockbezeichner' => $blockname,
                        'note_index_im_block' => -1,
                        'note_note' => $notenblock[-1]['note'],
                        'note_asv_note_manuell' => 0
                    );
                    if(array_key_exists('note_id', $notenblock[-1])){
                        $note['note_id'] = $notenblock[-1]['note_id'];
                    }
                    $this->save($note);
                }
            }
        }
        return 0;
    }
}