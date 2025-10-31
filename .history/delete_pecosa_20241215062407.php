<?php
require 'db.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM PECOSA WHERE ID = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: list_pecosa.php");
        exit();
    } else {
        echo "Error al eliminar.";
    }
}
?>
