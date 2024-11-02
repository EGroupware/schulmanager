<?php

/**
 * Schulmanager - Export grades report
 *
 * @link http://www.egroupware.org
 * @package schulmanager
 * @author Axel Wild <info-AT-wild-solutions.de>
 * @copyright (c) 2022 by Axel Wild <info-AT-wild-solutions.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

require_once('fpdf/fpdf.php');

use EGroupware\Api;

/**
 * This class is the UI-layer (user interface) of InfoLog
 */
class schulmanager_export_nbericht_pdf extends schulmanager_export_pdf //FPDF
{

    /** @var string tile in filename */
    var $typeTitle_file = '';

    var $showReturnInfo = false;
    var $signed = false;
    var $signer = '';
    var $schulleitung = null;
    var $notenbild_stichtag;
    var $notenbild_zeichnungstag;
    var $schule_ort;

    /**
     * constructor
     * @param $rows
     * @param $klassenname
     * @param $mode
     */
    function __construct($rows, $klassenname, $reportConfig)
    {
        parent::__construct($rows, $klassenname);
        $this->header_repeat = true;
        $this->typeTitle_file = 'Notenbericht';

        foreach($this->rows as &$row){
            $this->prepareGrades($row['faecher']);
        }

        $this->showReturnInfo = $reportConfig['showReturnInfo'];
        $this->signed = $reportConfig['signed'];
        $this->signer = $reportConfig['signer'];
        $this->schulleitung = $reportConfig['schulleitung'];
        $this->schule_ort = $reportConfig['schule_ort'];

        if(!empty($reportConfig['notenbild_stichtag'])){
            $this->notenbild_stichtag = date('d.m.Y', $reportConfig['notenbild_stichtag']);
        }
        else{
            $this->notenbild_stichtag = date('d.m.Y');
        }

        if(!empty($reportConfig['notenbild_zeichnungstag'])){
            $this->notenbild_zeichnungstag = date('d.m.Y', $reportConfig['notenbild_zeichnungstag']);
        }
        else{
            $this->notenbild_zeichnungstag = date('d.m.Y');
        }
    }

    /**
     * Extract grades to groups and join its weight
     * @param array $faecher
     * @return void
     */
    function prepareGrades(array &$faecher){
        foreach($faecher as $key => &$fach){
            $fach['noten_out_glnw'] = array();
            $fach['noten_out_klnw'] = array();

            // glnw
            for ($hj = 1; $hj <= 2; $hj++) {
                for ($i = 0; $i <= 2; $i++) {
                    if (is_array($fach['noten']['glnw_hj_'.$hj][$i]) && !empty($fach['noten']['glnw_hj_'.$hj][$i]['note'])) {
                        $group = $this->getGradeGroup($fach['noten']['glnw_hj_'.$hj][$i]['art'], true);
                        if(!array_key_exists($group, $fach['noten_out_glnw'])){
                            $fach['noten_out_glnw'][$group] = array();
                        }
                        $fach['noten_out_glnw'][$group][] = array(
                            'note' => $fach['noten']['glnw_hj_'.$hj][$i]['note'],
                            'gew'  => $fach['gew']['glnw_'.$hj.'_'.$i]
                        );
                    }
                }
            }
            // klnw
            for ($hj = 1; $hj <= 2; $hj++) {
                for ($i = 0; $i <= 11; $i++) {
                    if (is_array($fach['noten']['klnw_hj_'.$hj][$i]) && !empty($fach['noten']['klnw_hj_'.$hj][$i]['note'])) {
                        $group = $this->getGradeGroup($fach['noten']['klnw_hj_'.$hj][$i]['art'], false);
                        if(!array_key_exists($group, $fach['noten_out_klnw'])){
                            $fach['noten_out_klnw'][$group] = array();
                        }
                        $fach['noten_out_klnw'][$group][] = array(
                            'note' => $fach['noten']['klnw_hj_'.$hj][$i]['note'],
                            'gew'  => $fach['gew']['klnw_'.$hj.'_'.$i]
                        );
                    }
                }
            }
        }

    }

    /**
     * page header
     * @return void
     */
    function Header()
    {
        if ($this->page == 1 || $this->header_repeat){
            $this->SetFont('Times','B',14);
            $this->Cell(0,6,schulmanager_ui::getSchulname(),0,0,'C');
        }
    }

    /**
     * page footer*
     * @return void
     */
    function Footer()
    {
        if('{nb}' > 1){
            // Position at 1.5 cm from bottom
            $this->SetY(-25);
            if ($this->showReturnInfo) {
                // Arial italic 8
                $this->SetFont('Times', '', 10);
                $this->Cell(40, 4, 'Kenntnis genommen:', 0, 0, 'L');
                $this->Cell(133, 4, '', 'B', 0, 'L');
                $this->Ln();
                $this->SetFont('Times', '', 6);
                $this->Cell(40, 4, '', 0, 0, 'L');
                $this->Cell(133, 4, '(Ort, Datum, Unterschrift eines Erziehungsberechtigten)', 0, 0, 'C');
            }

            $this->Ln();
            $this->Ln();

            $this->SetFont('Times', '', 6);
            $footerTxt = utf8_decode('S = Schulaufgabe, T = Test, KA = Kurzarbeit, ksL = kleiner schriftlicher Leistungsnachweis, M = Mündlich, Ø Durchschnitt, tiefer gestellte Zahlen geben die Gewichtung der Noten an')."\n"
                        .utf8_decode('Notenstufen für die Leistungen: 1 = sehr gut, 2 = gut, 3 = befriedigend, 4 = ausreichend, 5 = mangelhaft, 6 = ungenügend, x = nicht mitgeschrieben');
            $this->MultiCell(0, 2, $footerTxt, 0, 'C');
        }
    }



    /**
     * creates Notenbogen
     * @return type
     */
    function createPDFKlassenNotenbericht(){
        $this->AliasNbPages();
        $this->SetFont('Arial','',11);

        foreach($this->rows as $row)
        {
            $this->AddPage("P");
            $this->lMargin = 20;
            $this->rMargin = 24;
            $this->SetFont('Arial','B',15);

            $this->createPageContent($row);

            $this->SetFont('Arial','',11);
        }

        $date = new DateTime();
        $filename = str_replace(' ', '_', $this->title).'_'.$this->typeTitle_file.'_'.$date->format('Y-m-d').'.pdf';

        $strPDF = $this->Output('D', $filename);
        return $strPDF;
    }

    function createPageContent($row)
    {
        // Titelzeite
        $this->Ln();
        $this->SetTextColor(0);
        $this->SetFont('Times', '', 10);
        $this->Cell($this->w / 2, 5, 'Schuljahr ' . $this->schuljahr, 0, 0, 'L');
        $this->Cell(0, 5, 'Klasse ' . $this->klassenname, 0, 0, 'R');
        $this->Ln();
        $this->Ln();

        $name = iconv('UTF-8', 'windows-1252//TRANSLIT', $row['nm_st']['st_asv_vornamen']) . ' ' . iconv('UTF-8', 'windows-1252//TRANSLIT', $row['nm_st']['st_asv_familienname']);
        $this->SetFont('Arial', 'B', 10);
        if ($row['nm_st']['geschlecht'] == 'M') {
            $title = iconv('UTF-8', 'windows-1252', 'Information über das Notenbild bis zum '.$this->notenbild_stichtag.' für den Schüler');
        } else {
            $title = iconv('UTF-8', 'windows-1252', 'Information über das Notenbild bis zum '.$this->notenbild_stichtag.' für die Schülerin');
        }
        $this->Cell(0, 8, $title, 0, 0, 'C');
        $this->Ln();
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 6, $name, 0, 0, 'C');
        $this->Ln();
        // End Titelzeile
        $this->SetFont('Arial', 'B', 8);
        if ($this->showReturnInfo) {
            $this->Cell(0, 4, utf8_decode('(Rücklauf an die Schule)'), 0, 0, 'C');
        }
        // Line break
        $this->Ln();

        $this->createReportTable($row['faecher'], $row['zz_gefaehrdung_value'], $row['zz_abweisung_value']);
        $this->Ln();

    }

    /**
     * Creates table
     * @param $header
     * @param $faecher
     * @return void
     */
    function createReportTable($faecher = null, $zz_gefaehrdung_value, $zz_abweisung_value)
    {
        $lehrer_so = new schulmanager_lehrer_so();
        $w = $this->w - $this->lMargin - $this->rMargin;
        //$this->cMargin = 1;
        $tw = 173; // table width

        $x = $this->GetX();
        $y = $this->GetY();
        // group border
        $this->SetLineWidth(0.3);
        $this->Cell($tw, 8, '', 1);

        $this->SetXY($x, $y);
        $this->SetLineWidth(0.2);
        $this->SetFont('Times', 'B', 10);
        $this->Cell(50, 8, 'Fach', 1, 0, 'L');
        $this->Cell(10, 8, '', 1, 0, 'L');
        $this->Cell(70, 8, 'Leistungsnachweise', 1, 0, 'C');
        $x = $this->GetX();
        $y = $this->GetY();
        $this->MultiCell(12, 4, utf8_decode('Ø') . ' GL' . "\n" . utf8_decode('Ø') . ' KL', 1, 'C', 0);
        $this->SetXY($x + 12, $y);
        $this->Cell(31, 8, 'Gesamt ' . utf8_decode('Ø'), 1, 0, 'C');
        $this->Ln();

        foreach ($faecher as $fach) {
            // output
            //$fachname = utf8_decode($fach['sf_asv_anzeigeform']);
            $fachname = utf8_decode($fach['fachname']);
            $x = $this->GetX();
            $y = $this->GetY();
            // group border
            $this->SetLineWidth(0.3);
            $this->Cell($tw, 8, '', 1);
            $this->SetXY($x, $y);
            $this->SetLineWidth(0.2);
            $this->SetFont('Times', '', 8);
            // Fach
            $this->Cell(50, 8, '', 0, 'L', 0);
            $this->SetFont('Times', '', 8);
            $this->Text($x + 1, $y + 4, $fachname);
            $this->SetFont('Times', 'I', 6);
            $this->Text($x + 1, $y + 6.5, utf8_decode($this->formatTeacherInfo($fach['teacher'])));
            $this->SetFont('Times', '', 8);
            // GL/KL
            $this->SetXY($x + 50, $y);
            $this->Cell(10, 4, 'GL', 'LB', 'L', 'C');
            $this->SetXY($x + 50, $y + 4);
            $this->Cell(10, 4, 'KL', 'L', 'L', 'C');
            // Leistungsnachweise
            $this->SetXY($x + 60, $y);
            $this->Cell(70, 4, '', 'LB', 'L', 'L');
            $this->writeGrades($x + 61, $y + 3, $fach['noten_out_glnw']);
            $this->SetXY($x + 60, $y + 4);
            $this->Cell(70, 4, '', 'L', 'L', 'L');
            $this->writeGrades($x + 61, $y + 7, $fach['noten_out_klnw']);
            // Ø Gl KL
            $this->SetXY($x + 130, $y);
            $this->Cell(12, 4, str_replace('.', ',', $fach['noten']['glnw_hj_2'][-1]['note']), 'LB', 'L', 'C');
            $this->SetXY($x + 130, $y + 4);
            $this->Cell(12, 4, str_replace('.', ',', $fach['noten']['klnw_hj_2'][-1]['note']), 'L', 'L', 'C');
            // Gesamt
            $this->SetXY($x + 142, $y);
            $this->Cell(31, 8, str_replace('.', ',', $fach['noten']['schnitt_hj_2'][-1]['note']), 'L', 'L', 'C');
            $this->SetXY($x, $y + 8);
            //$this->SetXY($x + 50,$y);
            //$this->Ln();
        }

        // Bemerkungen
        $y += 16;
        //$this->Ln();
        //$x = 0;
        //$this->Text($x, $y, utf8_decode($zz_gefaehrdung_value));
        //$this->Write(4, utf8_decode($zz_gefaehrdung_value));
        $this->MultiCell($tw, 4, utf8_decode($zz_gefaehrdung_value), 0, 'L', 0);
        //$this->Ln();
        //$this->Text($x, $y, utf8_decode($zz_abweisung_value));
        //$this->Write(4, utf8_decode($zz_abweisung_value));
        $this->MultiCell($tw, 4, utf8_decode($zz_abweisung_value), 0, 'L', 0);
        $this->Ln();

        // signing
        $y += 16;
        //$x = 0;
        $this->Text($x, $y, $this->schule_ort.', den '.$this->notenbild_zeichnungstag);

        if ($this->signed) {
            $this->SetFont('Times', 'B', 8);
            $this->SetXY($x, $y + 6);
            $this->Cell(50, 4, $this->schulleitung['ls_asv_zeugnisname2'].':', 0, 0, 'C');
            $this->SetXY($x + 110, $y + 6);
            $txtKlassleiter = 'Klassenleiterin:';
            if ($this->signer['geschlecht'] == 'M') {
                $txtKlassleiter = 'Klassenleiter:';
            }
            $this->Cell(50, 4, $txtKlassleiter, 0, 0, 'C');

            $this->SetFont('Times', '', 8);
            $this->SetXY($x, $y + 10);
            $this->Cell(50, 4, 'gez. '.utf8_decode($this->schulleitung['ls_asv_zeugnisname1']), 0, 0, 'C');
            $this->SetXY($x + 110, $y + 10);
            $this->Cell(50, 4, 'gez. ' . utf8_decode($this->signer['ls_asv_zeugnisname1']), 0, 0, 'C');
        }
    }

    /**
     * writes out grades
     * @param $x
     * @param $y
     * @param $gradeGroups
     * @return void
     */
    function writeGrades($x, $y, $gradeGroups){
        $offset = 0;
        foreach($gradeGroups as $key => $group){
            $this->SetFont('Times','',8);
            if($offset > 0){
                $x += $offset + 1; // new group
            }
            $this->Text($x, $y, $key);
            $offset = $this->GetStringWidth($key);
            foreach($group as $grade){
                $x += $offset + 0.3;
                $this->SetFont('Times','',8);
                $this->Text($x, $y, $grade['note']);
                $offset = $this->GetStringWidth($grade['note']);
                $this->SetFont('Times','',5);
                $x += $offset;
                $this->Text($x, $y + 0.5, $grade['gew']);
                $offset = $this->GetStringWidth($grade['gew']);
            }
        }
        $this->SetFont('Times','',8);
    }

    /**
     * Formats teacher information
     * @param $teacher
     * @return void
     */
    function formatTeacherInfo($teacher){
        $result = '';
        for ($i = 0; $i < count($teacher); $i++)  {
            $result .= $teacher[$i]['ls_asv_rufname'].' '.$teacher[$i]['ls_asv_familienname'];
            if($i < count($teacher)-1){
                $result .= ', ';
            }
        }
        return $result;
    }

    /**
     * return the group of given type
     * @param $gradeType f. e. 'Schulaufgabe'
     * @param bool $glnw
     * @return string f. e. 'S'
     */
    function getGradeGroup($gradeType, bool $glnw = false){
        $result = '';
        $config = Api\Config::read('schulmanager');
        if($glnw){
            //$lnw_config = $config['lnw_glnw_json'];
            $lnw_config = "{\"S\": \"Schulaufgabe\"}";
        }
        else{
            //$lnw_config = $config['lnw_klnw_json'];
            $lnw_config = "{
              \"KA\": \"Kurzarbeit\",
              \"T\": \"Test\",
              \"ksL\": [
                \"Stegreifaufgabe\",
                \"kleiner schiftlicher LNW\"
              ],
              \"M\": [
                \"Rechenschaftsablage\",
                \"Unterrichtsbeitrag\",
                \"Referat\",
                \"kleiner mündl. LNW\"
              ]
        }";
        }
        if(isset($lnw_config)){
            $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator(json_decode($lnw_config, true)));
            //$iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($lnw_config));
            foreach($iterator as $key => $value) {
                $keyOut = $key;
                if($iterator->getDepth() > 0){
                    $keyOut = $iterator->getSubIterator(0)->key();
                }
                if($value == $gradeType){
                    $result = $keyOut.':';
                    break;
                }
            }
        }

        return $result;
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



}

