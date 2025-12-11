<?php
require_once __DIR__ . '/../models/actividadesModel.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;

class ExportarActividades {
    private $model;

    public function __construct() {
        $this->model = new ActividadesModel();
    }

    public function generarResumenMensual($year, $month) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                  'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $mesNombre = strtoupper($meses[$month]);
        
        // ========== TÍTULO PRINCIPAL ==========
        $sheet->setCellValue('A1', "RESUMEN MENSUAL: $mesNombre $year");
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2B66B3']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);
        
        $currentRow = 3;
        
        // ========== SECCIÓN ACTIVIDADES ==========
        $sheet->setCellValue("A$currentRow", '📅 ACTIVIDADES PROGRAMADAS');
        $sheet->mergeCells("A$currentRow:F$currentRow");
        $sheet->getStyle("A$currentRow")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8E8E8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $currentRow++;
        
        // Encabezados de actividades
        $headers = ['Fecha', 'Horario', 'Título', 'Tipo', 'Responsable', 'Descripción'];
        foreach ($headers as $i => $header) {
            $col = chr(65 + $i); // A, B, C, D, E, F
            $sheet->setCellValue($col . $currentRow, $header);
        }
        
        $sheet->getStyle("A$currentRow:F$currentRow")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '18C5A3']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $currentRow++;
        
        // Datos de actividades
        $actividades = $this->model->getActivitiesReport($year, $month);
        $startActRow = $currentRow;
        
        if (count($actividades) > 0) {
            foreach ($actividades as $act) {
                $fecha = date('d-m-Y', strtotime($act['fecha']));
                $horario = substr($act['hora_inicio'], 0, 5) . ' - ' . substr($act['hora_fin'], 0, 5);
                
                $sheet->setCellValue("A$currentRow", $fecha);
                $sheet->setCellValue("B$currentRow", $horario);
                $sheet->setCellValue("C$currentRow", $act['titulo']);
                $sheet->setCellValue("D$currentRow", strtoupper($act['tipo']));
                $sheet->setCellValue("E$currentRow", $act['responsable']);
                $sheet->setCellValue("F$currentRow", $act['descripcion'] ?? '');
                
                $currentRow++;
            }
        }
        
        // Bordes para actividades
        if ($currentRow > $startActRow) {
            $sheet->getStyle("A$startActRow:F" . ($currentRow - 1))->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
            ]);
        }
        
        $currentRow += 2;
        
        // ========== SECCIÓN CUMPLEAÑOS ==========
        $sheet->setCellValue("A$currentRow", '🎂 CUMPLEAÑOS DEL MES');
        $sheet->mergeCells("A$currentRow:F$currentRow");
        $sheet->getStyle("A$currentRow")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8E8E8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $currentRow++;
        
        // Encabezados cumpleaños
        $sheet->setCellValue("A$currentRow", 'Día');
        $sheet->setCellValue("B$currentRow", 'Hermano/a');
        $sheet->mergeCells("B$currentRow:F$currentRow");
        
        $sheet->getStyle("A$currentRow:F$currentRow")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EC4899']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $currentRow++;
        
        // Datos cumpleaños
        $cumpleanos = $this->model->getBirthdaysReport($month);
        $startBdayRow = $currentRow;
        
        if (count($cumpleanos) > 0) {
            foreach ($cumpleanos as $c) {
                $dia = date('d', strtotime($c['fecha_nacimiento']));
                $nombre = $c['nombre'] . ' ' . $c['apellido'];
                
                $sheet->setCellValue("A$currentRow", $dia);
                $sheet->setCellValue("B$currentRow", $nombre);
                $sheet->mergeCells("B$currentRow:F$currentRow");
                
                $currentRow++;
            }
        }
        
        // Bordes para cumpleaños
        if ($currentRow > $startBdayRow) {
            $sheet->getStyle("A$startBdayRow:F" . ($currentRow - 1))->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
            ]);
        }
        
        $currentRow += 2;
        
        // ========== SECCIÓN ORACIONES ==========
        $sheet->setCellValue("A$currentRow", '🙏 PETICIONES DE ORACIÓN');
        $sheet->mergeCells("A$currentRow:F$currentRow");
        $sheet->getStyle("A$currentRow")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8E8E8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $currentRow++;
        
        // Encabezados oraciones
        $sheet->setCellValue("A$currentRow", 'Fecha');
        $sheet->setCellValue("B$currentRow", 'Solicitante');
        $sheet->setCellValue("C$currentRow", 'Petición');
        $sheet->mergeCells("C$currentRow:F$currentRow");
        
        $sheet->getStyle("A$currentRow:F$currentRow")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F59E0B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $currentRow++;
        
        // Datos oraciones
        $oraciones = $this->model->getPrayersReport($year, $month);
        $startPrayRow = $currentRow;
        
        if (count($oraciones) > 0) {
            foreach ($oraciones as $o) {
                $fecha = date('d-m-Y', strtotime($o['fecha']));
                
                $sheet->setCellValue("A$currentRow", $fecha);
                $sheet->setCellValue("B$currentRow", $o['solicitante']);
                $sheet->setCellValue("C$currentRow", $o['descripcion']);
                $sheet->mergeCells("C$currentRow:F$currentRow");
                
                $currentRow++;
            }
        }
        
        // Bordes para oraciones
        if ($currentRow > $startPrayRow) {
            $sheet->getStyle("A$startPrayRow:F" . ($currentRow - 1))->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
            ]);
        }
        
        // ========== AJUSTAR ANCHOS DE COLUMNA ==========
        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(22);
        $sheet->getColumnDimension('F')->setWidth(50);
        
        // ========== GENERAR ARCHIVO ==========
        $filename = "Resumen_IPCH_" . $meses[$month] . "_$year.xlsx";
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
?>