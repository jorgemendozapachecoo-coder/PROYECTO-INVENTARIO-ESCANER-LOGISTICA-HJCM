<?php
require_once 'db.php';
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

// Obtener el ID del producto a editar
if (isset($_GET['id'])) {
    $productId = intval($_GET['id']);

    // Obtener datos del producto a editar
    $sql = "SELECT * FROM PRODUCTOS_NUEVOS WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $producto = $result->fetch_assoc();
    } else {
        // Si no se encuentra el producto, redirigir a la lista de productos nuevos
        header("Location: new_products_scan.php");
        exit;
    }
} else {
    header("Location: new_products_scan.php");
    exit;
}

// Actualizar el producto
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombreArea = trim($_POST['nombre_area']);
    $codigoFamiliaActual = trim($_POST['codigo_familia_actual']);
    $denominacionBien = trim($_POST['denominacion_bien']);
    $codigoFamiliaCorrecto = trim($_POST['codigo_familia_correcto']);
    $denominacion = trim($_POST['denominacion']);
    $tipo = trim($_POST['tipo']);
    $marca = trim($_POST['marca']);
    $modelo = trim($_POST['modelo']);
    $serie = trim($_POST['serie']);
    $fechaAdquisicion = trim($_POST['fecha_adquisicion']);
    $fechaActual = date('Y-m-d');

    // Calcular la antigüedad
    $antiguedad = 0;
    if (!empty($fechaAdquisicion)) {
        $fechaAdquisicionDate = new DateTime($fechaAdquisicion);
        $hoy = new DateTime();
        $antiguedad = $hoy->diff($fechaAdquisicionDate)->y;
    }

    // Actualizar producto en PRODUCTOS_NUEVOS
    $sqlUpdate = "UPDATE PRODUCTOS_NUEVOS SET NOMBRE_AREA = ?, CODIGO_FAMILIA_ACTUAL = ?, DENOMINACION_BIEN = ?, CODIGO_FAMILIA_CORRECTO = ?, DENOMINACION = ?, TIPO = ?, MARCA = ?, MODELO = ?, SERIE = ?, FECHA_ADQUISICION = ?, FECHA_ACTUAL = ?, ANTIGUEDAD = ? WHERE ID = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param(
        "ssssssssssssi",
        $nombreArea,
        $codigoFamiliaActual,
        $denominacionBien,
        $codigoFamiliaCorrecto,
        $denominacion,
        $tipo,
        $marca,
        $modelo,
        $serie,
        $fechaAdquisicion,
        $fechaActual,
        $antiguedad,
        $productId
    );

    if ($stmtUpdate->execute()) {
        header("Location: new_products_scan.php");
        exit;
    } else {
        $mensaje = "Error al actualizar el producto. Por favor, intenta de nuevo.";
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
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container-fluid {
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            font-family: 'Arial Black', Arial, sans-serif;
            text-align: center;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            background-color: #ffffff;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <h2 class="mb-4">Editar Producto</h2>

        <div class="card">
            <form method="POST" action="edit_new_product.php?id=<?php echo $productId; ?>">
                <div class="row">
                <div class="col-md-6 mb-3">
    <label for="nombre_area" class="form-label">Nombre Área</label>
    <input type="text" id="nombre_area" name="nombre_area" class="form-control" 
           value="<?php echo htmlspecialchars($producto['NOMBRE_AREA'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
</div>
<div class="col-md-6 mb-3">
    <label for="codigo_familia_actual" class="form-label">Código Familia Actual</label>
    <input type="text" id="codigo_familia_actual" name="codigo_familia_actual" class="form-control" 
           value="<?php echo htmlspecialchars($producto['CODIGO_FAMILIA_ACTUAL'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
</div>
<div class="col-md-6 mb-3">
    <label for="denominacion_bien" class="form-label">Denominación Bien</label>
    <input type="text" id="denominacion_bien" name="denominacion_bien" class="form-control" 
           value="<?php echo htmlspecialchars($producto['DENOMINACION_BIEN'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
</div>
<div class="col-md-6 mb-3">
    <label for="codigo_familia_correcto" class="form-label">Código Familia Correcto</label>
    <input type="text" id="codigo_familia_correcto" name="codigo_familia_correcto" class="form-control" 
           value="<?php echo htmlspecialchars($producto['CODIGO_FAMILIA_CORRECTO'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
</div>
<div class="col-md-6 mb-3">
    <label for="denominacion" class="form-label">Denominación</label>
    <input type="text" id="denominacion" name="denominacion" class="form-control" 
           value="<?php echo htmlspecialchars($producto['DENOMINACION'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
</div>
<div class="col-md-6 mb-3">
    <label for="tipo" class="form-label">Tipo</label>
    <input type="text" id="tipo" name="tipo" class="form-control" 
           value="<?php echo htmlspecialchars($producto['TIPO'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
</div>
<div class="col-md-6 mb-3">
    <label for="marca" class="form-label">Marca</label>
    <input type="text" id="marca" name="marca" class="form-control" 
           value="<?php echo htmlspecialchars($producto['MARCA'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
</div>
<div class="col-md-6 mb-3">
    <label for="modelo" class="form-label">Modelo</label>
    <input type="text" id="modelo" name="modelo" class="form-control" 
           value="<?php echo htmlspecialchars($producto['MODELO'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
</div>
<div class="col-md-6 mb-3">
    <label for="serie" class="form-label">Serie</label>
    <input type="text" id="serie" name="serie" class="form-control" 
           value="<?php echo htmlspecialchars($producto['SERIE'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
</div>
<div class="col-md-6 mb-3">
    <label for="fecha_adquisicion" class="form-label">Fecha de Adquisición</label>
    <input type="date" id="fecha_adquisicion" name="fecha_adquisicion" class="form-control" 
           value="<?php echo htmlspecialchars($producto['FECHA_ADQUISICION'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
</div>

                <button type="submit" class="btn btn-primary w-100">Guardar Cambios</button>
            </form>
        </div>
    </div>

    <!-- Enlace a JavaScript de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
