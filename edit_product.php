<?php
require_once 'db.php';
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

// Verificar si se ha proporcionado un ID de producto para editar
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Obtener la información del producto actual
    $sql = "SELECT * FROM INVENTARIO WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error en la preparación: " . $conn->error);
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $producto = $result->fetch_assoc();
    } else {
        echo "Producto no encontrado.";
        exit;
    }
} else {
    echo "ID de producto no proporcionado.";
    exit;
}

// Actualizar la información del producto
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger los valores del formulario con verificación para evitar errores de clave no definida
    $item = $_POST['item'] ?? '';
    $nombre_area = $_POST['nombre_area'] ?? '';
    $codigo_familia_actual = $_POST['codigo_familia_actual'] ?? null;
    $denominacion_bien = $_POST['denominacion_bien'] ?? null;
    $codigo_familia_correcto = $_POST['codigo_familia_correcto'] ?? null;
    $denominacion = $_POST['denominacion'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    $marca = $_POST['marca'] ?? '';
    $modelo = $_POST['modelo'] ?? '';
    $serie = $_POST['serie'] ?? '';
    $fecha_adquisicion = $_POST['fecha_adquisicion'] ?? null;
    $codigo_patrimonial = $_POST['codigo_patrimonial'] ?? '';
    $fecha_actual = $_POST['fecha_actual'] ?? date('Y-m-d');
    $antiguedad = $_POST['antiguedad'] ?? 0;

    // Actualizar el registro en la base de datos
    $sqlUpdate = "UPDATE INVENTARIO SET ITEM = ?, NOMBRE_AREA = ?, CODIGO_FAMILIA_ACTUAL = ?, DENOMINACION_BIEN = ?, CODIGO_FAMILIA_CORRECTO = ?, DENOMINACION = ?, TIPO = ?, MARCA = ?, MODELO = ?, SERIE = ?, FECHA_ADQUISICION = ?, CODIGO_PATRIMONIAL = ?, FECHA_ACTUAL = ?, ANTIGÜEDAD = ? WHERE ID = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    if ($stmtUpdate === false) {
        die("Error en la preparación: " . $conn->error);
    }

    // Convertir a `NULL` los valores opcionales que están vacíos
    $codigo_familia_actual = empty($codigo_familia_actual) ? null : $codigo_familia_actual;
    $denominacion_bien = empty($denominacion_bien) ? null : $denominacion_bien;
    $codigo_familia_correcto = empty($codigo_familia_correcto) ? null : $codigo_familia_correcto;
    $fecha_adquisicion = empty($fecha_adquisicion) ? null : $fecha_adquisicion;

    // Vincular los parámetros
    $stmtUpdate->bind_param(
        "ssssssssssssssi",
        $item,
        $nombre_area,
        $codigo_familia_actual,
        $denominacion_bien,
        $codigo_familia_correcto,
        $denominacion,
        $tipo,
        $marca,
        $modelo,
        $serie,
        $fecha_adquisicion,
        $codigo_patrimonial,
        $fecha_actual,
        $antiguedad,
        $id
    );

    if ($stmtUpdate->execute()) {
        $_SESSION['mensaje'] = "Producto actualizado correctamente.";
        header("Location: view_inventory.php");
        exit;
    } else {
        echo "Error al actualizar el producto.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Producto - Sistema de Gestión</title>
    <!-- Enlace a Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Estilo Personalizado -->
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.2);
        }
        .form-label {
            font-weight: bold;
            font-family: 'Arial Black', Arial, sans-serif;
        }
        .btn-save {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-save:hover {
            background-color: #218838;
            border-color: #218838;
        }
        .btn-cancel {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        .btn-cancel:hover {
            background-color: #5a6268;
            border-color: #5a6268;
        }
        h2 {
            font-family: 'Arial Black', Arial, sans-serif;
            text-align: center;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>Editar Producto</h2>
            <form method="POST" action="">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="item" class="form-label">ITEM</label>
                        <input type="text" class="form-control" id="item" name="item" value="<?php echo htmlspecialchars($producto['ITEM'], ENT_QUOTES); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="nombre_area" class="form-label">Nombre Área</label>
                        <input type="text" class="form-control" id="nombre_area" name="nombre_area" value="<?php echo htmlspecialchars($producto['NOMBRE_AREA'], ENT_QUOTES); ?>" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="codigo_familia_actual" class="form-label">Código Familia Actual</label>
                        <input type="text" class="form-control" id="codigo_familia_actual" name="codigo_familia_actual" value="<?php echo htmlspecialchars($producto['CODIGO_FAMILIA_ACTUAL'], ENT_QUOTES); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="denominacion_bien" class="form-label">Denominación Bien</label>
                        <input type="text" class="form-control" id="denominacion_bien" name="denominacion_bien" value="<?php echo htmlspecialchars($producto['DENOMINACION_BIEN'], ENT_QUOTES); ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="codigo_familia_correcto" class="form-label">Código Familia Correcto</label>
                        <input type="text" class="form-control" id="codigo_familia_correcto" name="codigo_familia_correcto" value="<?php echo htmlspecialchars($producto['CODIGO_FAMILIA_CORRECTO'], ENT_QUOTES); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="denominacion" class="form-label">Denominación</label>
                        <input type="text" class="form-control" id="denominacion" name="denominacion" value="<?php echo htmlspecialchars($producto['DENOMINACION'], ENT_QUOTES); ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="tipo" class="form-label">Tipo</label>
                        <input type="text" class="form-control" id="tipo" name="tipo" value="<?php echo htmlspecialchars($producto['TIPO'], ENT_QUOTES); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="marca" class="form-label">Marca</label>
                        <input type="text" class="form-control" id="marca" name="marca" value="<?php echo htmlspecialchars($producto['MARCA'], ENT_QUOTES); ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="modelo" class="form-label">Modelo</label>
                        <input type="text" class="form-control" id="modelo" name="modelo" value="<?php echo htmlspecialchars($producto['MODELO'], ENT_QUOTES); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="serie" class="form-label">Serie</label>
                        <input type="text" class="form-control" id="serie" name="serie" value="<?php echo htmlspecialchars($producto['SERIE'], ENT_QUOTES); ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="fecha_adquisicion" class="form-label">Fecha de Adquisición</label>
                        <input type="date" class="form-control" id="fecha_adquisicion" name="fecha_adquisicion" value="<?php echo htmlspecialchars($producto['FECHA_ADQUISICION'], ENT_QUOTES); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="codigo_patrimonial" class="form-label">Código Patrimonial</label>
                        <input type="text" class="form-control" id="codigo_patrimonial" name="codigo_patrimonial" value="<?php echo htmlspecialchars($producto['CODIGO_PATRIMONIAL'], ENT_QUOTES); ?>" required>
                    </div>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-save btn-lg">Guardar Cambios</button>
                    <a href="view_inventory.php" class="btn btn-cancel btn-lg">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Enlace a JavaScript de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
