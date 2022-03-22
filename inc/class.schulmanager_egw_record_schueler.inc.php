<?php
/**
 * eGroupWare - Schulmanager - importexport
 *
 * IMPORTANT: Not used! Needs some refactoring!!!!
 *
 * @link http://www.egroupware.org
 * @package schulmanager
 * @author Axel Wild <info-AT-wild-solutions.de>
 * @copyright (c) 2022 by Axel Wild <info-AT-wild-solutions.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * compability layer for iface_egw_record needed for importexport
 */
class schulmanager_egw_record_schueler implements importexport_iface_egw_record
{

    private $identifier = '';
    private $schuelerentry = array();
    private $schueler_bo;

    // Used in conversions
    static $types = array(
        /*'float' => array('ts_quantity','ts_unitprice'),
        'select-account' => array('ts_owner','ts_modifier'),
        'date-time' => array('ts_start', 'ts_created', 'ts_modified'),
        'select-cat' => array('cat_id'),
        'links' => array('pl_id'),
        'select' => array('ts_status'),*/
    );



    /**
     * constructor
     * reads record from backend if identifier is given.
     *
     * @param string $_identifier
     */
    public function __construct( $_identifier='' ){
        $this->identifier = $_identifier;
        $this->schueler_bo = new schulmanager_schueler_bo();

        if (($data = $this->schueler_bo->read($this->identifier)))
        {
            $this->set_record($data);
        }
    }

    /**
     * magic method to set attributes of record
     *
     * @param string $_attribute_name
     */
    public function __get($_attribute_name) {
        return $this->schuelerentry[$_attribute_name];
    }

    /**
     * magig method to set attributes of record
     *
     * @param string $_attribute_name
     * @param data $data
     */
    public function __set($_attribute_name, $data) {
        $this->schueleretentry[$_attribute_name] = $data;
    }

    public function __unset($_attribute_name)
    {
        unset($this->schuelerentry[$_attribute_name]);
    }
    /**
     * converts this object to array.
     * @abstract We need such a function cause PHP5
     * dosn't allow objects do define it's own casts :-(
     * once PHP can deal with object casts we will change to them!
     *
     * @return array complete record as associative array
     */
    public function get_record_array() {
        return $this->schuelerentry;
    }

    /**
     * gets title of record
     *
     *@return string title
     */
    public function get_title()
    {
        return $this->schuelerentry['sch_asv_familienname'] . ' - ' . $this->schuelerentry['sch_asv_rufname'];
    }

    /**
     * sets complete record from associative array
     *
     * @todo add some checks
     * @return void
     */
    public function set_record(array $_record){
        $this->schuelerentry = $_record;
    }

    /**
     * gets identifier of this record
     *
     * @return string identifier of current record
     */
    public function get_identifier() {
        return $this->identifier;
    }

    /**
     * Gets the URL icon representitive of the record
     * This could be as general as the application icon, or as specific as a contact photo
     *
     * @return string Full URL of an icon, or appname/icon_name
     */
    public function get_icon() {
        return 'schulmanager/navbar';
    }

    /**
     * saves record into backend
     *
     * @return string identifier
     */
    public function save ( $_dst_identifier ) {
        unset($_dst_identifier);
    }

    /**
     * copies current record to record identified by $_dst_identifier
     *
     * @param string $_dst_identifier
     * @return string dst_identifier
     */
    public function copy ( $_dst_identifier ) {
        unset($_dst_identifier);
    }

    /**
     * moves current record to record identified by $_dst_identifier
     * $this will become moved record
     *
     * @param string $_dst_identifier
     * @return string dst_identifier
     */
    public function move ( $_dst_identifier ) {
        unset($_dst_identifier);
    }

    /**
     * delets current record from backend
     *
     */
    public function delete () {

    }

    /**
     * destructor
     *
     */
    public function __destruct() {
        unset ($this->schueler_bo);
    }

}
