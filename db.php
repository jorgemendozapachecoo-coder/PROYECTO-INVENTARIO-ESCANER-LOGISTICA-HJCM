<?php
// Mostrar errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Definiciones de conexión a la base de datos
$servername = "localhost";
$username = "root"; // Cambia si usas otro usuario de MySQL
$password = ""; // Cambia si tu usuario tiene contraseña
$dbname = "realinventario";

// Crear la conexión a la base de datos usando MySQLi
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Opcional: configurar el conjunto de caracteres para evitar problemas con acentos y caracteres especiales
$conn->set_charset("utf8");
?>
