<?php

/**
 * Schulmanager -
 *
 * @link http://www.egroupware.org
 * @package schulmanager
 * @author Axel Wild <info-AT-wild-solutions.de>
 * @copyright (c) 2022 by Axel Wild <info-AT-wild-solutions.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * combination of class and subject
 */
class schulmanager_klassengr_schuelerfa {

	/**
	 * @var string $klassengruppe_asv_id
	 */
	var $klassengruppe_asv_id;
	/**
	 * @var string $klassengruppe_asv_kennung
	 */
	var $klassengruppe_asv_kennung;
	/**
	 * @var string $klassengruppe_asv_kennung
	 */
	var $klasse_asv_klassenname;

	/**
	 * @var string $klasse_asv_id
	 */
	var $klasse_asv_id;

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
	function __construct($kg_asv_id, $kg_asv_kennung, $kl_asv_id, $kl_asv_klassenname, $sf_asv_id, $sf_asv_kurzform, $sf_asv_anzeigeform)
	{
		$this->klassengruppe_asv_id = $kg_asv_id;
		$this->klassengruppe_asv_kennung = $kg_asv_kennung;

		$this->klasse_asv_id = $kl_asv_id;
		$this->klasse_asv_klassenname = $kl_asv_klassenname;

		$this->schuelerfach_asv_id = $sf_asv_id;
		$this->schuelerfach_asv_kurzform = $sf_asv_kurzform;
		$this->schuelerfach_asv_anzeigeform = $sf_asv_anzeigeform;
	}

	public function getKlassengruppe_asv_id() {
		return $this->klassengruppe_asv_id;
	}

	public function getKlassengruppe_asv_kennung() {
		return $this->klassengruppe_asv_kennung;
	}
	
	public function getKlasse_asv_id() {
	    return $this->klasse_asv_id;
	}
	
	public function getKlasse_asv_klassenname() {
	    return $this->klasse_asv_klassenname;
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
	 * formats representing key
	 * @return string sample: '10A M'
	 */
	public function getFormatKgSf(){
		return $this->klasse_asv_klassenname.' '.$this->schuelerfach_asv_kurzform;
	}
}
