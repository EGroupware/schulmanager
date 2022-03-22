<?php

/**
 * SchulManager - User interface
 */
use EGroupware\Api;
use EGroupware\Api\Egw;

use EGroupware\Api\Framework;

use EGroupware\Api\Etemplate;

/**
 * This class is the UI-layer (user interface)
 */
class schulmanager_download_ui
{
	var $public_functions = array(
		'exportpdf_nb'	  => True,
		'exportpdf_kv' => true,
                'exportpdf_calm' => true,
		//'exportasv' => True,

//		'index' => True,
	);

	/**
	 * Constructor
	 *
	 * @return notenmanager_ui
	 */
	function __construct(Etemplate $etemplate = null)
	{

	}




	/**
	 * List Schulmanager entries
	 *
	 * @param array $content
	 * @param string $msg
	 */
	function index(array $content = null,$msg='')
	{
		$etpl = new Etemplate('schulmanager.download');

		if ($_GET['msg']) $msg = $_GET['msg'];

		$sel_options = array();
		$preserv = array();

		$filter = Api\Cache::getSession('schulmanager', 'filter');
		if(empty($filter)){
			$filter = 0;
		}


		$content = array(
			'nm' => Api\Cache::getSession('schulmanager', 'index'),
			'msg' => $msg,
		);


	//

		$readonlys = array(
			'button[edit]'     => false,
		);

		$preserv = $sel_options;
		//$this->template->read('schulmanager.notenmanager.index');
		//return $this->template->exec('schulmanager.notenmanager_ui.index',$content,$sel_options,$readonlys,$preserv);
		return $etpl->exec('schulmanager.schulmanager_download_ui.index',$content,$sel_options,$readonlys,$preserv);
	}




	/**
	 * List Schulmanager entries
	 *
	 * @param array $content
	 * @param string $msg
	 */
	function exportasv(array $content = null,$msg='')
	{
		if($GLOBALS['egw_info']['user']['apps']['admin'])
		{
			$xmlData = array(
				'type'	=> 'text/xml',
				'charset' => 'utf8',
				'filename'	=> 'noten-export.xml',
			);

			$path = 'asvexport.xml';
			$mime = '';

			$length = 0;
			// public static function safe(&$content, $path, &$mime='', &$length=0, $nocache=true, $force_download=true, $no_content_type=false)
			Api\Header\Content::safe($xmlData, $path, $mime, $length, True, True);
			echo $this->bo->asvNotenExport();
		}

		exit();
	}


	/**
	 * List Schulmanager entries
	 *
	 * @param array $content
	 * @param string $msg
	 */
	function exportpdf_nb(array $content = null,$msg='')
	{
		//$this->bo->pdfNotenExport();
		$meta = array();
		$session_rows = Api\Cache::getSession('schulmanager', 'notenmanager_rows');
		$filter = Api\Cache::getSession('schulmanager', 'filter');

		$myLehrer = new schulmanager_lehrer_bo($GLOBALS['egw_info']['user']['account_lid']);
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
                $session_rows = Api\Cache::getSession('schulmanager', 'klassen_schueler_list');
		$filter = Api\Cache::getSession('schulmanager', 'filter');

		$schueler_bo = new schulmanager_schueler_bo();
		$myLehrer = new schulmanager_lehrer_bo($GLOBALS['egw_info']['user']['account_lid']);
		$rowid = 0;
		$klasse_id = Api\Cache::getSession('schulmanager', 'klassen_filter_id');

		$klassenArray = $myLehrer->getKlassen();
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

		$exportpdf = new schulmanager_export_kv_pdf($session_rows, $klassenname);
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






}