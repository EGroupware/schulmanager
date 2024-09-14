<?php

/**
 * SchulManager - download User interface
 *
 * @link http://www.egroupware.org
 * @package schulmanager
 * @author Axel Wild <info-AT-wild-solutions.de>
 * @copyright (c) 2022 by Axel Wild <info-AT-wild-solutions.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */
use EGroupware\Api;
use EGroupware\Api\Etemplate;


require('fpdm/fpdm.php');
/**
 * This class is the UI-layer (user interface)
 */
class schulmanager_download_ui
{
	var $public_functions = array(
		'exportpdf_nb'	  => True,
		'exportpdf_kv' => true,
        'exportpdf_nbericht' => true,
        'exportpdf_calm' => true,
	);

	/**
	 * Constructor
	 * @return notenmanager_ui
	 */
	function __construct(Etemplate $etemplate = null)
	{
	}

	/**
	 * List Schulmanager entries
	 * @param array $content
	 * @param string $msg
	 */
	function index(array $content = null,$msg='')
	{
		$etpl = new Etemplate('schulmanager.download');

		if ($_GET['msg']) $msg = $_GET['msg'];

		$sel_options = array();

		$content = array(
			'nm' => Api\Cache::getSession('schulmanager', 'index'),
			'msg' => $msg,
		);
		$readonlys = array(
			'button[edit]'     => false,
		);

		$preserv = $sel_options;
		return $etpl->exec('schulmanager.schulmanager_download_ui.index',$content,$sel_options,$readonlys,$preserv);
	}

	/**
	 * List Schulmanager entries
	 * @param array $content
	 * @param string $msg
	 */
	function exportpdf_nb(array $content = null,$msg='')
	{
		$path = '';
        $mime = '';
        $length = 0;

        $meta = array();
        $session_rows = Api\Cache::getSession('schulmanager', 'notenmanager_rows');
        $filter = Api\Cache::getSession('schulmanager', 'filter');
        $myLehrer = new schulmanager_lehrer_bo();
        $klasse_fach = $myLehrer->getLessonList()[$filter];
        $exportpdf = new schulmanager_export_pdf($session_rows, $klasse_fach, $meta);

        $pdfOutput =  $exportpdf->createPDFFachNotenListe();
        Api\Header\Content::safe($pdfOutput, $path, $mime, $length, True, True);
        echo $pdfOutput;
        exit();
	}

	/**
	 * Creates PDF-Klassenview
	 * @param array $content
	 * @param type $msg
	 */
	function exportpdf_kv(array $content = null,$msg='')
	{
        $path = '';
        $mime = '';
        $length = 0;

	    $mode = 'stud';
        if ($_REQUEST['mode']) $mode = $_REQUEST['mode'];

        $session_rows = Api\Cache::getSession('schulmanager', 'klassen_schueler_list');

		$schueler_bo = new schulmanager_schueler_bo();
		$myLehrer = new schulmanager_lehrer_bo();
		$rowid = 0;
		$klasse_id = Api\Cache::getSession('schulmanager', 'klassen_filter_id');

		$klassenArray = $myLehrer->getClassLeaderClasses();
		$klassenname = '';
		if(array_key_exists($klasse_id, $klassenArray)){
			$klassenname = $klassenArray[$klasse_id];
		}

		foreach($session_rows as &$schueler)
		{
			$rowid++;
			$rows = array();
			$schueler_bo->getNotenAbstract($schueler, $rows, $rowid);
			$schueler['faecher'] = $rows;
		}

		$exportpdf = new schulmanager_export_kv_pdf($session_rows, $klassenname, $mode);
		$pdfOutput = $exportpdf->createPDFklassenNotenListe();
        Api\Header\Content::safe($pdfOutput, $path, $mime, $length, True, True);
        echo $pdfOutput;
        exit();
	}

    /**
     * Creates PDF-Notenbericht
     * @param array $content
     * @param type $msg
     */
    function exportpdf_nbericht(array $content = null,$msg='')
    {
        $config = Api\Config::read('schulmanager');

        $sm_bo = new schulmanager_bo();

        $showReturnInfo = false;
        $signed = false;
        $signerID = 0;
        if ($_REQUEST['showReturnInfo']) $showReturnInfo = $_REQUEST['showReturnInfo'];
        if ($_REQUEST['signed']) $signed = $_REQUEST['signed'];
        if ($_REQUEST['signerid']) $signerID = $_REQUEST['signerid'];

        $session_rows = Api\Cache::getSession('schulmanager', 'klassen_schueler_list');
        $kls = Api\Cache::getSession('schulmanager', 'klassenleitungen');

        $schueler_bo = new schulmanager_schueler_bo();
        $myLehrer = new schulmanager_lehrer_bo();
        $klasse_id = Api\Cache::getSession('schulmanager', 'klassen_filter_id');

        $klassenArray = $myLehrer->getClassLeaderClasses();
        $klassenname = '';
        if(array_key_exists($klasse_id, $klassenArray)){
            $klassenname = $klassenArray[$klasse_id];
        }

        foreach($session_rows as &$schueler)
        {
            // load subjects, grades and eights
            $schueler_bo->loadSubjectsAndGrades($schueler);
            $schueler_bo->getEvaluationInfo($schueler, $schueler);
        }

        $schulleitung = $sm_bo->getSchulleitung();

        $reportConfig = array(
            'showReturnInfo' => filter_var($showReturnInfo, FILTER_VALIDATE_BOOLEAN),
            'signed' => filter_var($signed, FILTER_VALIDATE_BOOLEAN),
            'signer' => $kls[$signerID],
            'schulleitung' => $schulleitung,
            'notenbild_stichtag' => $config['notenbild_stichtag'],
            'notenbild_zeichnungstag' => $config['notenbild_zeichnungstag'],
            'schule_ort' => $config['schule_ort'],
        );

        $exportpdf = new schulmanager_export_nbericht_pdf($session_rows, $klassenname, $reportConfig);
        $pdfOutput = $exportpdf->createPDFklassenNotenbericht();
        Api\Header\Content::safe($pdfOutput, $path, $mime, $length, True, True);
        echo $pdfOutput;
        exit();
    }

    /**
	 * Creates PDF-Schulaufgabenkalender Monatsübersicht
	 * @param array $content
	 * @param type $msg
	 */
	function exportpdf_calm(array $content = null,$msg='')
	{
            setlocale(LC_TIME, "de_DE.UTF-8");
			$dateSelected = new DateTime(Api\Cache::getSession('schulmanager', 'cal_sel_month_date'));
            $session_rows = Api\Cache::getSession('schulmanager', 'schulmanager_cal_rows');
            $days =  Api\Cache::getSession('schulmanager', 'cal_weekdays');

            $exportpdf = new schulmanager_export_cal_pdf($session_rows, $days, strftime('%B %Y', $dateSelected->getTimestamp()));
            echo $exportpdf->createPDFCalendarMonth();
            exit();
	}
}