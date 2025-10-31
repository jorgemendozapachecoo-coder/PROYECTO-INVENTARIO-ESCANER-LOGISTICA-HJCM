<?php
require __DIR__ . '/db.php';
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Leer y actualizar la versión desde el archivo version.txt
$versionFile = __DIR__ . '/version.txt';
if (!file_exists($versionFile)) {
    file_put_contents($versionFile, '1'); // Crea el archivo y lo inicializa con la versión 1
}
$version = (int)file_get_contents($versionFile);
$nextVersion = $version + 1;
file_put_contents($versionFile, (string)$nextVersion); // Incrementa y guarda la nueva versión

$filename = "Inventario_actualizado_version_{$version}.xlsx";

// Crear el archivo de Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Encabezados de la tabla
$headers = [
    'ITEM', 'NOMBRE AREA', 'CODIGO FAMILIA ACTUAL', 'DENOMINACION BIEN',
    'CODIGO FAMILIA CORRECTO', 'DENOMINACION', 'TIPO', 'MARCA', 'MODELO',
    'SERIE', 'FECHA DE ADQUISICION', 'CODIGO PATRIMONIAL', 'FECHA ACTUAL', 'ANTIGÜEDAD'
];
$sheet->fromArray($headers, NULL, 'A1');

// Aplicar estilos a los encabezados
$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['argb' => 'FFFFFF'],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['argb' => '007bff'], // Azul para encabezados
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['argb' => '000000'],
        ],
    ],
];
$sheet->getStyle('A1:N1')->applyFromArray($headerStyle);
$sheet->getRowDimension('1')->setRowHeight(20); // Altura de fila

// Agregar filtros a los encabezados
$sheet->setAutoFilter($sheet->calculateWorksheetDimension());

// Obtener datos del inventario
$sql = "SELECT * FROM INVENTARIO";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $rowIndex = 2; // Comienza después de los encabezados
    while ($row = $result->fetch_assoc()) {
        $antiguedad = 0;
        if (!empty($row['FECHA_ADQUISICION']) && $row['FECHA_ADQUISICION'] != '0000-00-00') {
            $fechaAdquisicionDate = new DateTime($row['FECHA_ADQUISICION']);
            $hoy = new DateTime();
            $antiguedad = $hoy->diff($fechaAdquisicionDate)->y;
        }

        // Agregar datos del producto
        $sheet->fromArray([
            $row['ITEM'] ?? '',
            $row['NOMBRE_AREA'] ?? '',
            $row['CODIGO_FAMILIA_ACTUAL'] ?? '',
            $row['DENOMINACION_BIEN'] ?? '',
            $row['CODIGO_FAMILIA_CORRECTO'] ?? '',
            $row['DENOMINACION'] ?? '',
            $row['TIPO'] ?? '',
            $row['MARCA'] ?? '',
            $row['MODELO'] ?? '',
            $row['SERIE'] ?? '',
            $row['FECHA_ADQUISICION'] ?? '',
            $row['CODIGO_PATRIMONIAL'] ?? '',
            $row['FECHA_ACTUAL'] ?? '',
            $antiguedad
        ], NULL, "A{$rowIndex}");

        // Aplicar color rojo a antigüedad mayor a 10 años
        if ($antiguedad > 10) {
            $sheet->getStyle("N{$rowIndex}")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF0000'], // Rojo
                ],
                'font' => [
                    'color' => ['argb' => 'FFFFFF'], // Blanco
                ],
            ]);
        }
        $rowIndex++;
    }
}

// Ajustar automáticamente el ancho de las columnas
foreach (range('A', $sheet->getHighestColumn()) as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Aplicar bordes a toda la tabla
$lastRow = $sheet->getHighestRow();
$tableStyle = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['argb' => '000000'],
        ],
    ],
];
$sheet->getStyle("A1:N{$lastRow}")->applyFromArray($tableStyle);

// Generar el archivo Excel
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
