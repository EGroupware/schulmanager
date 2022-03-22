<?php

/**
 * Schulmanager - Export calendar
 *
 * @link http://www.egroupware.org
 * @package schulmanager
 * @author Axel Wild <info-AT-wild-solutions.de>
 * @copyright (c) 2022 by Axel Wild <info-AT-wild-solutions.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

require_once('fpdf/fpdf.php');

class schulmanager_export_cal_pdf extends schulmanager_export_pdf //FPDF
{

    /**
     * days
     * @var
     */
	protected $days;
    /**
     * active year
     * @var string
     */
	protected $schuljahr;

    /**
     * Constructor
     * @param $rows
     * @param $days
     * @param $title
     */
	function __construct($rows, $days, $title)
	{
		parent::__construct($rows, $title);
		$this->days = $days;
		$this->schuljahr = schulmanager_ui::getSchuljahrXXXXYY();
	}

	/**
	 * creates sheet of grades
	 * @return type
	 */
	function createPDFCalendarMonth(){
		$this->AliasNbPages();
		$this->AddPage("L");
		$this->SetFont('Arial','',11);

		// Titelzeite
		$this->Ln();
		$this->SetTextColor(0);
		$this->SetFont('Arial','B',14);
		$this->Cell(120,8,$this->title,0,0,'L');
		// Title
		$this->Cell(30,8,utf8_decode('Schulaufgabenplan'),0,0,'C');
		$date = new DateTime();

		$this->SetFont('Arial','',10);
		$this->Cell(0,8,$date->format('d.m.Y - H:i'),0,0,'R');
		// End Titelzeile

		// Line break
		$this->Ln();

		$header = $this->createTableHeader();
		$this->createTable($header);
		$filename = str_replace(' ', '_', $this->title).'_Schulaufgaben_'.$date->format('Y-m-d').'.pdf';
		$strPDF = $this->Output('D', $filename);
		return $strPDF;
	}

	/**
	 * creates table header
	 * @return string
	 */
	function createTableHeader(){
		$header = array();
		$header[0] = array(
			't' => 'Klasse',
			'w' => 15,
			'class' => 0,
		);
		$header[1] = array(
			't' => '',
			'w' => 5,
			'class' => 0,
		);
		for($i=1; $i < 32; $i++) {
			$header[$i+1] = array(
				't' => $i.".\n".$this->days['nm_header']['hday_'.$i]['name'],
				'w' => 8.3,
				'class' => $this->days['nm_header']['hday_'.$i]['class'],
			);
		}
		return $header;
	}


    /**
     * Create table of month
     * @param $header
     * @param null $faecher
     */
	function createTable($header, $faecher = null){
		$this->createTableStruktur($header);

		foreach($this->rows as $key => $row)
		{
			$klasse = $this->rows[$key]['kg']['klasse'];
			$kennung = $this->rows[$key]['kg']['kennung'];
			if(!is_numeric($key)){
				continue;
			}
			$this->SetLineWidth(self::LW_TINY);

			$this->SetFontSize(10);
			$this->Cell($header[0]['w'],5,$klasse,1,0,'L',true);
			$this->Cell($header[1]['w'],5,$kennung,1,0,'L',true);

			for($i=1; $i < 32; $i++) {
				if($header[$i+1]['class'] == 'sm_cal_saso'){
					$this->SetFillColor(224,235,255);
				}
				else{
					$this->SetFillColor(255, 255, 255);
				}
				$x = $this->GetX();
				$y = $this->GetY();
				$w = $header[$i+1]['w'];

				//$this->Cell($w,5,$i,1,0,'L',true);
				$this->Multicell($w,5,$this->rows[$key]['cal'][$i]['teaser'],1,'C',true);

				if($i != 31){
					$this->SetXY($x+$w,$y);
				}
				else{
					$this->SetXY(0, $this->GetY()-5);
				}
			}

			$this->Ln();

			$this->SetLineWidth(self::LW_TINY);
		}
		// Closing line
	}

    /**
     * Create table structure
     * @param array $header
     */
	protected function createTableStruktur(array $header){
		// Colors, line width and bold font
		$this->SetFillColor(255,255,255);
		$this->SetTextColor(0,0,0);
		$this->SetDrawColor(0,0,0);
		$this->SetLineWidth(.1);
		$this->SetFont('','B');

		// HEADER
		for($i=0;$i<2;$i++){
			$x = $this->GetX();
			$y = $this->GetY();
			$w = $header[$i]['w'];
			$this->Multicell($w,8,$header[$i]['t'],1,'C',true);
			$this->SetXY($x+$w,$y);
		}
		$this->SetFont('');
		for($i=2;$i<33;$i++){
			$x = $this->GetX();
			$y = $this->GetY();
			$w = $header[$i]['w'];

			if($header[$i]['class'] == 'sm_cal_saso'){
				$this->SetFillColor(224,235,255);
			}
			else{
				$this->SetFillColor(255, 255, 255);
			}

			$this->Multicell($w,4,$header[$i]['t'],1,'C',true);
			if($i != 32){
				$this->SetXY($x+$w,$y);
			}
			else{
				$this->SetXY(0, $this->GetY()-4);
			}
		}

		// Rows
		$this->Ln();
		// Color and font restoration
		//$this->SetFillColor(224,235,255);
		$this->SetTextColor(0);
		$this->SetFont('');
	}
}

