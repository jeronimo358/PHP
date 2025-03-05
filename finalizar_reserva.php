<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['ID_usuario'])) {
    header('Location: login.php');
    exit();
}

$usuario_id = $_SESSION['ID_usuario'];
$habitacion_id = $_POST['ID_habitacion'];
$check_in = $_POST['check_in'];
$check_out = $_POST['check_out'];
$total_reserva = $_POST['total_reserva'];

// Conectar a la base de datos
$servidor = "localhost:3307";
$usuario = "root";
$contrasena = "";
$nombre_base_datos = "hoteldb";
$conexion = new mysqli($servidor, $usuario, $contrasena, $nombre_base_datos);

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

// Insertar la reserva en la base de datos
$query_reserva = "INSERT INTO Reservas (ID_usuario, ID_habitación, Fecha_check_in, Fecha_check_out, Total_reserva, Estado_reserva) 
                  VALUES (?, ?, ?, ?, ?, 'Confirmada')";
$stmt_reserva = $conexion->prepare($query_reserva);
$stmt_reserva->bind_param('iissd', $usuario_id, $habitacion_id, $check_in, $check_out, $total_reserva);
$stmt_reserva->execute();

// Obtener el ID de la reserva recién insertada
$reserva_id = $stmt_reserva->insert_id;

// Actualizar el estado de la habitación a "Ocupada"
$query_actualizar_habitacion = "UPDATE Habitaciones SET Estado = 'Ocupada' WHERE ID_habitación = ?";
$stmt_actualizar = $conexion->prepare($query_actualizar_habitacion);
$stmt_actualizar->bind_param('i', $habitacion_id);
$stmt_actualizar->execute();

// Si hay servicios seleccionados, agregar a la tabla de servicios reservados
if (isset($_POST['servicios'])) {
    foreach ($_POST['servicios'] as $servicio_id) {
        $query_servicio_reserva = "INSERT INTO Servicios_Reservas (ID_reserva, ID_servicio) VALUES (?, ?)";
        $stmt_servicio = $conexion->prepare($query_servicio_reserva);
        $stmt_servicio->bind_param('ii', $reserva_id, $servicio_id);
        $stmt_servicio->execute();
    }
}

echo "<h2>¡Reserva Confirmada!</h2>";
echo "<p>Tu reserva ha sido confirmada con éxito. ¡Gracias por elegir nuestro hotel!</p>";

$conexion->close();
?>
