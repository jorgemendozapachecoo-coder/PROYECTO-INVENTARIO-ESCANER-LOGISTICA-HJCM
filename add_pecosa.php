<?php
require_once 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener datos del formulario
    $codigoPatrimonial = trim($_POST['codigo_patrimonial']);
    $fechaEmision = $_POST['fecha_emision'];
    $fechaRecepcion = $_POST['fecha_recepcion'];
    $estrategia = $_POST['estrategia_dirigida'];
    $documentoTipo = $_POST['documento_tipo'];
    $destino = '';

    // 1. Verificar que el código patrimonial existe en la tabla INVENTARIO
    $sqlCheck = "SELECT CODIGO_PATRIMONIAL FROM INVENTARIO WHERE CODIGO_PATRIMONIAL = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param("s", $codigoPatrimonial);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows === 0) {
        die("Error: El Código Patrimonial '{$codigoPatrimonial}' no existe en la tabla INVENTARIO.");
    }

    // 2. Subir el archivo al servidor
    if (!empty($_FILES['documento']['name'])) {
        $archivoTmp = $_FILES['documento']['tmp_name'];
        $archivoNombre = basename($_FILES['documento']['name']);
        
        // Crear la carpeta uploads si no existe
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }

        // Definir el destino del archivo
        $destino = "uploads/" . $archivoNombre;

        // Mover el archivo
        if (!move_uploaded_file($archivoTmp, $destino)) {
            die("Error al subir el archivo. Asegúrate de que la carpeta 'uploads' tiene permisos de escritura.");
        }
    } else {
        die("Error: No se seleccionó ningún archivo.");
    }

    // 3. Insertar en la tabla PECOSA
    $sqlInsert = "INSERT INTO PECOSA (CODIGO_PATRIMONIAL, FECHA_EMISION, FECHA_RECEPCION, ESTRATEGIA_DIRIGIDA, DOCUMENTO_URL, DOCUMENTO_TIPO) 
                  VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sqlInsert);
    $stmt->bind_param("ssssss", $codigoPatrimonial, $fechaEmision, $fechaRecepcion, $estrategia, $destino, $documentoTipo);

    if ($stmt->execute()) {
        header("Location: list_pecosa.php");
        exit;
    } else {
        die("Error al insertar en la base de datos: " . $stmt->error);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar PECOSA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Agregar Documentación PECOSA</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="codigo_patrimonial" class="form-label">Código Patrimonial</label>
            <input type="text" name="codigo_patrimonial" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="fecha_emision" class="form-label">Fecha de Emisión</label>
            <input type="date" name="fecha_emision" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="fecha_recepcion" class="form-label">Fecha de Recepción</label>
            <input type="date" name="fecha_recepcion" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="estrategia_dirigida" class="form-label">Estrategia Dirigida</label>
            <input type="text" name="estrategia_dirigida" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="documento" class="form-label">Subir Documento</label>
            <input type="file" name="documento" class="form-control" accept=".doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg" required>
        </div>
        <div class="mb-3">
            <label for="documento_tipo" class="form-label">Tipo de Documento</label>
            <select name="documento_tipo" class="form-select" required>
                <option value="word">Word</option>
                <option value="excel">Excel</option>
                <option value="imagen">Imagen</option>
                <option value="PDF">PDF</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Agregar</button>
        <a href="list_pecosa.php" class="btn btn-secondary">Volver</a>
    </form>
</div>
</body>
</html>
