<?php

/**
 * EGroupware Schulmanager - ui
 *
 * @link http://www.egroupware.org
 * @package schulmanager
 * @author Axel Wild <info-AT-wild-solutions.de>
 * @copyright (c) 2022 by info-AT-wild-solutions.de
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

use EGroupware\Api;
use EGroupware\Api\Egw;
use EGroupware\Api\Framework;
use EGroupware\Api\Etemplate;

/**
 * This class is the UI-layer (user interface)
 */
class schulmanager_ui
{
    var $public_functions = array(
        'index'       => True,
        'edit'		  => True,
        'notenDetails'		  => True,
        'klassenview' => True,
        'exportasv_jz'	  => True,
        'exportasv_zz'	  => True,
        //'calendar_week' => True,
        'schuelerview' => True,
    );
    /**
     * reference to the infolog preferences of the user
     * @var array
     */
    var $prefs;
    /**
     * instance of the bo-class
     *
     * @var schulmanager_bo
     */
    var $bo;

    /**
     * instance of the bo-class
     *
     * @var wl_bo
     */
    var $wl_bo;

    /**
     * instance of the bo-class
     *
     * @var schueler_bo
     */
    var $schueler_bo;

    var $calendar_ui;


    /**
     * Constructor
     *
     * @return schulmanager_ui
     */
    function __construct(Etemplate $etemplate = null)
    {
        $this->bo = new schulmanager_bo();
        $this->wl_bo = new schulmanager_werteliste_bo();
        $this->schueler_bo = new schulmanager_schueler_bo();
        $this->calendar_ui = new schulmanager_cal_ui();
    }

    /**
     * Context menu
     * @return array
     */
    public static function get_actions(array $content)
    {
        $actions = array(
            'details' => array(
                'caption' => 'Noten-Details...',
                'group' => $group = 1,
                'allowOnMultiple' => false,
                'icon' => 'show',
                'postSubmit' => true,
                'shortcut' => array('ctrl' => true, 'shift' => true, 'keyCode' => 78, 'caption' => 'Ctrl + Shift + N'),
                'onExecute' => 'javaScript:app.schulmanager.onDetailsNote',
            ),
            'contact' => array(
                'caption' => 'Kontaktdaten...',
                'group' => $group = 1,
                'allowOnMultiple' => false,
                'icon' => 'show',
                'postSubmit' => true,
                'shortcut' => array('ctrl' => true, 'shift' => true, 'keyCode' => 78, 'caption' => 'Ctrl + Shift + N'),
                'onExecute' => 'javaScript:app.schulmanager.onContactData',
            ),
            'documents' => schulmanager_merge::document_action(
                //$GLOBALS['egw_info']['user']['preferences']['schulmanager']['document_dir'],
                '/templates/schulmanager',
                $group, 'Insert in document', 'document_'
            ),
        );
        return $actions;
    }

    /**
     * apply an action
     *
     * @param string/int $action
     * @param array $checked timesheet id's to use if !$use_all
     * @param boolean $use_all if true use all timesheets of the current selection (in the session)
     * @param int &$success number of succeded actions
     * @param int &$failed number of failed actions (not enought permissions)
     * @param string &$action_msg translated verb for the actions, to be used in a message like %1 timesheets 'deleted'
     * @param string/array $session_name 'index' or 'email', or array with session-data depending if we are in the main list or the popup
     * @return boolean true if all actions succeded, false otherwise
     */
    function action($action,$checked,$use_all,&$success,&$failed,&$action_msg,$session_name,&$msg)
    {
        $success = $failed = 0;
        switch($action)
        {
        }
        return "Unknown action '$action'!";
    }



    /**
     * List Schulmanager entries
     *
     * @param array $content
     * @param string $msg
     */
    function index(array $content = null,$msg='')
    {
        $config = Api\Config::read('schulmanager');
        $etpl = new Etemplate('schulmanager.notenmanager.index');

        if ($_GET['msg']) $msg = $_GET['msg'];

        $sel_options = array();
        $preserv = array();

        $filter = Api\Cache::getSession('schulmanager', 'filter');
        if(empty($filter)){
            $filter = 0;
        }

        if (is_array($content))
        {
            $button = @key($content['nm']['button']);

            unset($content['nm']['button']);
            if ($button)
            {
                if($button == 'edit'){
                    Framework::redirect_link('/index.php',array('menuaction' => 'schulmanager.schulmanager_ui.edit','ajax' => 'true'));
                }
            }
        }

        if (!is_array($content['nm']))
        {
            //$content = array();
            $content['nm'] = array();
            $content['msg'] = $msg;

            $content['nm']['get_rows']		= 'schulmanager.schulmanager_ui.get_rows';
            $content['nm']['no_filter'] 	= False;
            $content['nm']['filter_no_lang'] = true;
            $content['nm']['no_cat']	= true;
            $content['nm']['no_search']	= true;
            $content['nm']['no_filter2']	= true;
            $content['nm']['bottom_too']	= true;
            $content['nm']['order']		= 'nm_id';
            $content['nm']['sort']		= 'ASC';
            $content['nm']['row_id']	= 'nm_id';
            $content['nm']['favorites'] = false;
            $content['nm']['filter'] = $filter;
            $content['nm']['actions'] = self::get_actions($content);
            $content['nm']['no_columnselection'] = false;
            $content['nm']['num_rows'] = 5;

            $content['nm']['options-filter'] = $this->bo->getKlassenFachList();

            $readonlys = array(
                'button[export_pdf]'     => false,
            );
        }

        $content['nm']['edit_grades_enabled'] = (bool) $config['edit_grades_enabled'];
        $content['edit_grades_enabled'] = (bool) $config['edit_grades_enabled'];
        $readonlys = array(
            'button[edit]'     => false,
        );
        $preserv = $sel_options;
        return $etpl->exec('schulmanager.schulmanager_ui.index',$content,$sel_options,$readonlys,$preserv);
    }

    /**
     * Show details of grades
     * @param $content
     * @param $view
     * @return Etemplate\Request|string
     * @throws Api\Exception\AssertionFailed
     */
    function notenDetails($content = null, $view = false)
    {
        $msg = '';
        $config = Api\Config::read('schulmanager');

        $etpl = new Etemplate('schulmanager.notenmanager.notendetails');
        $preserv = array();
        $sel_options = array();

        //	else{

        Api\Cache::unsetSession('schulmanager', 'notenmanager_modified_records');
        $content = array();
        $content['msg'] = $msg ? $msg : $_GET['msg'];

        // edit disabled
        $edit_grades_enabled = (bool) $config['edit_grades_enabled'];
        $content['edit_grades_disabled'] = !$edit_grades_enabled;

        $gnlwList = schulmanager_werteliste_bo::getNotenArtList(True);
        $knlwList = schulmanager_werteliste_bo::getNotenArtList(False);

        $sel_options['notgebart_glnw'] = $gnlwList;
        $sel_options['notgebart_klnw'] = $knlwList;
        $sel_options['select_klasse'] = $this->bo->getKlassenFachList();
        $sel_options['select_schueler'] = array();

        // todo testen, ob filter isset
        $content['select_klasse'] = Api\Cache::getSession('schulmanager', 'filter');
        // TODO
        $selected_schueler_index = Api\Cache::getSession('schulmanager', 'details_filter_schueler');
        if(!isset($selected_schueler_index)){
            $selected_schueler_index = 0;
            Api\Cache::setSession('schulmanager', 'details_filter_schueler', $selected_schueler_index);
        }

        $xrsf_token = bin2hex(random_bytes(32));
        Api\Cache::setSession('schulmanager', 'token_notenDetails_modified', $xrsf_token);
        $content['token'] = $xrsf_token;

        $content['select_schueler'] = $selected_schueler_index;
        $rows = Api\Cache::getSession('schulmanager', 'notenmanager_rows');

        $sel_options['select_schueler'] = $this->extractSchuelerListFromRows($rows);

        $this->extractSchuelerDataFromRows($content, $rows, $selected_schueler_index);

        $readonlys = array(
            'button[save]'     => false,
            'button[apply]'    => false,
            'button[cancel]'   => false,
        );

        $preserv = $content;
        return $etpl->exec('schulmanager.schulmanager_ui.notendetails',$content,$sel_options,$readonlys,$preserv);
    }

    /**
     * List Schulmanager entries
     * @param array $content
     * @param string $msg
     */
    function edit($content = null, $view = false)
    {
        $config = Api\Config::read('schulmanager');
        $edit_grades_enabled = (bool) $config['edit_grades_enabled'];
        if(!$edit_grades_enabled){
            return;
        }
        $etpl = new Etemplate('schulmanager.notenmanager.edit');
        $preserv = array();
        $sel_options = array();
        if (is_array($content))
        {
            $button = @key($content['nm']['button']);

            unset($content['nm']['button']);
            if ($button)
            {
                if ($button == 'save' || $button == 'apply')
                {
                    // check token for security purpose
                    if(!$this->checkToken('token_note_modified', $content['nm']['token'])){
                        $msg  = 'ERROR: could not save date!';
                    }
                    else {
                        $modified_records = Api\Cache::getSession('schulmanager', 'notenmanager_modified_records');
                        $modified_gewichtung = Api\Cache::getSession('schulmanager', 'notenmanager_modified_gewichtung');
                        $msg = '';
                        if (isset($modified_records) || isset($modified_gewichtung)) {
                            $notenArtCombiList = schulmanager_werteliste_bo::getNotenArtListCombi();
                            $content['inputinfo']['art'] = $notenArtCombiList[$content['inputinfo']['notgebart']];

                            if (!($info_id = $this->bo->write($content['inputinfo']))) {
                                $content['msg'] = lang('Could not save entry');
                            } else {
                                $content['msg'] = lang('Entry saved');
                            }
                        } else {
                            $content['msg'] = lang('Nothing to save!');
                        }
                    }
                }
                if ($button == 'save' || $button == 'apply' || $button == 'cancel')
                {
                    Api\Cache::unsetSession('schulmanager', 'notenmanager_temp_rows');
                    Api\Cache::unsetSession('schulmanager', 'notenmanager_modified_records');
                    Api\Cache::unsetSession('schulmanager', 'notenmanager_modified_gewichtung');
                    Api\Cache::unsetSession('schulmanager', 'token_note_modified');
                }

                if ($button == 'save' || $button == 'cancel')
                {
                    Framework::redirect_link(Egw::link('/index.php',array('menuaction' => 'schulmanager.schulmanager_ui.index','ajax' => 'true')));
                }
            }
        }
        elseif(isset($_POST["action"]) && $_POST['action'] === 'buffer-modified'){
            if(isset($_POST['notekey'])){
                // Note wurde geändert
                $notekey = $_POST['notekey'];
                $noteval = $_POST['noteval'];

                $modified_records = Api\Cache::getSession('schulmanager', 'notenmanager_modified_records');
                if(!is_array($modified_records)){
                    // maybe first modified record
                    $modified_records = array();
                }
                $modified_records[$notekey] = $noteval;
                Api\Cache::setSession('schulmanager', 'notenmanager_modified_records', $modified_records);
            }
            elseif(isset($_POST['gewkey'])){
                // Gewichtung wurde geändert
                $gewkey = $_POST['gewkey'];
                $gewval = $_POST['gewval'];

                $modified_gewichtung = Api\Cache::getSession('schulmanager', 'notenmanager_modified_gewichtung');
                if(!is_array($modified_gewichtung)){
                    // maybe first modified record
                    $modified_gewichtung = array();
                }
                $modified_gewichtung[$gewkey] = $gewval;
                Api\Cache::setSession('schulmanager', 'notenmanager_modified_gewichtung', $modified_gewichtung);
            }

        }

        Api\Cache::unsetSession('schulmanager', 'notenmanager_modified_records');
        $content = array();
        $content['msg'] = $msg ? $msg : $_GET['msg'];

        $content['nm']['get_rows']		= 'schulmanager.schulmanager_ui.get_rows_edit';
        $content['nm']['no_filter'] 	= true;
        $content['nm']['filter_no_lang'] = true;
        $content['nm']['no_cat']	= true;
        $content['nm']['no_search']	= true;
        $content['nm']['no_filter2']	= true;
        $content['nm']['bottom_too']	= true;
        $content['nm']['order']		= 'nm_id';
        $content['nm']['sort']		= 'ASC';
        $content['nm']['row_id']	= 'nm_id';
        $content['nm']['lettersearch'] = false;
        $content['nm']['no_columnselection'] = false;
        $content['nm']['favorites'] = false;
        $content['nm']['options-filter'] = $this->bo->getKlassenFachList();

        $xrsf_token = bin2hex(random_bytes(32));
        Api\Cache::setSession('schulmanager', 'token_note_modified', $xrsf_token);

        $content['nm']['token'] = $xrsf_token;

        $content['inputinfo']['date'] = new DateTime();
        $content['inputinfo']['desc'] = '';

        $sel_options['notgebart'] = schulmanager_werteliste_bo::getNotenArtListCombi();// $wl_notgebart;

        $readonlys = array(
            'button[save]'     => false,
            'button[apply]'    => false,
            'button[cancel]'   => false,
        );

        $preserv = $content;
        // TODO 1. oder 2. Halbjahr eingeben
        return $etpl->exec('schulmanager.schulmanager_ui.edit',$content,$sel_options,$readonlys,$preserv);
    }

    /**
     * List Schulmanager entries
     *
     * @param array $content
     * @param string $msg
     */
    function klassenview(array $content = null,$msg='')
    {
        $etpl = new Etemplate('schulmanager.notenmanager.klassenview');
        $readonlys = array();
        $sel_options = array();
        $preserv = array();

        if (is_array($content))
        {
            $button = @key($content['nm']['button']);
            unset($content['nm']['button']);
            if ($button)
            {

            }
        }
        elseif(isset($_POST["action"]) && $_POST['action'] === 'export_pdf_klassenview'){

        }
        else{
            if (!is_array($content['nm']))
            {

                $content = array();
                $content['msg'] = $msg ? $msg : $_GET['msg'];
                $content['nm'] = array();
                $content['nm']['get_rows']		= 'schulmanager.schulmanager_ui.get_klassen_rows';
                $content['nm']['no_filter2']	= True;
                $content['nm']['no_cat']	= True;
                $content['nm']['bottom_too']	= true;
                $content['nm']['order']		= 'nm_id';
                $content['nm']['sort']		= 'ASC';
                $content['nm']['is_parent']		= 'is_par';
                $content['nm']['parent_id']		= 'schueler_id';
                $content['nm']['row_id']	= 'nm_id';
                $content['nm']['filter'] = 0;
                $content['nm']['options-filter'] = $this->bo->getClassLeaderClasses();

                $readonlys = array(
                    'button[export_pdf]'     => false,
                );
            }
        }

        $klsOptions = array();
        $kls = array();
        $selected_klasse_index = Api\Cache::getSession('schulmanager', 'klassen_filter_id');
        if(!isset($selected_klasse_index)) {
            $selected_klasse_index = 0;
        }
        $klassenasvids = Api\Cache::getSession('schulmanager', 'klassen_asv_ids');
        $kl_asv_id = $klassenasvids[$selected_klasse_index];
        $this->bo->getKlassenleitungen($kl_asv_id, $kls);
        Api\Cache::setSession('schulmanager', 'klassenleitungen', $kls);
        foreach($kls as $key => $value){
            $klsOptions[] = $value['ls_asv_zeugnisname1'];
        }
        $sel_options['klassleiter'] = $klsOptions;
        $content['klassleiter'] = $klsOptions;

        $preserv = $sel_options;
        return $etpl->exec('schulmanager.schulmanager_ui.klassenview',$content,$sel_options,$readonlys,$preserv);
    }

    /**
     * List Schulmanager entries
     *
     * @param array $content
     * @param string $msg
     */
    function exportasv_jz(array $content = null,$msg='')
    {
        if($GLOBALS['egw_info']['user']['apps']['admin'])
        {
            $xmlData = array(
                'type'	=> 'text/xml',
                'charset' => 'utf8',
                'filename'	=> 'noten-export-jz.xml',
            );

            $path = 'asvexport_jz.xml';
            $mime = '';

            $length = 0;
            // public static function safe(&$content, $path, &$mime='', &$length=0, $nocache=true, $force_download=true, $no_content_type=false)
            Api\Header\Content::safe($xmlData, $path, $mime, $length, True, True);
            echo $this->bo->asvNotenExport(2);
        }

        exit();
    }

    /**
     * List Schulmanager entries
     *
     * @param array $content
     * @param string $msg
     */
    function exportasv_zz(array $content = null,$msg='')
    {
        if($GLOBALS['egw_info']['user']['apps']['admin'])
        {
            $xmlData = array(
                'type'	=> 'text/xml',
                'charset' => 'utf8',
                'filename'	=> 'noten-export-zz.xml',
            );

            $path = 'asvexport_zz.xml';
            $mime = '';

            $length = 0;
            Api\Header\Content::safe($xmlData, $path, $mime, $length, True, True);
            echo $this->bo->asvNotenExport(1);
        }
        exit();
    }

    /**
     * query projects for nextmatch in the students-list
     *
     * reimplemented from Api\Storage\Base to disable action-buttons based on the Acl and make some modification on the data
     *
     * @param array &$query
     * @param array &$rows returned rows/cups
     * @param array &$readonlys eg. to disable buttons based on Acl
     * @param boolean $id_only if true only return (via $rows) an array of contact-ids, dont save state to session
     * @return int total number of contacts matching the selection
     */
    function get_rows(&$query_in,&$rows,&$readonlys=false,$id_only=false)
    {
        // todo if edit, dann rows aus session holen!
        $total = 0;
        if(isset($query_in['filter'])){
            Api\Cache::setSession('schulmanager', 'filter', $query_in['filter']);
        }
        else{
            // edit records
            Api\Cache::unsetSession('schulmanager', 'notenmanager_modified_records');
            $query_in['filter'] = Api\Cache::getSession('schulmanager', 'filter');
        }

        $this->bo->getSchuelerNotenList($query_in,$rows);
        // only for temporary calculation with ajax
        Api\Cache::setSession('schulmanager', 'notenmanager_temp_rows', $rows);
        Api\Cache::setSession('schulmanager', 'notenmanager_rows', $rows);
        return $query_in['total'];
    }

    /**
     * get rows for editing
     *
     * @param array &$query
     * @param array &$rows returned rows/cups
     * @param array &$readonlys eg. to disable buttons based on Acl
     * @param boolean $id_only if true only return (via $rows) an array of contact-ids, dont save state to session
     * @return int total number of contacts matching the selection
     */
    function get_rows_edit(&$query_in,&$rows,&$readonlys,$id_only=false)
    {
        // todo if edit, dann rows aus session holen!
        //$total = 0;
        if(isset($query_in['filter'])){
            $setSession = Api\Cache::setSession('schulmanager', 'filter', $query_in['filter']);
        }
        else{
            // edit records
            Api\Cache::unsetSession('schulmanager', 'notenmanager_modified_records');
            $query_in['filter'] = Api\Cache::getSession('schulmanager', 'filter');
        }

        // only check number of lines
        $this->bo->getSchuelerNotenList($query_in,$rows);
        if(is_null(Api\Cache::getSession('schulmanager', 'notenmanager_temp_rows'))){
            // situation after apply
            if(array_key_exists(0, $rows)) {
                // first call return number of rows, second call items with numeric keys
                Api\Cache::setSession('schulmanager', 'notenmanager_temp_rows', $rows);
                Api\Cache::setSession('schulmanager', 'notenmanager_rows', $rows);
            }
        }
        else{
            // load rows from session if exists, needed for reload when columns has been resized
            $rows = Api\Cache::getSession('schulmanager', 'notenmanager_temp_rows');
        }
        return $query_in['total'];
    }

    /**
     * query projects for nextmatch in the projects-list
     *
     * reimplemented from Api\Storage\Base to disable action-buttons based on the Acl and make some modification on the data
     *
     * @param array &$query
     * @param array &$rows returned rows/cups
     * @param array &$readonlys eg. to disable buttons based on Acl
     * @param boolean $id_only if true only return (via $rows) an array of contact-ids, dont save state to session
     * @return int total number of contacts matching the selection
     */
    function get_klassen_rows(&$query_in,&$rows)
    {
        $klassen_filter_id = Api\Cache::getSession('schulmanager', 'klassen_filter_id');
        if(!array_key_exists('col_filter', $query_in) || ($query_in['filter'] != $klassen_filter_id) || !isset( $query_in['col_filter']['schueler_id'])){
            Api\Cache::setSession('schulmanager', 'klassen_filter_id', $query_in['filter']);
            $this->bo->getKlassenSchuelerList($query_in,$rows,$readonlys,false);
            Api\Cache::setSession('schulmanager', 'klassen_schueler_list', $rows);
        }
        elseif(array_key_exists('col_filter', $query_in) && isset( $query_in['col_filter']['schueler_id'])){ //array_key_exists('schueler_id', $query_in['col_filter'])){
            //$query_in['test'] = $query_in['col_filter'];
            $klassen_schueler_list = Api\Cache::getSession('schulmanager', 'klassen_schueler_list');
            //$schueler_index = $query_in['col_filter']['schueler_id'] -1;
            $schueler = $klassen_schueler_list[$query_in['col_filter']['schueler_id']];
            $this->schueler_bo->getNotenAbstract($schueler, $rows, $query_in['col_filter']['schueler_id']);
        }
        /*else{
             //= $query_in['filter'];
            Api\Cache::setSession('schulmanager', 'klassen_filter_id', $query_in['filter']);
            $this->bo->getKlassenSchuelerList($query_in,$rows,$readonlys,$id_only);
            Api\Cache::setSession('schulmanager', 'klassen_schueler_list', $rows);
        }*/

        return count($rows);
    }

    /**
     * Loading weight of grades
     * @param $query
     * @throws Api\Json\Exception
     */
    function ajax_getGewichtungen($query) {
        $kg_asv_id = Api\Cache::getSession('schulmanager', 'filter_klassengruppe_asv_id');
        $sf_asv_id = Api\Cache::getSession('schulmanager', 'filter_schuelerfach_asv_id');

        // Gewichtungen
        $gewichtungen = array();
        $gew_bo = new schulmanager_note_gew_bo();
        $gew_bo->loadGewichtungen($kg_asv_id, $sf_asv_id, $gewichtungen);

        $result = array();
        foreach($gewichtungen AS $key => $gew){
            $result[$key] = $gew;
        }

        Api\Json\Response::get()->data($result);
    }

    /**
     * Ajax call, when grade was modified, before beeing saved
     * Revalidate avg-values with new grades.
     * @param type $noteKey
     * @param type $noteVal
     */
    function ajax_noteModified($noteKey, $noteVal, $token, $definition_date, $art, $description) {
        $result = array();

        // xsrf check
        if(!$this->checkToken('token_note_modified', $token)){
            $result['error_msg'] = 'ERROR: could not submit date!';
            Api\Json\Response::get()->data($result);
            return;
        }

        // Key/val in session speichern
        $modified_records = Api\Cache::getSession('schulmanager', 'notenmanager_modified_records');
        if(!is_array($modified_records)){
            // maybe first modified record
            $modified_records = array();
        }
        //$modified_records[$noteKey] = $noteVal;
        $modified_records[$noteKey] = array(
            'val' => $noteVal,
            'date' => strtotime($definition_date),
            'art' => $art,
            'desc' => $description,
        );
        Api\Cache::setSession('schulmanager', 'notenmanager_modified_records', $modified_records);

        // neue Schnitte berechnen
        $rows = Api\Cache::getSession('schulmanager', 'notenmanager_temp_rows');
        $gewichtungen = Api\Cache::getSession('schulmanager', 'notenmanager_gewichtungen');

        $keys = preg_split('/\\]\\[|\\[|\\]/', $noteKey, -1, PREG_SPLIT_NO_EMPTY);

        $schueler = $rows[$keys[0]];

        // clean up
        $this->resetAVGNoten($schueler);
        // ende clean up

        $schueler[$keys[1]][$keys[2]][$keys[3]][$keys[4]] = $noteVal;

        // manuelle Einträge
        if($keys[3] == -1 && !empty($noteVal)){
            // schnitt oder note wurde geändert und ist nicht nur gelöscht
            $schueler[$keys[1]][$keys[2]][$keys[3]]['manuell'] = true;
//			$schueler[$keys[1]][$keys[2]]['avgclass'] = 'nm_avg_manuell';
        }
        elseif($keys[3] == -1 && empty($noteVal)){
            // schnitt oder note wurde geändert und ist nicht nur gelöscht
            $schueler[$keys[1]][$keys[2]][$keys[3]]['manuell'] = false;
//			$schueler[$keys[1]][$keys[2]]['avgclass'] = 'nm_avg_auto';
        }

        // alternative Berechnung
        if($keys[2] === 'alt_b' && $keys[3] == -1){
            $schueler[$keys[1]][$keys[2]][$keys[3]]['note'] = $noteVal;
        }


        //foreach($rows AS $key => $schueler){
        schulmanager_lehrer_so::beforeSendToClient($schueler, $gewichtungen);		// Schnitte und noten neu berechnen
        $rows[$keys[0]] = $schueler;
        Api\Cache::setSession('schulmanager', 'notenmanager_temp_rows', $rows);

        $this->setAVGNoten($result, $keys[0], $schueler);

        Api\Json\Response::get()->data($result);
    }

    /**
     * Ajax call, wenn noten details have been modified
     * @param $noteKey
     * @param $noteVal
     * @param $token
     * @param $definition_date
     * @param $art
     * @param $description
     * @throws Api\Json\Exception
     */
    function ajax_noteDetailsModified($noteKey, $noteVal, $token, $definition_date, $art, $description, $typeFlag)
    {
        $result = array();

        // xsrf check
        if (!$this->checkToken('token_notenDetails_modified', $token)) {
            $result['error_msg'] = 'ERROR: could not submit changes!';
            Api\Json\Response::get()->data($result);
            return;
        }

        // validation
        if($noteVal < 1 || $noteVal > 6){
            Api\Json\Response::get()->data($result);
            return;
        }

        $schueler_id = Api\Cache::getSession('schulmanager', 'details_filter_schueler');
        $noteKey = $schueler_id."[noten]".$noteKey."[note]";

        // Key/val in session speichern
        $modified_records = Api\Cache::getSession('schulmanager', 'notenmanager_modified_records');
        if(!is_array($modified_records)){
            // maybe first modified record
            $modified_records = array();
        }

        if($typeFlag == "glnw"){
            $notenArtList = schulmanager_werteliste_bo::getNotenArtList(True);
            $art = $notenArtList[$art];
        }
        elseif ($typeFlag == "klnw"){
            $notenArtList = schulmanager_werteliste_bo::getNotenArtList(False);
            $art = $notenArtList[$art];
        }

        //$modified_records[$noteKey] = $noteVal;
        $modified_records[$noteKey] = array(
            'val' => $noteVal,
            'art' => $art,
            'desc' => $description,
        );

        Api\Cache::setSession('schulmanager', 'notenmanager_modified_records', $modified_records);

        $inputInfo = array(
            'desc' => $description,
            'art' => $art,
        );

        if(!empty($definition_date)){
            $inputInfo['date'] = strtotime($definition_date);
        }
        else{
            $inputInfo['date'] = null;
        }

        if (!($info_id = $this->bo->write($inputInfo))) {
            $result['msg'] = lang('Could not save entry');
        } else {
            $result['msg'] = lang('Entry saved');
        }

        // reset
        Api\Cache::unsetSession('schulmanager', 'notenmanager_modified_records');

        // todo same as in modified method
        // reload
        $selected_schueler_index = Api\Cache::getSession('schulmanager', 'details_filter_schueler');
        $klasse_id = Api\Cache::getSession('schulmanager', 'filter');
        $rows = array();
        $query_in = array(
            'filter' => $klasse_id,
            'total' => -1,
        );
        $readonlys = array();
        $this->get_rows($query_in, $rows, $readonlys);
        $this->extractSchuelerDataFromRows($result, $rows, $selected_schueler_index);

        Api\Json\Response::get()->data($result);
    }

    /**
     * delete grade from details view
     * @param $token
     */
    function ajax_noteDetailsDeleted($noteKey, $token)
    {
        $result = array();
        // xsrf check
        if (!$this->checkToken('token_notenDetails_modified', $token)) {
            $result['error_msg'] = 'ERROR: could not submit changes!';
            Api\Json\Response::get()->data($result);
            return;
        }

        $schueler_id = Api\Cache::getSession('schulmanager', 'details_filter_schueler');
        $noteKey = $schueler_id."[noten]".$noteKey."[note]";

        // Key/val in session speichern
        $modified_records = Api\Cache::getSession('schulmanager', 'notenmanager_modified_records');
        if(!is_array($modified_records)){
            // maybe first modified record
            $modified_records = array();
        }

        $modified_records[$noteKey] = array(
            'val' => null,
        );

        Api\Cache::setSession('schulmanager', 'notenmanager_modified_records', $modified_records);
        $inputInfo = array();

        if (!($info_id = $this->bo->write($inputInfo))) {
            $result['msg'] = lang('Could not delete entry');
        } else {
            $result['msg'] = lang('Entry deleted');
        }

        // reset
        Api\Cache::unsetSession('schulmanager', 'notenmanager_modified_records');

        // todo same as in modified method
        $this->responseJsonReload($result);
    }

    /**
     * response detail marks view as json content
     * @param $result
     */
    function responseJsonReload($result){
        $selected_schueler_index = Api\Cache::getSession('schulmanager', 'details_filter_schueler');
        $klasse_id = Api\Cache::getSession('schulmanager', 'filter');
        $rows = array();
        $query_in = array(
            'filter' => $klasse_id,
            'total' => -1,
        );
        $readonlys = array();
        $this->get_rows($query_in, $rows, $readonlys);
        $this->extractSchuelerDataFromRows($result, $rows, $selected_schueler_index);

        Api\Json\Response::get()->data($result);
    }

    /**
     * @param $stud_id
     * @throws Api\Json\Exception
     */
    function ajax_getStudentDetails($stud_id) {
        $result = array();

        Api\Cache::setSession('schulmanager', 'details_filter_schueler', $stud_id); // reset selected schueler
        // cached data
        $rows = Api\Cache::getSession('schulmanager', 'notenmanager_temp_rows');

        $stud = $rows[$stud_id];

        $result['details_name'] = $stud['nm_st']['st_asv_familienname'];
        $result['details_rufname'] = $stud['nm_st']['st_asv_rufname'];
        $result['details_austritt'] = $stud['nm_st']['st_asv_austrittsdatum'];

        $result['details_noten'] = $stud['noten'];

        $klassengr_schuelerfa = Api\Cache::getSession('schulmanager', 'actual_klassengr_schuelerfa');
        $result['details_klasse'] = $klassengr_schuelerfa->getKlasse_asv_klassenname();
        $result['details_fach'] = $klassengr_schuelerfa->getSchuelerfach_asv_anzeigeform();

        Api\Json\Response::get()->data($result);
    }

    /**
     * @param $schueler_id
     * @throws Api\Json\Exception
     */
    function ajax_getStudentContact($stud_id) {
        $result = array();
        Api\Cache::setSession('schulmanager', 'schueler_filter_id', $stud_id);
        // cached data
        $cachedRows = Api\Cache::getSession('schulmanager', 'notenmanager_temp_rows');
        $stud = $cachedRows[$stud_id];
        $result['contact_name'] = $stud['nm_st']['st_asv_familienname'];
        $result['contact_rufname'] = $stud['nm_st']['st_asv_rufname'];
        $klassengr_schuelerfa = Api\Cache::getSession('schulmanager', 'actual_klassengr_schuelerfa');
        $result['contact_klasse'] = $klassengr_schuelerfa->getKlasse_asv_klassenname();
        $result['contact_fach'] = $klassengr_schuelerfa->getSchuelerfach_asv_anzeigeform();

        $rows = array();
        $schuelerkommunikation_so = new schulmanager_schuelerkommunikation_so();
        $schuelerkommunikation_so->queryBySchueler($query_in,$rows, $cachedRows[$stud_id]['nm_st']['st_asv_id']);
        $sko_nm_rows = array();
        foreach($rows as $key => $values) {
            $sko_nm_rows[$key] = array(
                0 => $values['sko_nr'],
                1 => $values['sko_type'],
                2 => $values['sko_adress'],
                3 => $values['sko_note'],
            );
        }
        $result['sko_nm_rows'] = $sko_nm_rows;

        // Anschrift
        $schueleranschrift_so = new schulmanager_schueleranschrift_so();
        $rows = array();
        $schueleranschrift_so->queryBySchueler($query_in,$rows, $cachedRows[$stud_id]['nm_st']['st_asv_id']);
        $san_nm_rows = array();
        foreach($rows as $key => $values) {
            $san_nm_rows[$key] = array(
                0 => $values['san_nr'],
                1 => $values['san_anrede_anzeige'],
                2 => $values['san_asv_familienname'],
                3 => $values['san_asv_vornamen'],
                4 => $values['san_personentyp_anzeige'],
                5 => $values['san_asv_strasse'],
                6 => $values['san_asv_nummer'],
                7 => $values['san_asv_postleitzahl'],
                8 => $values['san_asv_ortsbezeichnung'],
            );
        }
        $result['san_nm_rows'] = $san_nm_rows;

        Api\Json\Response::get()->data($result);
    }

    function ajax_nbericht_prepare(){
        $klsOptions = array();
        $kls = array();
        $selected_klasse_index = Api\Cache::getSession('schulmanager', 'klassen_filter_id');
        if(!isset($selected_klasse_index)) {
            $selected_klasse_index = 0;
        }
        $klassenasvids = Api\Cache::getSession('schulmanager', 'klassen_asv_ids');
        $kl_asv_id = $klassenasvids[$selected_klasse_index];
        $this->bo->getKlassenleitungen($kl_asv_id, $kls);
        Api\Cache::setSession('schulmanager', 'klassenleitungen', $kls);
        foreach($kls as $key => $value){
            $klsOptions[] = $value['ls_asv_zeugnisname1'];
        }
        $content = array();
        $content['klassleiter'] = $klsOptions;
        Api\Json\Response::get()->data($content);
    }

    /**
     * selected klasse changed
     * @param $klasse_id
     */
    function ajax_DetailsKlasseChanged($klasse_id){
        // set filter and total=-1, otherwise only number of records will be returned (like first nextmatch call)
        $query_in = array(
            'filter' => $klasse_id,
            'total' => -1,
        );
        $rows = array();
        $readonlys = array();

        Api\Cache::setSession('schulmanager', 'details_filter_schueler', 0); // reset selected schueler
        $this->get_rows($query_in, $rows, $readonlys);

        //$schuelerList = $this->extractSchuelerFromRows($rows);
        $content = array();
        $content['select_schueler'] = $this->extractSchuelerListFromRows($rows);

        $this->extractSchuelerDataFromRows($content, $rows, 0);
        //Api\Json\Response::get()->data($schuelerList);
        Api\Json\Response::get()->data($content);
    }

    /**
     * selected klasse changed
     * @param $klasse_id
     */
    function ajax_DetailsSchuelerChanged($schueler_id){
        Api\Cache::setSession('schulmanager', 'details_filter_schueler', $schueler_id); // reset selected schueler

        $content = array();
        $rows = Api\Cache::getSession('schulmanager', 'notenmanager_rows');
        $this->extractSchuelerDataFromRows($content, $rows, $schueler_id);

        Api\Json\Response::get()->data($content);
    }

    /**
     * Extracts key values from rows
     * @param array $rows
     * @return array
     */
    function extractSchuelerListFromRows(array $rows){
        $result = array();
        foreach ($rows as $key => $value) {
            if (is_numeric($key)) {
                $result[$key] = $value['nm_st']['st_asv_familienname'].' '.$value['nm_st']['st_asv_rufname'];
            }
        }
        return $result;
    }

    /**
     * Extracts key values from rows
     * @param array $rows
     * @return array
     */
    function extractSchuelerDataFromRows(array &$content, array $rows, $selected_schueler_index){
        $klassengr_schuelerfa = Api\Cache::getSession('schulmanager', 'actual_klassengr_schuelerfa');

        if(!empty($klassengr_schuelerfa)){
            $content['klasse'] =  $klassengr_schuelerfa->getKlasse_asv_klassenname();
            $content['fach'] = $klassengr_schuelerfa->getSchuelerfach_asv_anzeigeform();

            $content['nm_st_familienname'] = $rows[$selected_schueler_index]['nm_st']['st_asv_familienname'];
            $content['nm_st_rufname'] = $rows[$selected_schueler_index]['nm_st']['st_asv_rufname'];

            $content['details_noten'] = $rows[$selected_schueler_index]['noten'];
            $content['altb'] = $rows[$selected_schueler_index]['noten']['alt_b']['-1']['checked'] ? 'ja (GLNW 1 : 1 kLNW)' : 'nein (GLNW 2 : 1 kLNW)';
        }
    }

    /**
     * checks if token and saved token are equal
     * @param $key
     * @param $token
     */
    function checkToken($key, $token){
        $sessToken = Api\Cache::getSession('schulmanager', $key);
        return !empty($sessToken) && !empty($token) && $sessToken == $token;

    }

    /**
     * Ajax call, wenn gew was modified, before beeing saved
     * Revalidate avg-values with new grades.
     * @param type $noteKey
     * @param type $noteVal
     */
    function ajax_gewModified($gewKey, $gewVal) {
        $result = array();
        // Key/val in session speichern
        $modified_gewichtung = Api\Cache::getSession('schulmanager', 'notenmanager_modified_gewichtung');
        if(!is_array($modified_gewichtung)){
            // maybe first modified record
            $modified_gewichtung = array();
        }

        if(empty($gewVal)){
            $gewVal = 1;
        }

        $modified_gewichtung[$gewKey] = $gewVal;


        Api\Cache::setSession('schulmanager', 'notenmanager_modified_gewichtung', $modified_gewichtung);

        $rows = Api\Cache::getSession('schulmanager', 'notenmanager_temp_rows');

        $gewichtungen = Api\Cache::getSession('schulmanager', 'notenmanager_gewichtungen');

        // commit gewichtung
        foreach($modified_gewichtung as $key => $val) {
            $gewichtungen[$key] = $val;
        }
        // reset avg grades
        foreach($rows as $key => $schueler){
            if(is_array($schueler) && array_key_exists('noten', $schueler)){
                $this->resetAVGNoten($schueler);
                schulmanager_lehrer_so::beforeSendToClient($schueler, $gewichtungen);
                // to result
                $this->setAVGNoten($result, $key, $schueler);
            }
        }
        Api\Cache::setSession('schulmanager', 'notenmanager_gewichtungen', $gewichtungen);
        Api\Cache::setSession('schulmanager', 'notenmanager_temp_rows', $rows);
        Api\Json\Response::get()->data($result);
    }

    /**
     * Ajax call, wenn gew was modified in header checkbox, before beeing saved
     * Revalidate avg-values with new grades.
     * @param type $noteKey
     * @param type $noteVal
     */
    function ajax_gewAllModified(string $gewKey, int $altb_Val) {
        $result = array();

        // Key/val in session speichern
        $modified_gewichtung = Api\Cache::getSession('schulmanager', 'notenmanager_modified_gewichtung');
        $modified_records = Api\Cache::getSession('schulmanager', 'notenmanager_modified_records');
        if(!is_array($modified_records)){
            // maybe first modified record
            $modified_records = array();
        }

        $rows = Api\Cache::getSession('schulmanager', 'notenmanager_temp_rows');
        foreach($rows as $key => &$schueler){
            $dummy = 0;
            if(is_array($schueler) && array_key_exists('noten', $schueler)){
                //$modified_records[$key.'[noten][alt_b][-1][checked]'] = $altb_Val;
                $modified_records[$key.'[noten][alt_b][-1][checked]'] =  array(
                    'val' => $altb_Val,
                    'date' => '',
                    'art' => '',
                    'desc' => '',
                );

                $schueler['noten']['alt_b']['-1']['note'] = $altb_Val;
                $schueler['noten']['alt_b']['-1']['checked'] = $altb_Val;
                if($altb_Val == 1){
                    // alt_b wurde geändert und ist nicht nur gelöscht
                    $schueler['noten']['alt_b']['-1']['manuell'] = true;
                }
                else{
                    // alt_b wurde geändert und ist nicht nur gelöscht
                    $schueler['noten']['alt_b']['-1']['manuell'] = false;
                }
            }
            $dummy++;
        }
        Api\Cache::setSession('schulmanager', 'notenmanager_modified_records', $modified_records);

        // commit modified gewichtung
        $gewichtungen = Api\Cache::getSession('schulmanager', 'notenmanager_gewichtungen');
        foreach($modified_gewichtung as $key => $gewval) {
            $gewichtungen[$key] = $gewval;
        }

        foreach($rows as $key => &$schueler){
            if(is_array($schueler) && array_key_exists('noten', $schueler)){
                $this->resetAVGNoten($schueler);
                schulmanager_lehrer_so::beforeSendToClient($schueler, $gewichtungen);
                // to result
                $this->setAVGNoten($result, $key, $schueler);
                //TODO: test ob geändert wurde
                $result[$key.'[noten][alt_b]']['[-1][checked]'] = $altb_Val == 1;
            }
        }

        Api\Cache::setSession('schulmanager', 'notenmanager_temp_rows', $rows);
        Api\Json\Response::get()->data($result);
    }


    /**
     * set all avg grades to result array
     * @param type $schueler
     */
    private function setAVGNoten(array &$result, $key, array $schueler){
        $result[$key.'[noten][glnw_hj_1]'] = array(
            '[-1][note]' => $schueler['noten']['glnw_hj_1'][-1]['note'],
            'avgclass' => $schueler['noten']['glnw_hj_1'][-1]['manuell'] == true ? 'nm_avg_manuell' : 'nm_avg_auto',
        );
        $result[$key.'[noten][klnw_hj_1]'] = array(
            '[-1][note]' => $schueler['noten']['klnw_hj_1'][-1]['note'],
            'avgclass' => $schueler['noten']['klnw_hj_1'][-1]['manuell'] == true ? 'nm_avg_manuell' : 'nm_avg_auto',
        );
        $result[$key.'[noten][schnitt_hj_1]'] = array(
            '[-1][note]' => $schueler['noten']['schnitt_hj_1'][-1]['note'],
            'avgclass' => $schueler['noten']['schnitt_hj_1'][-1]['manuell'] == true ? 'nm_avg_manuell' : 'nm_avg_auto',
        );

        $result[$key.'[noten][note_hj_1]'] = array(
            '[-1][note]' => $schueler['noten']['note_hj_1'][-1]['note'],
            'avgclass' => $schueler['noten']['note_hj_1'][-1]['manuell'] == true ? 'nm_avg_manuell' : 'nm_avg_auto',
        );
        // 2. HJ
        $result[$key.'[noten][glnw_hj_2]'] = array(
            '[-1][note]' => $schueler['noten']['glnw_hj_2'][-1]['note'],
            'avgclass' => $schueler['noten']['glnw_hj_2'][-1]['manuell'] == true ? 'nm_avg_manuell' : 'nm_avg_auto',
        );

        $result[$key.'[noten][klnw_hj_2]'] = array(
            '[-1][note]' => $schueler['noten']['klnw_hj_2'][-1]['note'],
            'avgclass' => $schueler['noten']['klnw_hj_2'][-1]['manuell'] == true ? 'nm_avg_manuell' : 'nm_avg_auto',
        );

        $result[$key.'[noten][schnitt_hj_2]'] = array(
            '[-1][note]' => $schueler['noten']['schnitt_hj_2'][-1]['note'],
            'avgclass' => $schueler['noten']['schnitt_hj_2'][-1]['manuell'] == true ? 'nm_avg_manuell' : 'nm_avg_auto',
        );

        $result[$key.'[noten][note_hj_2]'] = array(
            '[-1][note]' => $schueler['noten']['note_hj_2'][-1]['note'],
            'avgclass' => $schueler['noten']['note_hj_2'][-1]['manuell'] == true ? 'nm_avg_manuell' : 'nm_avg_auto',
        );
    }

    /**
     * reset all avg grades to empty string
     * @param type $schueler
     */
    private function resetAVGNoten(&$schueler){
        // clean up
        if($schueler['noten']['glnw_hj_1']['-1']['manuell']==0){
            $schueler['noten']['glnw_hj_1'][-1]['note'] = '';
        }
        if($schueler['noten']['klnw_hj_1']['-1']['manuell']==0){
            $schueler['noten']['klnw_hj_1'][-1]['note'] = '';
        }
        if($schueler['noten']['schnitt_hj_1']['-1']['manuell']==0){
            $schueler['noten']['schnitt_hj_1'][-1]['note'] = '';
        }
        if($schueler['noten']['note_hj_1']['-1']['manuell']==0){
            $schueler['noten']['note_hj_1'][-1]['note'] = '';
        }
        // 2. HJ
        if($schueler['noten']['glnw_hj_2']['-1']['manuell']==0){
            $schueler['noten']['glnw_hj_2']['-1']['note'] = '';
        }
        if($schueler['noten']['klnw_hj_2']['-1']['manuell']==0){
            $schueler['noten']['klnw_hj_2']['-1']['note'] = '';
        }
        if($schueler['noten']['schnitt_hj_2']['-1']['manuell']==0){
            $schueler['noten']['schnitt_hj_2']['-1']['note'] = '';
        }
        if($schueler['noten']['note_hj_2']['-1']['manuell']==0){
            $schueler['noten']['note_hj_2']['-1']['note'] = '';
        }
        // ende clean up
    }

    /**
     * List student view
     * @param array $content
     * @param string $msg
     */
    function schuelerview(array $content = null, $msg='')
    {
        $config = Api\Config::read('schulmanager');
        $etpl = new Etemplate('schulmanager.schuelerview');

        if ($_GET['msg']) $msg = $_GET['msg'];

        $sel_options = array();

        $sel_options['select_klasse'] = $this->bo->getClassLeaderClasses();

        // select klasse
        $selected_klasse_index = Api\Cache::getSession('schulmanager', 'klassen_filter_id');
        $selected_schueler_index = Api\Cache::getSession('schulmanager', 'schueler_filter_id');
        if(!isset($selected_klasse_index)){
            $selected_klasse_index = 0;
            $selected_schueler_index = 0;
            Api\Cache::setSession('schulmanager', 'klassen_filter_id', $selected_klasse_index);
            Api\Cache::setSession('schulmanager', 'schueler_filter_id', $selected_schueler_index);
        }
        if(!isset($selected_schueler_index)){
            $selected_schueler_index = 0;
            Api\Cache::setSession('schulmanager', 'schueler_filter_id', $selected_schueler_index);
        }

        $query_in = array(
            'filter' => $selected_klasse_index,
        );

        $rows = array();
        $this->get_klassen_rows($query_in,$rows);
        //$this->get_rows($query_in,$rows);
        $sel_options['select_schueler'] = $this->extractSchuelerListFromRows($rows);

        $readonlys = array();

        $content['msg'] = $msg;
        $content['select_klasse'] = $selected_klasse_index;
        $content['select_schueler'] = $selected_schueler_index;

        $content['header_klasse'] = $sel_options['select_klasse'][$selected_klasse_index];
        $content['header_schuelername'] = $sel_options['select_schueler'][$selected_schueler_index]; //$klassen_schueler_list[$schueler_id]['nm_st']['st_asv_familienname'].' '.$klassen_schueler_list[$schueler_id]['nm_st']['st_asv_rufname']

        // nextmatch Schuelerlaufbahn
        /*$content['sla_nm'] = array();
        $content['sla_nm']['get_rows']		= 'schulmanager.schulmanager_ui.sla_get_rows';
        $content['sla_nm']['no_filter'] 	= true;
        $content['sla_nm']['no_filter2']	= true;
        $content['sla_nm']['no_cat']	    = true;
        $content['sla_nm']['no_search']	    = true;
        $content['sla_nm']['header_left']	= false;
        $content['sla_nm']['bottom_too']	= true;
        $content['sla_nm']['order']		= 'sla_nm_id';
        $content['sla_nm']['sort']		= 'ASC';
        $content['sla_nm']['row_id']	= 'sla_nm_id';
        $content['sla_nm']['no_columnselection'] = false;

        // nextmatch Schuelerkommunikaton
        $content['sko_nm'] = array();
        $content['sko_nm']['get_rows']		= 'schulmanager.schulmanager_ui.sko_get_rows';
        $content['sko_nm']['no_filter'] 	= true;
        $content['sko_nm']['no_filter2']	= true;
        $content['sko_nm']['no_cat']	    = true;
        $content['sko_nm']['no_search']	    = true;
        $content['sko_nm']['header_left']	= false;
        $content['sko_nm']['bottom_too']	= true;
        $content['sko_nm']['order']		= 'sko_nm_id';
        $content['sko_nm']['sort']		= 'ASC';
        $content['sko_nm']['row_id']	= 'sko_nm_id';
        $content['sko_nm']['no_columnselection'] = false;

        // nextmatch Schueleranschrift
        $content['san_nm'] = array();
        $content['san_nm']['get_rows']		= 'schulmanager.schulmanager_ui.san_get_rows';
        $content['san_nm']['no_filter'] 	= true;
        $content['san_nm']['no_filter2']	= true;
        $content['san_nm']['no_cat']	    = true;
        $content['san_nm']['no_search']	    = true;
        $content['san_nm']['header_left']	= false;
        $content['san_nm']['bottom_too']	= true;
        $content['san_nm']['order']		= 'san_nm_id';
        $content['san_nm']['sort']		= 'ASC';
        $content['san_nm']['row_id']	= 'san_nm_id';
        $content['san_nm']['no_columnselection'] = false;
        */
        // nextmatch Noten
        $content['not_nm'] = array();
        $content['not_nm']['get_rows']		= 'schulmanager.schulmanager_ui.not_get_rows';
        $content['not_nm']['no_filter'] 	= true;
        $content['not_nm']['no_filter2']	= true;
        $content['not_nm']['no_cat']	    = true;
        $content['not_nm']['no_search']	    = true;
        $content['not_nm']['header_left']	= false;
        $content['not_nm']['bottom_too']	= true;
        $content['not_nm']['order']		= 'not_nm_id';
        $content['not_nm']['sort']		= 'ASC';
        $content['not_nm']['row_id']	= 'not_nm_id';
        $content['not_nm']['no_columnselection'] = false;

        $xrsf_token = bin2hex(random_bytes(32));
        Api\Cache::setSession('schulmanager', 'token_schuelerview', $xrsf_token);
        $content['token'] = $xrsf_token;

        $content['not_nm']['isadmin'] = isset($GLOBALS['egw_info']['user']['apps']['admin']);
        $content['isadmin'] = isset($GLOBALS['egw_info']['user']['apps']['admin']);

        $preserv = $sel_options;

        return $etpl->exec('schulmanager.schulmanager_ui.schuelerview',$content,$sel_options,$readonlys,$preserv);
    }

    /**
     * @param $query_in
     * @param $rows
     * @param $readonlys
     * @param false $id_only
     * @return mixed
     */
    function sla_get_rows(&$query_in,&$rows,&$readonlys,$id_only=false)
    {
        $schullaufbahn_so = new schulmanager_schullaufbahn_so();
        $klassen_schueler_list = Api\Cache::getSession('schulmanager', 'klassen_schueler_list');
        $schueler_id = Api\Cache::getSession('schulmanager', 'schueler_filter_id');
        $schullaufbahn_so->queryBySchueler($query_in,$rows, $klassen_schueler_list[$schueler_id]['nm_st']['st_asv_id']);

        return $query_in['total'];
    }

    /**
     * @param $query_in
     * @param $rows
     * @param $readonlys
     * @param false $id_only
     * @return mixed
     */
    function sko_get_rows(&$query_in,&$rows,&$readonlys,$id_only=false)
    {
        $schuelerkommunikation_so = new schulmanager_schuelerkommunikation_so();
        $klassen_schueler_list = Api\Cache::getSession('schulmanager', 'klassen_schueler_list');
        $schueler_id = Api\Cache::getSession('schulmanager', 'schueler_filter_id');
        $schuelerkommunikation_so->queryBySchueler($query_in,$rows, $klassen_schueler_list[$schueler_id]['nm_st']['st_asv_id']);

        return $query_in['total'];
    }

    /**
     * @param $query_in
     * @param $rows
     * @param $readonlys
     * @param false $id_only
     * @return mixed
     */
    function san_get_rows(&$query_in,&$rows,&$readonlys,$id_only=false)
    {
        $schueleranschrift_so = new schulmanager_schueleranschrift_so();
        $klassen_schueler_list = Api\Cache::getSession('schulmanager', 'klassen_schueler_list');
        $schueler_id = Api\Cache::getSession('schulmanager', 'schueler_filter_id');
        $schueleranschrift_so->queryBySchueler($query_in,$rows, $klassen_schueler_list[$schueler_id]['nm_st']['st_asv_id']);

        return $query_in['total'];
    }

    /**
     * @param $query_in
     * @param $rows
     * @param $readonlys
     * @param false $id_only
     * @return mixed
     */
    function not_get_rows(&$query_in,&$rows,&$readonlys,$id_only=false)
    {
        $schueler_id = Api\Cache::getSession('schulmanager', 'schueler_filter_id');
        $klassen_schueler_list = Api\Cache::getSession('schulmanager', 'klassen_schueler_list');
        $schueler = $klassen_schueler_list[$schueler_id];
        $this->schueler_bo->getNotenAbstract($schueler, $rows, $schueler_id);//$query_in['col_filter']['schueler_id']);

        //return $query_in['total'];
        return count($rows);
    }


    /**
     * class has been selected by user
     * @param $klasse_id
     * @throws Api\Json\Exception
     */
    function ajax_schuelerViewKlasseChanged($klasse_id)
    {
        $result = array();
        $query_in = array(
            'filter' => $klasse_id,
        );

        $rows = array();
        $this->bo->getKlassenSchuelerList($query_in,$rows,$readonlys,false);
        //$this->bo->getSchuelerNotenList($query_in,$rows);

        Api\Cache::setSession('schulmanager', 'klassen_schueler_list', $rows);

        $klassen_schueler_list = $this->extractSchuelerListFromRows($rows);
        $result['select_schueler'] = $klassen_schueler_list;

        $schueler_id = 0;
        Api\Cache::setSession('schulmanager', 'klassen_filter_id', $klasse_id);
        Api\Cache::setSession('schulmanager', 'schueler_filter_id', $schueler_id);

        $result['header_klasse'] = $this->bo->getClassLeaderClasses()[$klasse_id];
        $result['header_schuelername'] = $klassen_schueler_list[$schueler_id];
        $result['note_avg_schnitt_hj_1'] = $rows[0]['noten']['note_hj_1']['-1']['note'];
        $result['note_avg_note_hj_1'] = $rows[0]['noten']['note_hj_1']['-1']['note'];
        $result['note_avg_m_hj_1'] = $rows[0]['noten']['note_hj_1']['-1']['note'];
        $result['note_avg_v_hj_1'] = $rows[0]['noten']['note_hj_1']['-1']['note'];

        $result['note_avg_schnitt_hj_2'] = $rows[0]['noten']['note_hj_2']['-1']['note'];
        $result['note_avg_note_hj_2'] = $rows[0]['noten']['note_hj_2']['-1']['note'];
        $result['note_avg_m_hj_2'] = $rows[0]['noten']['note_hj_2']['-1']['note'];
        $result['note_avg_v_hj_2'] = $rows[0]['noten']['note_hj_2']['-1']['note'];

        // apply grid data
        $this->getSchuelerViewSchuelerData($result);

        Api\Json\Response::get()->data($result);;
    }

    /**
     * student has been selected by user
     * @param $schueler_id
     * @throws Api\Json\Exception
     */
    function ajax_schuelerViewSchuelerChanged($schueler_id)
    {
        Api\Cache::setSession('schulmanager', 'schueler_filter_id', $schueler_id);

        $klassen_schueler_list = Api\Cache::getSession('schulmanager', 'klassen_schueler_list');
        $result['header_schuelername'] = $klassen_schueler_list[$schueler_id]['nm_st']['st_asv_familienname'].' '.$klassen_schueler_list[$schueler_id]['nm_st']['st_asv_rufname'];

        $this->getSchuelerViewSchuelerData($result);

        Api\Json\Response::get()->data($result);
    }

    /**
     * Delete LNW in first period
     * @param $token
     * @return void
     * @throws Api\Json\Exception
     */
    function ajax_delLnwPerA($token){
        $result = 0;
        // xsrf check
        if(!$this->checkToken('token_schuelerview', $token)){
            $result['error_msg'] = 'ERROR: could not submit date!';
            Api\Json\Response::get()->data($result);
            return;
        }
        $schueler_id = Api\Cache::getSession('schulmanager', 'schueler_filter_id');
        $klassen_schueler_list = Api\Cache::getSession('schulmanager', 'klassen_schueler_list');
        $schueler = $klassen_schueler_list[$schueler_id];

        $schueler_so = new schulmanager_schueler_so();
        $schueler_so->delLnwPer($schueler, true, false);
        $this->bo->revalidateGrades($schueler['nm_st']['st_asv_id']);
        Api\Json\Response::get()->data($result);
    }

    /**
     * Delete LNW in second period
     * @param $token
     * @return void
     * @throws Api\Json\Exception
     */
    function ajax_delLnwPerB($token){
        $result = 0;
        // xsrf check
        if(!$this->checkToken('token_schuelerview', $token)){
            $result['error_msg'] = 'ERROR: could not submit date!';
            Api\Json\Response::get()->data($result);
            return;
        }
        $schueler_id = Api\Cache::getSession('schulmanager', 'schueler_filter_id');
        $klassen_schueler_list = Api\Cache::getSession('schulmanager', 'klassen_schueler_list');
        $schueler = $klassen_schueler_list[$schueler_id];

        $schueler_so = new schulmanager_schueler_so();
        $schueler_so->delLnwPer($schueler, false, true);
        $this->bo->revalidateGrades($schueler['nm_st']['st_asv_id']);
        Api\Json\Response::get()->data($result);
    }

    /**
     * Loads student data to result array
     * @param $result
     */
    function getSchuelerViewSchuelerData(&$result){
        $query_in = array('start' => 0,);
        $readonlys = array();

        // Schuelerlaufbahn
        $rows = array();
        $this->sla_get_rows($query_in, $rows, $readonlys);
        $sla_nm_rows = array();
        foreach($rows as $key => $values) {
            $sla_nm_rows[$key] = array(
                0 => $values['sla_nr'],
                1 => $values['sla_datum'],
                2 => $values['sla_klasse'],
                3 => $values['sla_vorgang'],
                4 => $values['sla_zusatz'],
                5 => $values['sla_bemerkung'],
            );
        }
        $result['sla_nm_rows'] = $sla_nm_rows;

        // Kontaktdaten
        $query_in = array('start' => 0,);
        $rows = array();
        $this->sko_get_rows($query_in, $rows, $readonlys);
        $sko_nm_rows = array();
        foreach($rows as $key => $values) {
            $sko_nm_rows[$key] = array(
                0 => $values['sko_nr'],
                1 => $values['sko_type'],
                2 => $values['sko_adress'],
                3 => $values['sko_note'],
            );
        }
        $result['sko_nm_rows'] = $sko_nm_rows;

        // Anschrift
        $query_in = array('start' => 0,);
        $rows = array();
        $this->san_get_rows($query_in, $rows, $readonlys);
        $san_nm_rows = array();
        foreach($rows as $key => $values) {
            $san_nm_rows[$key] = array(
                0 => $values['san_nr'],
                1 => $values['san_anrede_anzeige'],
                2 => $values['san_asv_familienname'],
                3 => $values['san_asv_vornamen'],
                4 => $values['san_personentyp_anzeige'],
                5 => $values['san_asv_strasse'],
                6 => $values['san_asv_nummer'],
                7 => $values['san_asv_postleitzahl'],
                8 => $values['san_asv_ortsbezeichnung'],
            );
        }
        $result['san_nm_rows'] = $san_nm_rows;

        // Noten
        $schueler_id = Api\Cache::getSession('schulmanager', 'schueler_filter_id');
        $query_in = array(
            'start' => 0,
            'col_filter' => array(
                'schueler_id' => $schueler_id,
            ),
        );
        $rows = array();
        //$this->get_klassen_rows($query_in, $rows, $readonlys);
        $klassen_schueler_list = Api\Cache::getSession('schulmanager', 'klassen_schueler_list');
        $schueler = $klassen_schueler_list[$schueler_id];
        $this->schueler_bo->getNotenAbstract($schueler, $rows, $query_in['col_filter']['schueler_id']);
        $noten_nm_rows = array();
        foreach($rows as $key => $values) {
            $noten_nm_rows[$key] = array(
                0 => $values['fachname'],
                1 => $values['noten']['alt_b'][-1]['checked'],
                2 => $values['noten']['glnw_hj_1']['concat'],
                3 => $values['noten']['klnw_hj_1']['concat'],
                4 => $values['noten']['glnw_hj_1']['-1']['note'],
                5 => $values['noten']['klnw_hj_1']['-1']['note'],
                6 => $values['noten']['schnitt_hj_1'][-1]['note'],
                7 => $values['noten']['note_hj_1'][-1]['note'],
                8 => $values['noten']['m_hj_1'][-1]['note'],
                9 => $values['noten']['v_hj_1'][-1]['note'],
                10 => $values['noten']['glnw_hj_2']['concat'],
                11 => $values['noten']['klnw_hj_2']['concat'],
                12 => $values['noten']['glnw_hj_2']['-1']['note'],
                13 => $values['noten']['klnw_hj_2']['-1']['note'],
                14 => $values['noten']['schnitt_hj_2'][-1]['note'],
                15 => $values['noten']['note_hj_2'][-1]['note'],
                16 => $values['noten']['m_hj_2'][-1]['note'],
                17 => $values['noten']['v_hj_2'][-1]['note'],
            );
        }
        $result['noten_nm_rows'] = $noten_nm_rows;
    }

    public static function getSchuljahrXXXX(){
        $config = Api\Config::read('schulmanager');
        return $config['schuljahr'];
    }

    public static function getSchuljahrXXXXYY(){
        $schuljahr = self::getSchuljahrXXXX();
        $schuljahrPlus = $schuljahr + 1;
        return $schuljahr.'/'.substr($schuljahrPlus, -2);
    }

    public static function getSchulname(){
        $config = Api\Config::read('schulmanager');
        return $config['schulname'];
    }
    public static function getSchulnameSub(){
        $config = Api\Config::read('schulmanager');
        return $config['schulname_sub'];
    }
    public static function getHeaderColorRGB(){
        // 0, 112, 192
        $config = Api\Config::read('schulmanager');
        $rgb = $config['color_header'];
        return preg_split('/;|,/', $rgb, 3);
    }
    public static function getLogoImageURL(){
        $config = Api\Config::read('schulmanager');
        return $config['logo_img_url'];
    }
}