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
use EGroupware\Api\Etemplate;

/**
 * This class is the UI-layer (user interface)
 */
class schulmanager_mntc_ui
{

    /**
     * instance of the bo-class
     * @var schulmanager_mntc_bo
     */
    var $bo;

    var $public_functions = array(
        'index'       => True,
    );

    /**
     * Constructor
     * @return schulmanager_mntc_ui
     */
    function __construct(Etemplate $etemplate = null)
    {
        $this->bo = new schulmanager_mntc_bo();
    }

    /**
     * Context menu
     * @return array
     */
    public static function get_actions(array $content)
    {
        $actions = array();
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
     * Maintenance
     *
     * @param array $content
     * @param string $msg
     */
    function index(array $content = null,$msg='')
    {
        $config = Api\Config::read('schulmanager');
        $etpl = new Etemplate('schulmanager.mntc');

        if ($_GET['msg']) $msg = $_GET['msg'];

        $sel_options = array();
        $preserv = array();

        if (!is_array($content['nm']))
        {
            $content = array();
        }

        $xrsf_token = bin2hex(random_bytes(32));
        Api\Cache::setSession('schulmanager', 'token_mntc', $xrsf_token);
        $content['token'] = $xrsf_token;

        $readonlys = array(
            'button[edit]'     => false,
        );
        $preserv = $sel_options;
        return $etpl->exec('schulmanager.schulmanager_mntc_ui.index',$content,$sel_options,$readonlys,$preserv);
    }

    /**
     * checks if token and saved token are equal
     * @param $key
     * @param $token
     * @return bool
     */
    function checkToken($key, $token){
        $sessToken = Api\Cache::getSession('schulmanager', $key);
        return !empty($sessToken) && !empty($token) && $sessToken == $token;
    }

    /**
     * delete all grades
     * @return void
     * @throws Api\Json\Exception
     */
    function ajax_resetAllGrades($token, $value){
        $result = array();

        if (!$this->checkToken('token_mntc', $token)) {
            $result['error_msg'] = 'ERROR: could not complete action!';
            Api\Json\Response::get()->data($result);
            return;
        }

        $config = Api\Config::read('schulmanager');
        $secret = $config['delete_grades_secret'];

        if ($secret != $value) {
            $result['error_msg'] = 'ERROR: wrong PIN!';
            Api\Json\Response::get()->data($result);
            return;
        }

        $this->bo->resetAllGrades();
        $result['msg'] = 'All grades were successfully deleted';

        Api\Json\Response::get()->data($result);
    }

}