<?php 
require_once 'db.php';
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

// Eliminar un producto del inventario
if (isset($_GET['delete_id'])) {
    $deleteId = intval($_GET['delete_id']);
    $sqlDelete = "DELETE FROM INVENTARIO WHERE ID = ?";
    $stmtDelete = $conn->prepare($sqlDelete);
    $stmtDelete->bind_param("i", $deleteId);
    $stmtDelete->execute();
    header("Location: view_inventory.php");
    exit;
}

// Eliminar todo el inventario
if (isset($_POST['delete_all'])) {
    $sqlDeleteAll = "DELETE FROM INVENTARIO";
    $conn->query($sqlDeleteAll);
    header("Location: view_inventory.php");
    exit;
}

// Obtener datos del inventario de la base de datos
$sql = "SELECT * FROM INVENTARIO";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inventario - Sistema de Gestión</title>
    <!-- Enlace a Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    /* Encabezado y Botón Cerrar Sesión */
    .d-flex.justify-content-between {
        background-color: #f1f1f1; /* Fondo gris claro */
        padding: 15px 20px;
        border-radius: 10px;
    }

    .btn-lg {
        font-size: 16px;
        font-weight: bold;
    }

    /* Contenedor de Botones */
    .button-container {
        background-color: #e9ecef; /* Fondo gris claro */
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.1);
    }

    .button-container .btn {
        margin: 5px;
        font-size: 14px;
        font-weight: bold;
        border-radius: 5px;
    }
</style>

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
        .table {
            width: 100%;
            table-layout: auto;
            border: 3px solid #000; /* Bordes más gruesos para la tabla */
        }
        .table th, .table td {
            text-align: center;
            vertical-align: middle;
            padding: 10px;
            font-size: 14px;
            font-family: 'Arial Black', Arial, sans-serif; /* Arial Black para encabezados y celdas */
            word-wrap: break-word;
            border: 2px solid #000; /* Bordes más gruesos para cada celda */
            white-space: normal; /* Permitir que el texto ocupe varias líneas */
        }
        .table th {
            background-color: #007bff;
            color: #ffffff;
            font-weight: bold;
        }
        .table td {
            background-color: #f9f9f9;
        }
        .highlighted {
            background-color: #00b300 !important; /* Fondo verde fuerte */
            color: #ffffff !important; /* Texto blanco */
            font-weight: bold;
        }
        .antiguedad-roja {
            background-color: #ff0000 !important; /* Fondo rojo fuerte para antigüedad mayor a 10 años */
            color: #ffffff !important; /* Texto blanco */
            font-weight: bold;
        }
        .btn-primary {
            background-color: #004085;
            border-color: #004085;
        }
        .btn-primary:hover {
            background-color: #003366;
            border-color: #003366;
        }
        h2 {
            font-family: 'Arial Black', Arial, sans-serif;
            text-align: center;
        }
        .button-container {
            background-color: #e9ecef; /* Fondo gris claro para el contenedor de botones */
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .button-container .btn {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <h2 class="mb-4">LOGISTICA-CENTRO MATERNO INFANTIL JOSE CARLOS MARIATEGUI</h2>
        <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="m-0">INVENTARIO</h2>
    <!-- Botón Cerrar Sesión -->
    <a href="logout.php" class="btn btn-danger btn-lg">Cerrar Sesión</a>
</div>

<div class="button-container d-flex flex-wrap justify-content-center gap-3">
    <a href="import_inventory.php" class="btn btn-primary">Importar Inventario desde Excel</a>
    <a href="new_products_scan.php" class="btn btn-success">Escanear Nuevos Productos</a>
    <a href="export_inventory.php" class="btn btn-info">Exportar Inventario a Excel</a>
    <a href="list_pecosa.php" class="btn btn-warning">Documentación PECOSA</a>
    <form method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de que deseas eliminar todo el inventario?');">
        <button type="submit" name="delete_all" class="btn btn-danger">Eliminar Todo el Inventario</button>
    </form>
</div>

   <!-- Buscador de Código Patrimonial -->
        <div class="mb-4">
            <input type="text" id="barcodeInput" class="form-control" placeholder="Ingrese el Código Patrimonial o escanee el código de barras" autofocus>
        </div>

        <div class="table-responsive">
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
                    // Verificar si hay datos en la tabla de inventario
                    if ($result && $result->num_rows > 0) {
                        // Iterar sobre los resultados y mostrarlos en la tabla
                        while ($row = $result->fetch_assoc()) {
                            $id = $row['ID'];

                            // Calcular la antigüedad basada en la fecha de adquisición
                            $fechaAdquisicion = $row['FECHA_ADQUISICION'];
                            $antiguedad = 0;
                            if ($fechaAdquisicion != '0000-00-00' && !empty($fechaAdquisicion)) {
                                $fechaAdquisicionDate = new DateTime($fechaAdquisicion);
                                $hoy = new DateTime();
                                $antiguedad = $hoy->diff($fechaAdquisicionDate)->y;
                            }

                            // Actualizar la fecha actual si no está establecida
                            if ($row['FECHA_ACTUAL'] === '0000-00-00' || empty($row['FECHA_ACTUAL'])) {
                                $fechaActual = date('Y-m-d');
                            } else {
                                $fechaActual = htmlspecialchars($row['FECHA_ACTUAL'], ENT_QUOTES, 'UTF-8');
                            }

                            echo "<tr data-codigo-patrimonial='" . htmlspecialchars($row['CODIGO_PATRIMONIAL'], ENT_QUOTES, 'UTF-8') . "'>";
                            echo "<td>" . htmlspecialchars($row['ITEM'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>";
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
                            echo "<td>" . $fechaActual . "</td>";

                            // Agregar clase en rojo si la antigüedad es mayor a 10 años
                            $antiguedadClass = ($antiguedad > 10) ? 'antiguedad-roja' : '';
                            echo "<td class='" . $antiguedadClass . "'>" . htmlspecialchars($antiguedad, ENT_QUOTES, 'UTF-8') . "</td>";

                            echo "<td>
                                    <a href='edit_product.php?id=" . $id . "' class='btn btn-warning btn-sm'>Editar</a>
                                    <a href='view_inventory.php?delete_id=" . $id . "' class='btn btn-danger btn-sm' onclick='clearSearch(); return confirm(\"¿Estás seguro de que deseas eliminar este producto?\");'>Eliminar</a>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        // Mostrar un mensaje si no hay productos en el inventario
                        echo "<tr><td colspan='15' class='text-center'>No hay productos en el inventario. Importa un archivo Excel para comenzar.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Enlace a JavaScript de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let debounceTimer;

        document.getElementById('barcodeInput').addEventListener('input', function (e) {
            const inputValue = this.value.trim().toUpperCase();
            if (e.inputType === 'insertText' && inputValue.length === 1) {
                // Para entrada manual, no hacer nada hasta que el usuario presione Enter.
                return;
            }

            if (inputValue !== '' && e.inputType !== 'insertText') {
                performSearch(inputValue);
            }
        });

        document.getElementById('barcodeInput').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const inputValue = this.value.trim().toUpperCase();
                if (inputValue !== '') {
                    performSearch(inputValue);
                }
            }
        });

        function performSearch(inputValue) {
            const rows = document.querySelectorAll('#inventoryTable tbody tr');

            // Eliminar la clase de resaltado anterior y estilos en línea
            rows.forEach(row => {
                row.querySelectorAll('td').forEach(td => {
                    td.classList.remove('highlighted');
                });
            });

            let found = false;
            rows.forEach(row => {
                const codigoPatrimonial = row.getAttribute('data-codigo-patrimonial').toUpperCase();
                if (codigoPatrimonial === inputValue) {
                    // Resaltar solo la fila encontrada
                    row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    row.querySelectorAll('td:not(:nth-last-child(-n+3))').forEach(td => {
                        td.classList.add('highlighted');
                    });
                    found = true;
                }
            });

            // Solo mostrar la alerta si el usuario presiona Enter y no se encuentra el valor
            if (!found && event.type === 'keypress' && event.key === 'Enter') {
                alert("Código Patrimonial no encontrado");
            }
        }

        function clearSearch() {
            document.getElementById('barcodeInput').value = '';
        }
    </script>
</body>
</html>
