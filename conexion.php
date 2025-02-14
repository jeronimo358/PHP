<?php
// Variables de conexión
$servidor = "localhost:3307"; // Nombre del servidor (por defecto localhost)
$usuario = "root";       // Usuario (por defecto root en XAMPP)
$contrasena = "";        // Contraseña (por defecto vacío en XAMPP)
$nombre_base_datos = "hoteldb"; // Nombre de la base de datos


// Establecer la conexión
$conexion = mysqli_connect($servidor, $usuario, $contrasena, $nombre_base_datos, );

// Verificar si la conexión fue exitosa
if (!$conexion) {
    die("Error de conexión al servidor MySQL: " . mysqli_connect_error());
}

// Verificar si la base de datos se ha seleccionado correctamente
if (!mysqli_select_db($conexion, $nombre_base_datos)) {
    die("Error al seleccionar la base de datos: " . mysqli_error($conexion));
}

// Si llegamos aquí, la conexión y la selección de base de datos fueron exitosas
echo "Conexión exitosa a la base de datos '$nombre_base_datos' en el servidor '$servidor'.";

// Cerrar la conexión cuando ya no sea necesaria
// mysqli_close($conexion);
?>