<?php
require 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $edad = isset($_POST['edad']) ? (int)$_POST['edad'] : "NULL";
    $curso = !empty($_POST['curso']) ? "'" . trim($_POST['curso']) . "'" : "NULL";
    $promociona = isset($_POST['promociona']) ? (int)$_POST['promociona'] : 0;

    if (!empty($nombre)) {
        $sql = "INSERT INTO alumnos (nombre, edad, curso, promociona) VALUES ('$nombre', $edad, $curso, $promociona)";
        
        if (mysqli_query($conexion, $sql)) {
            echo "<div class='alert alert-success' role='alert'>Alumno insertado correctamente.</div>";
        } else {
            echo "<div class='alert alert-danger' role='alert'>Error al insertar el alumno: " . mysqli_error($conexion) . "</div>";
        }
    } else {
        echo "<div class='alert alert-warning' role='alert'>El campo nombre es obligatorio.</div>";
    }
    
    mysqli_close($conexion);
}
?>

<a href="opciones.php" class="btn btn-primary">Volver</a>