<?php
// Incluir el archivo de conexión a la base de datos
include 'db.php';
session_start();

// Procesar el formulario de inicio de sesión cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_usuario = $_POST['input_usuario'];
    $contrasena = $_POST['contrasena'];

    // Verificar si el usuario existe, usando correo electrónico o nombre de usuario
    $sql = "SELECT * FROM USUARIOS WHERE EMAIL = ? OR NOMBRE_USUARIO = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $input_usuario, $input_usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $usuario = $result->fetch_assoc();

        // Verificar la contraseña
        if (password_verify($contrasena, $usuario['CONTRASENA'])) {
            $_SESSION['id_usuario'] = $usuario['ID_USUARIO'];
            $_SESSION['nombre_usuario'] = $usuario['NOMBRE_USUARIO'];
            $_SESSION['tipo_usuario'] = $usuario['TIPO_USUARIO'];

            // Redirigir al inventario después del inicio de sesión exitoso
            header("Location: view_inventory.php");
            exit();
        } else {
            $mensaje = "<div class='alert alert-danger'>Contraseña incorrecta.</div>";
        }
    } else {
        $mensaje = "<div class='alert alert-danger'>Usuario no encontrado.</div>";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inicio de Sesión - Sistema de Inventario</title>
    <!-- Enlace a Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Estilo Personalizado -->
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-container {
            max-width: 500px;
            margin: 100px auto;
            padding: 20px;
            background: #007bff; /* Fondo azul */
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .form-label {
            color: white; /* Texto blanco */
            font-weight: bold; /* Negrita */
            font-family: 'Arial Black', Arial, sans-serif; /* Arial Black */
        }
        .text-center button {
            background-color: #004085; /* Fondo del botón */
            border-color: #004085; /* Borde del botón */
        }
        .text-center button:hover {
            background-color: #003366; /* Color del botón al pasar el ratón por encima */
            border-color: #003366; /* Borde del botón al pasar el ratón por encima */
        }
        .link-register {
            color: yellow; /* Enlace en amarillo */
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2 class="text-center text-white">Inicio de Sesión</h2> <!-- Título en blanco -->
            <?php
            if (isset($mensaje)) {
                echo $mensaje;
            }
            ?>
            <form action="login.php" method="post">
                <div class="mb-3">
                    <label for="input_usuario" class="form-label">Correo Electrónico o Nombre de Usuario</label>
                    <input type="text" class="form-control" id="input_usuario" name="input_usuario" required>
                </div>
                <div class="mb-3">
                    <label for="contrasena" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                </div>
            </form>
            <div class="text-center mt-3">
                <a href="register.php" class="link-register">Si no tienes cuenta, regístrate aquí</a>
            </div>
        </div>
    </div>

    <!-- Enlace a JavaScript de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
