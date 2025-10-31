<?php
// Incluir el archivo de conexión a la base de datos
include 'db.php';

// Procesar el formulario de registro cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_usuario = $_POST['nombre_usuario'];
    $email = $_POST['email'];
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_BCRYPT);
    $tipo_usuario = $_POST['tipo_usuario'];

    // Verificar si el email ya está registrado
    $sql = "SELECT * FROM USUARIOS WHERE EMAIL = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $mensaje = "<div class='alert alert-danger'>El correo electrónico ya está registrado.</div>";
    } else {
        // Insertar nuevo usuario
        $sql = "INSERT INTO USUARIOS (NOMBRE_USUARIO, EMAIL, CONTRASENA, TIPO_USUARIO) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $nombre_usuario, $email, $contrasena, $tipo_usuario);

        if ($stmt->execute()) {
            // Redirigir al login después de un registro exitoso sin mostrar mensajes adicionales
            header("Location: login.php");
            exit(); // Detener el script después de la redirección
        } else {
            $mensaje = "<div class='alert alert-danger'>Error al registrar el usuario.</div>";
        }
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
    <title>Registro de Usuario - Sistema de Inventario</title>
    <!-- Enlace a Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Estilo Personalizado -->
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-container {
            max-width: 500px;
            margin: 50px auto;
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
        .link-login {
            color: yellow; /* Enlace en amarillo */
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2 class="text-center text-white">Registro de Usuario</h2> <!-- Título en blanco -->
            <?php
            // Mostrar el mensaje si existe
            if (isset($mensaje)) {
                echo $mensaje;
            }
            ?>
            <form action="register.php" method="post">
                <div class="mb-3">
                    <label for="nombre_usuario" class="form-label">Nombre de Usuario</label>
                    <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="contrasena" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                </div>
                <div class="mb-3">
                    <label for="tipo_usuario" class="form-label">Tipo de Usuario</label>
                    <select id="tipo_usuario" name="tipo_usuario" class="form-select" required>
                        <option value="ADMIN">Administrador</option>
                        <option value="USUARIO">Usuario</option>
                    </select>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Registrar</button>
                </div>
            </form>
            <div class="text-center mt-3">
                <a href="login.php" class="link-login">Si ya tienes cuenta, inicia sesión aquí</a>
            </div>
        </div>
    </div>

    <!-- Enlace a JavaScript de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
