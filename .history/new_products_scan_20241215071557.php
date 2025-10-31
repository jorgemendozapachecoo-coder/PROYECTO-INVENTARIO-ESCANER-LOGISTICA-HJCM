<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

// Agregar producto escaneado al inventario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigoPatrimonial = $_POST['codigo_patrimonial'] ?? '';

    if (!empty($codigoPatrimonial)) {
        // Verificar si el producto existe en la tabla INVENTARIO
        $sqlCheck = "SELECT * FROM INVENTARIO WHERE CODIGO_PATRIMONIAL = ?";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bind_param("s", $codigoPatrimonial);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();

        if ($resultCheck->num_rows > 0) {
            // Producto existente: mostrar mensaje
            $message = "El producto con Código Patrimonial '$codigoPatrimonial' ya existe en el inventario.";
            $alertType = "warning";
        } else {
            // Insertar nuevo producto con el código escaneado
            $sqlInsert = "INSERT INTO INVENTARIO (CODIGO_PATRIMONIAL, FECHA_ACTUAL) VALUES (?, CURDATE())";
            $stmtInsert = $conn->prepare($sqlInsert);
            $stmtInsert->bind_param("s", $codigoPatrimonial);
            if ($stmtInsert->execute()) {
                $message = "Producto agregado exitosamente con Código Patrimonial: '$codigoPatrimonial'.";
                $alertType = "success";
            } else {
                $message = "Error al agregar el producto. Inténtalo de nuevo.";
                $alertType = "danger";
            }
        }
    } else {
        $message = "El campo de Código Patrimonial no puede estar vacío.";
        $alertType = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escanear Nuevos Productos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            font-family: 'Arial Black', sans-serif;
            margin-bottom: 20px;
        }
        .form-control {
            text-align: center;
            font-size: 1.2rem;
        }
        .alert {
            text-align: center;
        }
        .btn-custom {
            display: block;
            margin: 0 auto;
            width: 200px;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Escanear Nuevos Productos</h2>

    <!-- Mensajes de Alerta -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $alertType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Formulario para Escanear Código Patrimonial -->
    <form method="POST" class="text-center">
        <div class="mb-3">
            <label for="codigo_patrimonial" class="form-label">Escanea o Ingresa el Código Patrimonial</label>
            <input type="text" name="codigo_patrimonial" id="codigo_patrimonial" class="form-control" autofocus placeholder="Escanea o escribe el código aquí" required>
        </div>
        <button type="submit" class="btn btn-success btn-custom">Agregar Producto</button>
        <a href="view_inventory.php" class="btn btn-secondary btn-custom mt-3">Volver al Inventario</a>
    </form>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
