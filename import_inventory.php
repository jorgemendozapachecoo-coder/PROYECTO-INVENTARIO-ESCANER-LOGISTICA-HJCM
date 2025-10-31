<?php
require_once 'db.php';
require 'vendor/autoload.php';  // Asegúrate de tener PhpSpreadsheet instalada con Composer

use PhpOffice\PhpSpreadsheet\IOFactory;

session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

// Crear archivo de registro para errores
$logFile = 'log.txt';
function logError($message) {
    global $logFile;
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $message . PHP_EOL, FILE_APPEND);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['inventory_file'])) {
    try {
        // Validar si hubo error en la carga del archivo
        if ($_FILES['inventory_file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Error al cargar el archivo.');
        }

        // Cargar el archivo Excel
        $spreadsheet = IOFactory::load($_FILES['inventory_file']['tmp_name']);
        $worksheet = $spreadsheet->getActiveSheet();

        // Leer la primera fila para obtener los encabezados
        $headerRow = $worksheet->rangeToArray('A1:N1', null, true, true, true);
        if (empty($headerRow) || !isset($headerRow[1])) {
            throw new Exception('El archivo Excel no contiene encabezados válidos.');
        }
        $headers = array_map('trim', $headerRow[1]);

        // Definir los encabezados esperados del archivo Excel
        $expectedHeaders = [
            'ITEM', 'NOMBRE_AREA', 'CODIGO_FAMILIA_ACTUAL', 'DENOMINACION_BIEN',
            'CODIGO_FAMILIA_CORRECTO', 'DENOMINACION', 'TIPO', 'MARCA', 'MODELO',
            'SERIE', 'FECHA_ADQUISICION', 'CODIGO_PATRIMONIAL', 'FECHA_ACTUAL', 'ANTIGÜEDAD'
        ];

        // Validar que los encabezados requeridos existan en el archivo
        $headerIndexes = [];
        foreach ($expectedHeaders as $expectedHeader) {
            $index = array_search($expectedHeader, $headers, true);
            if ($index === false) {
                throw new Exception("Error: Falta el encabezado requerido: " . $expectedHeader);
            }
            $headerIndexes[$expectedHeader] = $index; // Asociar el nombre del encabezado con su índice de columna
        }

        // Obtener la conexión a la base de datos
        $filasInsertadas = 0;
        $filasOmitidas = 0;

        // Iterar sobre las filas a partir de la segunda (A2:N)
        foreach ($worksheet->getRowIterator(2) as $row) {
            $rowIndex = $row->getRowIndex();
            $rowDataArray = $worksheet->rangeToArray('A' . $rowIndex . ':N' . $rowIndex, null, true, true, true);

            // Validar que la fila no esté vacía y sea un array válido
            if (isset($rowDataArray[$rowIndex]) && !empty(array_filter($rowDataArray[$rowIndex]))) {
                $rowData = $rowDataArray[$rowIndex];

                // Mapear las columnas del Excel a las variables de la base de datos usando los encabezados detectados
                $item = $rowData[$headerIndexes['ITEM']] ?? '';
                $nombre_area = $rowData[$headerIndexes['NOMBRE_AREA']] ?? '';
                $codigo_familia_actual = $rowData[$headerIndexes['CODIGO_FAMILIA_ACTUAL']] ?? '';
                $denominacion_bien = $rowData[$headerIndexes['DENOMINACION_BIEN']] ?? '';
                $codigo_familia_correcto = $rowData[$headerIndexes['CODIGO_FAMILIA_CORRECTO']] ?? '';
                $denominacion = $rowData[$headerIndexes['DENOMINACION']] ?? '';
                $tipo = $rowData[$headerIndexes['TIPO']] ?? '';
                $marca = $rowData[$headerIndexes['MARCA']] ?? '';
                $modelo = $rowData[$headerIndexes['MODELO']] ?? '';
                $serie = $rowData[$headerIndexes['SERIE']] ?? '';

                // Manejo de fechas (permitir vacío y convertir si es necesario)
                $fecha_adquisicion = null;
                if (!empty($rowData[$headerIndexes['FECHA_ADQUISICION']])) {
                    $fecha_adquisicion = DateTime::createFromFormat('d/m/Y', $rowData[$headerIndexes['FECHA_ADQUISICION']]) ?: DateTime::createFromFormat('Y-m-d', $rowData[$headerIndexes['FECHA_ADQUISICION']]);
                    $fecha_adquisicion = $fecha_adquisicion ? $fecha_adquisicion->format('Y-m-d') : null;
                }

                $codigo_patrimonial = $rowData[$headerIndexes['CODIGO_PATRIMONIAL']] ?? '';

                // Calcular `FECHA_ACTUAL` como la fecha de importación si no está presente
                $fecha_actual = !empty($rowData[$headerIndexes['FECHA_ACTUAL']]) ? DateTime::createFromFormat('d/m/Y', $rowData[$headerIndexes['FECHA_ACTUAL']]) ?: DateTime::createFromFormat('Y-m-d', $rowData[$headerIndexes['FECHA_ACTUAL']]) : new DateTime();
                $fecha_actual = $fecha_actual ? $fecha_actual->format('Y-m-d') : null;

                // Calcular `ANTIGÜEDAD` si no está presente
                $antiguedad = is_numeric($rowData[$headerIndexes['ANTIGÜEDAD']]) ? (int)$rowData[$headerIndexes['ANTIGÜEDAD']] : (!empty($fecha_adquisicion) ? date('Y') - (int)date('Y', strtotime($fecha_adquisicion)) : null);

                // Verificar que los campos obligatorios no estén vacíos
                if (empty($item) || empty($nombre_area) || empty($denominacion_bien) || empty($codigo_patrimonial)) {
                    logError("Fila $rowIndex omitida: Campos obligatorios vacíos (ITEM, NOMBRE_AREA, DENOMINACION_BIEN, CODIGO_PATRIMONIAL).");
                    $filasOmitidas++;
                    continue;
                }

                try {
                    // Insertar o actualizar en la base de datos
                    $query = "INSERT INTO INVENTARIO (ITEM, NOMBRE_AREA, CODIGO_FAMILIA_ACTUAL, DENOMINACION_BIEN, CODIGO_FAMILIA_CORRECTO, DENOMINACION, TIPO, MARCA, MODELO, SERIE, FECHA_ADQUISICION, CODIGO_PATRIMONIAL, FECHA_ACTUAL, ANTIGÜEDAD) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) 
                              ON DUPLICATE KEY UPDATE 
                              NOMBRE_AREA = VALUES(NOMBRE_AREA), 
                              CODIGO_FAMILIA_ACTUAL = VALUES(CODIGO_FAMILIA_ACTUAL), 
                              DENOMINACION_BIEN = VALUES(DENOMINACION_BIEN), 
                              CODIGO_FAMILIA_CORRECTO = VALUES(CODIGO_FAMILIA_CORRECTO), 
                              DENOMINACION = VALUES(DENOMINACION), 
                              TIPO = VALUES(TIPO), 
                              MARCA = VALUES(MARCA), 
                              MODELO = VALUES(MODELO), 
                              SERIE = VALUES(SERIE), 
                              FECHA_ADQUISICION = VALUES(FECHA_ADQUISICION), 
                              FECHA_ACTUAL = VALUES(FECHA_ACTUAL), 
                              ANTIGÜEDAD = VALUES(ANTIGÜEDAD)";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("ssssssssssssis", $item, $nombre_area, $codigo_familia_actual, $denominacion_bien, $codigo_familia_correcto, $denominacion, $tipo, $marca, $modelo, $serie, $fecha_adquisicion, $codigo_patrimonial, $fecha_actual, $antiguedad);
                    $stmt->execute();

                    $filasInsertadas++;
                    logError("Fila $rowIndex importada correctamente.");

                } catch (Exception $e) {
                    logError("Error al insertar la fila $rowIndex: " . $e->getMessage());
                }
            } else {
                $filasOmitidas++;
            }
        }

        // Mostrar mensaje final con el total de filas insertadas/actualizadas
        if ($filasInsertadas > 0) {
            echo "<div class='alert alert-success text-center'>Importación completada con éxito. Total de filas insertadas/actualizadas: $filasInsertadas.</div>";
        } else {
            echo "<div class='alert alert-warning text-center'>No se importaron datos. Total de filas omitidas: $filasOmitidas. Revisa el archivo Excel y verifica los registros en log.txt para más detalles.</div>";
        }

        // Redirigir después de 3 segundos
        echo "<script>
                setTimeout(function() {
                    window.location.href = 'view_inventory.php';
                }, 3000);
              </script>";

    } catch (Exception $e) {
        logError($e->getMessage());
        echo "<div class='alert alert-danger text-center'>Error al importar el archivo Excel: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Importar Inventario</title>
</head>
<body>
    <div class="container mt-5">
        <!-- Botón para volver al inventario principal -->
        <div class="text-start mb-3">
            <a href="view_inventory.php" class="btn btn-secondary">Volver al Inventario Principal</a>
        </div>
        <h2 class="text-center">Importar Inventario desde Excel</h2>
        <form method="POST" enctype="multipart/form-data" class="mx-auto" style="max-width: 400px;">
            <div class="mb-3">
                <label for="inventory_file" class="form-label">Archivo Excel:</label>
                <input type="file" name="inventory_file" id="inventory_file" class="form-control" accept=".xlsx, .xls" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Importar Inventario</button>
        </form>
    </div>
</body>
</html>
