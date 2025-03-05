<?php
session_start();

// Verificar si el usuario esta autenticado (si tiene sesion activa)
if (!isset($_SESSION['ID_usuario'])) {
    header('Location: login.php'); // Redirigir al login si no esta autenticado
    exit();
}

// Obtener el ID del usuario desde la sesion
$usuario_id = $_SESSION['ID_usuario'];

echo "<h2>Bienvenido, " . $_SESSION['Nombre'] . "!</h2>";

// Conectar a la base de datos
$servidor = "localhost:3307"; // Cambiar si es necesario
$usuario = "root"; // Cambiar si es necesario
$contrasena = ""; // Cambiar si es necesario
$nombre_base_datos = "hoteldb"; // Nombre de la base de datos
$conexion = new mysqli($servidor, $usuario, $contrasena, $nombre_base_datos);

// Verificar si la conexion fue exitosa
if ($conexion->connect_error) {
    die("Conexion fallida: " . $conexion->connect_error);
}

// Obtener las habitaciones disponibles
$query_habitaciones = "SELECT * FROM Habitaciones WHERE Estado = 'Disponible'";
$result_habitaciones = $conexion->query($query_habitaciones);

echo "<h3>Selecciona una habitacion:</h3>";
echo "<form action='reserva.php' method='post'>";
echo "<select name='ID_habitacion' required>";
echo "<option value='' disabled selected>Seleccione una habitacion</option>";

if ($result_habitaciones->num_rows > 0) {
    while ($habitacion = $result_habitaciones->fetch_assoc()) {
        echo "<option value='" . $habitacion['ID_habitacion'] . "'>" . 
             $habitacion['Tipo'] . " - $" . $habitacion['Precio_noche'] . " por noche</option>";
    }
}

echo "</select><br><br>";

// Fechas de entrada y salida
echo "<label for='check_in'>Fecha de entrada:</label><br>";
echo "<input type='date' id='check_in' name='check_in' required><br><br>";

echo "<label for='check_out'>Fecha de salida:</label><br>";
echo "<input type='date' id='check_out' name='check_out' required><br><br>";

// Obtener los servicios disponibles
$query_servicios = "SELECT * FROM Servicios";
$result_servicios = $conexion->query($query_servicios);

echo "<h3>Selecciona los servicios adicionales:</h3>";
while ($servicio = $result_servicios->fetch_assoc()) {
    echo "<label for='servicio_" . $servicio['ID_servicio'] . "'>" . $servicio['Nombre'] . " - $" . $servicio['Precio'] . "</label><br>";
    echo "<input type='checkbox' name='servicios[]' value='" . $servicio['ID_servicio'] . "' id='servicio_" . $servicio['ID_servicio'] . "'> <br>";
}

echo "<br><input type='submit' value='Calcular total y confirmar reserva'></form>";

$conexion->close();
?>
