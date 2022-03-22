<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use EGroupware\Api;
/**
 * Description of class
 *
 * @author axel
 */
class schulmanager_note_gew_so extends Api\Storage {

	var $sm_note_gew_table = 'egw_schulmanager_note_gew';

	var $value_col = array();

	public function __construct(){
		//parent::__construct('schulmanager', $this->sm_note_gew_table);
        $this->db = clone($GLOBALS['egw']->db);
        $this->db->set_app('schulmanager');
		$this->all_tables = array($this->sm_note_gew_table);

		$this->setup_table('schulmanager', $this->sm_note_gew_table);

		$this->debug = 0;

		$this->value_col[] = 'ngew_id';
		$this->value_col[] = 'ngew_asv_schueler_schuelerfach_id';
		$this->value_col[] = 'ngew_asv_klassengruppe_id';
		$this->value_col[] = 'ngew_blockbezeichner';
		$this->value_col[] = 'ngew_index_im_block';
		$this->value_col[] = 'ngew_gew';
		$this->value_col[] = 'ngew_create_date';
		$this->value_col[] = 'ngew_create_user';
		$this->value_col[] = 'ngew_update_date';
		$this->value_col[] = 'ngew_update_user';

	}


	/**
	 * saves a resource including extra fields
	 *
	 * @param array $note key => value
	 * @return mixed id of resource if all right, false if fale
	 */
	function &load($kg_asv_id, $sf_asv_id, &$gewichtungen)
	{
		$key_col = '';

		$filter = array();
		$filter[] = "ngew_asv_klassengruppe_id='".$kg_asv_id."'";
		$filter[] = "ngew_asv_schueler_schuelerfach_id='".$sf_asv_id."'";

		$result = $this->query_list($this->value_col, $key_col, $filter);
		$this->setDefaultGew($gewichtungen);

		foreach($result as $row){
			$gewKey = $row[3];
			// schnitt_g und schnitt_k ohne index_im_block, bzw index_im_block = -1
			if($row[4] >= 0){
				// normale noten im block
				$gewKey .= '_'.$row[4];
			}

			$gewVal = $row[5];

			$gewichtungen[$gewKey] = $gewVal;
		}
		// copy gewichtungen schnitt
		$rows['schnitt_g_2'] = $rows['schnitt_g'];
		$rows['schnitt_k_2'] = $rows['schnitt_k'];
	}

	/**
	 * Setzt die default Gewichtungen
	 */
	function setDefaultGew(&$rows){
		$rows['glnw_1_0'] = 1;
		$rows['glnw_1_1'] = 1;
		$rows['glnw_1_2'] = 1;

		for ($i = 0; $i < 6; $i++) {
			$rows['klnw_1_'.$i] = 1;
		}

		$rows['glnw_2_0'] = 1;
		$rows['glnw_2_1'] = 1;
		$rows['glnw_2_2'] = 1;

		for ($i = 0; $i < 6; $i++) {
			$rows['klnw_2_'.$i] = 1;
		}

		$rows['schnitt_g'] = 2;
		$rows['schnitt_g_2'] = 2;
		$rows['schnitt_k'] = 1;
		$rows['schnitt_k_2'] = 1;
	}

	/**
	 *
	 * @param type $gew Gewichtung
	 * @param type $kg_id Klassengruppe_id
	 * @param type $sf_id schuelerfach_id
	 * @param type $blockbezeichner Blockbezeichner
	 * @param type $index_im_block Index im Block
	 */
	function save($gew, $kg_asv_id, $sf_asv_id, $blockbezeichner, $index_im_block)
	{
		$time = date("Y-m-d H:i:s").'.000';//time();
		$kennung = $GLOBALS['egw_info']['user']['account_lid'];

		// load saved gewichtungen
		$key_col = "";

		$filter = array();
		$filter[] = "ngew_asv_klassengruppe_id='".$kg_asv_id."'";
		$filter[] = "ngew_asv_schueler_schuelerfach_id='".$sf_asv_id."'";
		$filter[] = "ngew_blockbezeichner='".$blockbezeichner."'";
		$filter[] = "ngew_index_im_block='".$index_im_block."'";

		$result = $this->query_list($this->value_col, $key_col, $filter);

		$gewichtung = array(
				'ngew_asv_klassengruppe_id' => $kg_asv_id,
				'ngew_asv_schueler_schuelerfach_id' => $sf_asv_id,
				'ngew_blockbezeichner' => $blockbezeichner,
				'ngew_index_im_block' => $index_im_block,
				'ngew_gew' => $gew,
				'ngew_update_user' => $kennung,
				'ngew_update_date' => $time,
			);


		// array_key_first php >= 7.3
		if(sizeof($result) > 0){
			// modified != 1
			$ids = array_keys($result);
			$gewichtung['ngew_id'] = $ids[0];

			$this->data = $gewichtung;
			if($gew != 1){
				if(parent::update($gewichtung, true) != 0) return false;
			}
			else{
				// delete, if gew == 1 (default)
				if(parent::delete() != 0) return false;
			}
		}
		else{
			if($gew != 1){
				// not saved before
				$gewichtung['ngew_create_user'] = $kennung;
				$gewichtung['ngew_create_date'] = $time;
				$this->data = $gewichtung;
				if(parent::save() != 0) return false;
			}
		}

		/**/
	}


//put your code here
}
