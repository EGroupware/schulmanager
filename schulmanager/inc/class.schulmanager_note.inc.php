<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Combination of Klassengruppe ans Schuelerfach
 *
 * @author axel
 */
class schulmanager_note {

	/**
	 * @var int $id
	 */
	var $id;
	/**
	 * @var string $asv_id
	 */
	var $asv_id;

	/**
	 * @var int
	 */
	var $note;

	/**
	 * @var string $schuelerfach_asv_id
	 */
	var $schuelerfach_asv_id;
	/**
	 * @var string $schuelerfach_asv_kurzform
	 */
	var $schuelerfach_asv_kurzform;
	/**
	 * @var string $schuelerfach_asv_anzeigeform
	 */
	var $schuelerfach_asv_anzeigeform;

	/**
	 * Constructor
	 */
	function __construct($kg_asv_id, $kl_asv_id, $kl_asv_klassenname, $sf_asv_id, $sf_asv_kurzform, $sf_asv_anzeigeform)
	{
		$this->klassengruppe_asv_id = $kg_asv_id;

		$this->klasse_asv_id = $kl_asv_id;
		$this->klasse_asv_klassenname = $kl_asv_klassenname;

		$this->schuelerfach_asv_id = $sf_asv_id;
		$this->schuelerfach_asv_kurzform = $sf_asv_kurzform;
		$this->schuelerfach_asv_anzeigeform = $sf_asv_anzeigeform;
	}

	public function getKlassengruppe_asv_id() {
		return $this->klassengruppe_asv_id;
	}

	public function getKlassengruppe_asv_klassenname() {
		return $this->klassengruppe_asv_klassenname;
	}

	public function getSchuelerfach_asv_id() {
		return $this->schuelerfach_asv_id;
	}

	public function getSchuelerfach_asv_kurzform() {
		return $this->schuelerfach_asv_kurzform;
	}

	public function getSchuelerfach_asv_anzeigeform() {
		return $this->schuelerfach_asv_anzeigeform;
	}

	/**
	 *
	 * @return string sample: '10A M'
	 */
	public function getFormatKgSf(){
		return $this->klasse_asv_klassenname.' '.$this->schuelerfach_asv_kurzform;
	}




//put your code here
}
