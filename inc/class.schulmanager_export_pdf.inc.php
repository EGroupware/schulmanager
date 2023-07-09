<?php

/**
 * Schulmanager - Export single student view
 *
 * @link http://www.egroupware.org
 * @package schulmanager
 * @author Axel Wild <info-AT-wild-solutions.de>
 * @copyright (c) 2022 by Axel Wild <info-AT-wild-solutions.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

require_once('fpdf/fpdf.php');

/**
 * This class is the UI-layer (user interface) of InfoLog
 */
class schulmanager_export_pdf extends FPDF
{
	const LW_TINY = 0.1;
	const LW_MEDIUM = 0.4;

	protected $rows;
	protected $title;
	protected $schuljahr;
    protected $klassenname;

	protected $header_repeat;

    /**
     * constructor
     * @param $rows
     * @param $klassenname
     * @param $meta
     */
	function __construct($rows, $klassenname, $meta = array())
	{
		parent::__construct();
		$this->rows = $rows;
		$this->title = $klassenname;
        $this->klassenname = $klassenname;
		$this->schuljahr = schulmanager_ui::getSchuljahrXXXXYY();
		$this->header_repeat = false;
	}

    /**
     * page header
     * @return void
     */
	function Header()
	{
		if ($this->page == 1 || $this->header_repeat){
			$rgb = schulmanager_ui::getHeaderColorRGB();
			$this->setXY(10, 8);
			$this->SetTextColor($rgb[0], $rgb[1], $rgb[2]);
			$this->SetDrawColor($rgb[0], $rgb[1], $rgb[2]);
			$this->SetFont('Arial','B',14);
			$this->Cell(50,6,schulmanager_ui::getSchulname(),0,0,'L');
			$this->setXY(10, 13);
			$this->SetFont('Arial','',11);
			$this->Cell(50,5,schulmanager_ui::getSchulnameSub(),0,0,'L');
			// logo
			$imgLogo = schulmanager_ui::getLogoImageURL();
			if(!empty($imgLogo)){
				$this->Image($imgLogo, 250, 2, 40, 0);
			}
			$this->Line(10, 18, 290, 18);
		}
	}

    /**
     * page footer
     * @return void
     */
	function Footer()
	{
		if('{nb}' > 1){
			// Position at 1.5 cm from bottom
			$this->SetY(-15);
			// Arial italic 8
			$this->SetFont('Arial','I',8);
			// Page number
			$this->Cell(0,10,'Seite '.$this->PageNo().'/{nb}',0,0,'C');
		}
	}

    /**
     * FachNoten list
     * @return string
     */
	function createPDFFachNotenListe(){
		//$pdf = new PDF();
		$this->AliasNbPages();
		$this->AddPage("L");
		$this->SetFont('Arial','',11);

		// Titelzeite
		$this->Ln();
		$this->SetTextColor(0);
		$this->SetFont('Arial','B',14);
		$this->Cell(120,8,$this->title,0,0,'L');
		// Title
		$this->Cell(30,8,utf8_decode('Notenübersicht'),0,0,'C');
		$date = new DateTime();
		$this->SetFont('Arial','',10);
		$this->Cell(0,8,$date->format('d.m.Y - H:i'),0,0,'R');
		// End Titelzeile

		// Line break
		$this->Ln();
		$header = $this->createTableHeader();
		$this->createTable($header);
		$filename = str_replace(' ', '_', $this->title).'_Fachnoten_'.$date->format('Y-m-d').'.pdf';

		return $this->Output('D', $filename);
	}

	/**
	 * creates table header
	 * @return string
	 */
	function createTableHeader(){
		$header = array();
		$header[0] = array(
			't' => '',
			'w' => 7,
		);
		$header[1] = array(
			't' => 'Name',
			'w' => 50,
		);
		$header[2] = array(
			't' => 'a',
			'w' => 6,
		);
		$header[3] = array(
			't' => 'G',
			'w' => 15,
		);
		$header[4] = array(
			't' => 'k',
			'w' => 33,
		);
		$header[5] = array(
			't' => 'ØG',
			'w' => 12,
		);
		$header[6] = array(
			't' => 'Øk',
			'w' => 12,
		);
		$header[7] = array(
			't' => 'Ø',
			'w' => 12,
		);
		$header[8] = array(
			't' => 'ZZ',
			'w' => 8,
		);
		$header[9] = array(
			't' => 'M',
			'w' => 8,
		);
		$header[10] = array(
			't' => 'V',
			'w' => 8,
		);
		// 2. Halbjahr
		$header[11] = array(
			't' => 'G',
			'w' => 15,
		);
		$header[12] = array(
			't' => 'k',
			'w' => 33,
		);
		$header[13] = array(
			't' => 'ØG',
			'w' => 12,
		);
		$header[14] = array(
			't' => 'Øk',
			'w' => 12,
		);
		$header[15] = array(
			't' => 'Ø',
			'w' => 12,
		);
		$header[16] = array(
			't' => 'JZ',
			'w' => 8,
		);
		$header[17] = array(
			't' => 'M',
			'w' => 8,
		);
		$header[18] = array(
			't' => 'V',
			'w' => 8,
		);
		return $header;
	}

    /**
     * create table
     * @param $header
     * @param $faecher
     * @return void
     */
	function createTable($header, $faecher = null){
		$this->createNotenTableStruktur($header);

		foreach($this->rows as $key => $row)
		{
			if(!is_numeric($key)){
				continue;
			}
			$this->SetLineWidth(self::LW_TINY);
			$nr = $row['nm_id'].'.';
			$name = $row['nm_st']['st_asv_familienname'].' '.$row['nm_st']['st_asv_rufname'];
			$altb = $row['noten']['alt_b']['-1']['checked'] ? 'x' : '';
			// 1. Halbjahr
			$glnw_hj_1 = $this->createNotenContent($row['noten']['glnw_hj_1'], 3);
			$klnw_hj_1 = $this->createNotenContent($row['noten']['klnw_hj_1'], 12);
			// trim?
            $klnw_hj_1 = is_null($klnw_hj_1) ? null : $this->trimString($klnw_hj_1, 7);

			$glnw_hj_1_avg = str_replace('.', ',', $row['noten']['glnw_hj_1']['-1']['note']);
			$glnw_hj_1_avg_manuell = $row['noten']['glnw_hj_1']['-1']['manuell'];
			$klnw_hj_1_avg = str_replace('.', ',', $row['noten']['klnw_hj_1']['-1']['note']);
			$klnw_hj_1_avg_manuell = $row['noten']['klnw_hj_1']['-1']['manuell'];
			$schnitt_hj_1 = str_replace('.', ',', $row['noten']['schnitt_hj_1']['-1']['note']);
			$schnitt_hj_1_manuell = $row['noten']['schnitt_hj_1']['-1']['manuell'];
			$note_hj_1 = $row['noten']['note_hj_1']['-1']['note'];
			$note_hj_1_manuell = $row['noten']['note_hj_1']['-1']['manuell'];
			$m_hj_1 = $row['noten']['m_hj_1']['-1']['note'];
			$v_hj_1 = $row['noten']['v_hj_1']['-1']['note'];
			// 2. Halbjahr
			$glnw_hj_2 = $this->createNotenContent($row['noten']['glnw_hj_2'], 3);
			$klnw_hj_2 = $this->createNotenContent($row['noten']['klnw_hj_2'], 12);
            $klnw_hj_2 = is_null($klnw_hj_2) ? null : $this->trimString($klnw_hj_2, 7);
			$glnw_hj_2_avg = str_replace('.', ',', $row['noten']['glnw_hj_2']['-1']['note']);
			$glnw_hj_2_avg_manuell = $row['noten']['glnw_hj_2']['-1']['manuell'];
			$klnw_hj_2_avg = str_replace('.', ',', $row['noten']['klnw_hj_2']['-1']['note']);
			$klnw_hj_2_avg_manuell = $row['noten']['klnw_hj_2']['-1']['manuell'];
			$schnitt_hj_2 = str_replace('.', ',', $row['noten']['schnitt_hj_2']['-1']['note']);
			$schnitt_hj_2_manuell = $row['noten']['schnitt_hj_2']['-1']['manuell'];
			$note_hj_2 = $row['noten']['note_hj_2']['-1']['note'];
			$note_hj_2_manuell = $row['noten']['note_hj_2']['-1']['manuell'];
			$m_hj_2 = $row['noten']['m_hj_2']['-1']['note'];
			$v_hj_2 = $row['noten']['v_hj_2']['-1']['note'];


			$this->SetFontSize(10);
			$this->Cell($header[0]['w'],5,$nr,'LR',0,'R',$fill);

			$this->Cell($header[1]['w'],5,iconv('UTF-8', 'windows-1252', $name),'LR',0,'L',$fill);
			$this->Cell($header[2]['w'],5,$altb,'L',0,'C',$fill);
			$this->SetFont('','B');
			$this->SetLineWidth(self::LW_MEDIUM);
			$this->Cell($header[3]['w'],5,$glnw_hj_1,'L',0,'C',$fill);
			$this->SetFont('','');
            $this->SetFontSize(8);
			$this->SetLineWidth(self::LW_TINY);
			$this->Cell($header[4]['w'],5,$klnw_hj_1,'L',0,'C',$fill);

			// Schnitte 1. HJ
			$this->SetLineWidth(self::LW_MEDIUM);
			$this->SetFontSize(10);
			$glnw_hj_1_avg_manuell == 1 ? $this->SetFont('', 'U') : $this->SetFont('', '');
			$this->Cell($header[5]['w'],5,$glnw_hj_1_avg,'L',0,'C',$fill);
			$this->SetLineWidth(self::LW_TINY);
			$klnw_hj_1_avg_manuell == 1 ? $this->SetFont('', 'U') : $this->SetFont('', '');
			$this->Cell($header[6]['w'],5,$klnw_hj_1_avg,'L',0,'C',$fill);
			$schnitt_hj_1_manuell == 1 ? $this->SetFont('', 'U') : $this->SetFont('', '');
			$this->Cell($header[7]['w'],5,$schnitt_hj_1,'L',0,'C',$fill);
			//$this->SetFont('','B');
			$note_hj_1_manuell == 1 ? $this->SetFont('', 'BU') : $this->SetFont('', 'B');
			$this->Cell($header[8]['w'],5,$note_hj_1,'LR',0,'C',$fill);
			$this->SetFont('');
			$this->Cell($header[9]['w'],5,$m_hj_1,'LR',0,'C',$fill);
			$this->Cell($header[10]['w'],5,$v_hj_1,'LR',0,'C',$fill);

			// 2. Halbjahr
			$this->SetLineWidth(self::LW_MEDIUM);
			$this->Cell($header[11]['w'],5,$glnw_hj_2,'L',0,'C',$fill);
			$this->SetLineWidth(self::LW_TINY);
			//$this->SetFont('');
			$this->SetFontSize(8);
            //$this->SetFontSize(6);
			$this->Cell($header[12]['w'],5, $klnw_hj_2,'L',0,'C',$fill);
			$this->SetLineWidth(self::LW_MEDIUM);
			$this->SetFontSize(10);
			// Schnitte 2. HJ
			$glnw_hj_2_avg_manuell == 1 ? $this->SetFont('', 'U') : $this->SetFont('', '');
			$this->Cell($header[13]['w'],5,$glnw_hj_2_avg,'L',0,'C',$fill);
			$this->SetLineWidth(self::LW_TINY);
			$klnw_hj_2_avg_manuell == 1 ? $this->SetFont('', 'U') : $this->SetFont('', '');
			$this->Cell($header[14]['w'],5,$klnw_hj_2_avg,'L',0,'C',$fill);
			$schnitt_hj_2_manuell == 1 ? $this->SetFont('', 'U') : $this->SetFont('', '');
			$this->Cell($header[15]['w'],5,$schnitt_hj_2,'LR',0,'C',$fill);
			$this->SetFont('','B');
			$this->SetLineWidth(self::LW_MEDIUM);
			$note_hj_2_manuell == 1 ? $this->SetFont('', 'BU') : $this->SetFont('', 'B');
			$this->Cell($header[16]['w'],5,$note_hj_2,'LR',0,'C',$fill);
			$this->SetFont('');
			$this->Cell($header[17]['w'],5,$m_hj_2,'LR',0,'C',$fill);
			$this->Cell($header[18]['w'],5,$v_hj_2,'LR',0,'C',$fill);
			$this->Ln();
			$fill = !$fill;
			$this->SetLineWidth(self::LW_TINY);
		}
		// Closing line
		// TODO $this->Cell(array_sum($wHead),0,'','T');
	}

    /**
     * Checks if string has to be trimmed
     * @param string $notenoutput
     * @param int $maxLen
     */
	protected function trimString(string $notenoutput, int $maxLen){
	    if(str_word_count($notenoutput, 0, '|') > $maxLen){
            $notenoutput = str_replace(' ', '', $notenoutput);
        }
	    return $notenoutput;
    }

    /**
     * @param array $header
     * @return void
     */
	protected function createNotenTableStruktur(array $header){
		// Colors, line width and bold font
		$this->SetFillColor(255,255,255);
		$this->SetTextColor(0,0,0);
		$this->SetDrawColor(0,0,0);
		$this->SetLineWidth(.3);
		$this->SetFont('','B');
		// Header
		//$w = array(40, 35, 40, 45);
		$wHead = array();
		// 1. HJ
		$w1 = 0;	// ZZ
		$w2 = 0;	// 2. HJ
		$w3 = 0;	// JZ
		for($i=0;$i<3;$i++){
			$wHead[0] = $wHead[0] + $header[$i]['w'];	// empty over name
		}
		for($i=3;$i<5;$i++){
			$wHead[1] = $wHead[1] + $header[$i]['w'];	// G+k 1. Halbjahr
		}
		for($i=5;$i<11;$i++){
			$wHead[2] = $wHead[2] + $header[$i]['w']; // asv ZZ
		}
		for($i=11;$i<13;$i++){
			$wHead[3] = $wHead[3] + $header[$i]['w']; // G+k 1. Halbjahr
		}
		for($i=13;$i<count($header);$i++){
			$wHead[4] = $wHead[4] + $header[$i]['w']; // asvg JZ
		}

		// HEAD
		$this->Cell($wHead[0],5,'',0,0,'C',true);
		$this->SetLineWidth(self::LW_MEDIUM);
		$this->Cell($wHead[1],5,'1. Halbjahr','LT',0,'C',true);
		$this->Cell($wHead[2],5,'ZZ','LT',0,'C',true);
		$this->Cell($wHead[3],5,'2. Halbjahr','LT',0,'C',true);
		$this->Cell($wHead[4],5,'JZ',1,0,'C',true);
		$this->Ln();
		$this->SetLineWidth(self::LW_TINY);

		// HEADER
		for($i=0;$i<3;$i++){
			$this->Cell($header[$i]['w'],5,$header[$i]['t'],1,0,'C',true);
		}
		$this->SetLineWidth(self::LW_MEDIUM);
		for($i=3;$i<count($header);$i++){
			$this->Cell($header[$i]['w'],5,iconv('UTF-8', 'windows-1252', $header[$i]['t']),1,0,'C',true);
		}
		$this->SetLineWidth(self::LW_TINY);

		// Rows
		$this->Ln();
		// Color and font restoration
		$this->SetFillColor(224,235,255);
		$this->SetTextColor(0);
		$this->SetFont('');
		// Data
	}

    /**
     * content of grades
     * @param $block
     * @param $len
     * @return string
     */
	function createNotenContent($block, $len){
		$result = '';
		for($i=0;$i<$len;$i++){
			$result = $result.$block[$i]['note'];
			if($i<$len-1 && strlen($block[$i+1]['note']) > 0){
				$result = $result.' | ';
			}
		}
		return $result;
	}
}

