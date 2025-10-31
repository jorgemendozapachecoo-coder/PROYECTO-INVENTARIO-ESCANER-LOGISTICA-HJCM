<?php  
require_once 'db.php';
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

// Obtener registros PECOSA
$sql = "SELECT * FROM PECOSA";
$result = $conn->query($sql);

$baseUrl = "http://localhost/realinventario/"; // Ruta base del servidor
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Documentación PECOSA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .table th, .table td { text-align: center; vertical-align: middle; }
        iframe { width: 100%; height: 600px; border: none; }
    </style>
</head>
<body>
<div class="container mt-4">
    <h2 class="text-center mb-4">Documentación PECOSA</h2>

    <!-- Botones de Navegación -->
    <div class="d-flex justify-content-between mb-3">
        <a href="add_pecosa.php" class="btn btn-success">Añadir Nueva PECOSA</a>
        <a href="view_inventory.php" class="btn btn-secondary">Volver al Inventario</a>
    </div>

    <!-- Tabla de Documentación PECOSA -->
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Código Patrimonial</th>
                <th>Fecha de Emisión</th>
                <th>Fecha de Recepción</th>
                <th>Estrategia Dirigida</th>
                <th>Documento</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['ID']); ?></td>
                        <td><?php echo htmlspecialchars($row['CODIGO_PATRIMONIAL']); ?></td>
                        <td><?php echo htmlspecialchars($row['FECHA_EMISION']); ?></td>
                        <td><?php echo htmlspecialchars($row['FECHA_RECEPCION']); ?></td>
                        <td><?php echo htmlspecialchars($row['ESTRATEGIA_DIRIGIDA']); ?></td>
                        <td>
                            <?php
                            $fileUrl = $baseUrl . htmlspecialchars($row['DOCUMENTO_URL']);
                            $fileType = htmlspecialchars($row['DOCUMENTO_TIPO']);

                            // Mostrar botón dependiendo del tipo de archivo
                            if ($fileType === 'imagen' || $fileType === 'pdf') {
                                echo "<button class='btn btn-info btn-sm' onclick=\"showDocument('$fileUrl', '$fileType')\">Ver Documento</button>";
                            } else {
                                echo "<a href='$fileUrl' class='btn btn-warning btn-sm' download>Descargar Documento</a>";
                            }
                            ?>
                        </td>
                        <td>
                            <a href="delete_pecosa.php?id=<?php echo $row['ID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Deseas eliminar este documento?');">Eliminar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">No hay documentación PECOSA registrada.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal para Visualización de Documentos -->
<div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="documentModalLabel">Visualización de Documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <iframe id="documentFrame" src="" frameborder="0"></iframe>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showDocument(url, type) {
    const iframe = document.getElementById('documentFrame');

    // Configuración para mostrar imágenes y PDF
    if (type === 'imagen' || type === 'pdf') {
        iframe.src = url;
    } else {
        alert("Este tipo de archivo no puede visualizarse. Se descargará automáticamente.");
        window.location.href = url; // Redirigir para descargar archivo
        return;
    }

    // Mostrar el modal
    const modal = new bootstrap.Modal(document.getElementById('documentModal'));
    modal.show();
}
</script>
</body>
</html>
