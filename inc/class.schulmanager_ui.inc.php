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
        'devtest' => True,
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

    var $sreport_so;


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
        $this->sreport_so = new schulmanager_sreportcontent_so();
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
     * DevTest
     *
     * @param array $content
     * @param string $msg
     */
    function devtest(array $content = null,$msg='')
    {
        Api\Header\ContentSecurityPolicy::add('script-src', "unsafe-inline");
        $config = Api\Config::read('schulmanager');
        $etpl = new Etemplate('schulmanager.devtest');


        if (!is_array($content))
        {
            $content = array('nm' => Api\Cache::getSession('schulmanager', 'devtest'));
            if (!is_array($content['nm']) || !$content['nm']['get_devtest_rows'])
            {
                if (!is_array($content['nm'])) $content['nm'] = array();
                $content['nm'] += array(
                    'get_rows'   => 'schulmanager.schulmanager_ui.get_devtest_rows',
                    'no_cat'     => true,
                    'no_filter'  => true,
                    'no_filter2' => true,
                    'num_rows'   => 999,
                    'order'      => 'start_order',
                    'sort'       => 'ASC',
                    'show_result'=> 1,
                    'hide_header'=> true,
                );
            }
            if ($_GET['msg']) $msg = $_GET['msg'];
        }

        $content += array(
            'devtest' => 'weertz'
        );

        $sel_options = array();
        $preserv = array();
        $readonlys = array();
        $preserv = $sel_options;

        // fake call to get_rows()
        if (!$content['no_list'])
        {
            $this->get_devtest_rows($content['nm'], $content['nm']['rows'], $readonlys);
            array_unshift($content['nm']['rows'], false);	// 1 header rows
        }

        return $etpl->exec('schulmanager.schulmanager_ui.devtest', $content, $sel_options, $readonlys, $preserv);
    }

    function get_devtest_rows(&$query_in,&$rows,&$readonlys)
    {
        $rows[0] = array(
          'nr' => '1',
          'nachname' => 'Maier',
          'test' => 'ckecked'
        );

        $rows[1] = array(
            'nr' => '2',
            'nachname' => 'Huber',
            'test' => false
        );
        return 2;
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


        $content['select_klasse'] = Api\Cache::getSession('schulmanager', 'filter');

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

        $sel_options['select_schueler'] = $this->extractSchuelerListFromRows($rows, true);

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
            $button = @key($content['button']);

            unset($content['button']);
            if ($button)
            {
                if ($button == 'save' || $button == 'apply')
                {
                    // check token for security purpose
                    if(!$this->checkToken('token_note_modified', $content['token'])){
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
        // only grid
        $content['nm']['num_rows'] = 999;
        //$content['nm']['template'] = 'schulmanager.notenmanager.edit.rows';

        $xrsf_token = bin2hex(random_bytes(32));
        Api\Cache::setSession('schulmanager', 'token_note_modified', $xrsf_token);

        //$content['nm']['token'] = $xrsf_token;
        $content['token'] = $xrsf_token;

        $content['klnwReadOnly'] = "false";

        $content['inputinfo']['date'] = new DateTime();
        $content['inputinfo']['desc'] = '';

        $sel_options['notgebart'] = schulmanager_werteliste_bo::getNotenArtListCombi();// $wl_notgebart;

        $readonlys = array(
            'button[save]'     => false,
            'button[apply]'    => false,
            'button[cancel]'   => false,
        );

        $preserv = $content;

        // fake call to get_rows()
        if (!$content['no_list'])
        {
            if(!is_array($content['nm']['rows'])){
                $content['nm']['rows'] = array();
            }
            $content['nm']['total'] = 0; // nm loads twice, here not usefull
            $this->get_rows_edit($content['nm'], $content['nm']['rows'], $readonlys);
            array_unshift($content['nm']['rows'], false);	// 1 header rows
        }

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
        if(isset($query_in['filter'])){
            // load records
            if($query_in['filter'] != Api\Cache::getSession('schulmanager', 'filter')){
                unset($query_in['rows_total']);  // load new group
                Api\Cache::unsetSession('schulmanager', 'notenmanager_temp_rows');
                Api\Cache::unsetSession('schulmanager', 'notenmanager_rows');
            }
            Api\Cache::setSession('schulmanager', 'filter', $query_in['filter']);
        }
        else{
            // edit records
            Api\Cache::unsetSession('schulmanager', 'notenmanager_modified_records');
            $query_in['filter'] = Api\Cache::getSession('schulmanager', 'filter');
        }

        $this->bo->getSchuelerNotenList($query_in,$rows);
        $this->mergeSessionRows($rows);
        //Api\Cache::setSession('schulmanager', 'notenmanager_temp_rows', $rows);
        //Api\Cache::setSession('schulmanager', 'notenmanager_rows', $rows);
        return $query_in['rows_total'];
    }

    /**
     * Merge lazy loaded rows into cached rows, if exists
     * @param $rows
     * @return void
     */
    function mergeSessionRows(&$rows){
        $sessionRows = Api\Cache::getSession('schulmanager', 'notenmanager_rows');
        if(is_array($sessionRows)) {
            foreach ($rows as $key => $value) {
                if (is_numeric($key)) {
                    $sessionRows[$key] = $value;
                }
            }
        }
        else{
            $sessionRows = $rows;
        }
        Api\Cache::setSession('schulmanager', 'notenmanager_temp_rows', $sessionRows);
        Api\Cache::setSession('schulmanager', 'notenmanager_rows', $sessionRows);
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
        return $query_in['rows_total'];
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
            $klassen_schueler_list = Api\Cache::getSession('schulmanager', 'klassen_schueler_list');
            $schueler = $klassen_schueler_list[$query_in['col_filter']['schueler_id']];
            $this->schueler_bo->getNotenAbstract($schueler, $rows, $query_in['col_filter']['schueler_id']);
        }
        return count($rows);
    }

    /**
     * Loading weight of grades
     * @param $query
     * @throws Api\Json\Exception
     */
    function ajax_getGewichtungen($query) {
        $koppel_id = Api\Cache::getSession('schulmanager', 'filter_koppel_id');

        // Gewichtungen
        $gewichtungen = array();
        $gew_bo = new schulmanager_note_gew_bo();
        $gew_bo->loadGewichtungen($koppel_id, $gewichtungen);

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
    function ajax_noteModified($noteKey, $noteVal, $token, $definition_date, $art, $description)
    {
        $result = array();
        $note_bo = new schulmanager_note_bo();

        // xsrf check
        if (!$this->checkToken('token_note_modified', $token)) {
            $result['error_msg'] = 'ERROR: could not submit date!';
            Api\Json\Response::get()->data($result);
            return;
        }

        // Key/val in session speichern
        $modified_records = Api\Cache::getSession('schulmanager', 'notenmanager_modified_records');
        if (!is_array($modified_records)) {
            // maybe first modified record
            $modified_records = array();
        }
        // inc key because of array shift
        $keys = preg_split('/\\]\\[|\\[|\\]/', $noteKey, -1, PREG_SPLIT_NO_EMPTY);

        // validation for major and minor marks input
        if ($keys[3] >= 0 && (($art == 0 && str_starts_with($keys[2], 'klnw_hj_')) || ($art > 0 && str_starts_with($keys[2], 'glnw_hj_')))){
            $result['error_msg'] = "Art der Note nicht für die Eingabe gültig";
        }
        else{
            // remove unnecessary art and descriptions, not marks input processed
            if ($keys[3] == -1){
                $art = '';
                $description = '';
            }

            $modNoteKey = ($keys[0] - 1).'['.$keys[1].']['.$keys[2].']['.$keys[3].']['.$keys[4].']';
            //$modified_records[$noteKey] = $noteVal;
            $modified_records[$modNoteKey] = array(
                'val' => $noteVal,
                'date' => strtotime($definition_date),
                'art' => $art,
                'desc' => $description,
            );
            Api\Cache::setSession('schulmanager', 'notenmanager_modified_records', $modified_records);
        }

        // neue Schnitte berechnen
        $rows = Api\Cache::getSession('schulmanager', 'notenmanager_temp_rows');
        $gewichtungen = Api\Cache::getSession('schulmanager', 'notenmanager_gewichtungen');

        $keys[0] = $keys[0] - 1; // shifted in grid!!!!
        $schueler = $rows[$keys[0]];

        // clean up
        $this->resetAVGNoten($schueler);
        // ende clean up

        $schueler[$keys[1]][$keys[2]][$keys[3]][$keys[4]] = $noteVal;

        // manuelle Einträge
        if($keys[3] == -1 && !empty($noteVal)){
            // schnitt oder note wurde geändert und ist nicht nur gelöscht
            $schueler[$keys[1]][$keys[2]][$keys[3]]['manuell'] = true;
        }
        elseif($keys[3] == -1 && empty($noteVal)){
            // schnitt oder note wurde geändert und ist nicht nur gelöscht
            $schueler[$keys[1]][$keys[2]][$keys[3]]['manuell'] = false;
        }

        // alternative Berechnung
        if($keys[2] === 'alt_b' && $keys[3] == -1){
            $schueler[$keys[1]][$keys[2]][$keys[3]]['note'] = $noteVal;
        }

        $note_bo->beforeSendToClient($schueler, $gewichtungen);		// Schnitte und noten neu berechnen
        $rows[$keys[0]] = $schueler;
        Api\Cache::setSession('schulmanager', 'notenmanager_temp_rows', $rows);

        // $keys[0] + 1 backshift to view in grid
        $this->setAVGNoten($result, $keys[0] + 1, $schueler);
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
            'start' => 0,
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

        $actual_lesson = Api\Cache::getSession('schulmanager', 'actual_lesson');
        $result['details_klasse'] = $stud['klasse']['name'];
        $result['details_fach'] = $actual_lesson['bezeichnung'];

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
        $actual_lesson = Api\Cache::getSession('schulmanager', 'actual_lesson');
        $result['details_klasse'] = $stud['klasse']['name'];
        $result['details_fach'] = $actual_lesson['bezeichnung'];

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
        $query_in = array(
            'filter' => $klasse_id,
        );
        $rows = array();
        $readonlys = array();

        Api\Cache::setSession('schulmanager', 'details_filter_schueler', 0); // reset selected schueler
        $this->get_rows($query_in, $rows, $readonlys);

        //$schuelerList = $this->extractSchuelerFromRows($rows);
        $content = array();
        $content['select_schueler'] = $this->extractSchuelerListFromRows($rows, true);

        $this->extractSchuelerDataFromRows($content, $rows, 0);
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
    function extractSchuelerListFromRows(array $rows, $addClassInfo = false){
        $result = array();
        foreach ($rows as $key => $value) {
            if (is_numeric($key)) {
                $result[$key] = $value['nm_st']['st_asv_familienname'].' '.$value['nm_st']['st_asv_rufname'];
                if($addClassInfo) {
                    $result[$key] = $result[$key].' ('.$value['klasse']['name'].')';
                }
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
        $actual_lesson = Api\Cache::getSession('schulmanager', 'actual_lesson');

        if(!empty($actual_lesson)){
            $content['klasse'] =  $rows[$selected_schueler_index]['klasse']['name'];
            $content['fach'] =  $actual_lesson['fach_name'];

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
     * Ajax call, wenn gew was modified, before being saved
     * Revalidate avg-values with new grades.
     * @param type $noteKey
     * @param type $noteVal
     */
    function ajax_gewModified($gewKey, $gewVal) {
        $result = array();
        $note_bo = new schulmanager_note_bo();
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
                $grid_row = $key + 1;
                $this->resetAVGNoten($schueler);
                $note_bo->beforeSendToClient($schueler, $gewichtungen);
                // to result, $key + 1 shift in grid, first row is the header
                $this->setAVGNoten($result, $grid_row, $schueler);
            }
        }
        Api\Cache::setSession('schulmanager', 'notenmanager_gewichtungen', $gewichtungen);
        Api\Cache::setSession('schulmanager', 'notenmanager_temp_rows', $rows);
        Api\Json\Response::get()->data($result);
    }

    /**
     * Ajax call, when gew was modified in header checkbox, before being saved
     * Revalidate avg-values with new grades.
     * @param type $noteKey
     * @param type $noteVal
     */
    function ajax_gewAllModified(string $gewKey, int $altb_Val) {
        $result = array();
        $note_bo = new schulmanager_note_bo();

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
                $grid_row = $key + 1; // first row is the header in grid, not nm
                $this->resetAVGNoten($schueler);
                $note_bo->beforeSendToClient($schueler, $gewichtungen);
                // to result
                $this->setAVGNoten($result, $grid_row, $schueler);
                //TODO: test ob geändert wurde
                $result[$grid_row.'[noten][alt_b]']['[-1][checked]'] = $altb_Val == 1;
            }
        }

        Api\Cache::setSession('schulmanager', 'notenmanager_temp_rows', $rows);
        Api\Json\Response::get()->data($result);
    }


    /**
     * set all avg grades to result array
     * @param array $result
     * @param $key
     * @param array $schueler
     * @return void
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

        $wl_gefaehrdung = schulmanager_werteliste_bo::getGefaehrdungList(true);
        $select_gefaehrung = array();
        $select_gefaehrung[] = "";
        foreach($wl_gefaehrdung as $key => $value){
            $select_gefaehrung[] = $value;
        }
        $sel_options['select_zz_gefaehrdung'] = $select_gefaehrung;

        $sel_options['select_klasse'] = $this->bo->getClassLeaderClasses();

        $classLeaderClasses = $this->bo->getClassLeaderClasses(false);

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

        $selClass = $classLeaderClasses[$selected_klasse_index];
        $content['header_klassleitung_k'] = $selClass['1111_K_givenname'].' '.$selClass['1111_K_sn'];
        $content['header_klassleitung_s'] = $selClass['1111_S_givenname'].' '.$selClass['1111_S_sn'];
        $content['header_schuelername'] = $sel_options['select_schueler'][$selected_schueler_index]; //$klassen_schueler_list[$schueler_id]['nm_st']['st_asv_familienname'].' '.$klassen_schueler_list[$schueler_id]['nm_st']['st_asv_rufname']

        $xrsf_token = bin2hex(random_bytes(32));
        Api\Cache::setSession('schulmanager', 'token_schuelerview', $xrsf_token);
        $content['token'] = $xrsf_token;

        $content['not_nm']['isadmin'] = isset($GLOBALS['egw_info']['user']['apps']['admin']);
        $content['isadmin'] = isset($GLOBALS['egw_info']['user']['apps']['admin']);

        $preserv = $sel_options;

        // fake call to get_rows()
        if (!$content['no_list'])
        {
            if(!is_array($content['nm']['rows'])){
                $content['not_nm']['rows'] = array();
            }
            $content['not_nm']['total'] = 0; // nm loads twice, here not usefull
            $this->not_get_rows($content['not_nm'], $content['not_nm']['rows'], $readonlys);
            array_unshift($content['not_nm']['rows'], false);	// 1 header rows
        }

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
        $this->schueler_bo->getNotenAbstract($schueler, $rows, $schueler_id, true);

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

        $selKlasse = $this->bo->getClassLeaderClasses(false)[$klasse_id];

        $result['header_klasse'] = $selKlasse['name'];
        $selClass = $result['header_klasse'][$klasse_id];
        $result['header_klassleitung_k'] = $selKlasse['1111_K_givenname'].' '.$selKlasse['1111_K_sn'];
        $result['header_klassleitung_s'] = $selKlasse['1111_S_givenname'].' '.$selKlasse['1111_S_sn'];


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

    function ajax_schuelerview_zz_commit($gefaehrd, $abweis, $token){
        $config = Api\Config::read('schulmanager');
        $result = array();
        if(!$this->checkToken('token_schuelerview', $token)){
            $result['error_msg'] = 'ERROR: could not submit date!';
            Api\Json\Response::get()->data($result);
            return;
        }

        $wl_gefaehrdung_list = schulmanager_werteliste_bo::getGefaehrdungList(false);

        $schueler_id = Api\Cache::getSession('schulmanager', 'schueler_filter_id');
        $klassen_schueler_list = Api\Cache::getSession('schulmanager', 'klassen_schueler_list');
        $schueler = $klassen_schueler_list[$schueler_id];
        $schueler_stamm_id = $schueler['nm_st']['st_asv_id'];

        if($gefaehrd != 0){
            // -1, 0 is empty list item
            $wl_gefaehrd = $wl_gefaehrdung_list[((int)$gefaehrd) - 1];
            $this->sreport_so->saveItem($schueler_stamm_id, 'key_zz_gefaehrdung', $wl_gefaehrd['asv_wert_langform'], $wl_gefaehrd['asv_wert_id'], $wl_gefaehrd['asv_wert_kurzform'], $wl_gefaehrd['asv_wert_anzeigeform']);
        }
        else{
            $this->sreport_so->saveItem($schueler_stamm_id, 'key_zz_gefaehrdung', '');
        }
        if($abweis){
            if($schueler['nm_st']['geschlecht'] == 'M'){
                $zz_abweisung_value = str_replace("##der_die_schueler_in##", "Der Schüler", $config['notenbild_zz_abweisung']);
            }
            else{
                $zz_abweisung_value = str_replace("##der_die_schueler_in##", "Die Schülerin", $config['notenbild_zz_abweisung']);
            }
            $this->sreport_so->saveItem($schueler_stamm_id, 'key_zz_abweisung', $zz_abweisung_value);
        }
        else{
            $this->sreport_so->saveItem($schueler_stamm_id, 'key_zz_abweisung', '');
        }
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

        $note_so = new schulmanager_note_so();
        $note_so->delLnwPer($schueler, true, false);
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

        $note_so = new schulmanager_note_so();
        $note_so->delLnwPer($schueler, false, true);
        $this->bo->revalidateGrades($schueler['nm_st']['st_asv_id']);
        Api\Json\Response::get()->data($result);
    }

    /**
     * Loads student data to result array
     * @param $result
     */
    function getSchuelerViewSchuelerData(&$result)
    {
        $query_in = array('start' => 0,);
        $readonlys = array();

        // Schuelerlaufbahn
        $rows = array();
        $this->sla_get_rows($query_in, $rows, $readonlys);
        $sla_nm_rows = array();
        foreach ($rows as $key => $values) {
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
        foreach ($rows as $key => $values) {
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
        foreach ($rows as $key => $values) {
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
        $this->schueler_bo->getNotenAbstract($schueler, $rows, $query_in['col_filter']['schueler_id'], true);
        $noten_nm_rows = array();
        foreach ($rows as $key => $values) {
            $noten_nm_rows[] = array(
                0 => $values['fachname'],
                1 => $values['noten']['alt_b'],
                2 => $values['noten']['glnw'],
                3 => $values['noten']['klnw'],
                4 => $values['noten']['glnw_avg'],
                5 => $values['noten']['klnw_avg'],
                6 => $values['noten']['schnitt'],
                7 => $values['noten']['note'],
            );
        }
        $result['noten_nm_rows'] = $noten_nm_rows;

        // Informationen für Zeugnisse und Notenberichte
        $this->schueler_bo->getEvaluationInfo($schueler, $result);
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