<?php

/**
 * EGroupware Schulmanager - student contact - bussiness object
 *
 * @link http://www.egroupware.org
 * @package schulmanager
 * @author Axel Wild <info-AT-wild-solutions.de>
 * @copyright (c) 2022 by info-AT-wild-solutions.de
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * Contact of a student
 * @author axel
 */
class schulmanager_schueleranschrift_bo {

    /**
     * Instance of  so object
     * @var schulmanager_so
     */
    var $so;

    var $schueler_stamm_id;

    var $anschriftData;

    var $adressBlock;


    /**
     * Constructor
     */
    function __construct()
    {
    }

    /**
     * Read from DB
     * @param $schueler_stamm_id
     * @return void
     */
    function read($schueler_stamm_id)
    {
        $this->so = new schulmanager_schueleranschrift_so();
        $this->schueler_stamm_id = $schueler_stamm_id;
        $this->so = new schulmanager_schueleranschrift_so();
        $query_in = array('start' => 0,);
        $this->anschriftData = array();
        $this->so->queryBySchueler($query_in,$this->anschriftData, $schueler_stamm_id);
    }

    public function getHauptAnsprechPartner(){
        foreach ($this->anschriftData as $key => $value) {
            if ($value['san_asv_hauptansprechpartner']) {
                return $value;
            }
        }

    }
}