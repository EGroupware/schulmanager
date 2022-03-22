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
class schulmanager_noteblock_bo{


	/**
	 * @var array noten
	 */
	var $noten;

	/**
	 * @var string $schueler_schuljahr_id
	 */
	var $schueler_schuljahr_id;
	/**
	 * @var string $schuelerfach_asv_kurzform
	 */
	var $schueler_schuelerfach_id;
	/**
	 * @var string $schuelerfach_asv_anzeigeform
	 */
	var $blockbezeichner;


	/**
	 * Constructor
	 */
	function __construct($schueler_schuljahr_id, $schueler_schuelerfach_id, $blockbezeichner)
	{
		$this->schueler_schuljahr_id = $schueler_schuljahr_id;
		$this->schueler_schuelerfach_id = $schueler_schuelerfach_id;
		$this->blockbezeichner = $blockbezeichner;

	}


	function getSchueler_schuljahr_id() {
		return $this->schueler_schuljahr_id;
	}

	function getSchueler_schuelerfach_id() {
		return $this->schueler_schuelerfach_id;
	}

	function getBlockbezeichner() {
		return $this->blockbezeichner;
	}

	/**
	 *
	 * @param schulmanager_note_bo $note
	 */
	function addNote($note){
		$this->noten[$note->getIndex_in_block()] = $note;
	}

	/**
	 *
	 * @param int $index_in_block
	 * @return schulmanager_note_bo
	 */
	function getNote($index_in_block){
		return $this->noten[$index_in_block];
	}



//put your code here
}
