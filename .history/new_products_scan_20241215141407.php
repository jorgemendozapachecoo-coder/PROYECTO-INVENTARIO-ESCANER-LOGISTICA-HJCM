<?php 
require_once 'db.php';
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

// Inicializar variables para manejar mensajes de éxito/error
$mensaje = '';
$mostrarFormulario = false;

// Añadir o buscar producto al escanear
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['codigo_patrimonial'])) {
    $codigoPatrimonial = trim($_POST['codigo_patrimonial']);

    // Verificar si el producto ya existe en PRODUCTOS_NUEVOS
    if ($resultCheckInventario->num_rows > 0) {
        // Producto existe en INVENTARIO
        if ($resultCheckInventario->num_rows > 0) {
            // Producto existe en INVENTARIO
            $productoExistente = $resultCheckInventario->fetch_assoc();
        } else {
            // Producto no existe en ninguna tabla, inicializar campos vacíos
            $productoExistente = [
                'NOMBRE_AREA' => '',
                'CODIGO_FAMILIA_ACTUAL' => '',
                'DENOMINACION_BIEN' => '',
                'CODIGO_FAMILIA_CORRECTO' => '',
                'DENOMINACION' => '',
                'TIPO' => '',
                'MARCA' => '',
                'MODELO' => '',
                'SERIE' => '',
                'FECHA_ADQUISICION' => '',
            ];
            $mostrarFormulario = true; // Mostrar el formulario con campos vacíos
        }
        
    
    $stmtCheckNuevos = $conn->prepare($sqlCheckNuevos);
    $stmtCheckNuevos->bind_param("s", $codigoPatrimonial);
    $stmtCheckNuevos->execute();
    $resultCheckNuevos = $stmtCheckNuevos->get_result();

    if ($resultCheckNuevos->num_rows > 0) {
        // Si el producto ya existe en PRODUCTOS_NUEVOS, abrir el formulario para completarlo
        $productoExistente = $resultCheckNuevos->fetch_assoc();
        $mostrarFormulario = true; // Mostrar el formulario para llenar detalles adicionales
    } else {
        // Producto no existe en PRODUCTOS_NUEVOS, verificar si existe en INVENTARIO
        $sqlCheckInventario = "SELECT * FROM INVENTARIO WHERE CODIGO_PATRIMONIAL = ?";
        $stmtCheckInventario = $conn->prepare($sqlCheckInventario);
        $stmtCheckInventario->bind_param("s", $codigoPatrimonial);
        $stmtCheckInventario->execute();
        $resultCheckInventario = $stmtCheckInventario->get_result();

        if ($resultCheckInventario->num_rows > 0) {
            // Producto existe en INVENTARIO, agregar todos los datos a PRODUCTOS_NUEVOS
            $productoExistente = $resultCheckInventario->fetch_assoc();

            $fechaActual = date('Y-m-d');

            // Calcular la antigüedad
            $antiguedad = 0;
            if (!empty($productoExistente['FECHA_ADQUISICION'])) {
                $fechaAdquisicionDate = new DateTime($productoExistente['FECHA_ADQUISICION']);
                $hoy = new DateTime();
                $antiguedad = $hoy->diff($fechaAdquisicionDate)->y;
            }

            // Insertar producto en PRODUCTOS_NUEVOS con todos los datos
            $sqlInsert = "INSERT INTO PRODUCTOS_NUEVOS (CODIGO_PATRIMONIAL, NOMBRE_AREA, CODIGO_FAMILIA_ACTUAL, DENOMINACION_BIEN, CODIGO_FAMILIA_CORRECTO, DENOMINACION, TIPO, MARCA, MODELO, SERIE, FECHA_ADQUISICION, FECHA_ACTUAL, ANTIGUEDAD) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmtInsert = $conn->prepare($sqlInsert);
            $stmtInsert->bind_param(
                "ssssssssssssi",
                $productoExistente['CODIGO_PATRIMONIAL'],
                $productoExistente['NOMBRE_AREA'],
                $productoExistente['CODIGO_FAMILIA_ACTUAL'],
                $productoExistente['DENOMINACION_BIEN'],
                $productoExistente['CODIGO_FAMILIA_CORRECTO'],
                $productoExistente['DENOMINACION'],
                $productoExistente['TIPO'],
                $productoExistente['MARCA'],
                $productoExistente['MODELO'],
                $productoExistente['SERIE'],
                $productoExistente['FECHA_ADQUISICION'],
                $fechaActual,
                $antiguedad
            );

            if ($stmtInsert->execute()) {
                $mensaje = "El producto con el código patrimonial '{$codigoPatrimonial}' se ha añadido a la lista automáticamente.";
            } else {
                $mensaje = "Error al añadir el producto existente. Por favor, intenta de nuevo.";
            }
        } else {
            // Producto no existe en INVENTARIO ni en PRODUCTOS_NUEVOS, agregarlo automáticamente a PRODUCTOS_NUEVOS
            $fechaActual = date('Y-m-d');
            $sqlInsertNuevo = "INSERT INTO PRODUCTOS_NUEVOS (CODIGO_PATRIMONIAL, FECHA_ACTUAL) VALUES (?, ?)";
            $stmtInsertNuevo = $conn->prepare($sqlInsertNuevo);
            $stmtInsertNuevo->bind_param("ss", $codigoPatrimonial, $fechaActual);
            
            if ($stmtInsertNuevo->execute()) {
                $mensaje = "El nuevo producto con el código patrimonial '{$codigoPatrimonial}' se ha añadido a la lista.";
            } else {
                $mensaje = "Error al añadir el nuevo producto. Por favor, intenta de nuevo.";
            }
        }
    }
}

// Añadir nuevo producto si se completa el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nuevo_producto'])) {
    $codigoPatrimonial = trim($_POST['codigo_patrimonial']);
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

    // Actualizar producto en PRODUCTOS_NUEVOS con los detalles adicionales
    $sqlUpdate = "UPDATE PRODUCTOS_NUEVOS SET NOMBRE_AREA = ?, CODIGO_FAMILIA_ACTUAL = ?, DENOMINACION_BIEN = ?, CODIGO_FAMILIA_CORRECTO = ?, DENOMINACION = ?, TIPO = ?, MARCA = ?, MODELO = ?, SERIE = ?, FECHA_ADQUISICION = ?, FECHA_ACTUAL = ?, ANTIGUEDAD = ? WHERE CODIGO_PATRIMONIAL = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param(
        "sssssssssssis",
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
        $codigoPatrimonial
    );

    if ($stmtUpdate->execute()) {
        $mensaje = "Producto actualizado correctamente con el código patrimonial '{$codigoPatrimonial}'.";
        // Redirigir para recargar la página y mostrar el producto actualizado
        header("Location: new_products_scan.php#inventoryTable");
        exit;
    } else {
        $mensaje = "Error al actualizar el producto. Por favor, intenta de nuevo.";
    }
}

// Eliminar producto de PRODUCTOS_NUEVOS
if (isset($_GET['delete_id'])) {
    $deleteId = intval($_GET['delete_id']);
    $sqlDelete = "DELETE FROM PRODUCTOS_NUEVOS WHERE ID = ?";
    $stmtDelete = $conn->prepare($sqlDelete);
    $stmtDelete->bind_param("i", $deleteId);
    $stmtDelete->execute();
    header("Location: new_products_scan.php");
    exit;
}

/// Transferir productos de PRODUCTOS_NUEVOS al INVENTARIO con ITEM incrementado
if (isset($_POST['transfer_products'])) {
    $sqlGetProducts = "SELECT * FROM PRODUCTOS_NUEVOS";
    $resultProducts = $conn->query($sqlGetProducts);

    if ($resultProducts && $resultProducts->num_rows > 0) {
        $transferCount = 0;

        while ($product = $resultProducts->fetch_assoc()) {
            // Obtener el último ITEM
            $sqlLastItem = "SELECT MAX(ITEM) AS last_item FROM INVENTARIO";
            $resultLastItem = $conn->query($sqlLastItem);
            $lastItemRow = $resultLastItem->fetch_assoc();
            $newItem = ($lastItemRow['last_item'] ?? 0) + 1;

            // Insertar en INVENTARIO
            $sqlInsertInventory = "INSERT INTO INVENTARIO (ITEM, CODIGO_PATRIMONIAL, NOMBRE_AREA, CODIGO_FAMILIA_ACTUAL, DENOMINACION_BIEN, CODIGO_FAMILIA_CORRECTO, DENOMINACION, TIPO, MARCA, MODELO, SERIE, FECHA_ADQUISICION, FECHA_ACTUAL, ANTIGÜEDAD)
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmtInsertInventory = $conn->prepare($sqlInsertInventory);
            $stmtInsertInventory->bind_param(
                "issssssssssssi",
                $newItem,
                $product['CODIGO_PATRIMONIAL'],
                $product['NOMBRE_AREA'],
                $product['CODIGO_FAMILIA_ACTUAL'],
                $product['DENOMINACION_BIEN'],
                $product['CODIGO_FAMILIA_CORRECTO'],
                $product['DENOMINACION'],
                $product['TIPO'],
                $product['MARCA'],
                $product['MODELO'],
                $product['SERIE'],
                $product['FECHA_ADQUISICION'],
                $product['FECHA_ACTUAL'],
                $product['ANTIGÜEDAD']
            );

            if ($stmtInsertInventory->execute()) {
                // Eliminar de PRODUCTOS_NUEVOS
                $stmtDelete = $conn->prepare("DELETE FROM PRODUCTOS_NUEVOS WHERE ID = ?");
                $stmtDelete->bind_param("i", $product['ID']);
                $stmtDelete->execute();

                $transferCount++;
            }
        }
        $mensaje = "Se trasladaron {$transferCount} productos al inventario principal.";
    } else {
        $mensaje = "No hay productos en la lista de nuevos productos para trasladar.";
    }
}


            // Insertar el producto en INVENTARIO con ITEM incrementado
            $sqlInsertInventory = "INSERT INTO INVENTARIO (ITEM, CODIGO_PATRIMONIAL, NOMBRE_AREA, CODIGO_FAMILIA_ACTUAL, DENOMINACION_BIEN, CODIGO_FAMILIA_CORRECTO, DENOMINACION, TIPO, MARCA, MODELO, SERIE, FECHA_ADQUISICION, FECHA_ACTUAL, ANTIGÜEDAD)
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmtInsertInventory = $conn->prepare($sqlInsertInventory);
            $stmtInsertInventory->bind_param(
                "issssssssssssi",
                $newItem,
                $product['CODIGO_PATRIMONIAL'],
                $product['NOMBRE_AREA'],
                $product['CODIGO_FAMILIA_ACTUAL'],
                $product['DENOMINACION_BIEN'],
                $product['CODIGO_FAMILIA_CORRECTO'],
                $product['DENOMINACION'],
                $product['TIPO'],
                $product['MARCA'],
                $product['MODELO'],
                $product['SERIE'],
                $product['FECHA_ADQUISICION'],
                $product['FECHA_ACTUAL'],
                $product['ANTIGÜEDAD']
            );

            if ($stmtInsertInventory->execute()) {
                $transferCount++;
            }
    
   // Mensaje de éxito
        $mensaje = "Se trasladaron {$transferCount} productos al inventario principal.";
    else {
        $mensaje = "No hay productos en la lista de nuevos productos para trasladar.";
    }
    {}



// Obtener datos de los productos nuevos
$sql = "SELECT * FROM PRODUCTOS_NUEVOS";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Escanear Nuevos Productos - Sistema de Gestión</title>
    <!-- Enlace a Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Roboto', sans-serif;
        }
        .container-fluid {
            padding: 40px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.2);
        }
        h2 {
            font-family: 'Roboto', sans-serif;
            font-weight: 700;
            text-align: center;
            margin-bottom: 40px;
            color: #333;
        }
        .button-container {
            background-color: #f0f0f0;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .button-container .btn {
            margin-right: 15px;
            font-weight: bold;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            background-color: #ffffff;
            margin-bottom: 30px;
        }
        .table {
            table-layout: auto;
            border-collapse: separate;
            border-spacing: 0 15px;
        }
        .table th, .table td {
            text-align: center;
            vertical-align: middle;
            padding: 15px;
            border: 1px solid #ddd;
        }
        .table th {
            background-color: #007bff;
            color: #ffffff;
            font-weight: bold;
        }
        .table td {
            background-color: #f9f9f9;
        }
        .antiguedad-roja {
            background-color: #ff0000 !important; /* Fondo rojo fuerte para antigüedad mayor a 10 años */
            color: #ffffff !important; /* Texto blanco */
            font-weight: bold;
        }
        .btn-primary, .btn-warning, .btn-danger {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <h2>Escanear Nuevos Productos</h2>
        <div class="button-container text-center mt-3">
    <a href="view_inventory.php" class="btn btn-primary btn-lg">Volver al Inventario</a>
    <form method="POST" action="new_products_scan.php" class="text-center mt-3">
    <button type="submit" name="transfer_products" class="btn btn-success btn-lg">
        Trasladar al Inventario Principal
    </button>
    </form>
    <a href="export_new_products.php" class="btn btn-info btn-lg">Exportar Nuevos Productos a Excel</a>
</div>
        <?php if ($mensaje): ?>
            <div class="alert alert-info text-center">
                <?php echo htmlspecialchars($mensaje ?? '', ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <!-- Escaneo o ingreso de código patrimonial -->
        <div class="card">
            <form method="POST" action="new_products_scan.php">
                <div class="mb-3">
                    <label for="barcodeInput" class="form-label">Escanee el Código Patrimonial o ingréselo manualmente</label>
                    <input type="text" id="barcodeInput" name="codigo_patrimonial" class="form-control form-control-lg" placeholder="Código Patrimonial" autofocus required>
                </div>
                <button type="submit" class="btn btn-success btn-lg w-100">Verificar Código Patrimonial</button>
            </form>
        </div>

        <!-- Formulario para agregar nuevo producto si se escanea por segunda vez -->
        <?php if ($mostrarFormulario): ?>
            <div class="card" id="newProductForm">
                <h3 class="text-center mb-4">Añadir Nuevo Producto</h3>
                <form method="POST" action="new_products_scan.php">
                    <input type="hidden" name="codigo_patrimonial" value="<?php echo htmlspecialchars($codigoPatrimonial ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <!-- Agregar todos los encabezados -->
                    <div class="row">
                    <div class="row">
    <!-- Nombre Área -->
    <div class="col-md-6 mb-3">
        <label for="nombre_area" class="form-label">Nombre Área</label>
        <input type="text" id="nombre_area" name="nombre_area" 
               value="<?php echo isset($productoExistente['NOMBRE_AREA']) ? htmlspecialchars($productoExistente['NOMBRE_AREA']) : ''; ?>" 
               class="form-control">
    </div>
    
    <!-- Código Familia Actual -->
    <div class="col-md-6 mb-3">
        <label for="codigo_familia_actual" class="form-label">Código Familia Actual</label>
        <input type="text" id="codigo_familia_actual" name="codigo_familia_actual" 
               value="<?php echo isset($productoExistente['CODIGO_FAMILIA_ACTUAL']) ? htmlspecialchars($productoExistente['CODIGO_FAMILIA_ACTUAL']) : ''; ?>" 
               class="form-control">
    </div>

    <!-- Denominación Bien -->
    <div class="col-md-6 mb-3">
        <label for="denominacion_bien" class="form-label">Denominación Bien</label>
        <input type="text" id="denominacion_bien" name="denominacion_bien" 
               value="<?php echo isset($productoExistente['DENOMINACION_BIEN']) ? htmlspecialchars($productoExistente['DENOMINACION_BIEN']) : ''; ?>" 
               class="form-control">
    </div>

    <!-- Código Familia Correcto -->
    <div class="col-md-6 mb-3">
        <label for="codigo_familia_correcto" class="form-label">Código Familia Correcto</label>
        <input type="text" id="codigo_familia_correcto" name="codigo_familia_correcto" 
               value="<?php echo isset($productoExistente['CODIGO_FAMILIA_CORRECTO']) ? htmlspecialchars($productoExistente['CODIGO_FAMILIA_CORRECTO']) : ''; ?>" 
               class="form-control">
    </div>

    <!-- Denominación -->
    <div class="col-md-6 mb-3">
        <label for="denominacion" class="form-label">Denominación</label>
        <input type="text" id="denominacion" name="denominacion" 
               value="<?php echo isset($productoExistente['DENOMINACION']) ? htmlspecialchars($productoExistente['DENOMINACION']) : ''; ?>" 
               class="form-control">
    </div>

    <!-- Tipo -->
    <div class="col-md-6 mb-3">
        <label for="tipo" class="form-label">Tipo</label>
        <input type="text" id="tipo" name="tipo" 
               value="<?php echo isset($productoExistente['TIPO']) ? htmlspecialchars($productoExistente['TIPO']) : ''; ?>" 
               class="form-control">
    </div>

    <!-- Marca -->
    <div class="col-md-6 mb-3">
        <label for="marca" class="form-label">Marca <span class="text-danger">*</span></label>
        <input type="text" id="marca" name="marca" 
               value="<?php echo isset($productoExistente['MARCA']) ? htmlspecialchars($productoExistente['MARCA']) : ''; ?>" 
               class="form-control" required>
    </div>

    <!-- Modelo -->
    <div class="col-md-6 mb-3">
        <label for="modelo" class="form-label">Modelo <span class="text-danger">*</span></label>
        <input type="text" id="modelo" name="modelo" 
               value="<?php echo isset($productoExistente['MODELO']) ? htmlspecialchars($productoExistente['MODELO']) : ''; ?>" 
               class="form-control" required>
    </div>

    <!-- Serie -->
    <div class="col-md-6 mb-3">
        <label for="serie" class="form-label">Serie</label>
        <input type="text" id="serie" name="serie" 
               value="<?php echo isset($productoExistente['SERIE']) ? htmlspecialchars($productoExistente['SERIE']) : ''; ?>" 
               class="form-control">
    </div>

    <!-- Fecha de Adquisición -->
    <div class="col-md-6 mb-3">
        <label for="fecha_adquisicion" class="form-label">Fecha de Adquisición</label>
        <input type="date" id="fecha_adquisicion" name="fecha_adquisicion" 
               value="<?php echo isset($productoExistente['FECHA_ADQUISICION']) ? htmlspecialchars($productoExistente['FECHA_ADQUISICION']) : ''; ?>" 
               class="form-control">
    </div>
</div>

<!-- Botón de envío -->
<button type="submit" name="nuevo_producto" class="btn btn-primary w-100">Guardar Producto</button>


                </form>
            </div>
        <?php endif; ?>

        <!-- Tabla de productos escaneados recientemente -->
        <div class="table-responsive mt-4">
            <table class="table table-bordered table-hover" id="inventoryTable">
                <thead>
                    <tr>
                        <th>ITEM</th>
                        <th>NOMBRE AREA</th>
                        <th>CODIGO FAMILIA ACTUAL</th>
                        <th>DENOMINACION BIEN</th>
                        <th>CODIGO FAMILIA CORRECTO</th>
                        <th>DENOMINACION</th>
                        <th>TIPO</th>
                        <th>MARCA</th>
                        <th>MODELO</th>
                        <th>SERIE</th>
                        <th>FECHA DE ADQUISICION</th>
                        <th>CODIGO PATRIMONIAL</th>
                        <th>FECHA ACTUAL</th>
                        <th>ANTIGÜEDAD</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Mostrar los productos nuevos desde PRODUCTOS_NUEVOS
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            // Calcular la antigüedad basada en la fecha de adquisición
                            $fechaAdquisicion = $row['FECHA_ADQUISICION'];
                            $antiguedad = 0;
                            if ($fechaAdquisicion != '0000-00-00' && !empty($fechaAdquisicion)) {
                                $fechaAdquisicionDate = new DateTime($fechaAdquisicion);
                                $hoy = new DateTime();
                                $antiguedad = $hoy->diff($fechaAdquisicionDate)->y;
                            }

                            // Determinar la clase CSS basada en la antigüedad
                            $antiguedadClass = ($antiguedad > 10) ? 'antiguedad-roja' : '';

                            echo "<tr data-codigo-patrimonial='" . htmlspecialchars($row['CODIGO_PATRIMONIAL'], ENT_QUOTES, 'UTF-8') . "'>";
                            echo "<td>" . htmlspecialchars($row['ID'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . htmlspecialchars($row['NOMBRE_AREA'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . htmlspecialchars($row['CODIGO_FAMILIA_ACTUAL'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . htmlspecialchars($row['DENOMINACION_BIEN'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . htmlspecialchars($row['CODIGO_FAMILIA_CORRECTO'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . htmlspecialchars($row['DENOMINACION'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . htmlspecialchars($row['TIPO'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . htmlspecialchars($row['MARCA'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . htmlspecialchars($row['MODELO'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . htmlspecialchars($row['SERIE'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . htmlspecialchars($fechaAdquisicion ?? '', ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . htmlspecialchars($row['CODIGO_PATRIMONIAL'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . htmlspecialchars($row['FECHA_ACTUAL'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td class='" . $antiguedadClass . "'>" . htmlspecialchars($antiguedad, ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>
                                    <a href='edit_new_product.php?id=" . htmlspecialchars($row['ID'] ?? '', ENT_QUOTES, 'UTF-8') . "' class='btn btn-warning btn-sm'>Editar</a>
                                    <a href='new_products_scan.php?delete_id=" . htmlspecialchars($row['ID'] ?? '', ENT_QUOTES, 'UTF-8') . "' class='btn btn-danger btn-sm' onclick='return confirm(\"¿Estás seguro de que deseas eliminar este producto?\");'>Eliminar</a>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='15' class='text-center'>No hay productos en la lista. Escanee o ingrese un código patrimonial para empezar.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Enlace a JavaScript de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
