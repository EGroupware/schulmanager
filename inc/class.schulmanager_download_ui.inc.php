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
        'exportpdf_calm' => true,
        //'exportpdf_test' => true,
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
		$meta = array();
		$session_rows = Api\Cache::getSession('schulmanager', 'notenmanager_rows');
		$filter = Api\Cache::getSession('schulmanager', 'filter');

		$myLehrer = new schulmanager_lehrer_bo();
		$klasse_fach = $myLehrer->getKlasseUnterrichtList()[$filter];
		$exportpdf = new schulmanager_export_pdf($session_rows, $klasse_fach, $meta);

		return $exportpdf->createPDFFachNotenListe();
	}

	/**
	 * Creates PDF-Klassenview
	 * @param array $content
	 * @param type $msg
	 */
	function exportpdf_kv(array $content = null,$msg='')
	{
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
		return $exportpdf->createPDFklassenNotenListe();
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
            return $exportpdf->createPDFCalendarMonth();
	}

    // Maybe this will be used for school reports
    /*
    function exportpdf_test(array $content = null,$msg='')
    {
        $fields = array(
            'testabc' => 'TEST abc',
        );
        // getcwd().
        $pdf = new FPDM('schulmanager/inc/pdftempl/formtest2.pdf');
        $pdf->Load($fields, false); // second parameter: false if field values are in ISO-8859-1, true if UTF-8
        $pdf->Merge();
        return $pdf->Output();
    }
    */
}