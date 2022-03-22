<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of class
 *
 * @author axel
 */
class schulmanager_schueler_bo{


	/**
	 * @var string $schueler_schuljahr_id
	 */
	//var $schueler_schuljahr_id;

	/**
	 * Instance of the so schueler
	 *
	 * @var schulmanager_schueler_so
	 */
	var $so;

	/**
	 * Constructor
	 */
	function __construct()
	{
		//$this->schueler_schuljahr_id = $schueler_schuljahr_id;
		$this->so = new schulmanager_schueler_so();

	}

	/**
	 * Liefert eine Notenübersicht über alle belegten Fächer
	 * @param type $schueler_id
	 * @param type $rows
	 */
	function getNotenAbstract($schueler, &$rows, $rowid){
		$schueler_schuljahr_id = $schueler['nm_st']['sch_schuljahr_asv_id'];
		$this->so->getNotenAbstract($schueler_schuljahr_id, $rows, $rowid);
	}



//put your code here
}
