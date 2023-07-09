<?php

/**
 * Schulmanager - Export class view
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
class schulmanager_export_kv_pdf extends schulmanager_export_pdf //FPDF
{
    const BLOCK_ZZ_WIDTH = 111;
    const BLOCK_ZZ_HEIGHT = 50;
    
    const BLOCK_ABSLEIST_WIDTH = 111;
    const BLOCK_ABSLEIST_HEIGHT = 15;
    
    const BLOCK_JZ_WIDTH = 168;
    const BLOCK_JZ_HEIGHT = 65;
    
    var $block_jz_x;
    var $block_jz_y;

    /**
     * mode stud for students, teacher fo teacher: stud, teacher_zz, teacher_jz
     * @var string
     */
    var $mode = 'stud';

    /** @var string title in exported view */
    var $typeTitle = '';
    /** @var string tile in filename */
    var $typeTitle_file = '';

    /**
     * constructor
     * @param $rows
     * @param $klassenname
     * @param $mode
     */
	function __construct($rows, $klassenname, $mode)
	{
		parent::__construct($rows, $klassenname);
		$this->header_repeat = true;
		$this->mode = $mode;
		$this->typeTitle = $this->isTeacherMod() ? 'Notenbogen' : iconv('UTF-8', 'windows-1252', 'Information über das Notenbild');
        $this->typeTitle_file = $this->isTeacherMod() ? 'Notenbogen' : 'Notenbild';
	}

	/**
	 * Appends "Geschichte - Sozialkund"
	 * @param type $rows
	 * @param type $klassenname
	 */
	function appendFachGSk(&$rows, $klassenname){
		if(strpos($klassenname, '10') === 0){
			$fGSk = array(
				'fachname' => 'Geschichte + Sozialkunde',
			);
			foreach($rows as &$schueler){
				$schueler['faecher'][] = $fGSk;
			}
		}
	}

    /**
     * Returns mode, for teacher or students
     * @return bool
     */
	function isTeacherMod(){
	    return ($this->mode == 'teacher_zz' || $this->mode == 'teacher_jz');
    }

    function isTeacherModJZ(){
        return $this->mode == 'teacher_jz';
    }

	/**
	 * creates Notenbogen
	 * @return type
	 */
	function createPDFKlassenNotenListe(){
		$this->AliasNbPages();
		$this->SetFont('Arial','',11);

		foreach($this->rows as $row)
		{
			$this->AddPage("L");
			$this->SetFont('Arial','B',15);

			$this->createInfoSchueler($row);

			$this->SetFont('Arial','',11);

			$header = $this->createTableHeader();
			$this->createTable($header, $row['faecher']);

			if($this->isTeacherMod()){
                // Block Zwischenzeugnis
                $this->createZZBlock($row);
                // Block Mitteilung absinkende Leistung
                $this->createAbsLeistungBlock();

                // Block Jahreszeugnis
                $this->createJZBlock($row);
            }
		}

		$date = new DateTime();
		$filename = str_replace(' ', '_', $this->title).'_'.$this->typeTitle_file.'_'.$date->format('Y-m-d').'.pdf';

		$strPDF = $this->Output('D', $filename);
		return $strPDF;
	}

	function createInfoSchueler($row){
		// Titelzeite
		$this->Ln();
		$this->SetTextColor(0);
		$this->SetFont('Arial','B',14);
		$name = iconv('UTF-8', 'windows-1252//TRANSLIT', $row['nm_st']['st_asv_familienname']).' '.iconv('UTF-8', 'windows-1252//TRANSLIT', $row['nm_st']['st_asv_rufname']);

        $this->Cell(120,8, $this->title.' '.$name,0,0,'L');
		// Title
		$this->Cell(30,8,$this->typeTitle,0,0,'C');
		$date = new DateTime();
		$this->SetFont('Arial','',10);
		$this->Cell(0,8,$date->format('d.m.Y - H:i'),0,0,'R');
		// End Titelzeile

		// Line break
		$this->Ln();
	}

    /**
     * Creates table
     * @param $header
     * @param $faecher
     * @return void
     */
	function createTable($header, $faecher = null){
		$this->createNotenTableStruktur($header);

		foreach($faecher as $fach)
		{
			$this->SetLineWidth(self::LW_TINY);
			$nr = $fach['row_id']; //$row['nm_id']
			$fachname = $fach['fachname'];
			$altb = $fach['noten']['alt_b']['-1']['checked'] ? 'x' : '';
			// 1. Halbjahr
			$glnw_hj_1 = $fach['noten']['glnw_hj_1']['concat'];
			$klnw_hj_1 = $fach['noten']['klnw_hj_1']['concat'];
            $klnw_hj_1 = is_null($klnw_hj_1) ? null : $this->trimString($klnw_hj_1, 7);

			$glnw_hj_1_avg = str_replace('.', ',', $fach['noten']['glnw_hj_1']['-1']['note']);
			$glnw_hj_1_avg_manuell = $fach['noten']['glnw_hj_1']['-1']['manuell'];

			$klnw_hj_1_avg = str_replace('.', ',', $fach['noten']['klnw_hj_1']['-1']['note']);
			$klnw_hj_1_avg_manuell = $fach['noten']['klnw_hj_1']['-1']['manuell'];

			$schnitt_hj_1 = $this->isTeacherMod() ? str_replace('.', ',', $fach['noten']['schnitt_hj_1']['-1']['note']) : '';
			$schnitt_hj_1_manuell = $this->isTeacherMod() ? $fach['noten']['schnitt_hj_1']['-1']['manuell'] : '';

			$note_hj_1 = $this->isTeacherMod() ? $fach['noten']['note_hj_1']['-1']['note'] : '';
			$note_hj_1_manuell = $this->isTeacherMod() ? $fach['noten']['note_hj_1']['-1']['manuell'] : '';

			$m_hj_1 = $this->isTeacherMod() ? $fach['noten']['m_hj_1']['-1']['note'] : '';
			$v_hj_1 = $this->isTeacherMod() ? $fach['noten']['v_hj_1']['-1']['note'] : '';
			// 2. Halbjahr
			$glnw_hj_2 = $this->isTeacherModJZ() ? $fach['noten']['glnw_hj_2']['concat'] : '';
			$klnw_hj_2 = $this->isTeacherModJZ() ? $fach['noten']['klnw_hj_2']['concat'] : '';
            $klnw_hj_2 = is_null($klnw_hj_2) ? null : $this->trimString($klnw_hj_2, 7);

			$glnw_hj_2_avg =		 $this->isTeacherModJZ() ? str_replace('.', ',', $fach['noten']['glnw_hj_2']['-1']['note']) : '';
			$glnw_hj_2_avg_manuell = $this->isTeacherModJZ() ? $fach['noten']['glnw_hj_2']['-1']['manuell'] : '';

			$klnw_hj_2_avg = $this->isTeacherModJZ() ? str_replace('.', ',', $fach['noten']['klnw_hj_2']['-1']['note']) : '';
			$klnw_hj_2_avg_manuell = $this->isTeacherModJZ() ? $fach['noten']['klnw_hj_2']['-1']['manuell'] : '';

			$schnitt_hj_2 = $this->isTeacherModJZ() ? str_replace('.', ',', $fach['noten']['schnitt_hj_2']['-1']['note']) : '';
			$schnitt_hj_2_manuell = $this->isTeacherModJZ() ? $fach['noten']['schnitt_hj_2']['-1']['manuell'] : '';

			$note_hj_2 = $this->isTeacherModJZ() ? $fach['noten']['note_hj_2']['-1']['note'] : '';
			$note_hj_2_manuell = $this->isTeacherModJZ() ? $fach['noten']['note_hj_2']['-1']['manuell'] : '';

			$m_hj_2 = $this->isTeacherModJZ() ? $fach['noten']['m_hj_2']['-1']['note'] : '';
			$v_hj_2 = $this->isTeacherModJZ() ? $fach['noten']['v_hj_2']['-1']['note'] : '';

			$this->Cell($header[0]['w'],5,$nr,'LR',0,'R',$fill);
			$this->Cell($header[1]['w'],5,iconv('UTF-8', 'windows-1252', $fachname),'LR',0,'L',$fill);
			$this->Cell($header[2]['w'],5,$altb,'L',0,'C',$fill);
			//$this->SetFont('','B');
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
     * content of grades
     * @param $block
     * @param $len
     * @return string
     */
	function createNotenContent($block, $len){
		$result = '';
		for($i=0;$i<$len;$i++){
			$result = $result.$block[$i]['note'];
			if($i<$len-1){
				$result = $result.' ';
			}
		}
		return $result;
	}

    /**
     * block for Zwischenzeugnis
     * @param array $row
     * @return void
     */
	function createZZBlock(array $row){
		$this->SetFillColor(255,255,255);
		$this->Ln();
		$x = $this->GetX();
		$y = $this->GetY();
		$this->block_jz_x = $x + self::BLOCK_ZZ_WIDTH;         // remember for block jz
		$this->block_jz_y = $y;
		$boundsX = $x;
		$boundsY = $y;
		$boundsW = self::BLOCK_ZZ_WIDTH;
		$boundsH = self::BLOCK_ZZ_HEIGHT;
		
		// Frame
		$this->Rect($boundsX, $boundsY, $boundsW, $boundsH);
		// Titel
		$this->SetFont('Arial','BU',10);
		$this->Cell(111,6,'Zwischenzeugnis',0,0,'C');
		$this->SetFont('Arial','',8);
		$this->Ln();
		// Mitarbeit / Verhalten
		$this->Cell(8,4,'','L',0,'L');
		$this->Cell(15,4,'Mitarbeit:',0,0,'L');
		$this->Cell(15,4,'','B',0,'C');
		$this->SetTextColor(120,120,120);
		$this->Cell(10,4,utf8_decode('(Ø').$row['noten']['m_hj_1']['-1']['note'].utf8_decode(')'),0,0,'C');
		$this->SetTextColor(0,0,0);
		$this->Cell(5,4,'',0,0,'C');
		$this->Cell(15,4,'Verhalten:',0,0,'L');
		$this->Cell(15,4,'','B',0,'C');
		$this->SetTextColor(120,120,120);
		$this->Cell(10,4,utf8_decode('(Ø').$row['noten']['v_hj_1']['-1']['note'].utf8_decode(')'),0,0,'C');
		$this->SetTextColor(0,0,0);
		$this->Ln();
		$this->SetXY($this->GetX(), $this->GetY() + 2);

		// Gefährdet
		$xGef = $this->GetX();
		$yGef = $this->GetY();


		$x = $this->GetX();
		$y = $this->GetY();
		$this->Rect($x + 2, $y, 3, 3);
		$this->Cell(8,4,'',0,0,'C');
		$this->Cell(17,4,utf8_decode('Vorrücken'),0,0,'L');
		$this->Cell(20,4,'','B',0,'C');
		$this->Cell(20,4,utf8_decode('gefährdet'),0,0,'L');
		$this->Line($this->GetX(), $this->GetY(), $this->GetX(),$this->GetY() + 28);
		$this->Line($this->GetX()-1, $this->GetY(), $this->GetX(),$this->GetY());
		$this->Line($this->GetX()-1, $this->GetY() + 28, $this->GetX(),$this->GetY() + 28);
		// weiteres Absinken
		$this->Ln(6);
		$x = $this->GetX();
		$y = $this->GetY();
		$this->Rect($x + 2, $y + 1, 3, 3);
		$this->Cell(8,4,'',0,0,'C');
		$content = utf8_decode('Bei weiterem Absinken der Leistungen')."\n".utf8_decode('ist das Vorrücken gefährdet (§ 40 GSO)');
		$this->MultiCell(57,3,$content,0,'L',0);
		$this->Ln(2);
		//$this->Ln();
		$x = $this->GetX();
		$y = $this->GetY();
		$this->Rect($x + 2, $y + 1, 3, 3);
		$this->Cell(8,4,'',0,0,'C');
		$content = utf8_decode('Der/Die Schüler/in dürfte nach')."\n".utf8_decode('Art. 53 (3)/Art. 55 (1) Nr. 6 BayEUG,')."\n".utf8_decode('§ 14 GSO die Jahrgangsstufe des')."\n".utf8_decode('Gymnasiums nicht wiederholen.');
		$this->MultiCell(57,3,$content,0,'L',0);
		// Fußnote
		$this->SetFont('Arial','',5);
		$this->Cell(8,2,'',0,0,'C');
		$this->Cell(55,2,'(Nichtzutreffendes streichen)',0,0,'R');
		$this->SetFont('Arial','',8);
		// ab 9 keine Bemerkung
		$this->SetXY($xGef + 65, $yGef + 8);
		$this->Rect($xGef + 67, $yGef + 8, 3, 3);
		$this->Cell(8,3,'',0,0,'C');
		$this->MultiCell(35,3,utf8_decode('ab Jgst. 9:'));
		$this->SetXY($xGef + 67, $yGef + 12);
		$this->MultiCell(43,3,utf8_decode("keine Zeugnisbemerkung,\ndafür Mitteilung versandt."),0,'L');
		// Datum Unterschrift
		$this->Ln(3);
		$this->SetXY($this->GetX(), $this->GetY() + 8);
		$this->Cell(50,4,utf8_decode('Datum:'),0,0,'L');
		$this->Cell(50,4,utf8_decode('Klassenleiter/in:'),0,0,'L');
		$this->SetXY($boundsX, $boundsY + $boundsH);
	}

    /**
     * Block Zwischenzeugnis
     * @return void
     */
	function createAbsLeistungBlock(){
		$x = $this->GetX();
		$y = $this->GetY();
		$boundsX = $x;
		$boundsY = $y;
		
		$boundsW = self::BLOCK_ABSLEIST_WIDTH;
		$boundsH = self::BLOCK_ABSLEIST_HEIGHT;

		$this->Rect($boundsX, $boundsY, $boundsW, $boundsH);
		$this->Cell(8,4,utf8_decode('Mitteilung über absinkende Leistung:'),0,0,'L');
		$this->Ln();

		$this->Cell(50,4,utf8_decode('Datum:'),0,0,'L');
		$this->Cell(50,4,utf8_decode('Klassenleiter/in:'),0,0,'L');

		$this->SetXY($boundsX, $boundsY + $boundsH);
	}

    /**
     * Block Jahreszeugnis
     * @param array $row
     * @return void
     */
	function createJZBlock(array $row){
	    $this->SetFillColor(255,255,255);
	    //$this->Ln();
	    $boundsX = $this->block_jz_x;
	    $boundsY = $this->block_jz_y;
	    $boundsW = self::BLOCK_JZ_WIDTH;
	    $boundsH = self::BLOCK_JZ_HEIGHT;
	    
	    $lMarginOld = $this->lMargin;
	    $tMarginOld = $this->tMargin;
	    $rMarginOld = $this->rMargin;
	    
	    
	    $this->SetXY($boundsX, $boundsY);
	    $this->setMargins($boundsX, $boundsY, $rMarginOld);
	    // Frame
	    $this->Rect($boundsX, $boundsY, $boundsW, $boundsH);
	    // Titel
	    $this->SetFont('Arial','BU',10);
	    $this->Cell(self::BLOCK_JZ_WIDTH,6,'Jahreszeugnis',0,0,'C');
	    $this->SetFont('Arial','',8);
	    $this->Ln();
	    // Mitarbeit / Verhalten
	    $this->Cell(8,4,'','L',0,'L');
	    $this->Cell(15,4,'Mitarbeit:',0,0,'L');
	    $this->Cell(15,4,'','B',0,'C');
	    $this->SetTextColor(120,120,120);
	    $this->Cell(10,4,utf8_decode('(Ø').$row['noten']['m_hj_2']['-1']['note'].utf8_decode(')'),0,0,'C');
	    $this->SetTextColor(0,0,0);
	    $this->Cell(5,4,'',0,0,'C');
	    $this->Cell(15,4,'Verhalten:',0,0,'L');
	    $this->Cell(15,4,'','B',0,'C');
	    $this->SetTextColor(120,120,120);
	    $this->Cell(10,4,utf8_decode('(Ø').$row['noten']['v_hj_2']['-1']['note'].utf8_decode(')'),0,0,'C');
	    $this->SetTextColor(0,0,0);
	    //$this->Cell(17,8,'','R',0,'C');
	    $this->Ln();
	    $this->SetXY($this->GetX(), $this->GetY() + 2);
	    
	    // Gefährdet
	    $xGef = $this->GetX();
	    $yGef = $this->GetY();
	    
	    // 1. checkbox
	    $x = $this->GetX();
	    $y = $this->GetY();	    
	    $this->Rect($x + 2, $y, 3, 3);
	    $this->Cell(8,4,'',0,0,'C');
	    $this->Cell(106,4,utf8_decode('Der/Die Schüler/in erhält die vorläufige Erlaubnis zum Besuch der Jahrgangsstufe'),0,0,'L');
	    $this->Cell(20,4,'','B',0,'C');
	    $this->Cell(20,4,utf8_decode('.'),0,0,'L');
	    
	    // 2. checkbox
	    $this->Ln(6);
	    $x = $this->GetX();
	    $y = $this->GetY();
	    $this->Rect($x + 2, $y + 1, 3, 3);
	    $this->Cell(8,4,'',0,0,'C');
	    $this->Cell( 98,4,utf8_decode('Die Erlaubnis zum Vorrücken in die nächsthöhere Jahrgangsstufe hat er/sie'),0,0,'L');
	    $this->Cell(20,4,'','B',0,'C');
	    $this->Cell(20,4,utf8_decode('erhalten.'),0,0,'L');
	    //$this->SetFillColor(200,200,255);
	    //$this->MultiCell(57,3,$content,0,'L',0);
	    
	    // 3. checkbox
	    $this->Ln(6);	   
	    $x = $this->GetX();
	    $y = $this->GetY();
	    $this->Rect($x + 2, $y + 1, 3, 3);
	    $this->Cell(8,4,'',0,0,'C');
	    $content = utf8_decode('Der/Die Schüler/in ist damit zum Eintritt in die Qualifikationsphase der Oberstufe des Gymnasiums berechtigt;')."\n".utf8_decode('dies schließt den Nachweis eines mittleren Schulabschlusses ein.');	    
	    $this->MultiCell(156,3,$content,0,'L',0);
	    
	    // 4. checkbox
	    $this->Ln(3);
	    $x = $this->GetX();
	    $y = $this->GetY();
	    $this->Rect($x + 2, $y + 1, 3, 3);
	    $this->Cell(8,4,'',0,0,'C');
	    //$content = utf8_decode('Der/Die Schüler/in darf nach Art. 53 (3)/Art. 55(1) Nr. 6 BayEUG, § 14 die Jahrgangsstufe')."\n".utf8_decode('des Gymnasiums nicht wiederholen.');
	    //$this->SetFillColor(200,200,255);
	    //$this->MultiCell(120,3,$content,0,'L',0);
	    $this->Cell( 122,4,utf8_decode('Der/Die Schüler/in darf nach Art. 53 (3)/Art. 55(1) Nr. 6 BayEUG, § 14 GSO die Jahrgangsstufe'),0,0,'L');
	    $this->Cell(20,4,'','B',0,'C');
	    $this->Ln(3);
	    $this->Cell(8,4,'',0,0,'C'); // empty space
	    $this->Cell(40,4,utf8_decode('des Gymnasiums nicht wiederholen.'),0,0,'L');
	    $this->SetFont('Arial','',5);
	    $this->Cell(8,2,'',0,0,'C');
	    $this->Cell(55,4,'(Nichtzutreffendes streichen)',0,0,'L');
	    $this->SetFont('Arial','',8);
	    
	    // 5. checkbox
	    $this->Ln(6);
	    $x = $this->GetX();
	    $y = $this->GetY();
	    $this->Rect($x + 2, $y + 1, 3, 3);
	    $this->Cell(8,4,'',0,0,'C');
	    $content = utf8_decode('Die mit diesem Zeugnis nachgewiesene Schulbildung schließt die Berechtigung des erfolgreichen ')."\n".utf8_decode('Abschlusses der Mittelschule ein. (§ 39 (9) GSO).');
	    $this->MultiCell(156,3,$content,0,'L',0);
	    
	    
	    // Datum, Unterschrift, Schulleiter
	    $this->Ln(5);
	    //$this->SetXY($this->GetX(), $this->GetY() + 5);
	    $this->Cell(50,4,utf8_decode('Datum:'),0,0,'L');
	    $this->Cell(50,4,utf8_decode('Klassenleiter/in:'),0,0,'L');
	    $this->Cell(50,4,utf8_decode('Schulleiter/in:'),0,0,'L');
	    
	    $this->SetXY($boundsX, $boundsY + $boundsH);
	    $this->setMargins($lMarginOld, $tMarginOld, $rMarginOld);
	}
}

