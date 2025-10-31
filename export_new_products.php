<?php
ob_start(); // Evitar salida previa
require __DIR__ . '/db.php';
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Verificar conexión
if (!$conn) {
    die("Error de conexión: " . $conn->connect_error);
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Encabezados
$headers = [
    'ITEM', 'NOMBRE AREA', 'CODIGO FAMILIA ACTUAL', 'DENOMINACION BIEN',
    'CODIGO FAMILIA CORRECTO', 'DENOMINACION', 'TIPO', 'MARCA', 'MODELO',
    'SERIE', 'FECHA DE ADQUISICION', 'CODIGO PATRIMONIAL', 'FECHA ACTUAL', 'ANTIGÜEDAD'
];
$sheet->fromArray($headers, NULL, 'A1');

// Aplicar estilo a los encabezados
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFF']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '007bff']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '000000']]],
];
$sheet->getStyle('A1:N1')->applyFromArray($headerStyle);
$sheet->getRowDimension('1')->setRowHeight(20); // Altura de fila

// Aplicar filtros a los encabezados
$sheet->setAutoFilter($sheet->calculateWorksheetDimension());

// Obtener datos de la tabla PRODUCTOS_NUEVOS
$sql = "SELECT * FROM PRODUCTOS_NUEVOS";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $rowIndex = 2;
    while ($row = $result->fetch_assoc()) {
        // Calcular antigüedad si no está definida
        $antiguedad = $row['ANTIGÜEDAD'] ?? 0;
        if (!empty($row['FECHA_ADQUISICION']) && $row['FECHA_ADQUISICION'] != '0000-00-00') {
            $fechaAdquisicionDate = new DateTime($row['FECHA_ADQUISICION']);
            $hoy = new DateTime();
            $antiguedad = $hoy->diff($fechaAdquisicionDate)->y;
        }

        // Insertar datos en la fila actual
        $sheet->fromArray([
            $row['ID'] ?? '',
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
            $antiguedad,
        ], NULL, "A{$rowIndex}");

        // Aplicar formato rojo si antigüedad > 10 años
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
} else {
    die("No hay datos en la tabla PRODUCTOS_NUEVOS.");
}

// Ajustar automáticamente el tamaño de columnas
foreach (range('A', 'N') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Aplicar bordes a toda la tabla
$lastRow = $sheet->getHighestRow();
$tableStyle = [
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '000000']],
    ],
];
$sheet->getStyle("A1:N{$lastRow}")->applyFromArray($tableStyle);

// Nombre del archivo con versión
$versionFile = __DIR__ . '/version_new_products.txt';
if (!file_exists($versionFile)) {
    file_put_contents($versionFile, '1');
}
$version = (int)file_get_contents($versionFile);
$nextVersion = $version + 1;
file_put_contents($versionFile, (string)$nextVersion);

$filename = "Nuevos_Productos_Version_{$version}.xlsx";

// Enviar el archivo para descarga
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
