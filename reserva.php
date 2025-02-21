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
$servicios_seleccionados = isset($_POST['servicios']) ? $_POST['servicios'] : [];

if ($habitacion_id) {
    // Conectar a la base de datos
    $servidor = "localhost:3307";
    $usuario = "root";
    $contrasena = "";
    $nombre_base_datos = "hoteldb";
    $conexion = new mysqli($servidor, $usuario, $contrasena, $nombre_base_datos);

    if ($conexion->connect_error) {
        die("Conexión fallida: " . $conexion->connect_error);
    }

    // Obtener el precio de la habitación seleccionada
    $query_habitacion = "SELECT Precio_noche FROM Habitaciones WHERE ID_habitación = ?";
    $stmt_habitacion = $conexion->prepare($query_habitacion);
    $stmt_habitacion->bind_param('i', $habitacion_id);
    $stmt_habitacion->execute();
    $resultado_habitacion = $stmt_habitacion->get_result();
    $habitacion = $resultado_habitacion->fetch_assoc();
    $precio_habitacion = $habitacion['Precio_noche'];

    // Calcular el número de noches de la estancia
    $fecha_check_in = new DateTime($check_in);
    $fecha_check_out = new DateTime($check_out);
    $intervalo = $fecha_check_in->diff($fecha_check_out);
    $numero_noches = $intervalo->days;

    // Calcular el costo de la habitación para el número de noches
    $total_habitacion = $precio_habitacion * $numero_noches;

    // Calcular el costo de los servicios seleccionados
    $total_servicios = 0;
    foreach ($servicios_seleccionados as $servicio_id) {
        $query_servicio = "SELECT Precio FROM Servicios WHERE ID_servicio = ?";
        $stmt_servicio = $conexion->prepare($query_servicio);
        $stmt_servicio->bind_param('i', $servicio_id);
        $stmt_servicio->execute();
        $resultado_servicio = $stmt_servicio->get_result();
        $servicio = $resultado_servicio->fetch_assoc();
        $total_servicios += $servicio['Precio'];
    }

    // Total de la reserva
    $total_reserva = $total_habitacion + $total_servicios;

    // Mostrar el resumen de la reserva
    echo "<h2>Resumen de tu reserva</h2>";
    echo "<p><strong>Habitación seleccionada:</strong> " . $habitacion['Tipo'] . "</p>";
    echo "<p><strong>Precio por noche:</strong> $" . $precio_habitacion . "</p>";
    echo "<p><strong>Número de noches:</strong> " . $numero_noches . "</p>";
    echo "<p><strong>Total habitación:</strong> $" . $total_habitacion . "</p>";

    echo "<h3>Servicios seleccionados:</h3>";
    foreach ($servicios_seleccionados as $servicio_id) {
        $query_servicio = "SELECT Nombre, Precio FROM Servicios WHERE ID_servicio = ?";
        $stmt_servicio = $conexion->prepare($query_servicio);
        $stmt_servicio->bind_param('i', $servicio_id);
        $stmt_servicio->execute();
        $resultado_servicio = $stmt_servicio->get_result();
        $servicio = $resultado_servicio->fetch_assoc();
        echo "<p>" . $servicio['Nombre'] . " - $" . $servicio['Precio'] . "</p>";
    }

    echo "<h3>Total de la reserva: $" . $total_reserva . "</h3>";

    // Confirmar reserva
    echo "<form action='finalizar_reserva.php' method='post'>
            <input type='hidden' name='ID_habitacion' value='" . $habitacion_id . "'>
            <input type='hidden' name='check_in' value='" . $check_in . "'>
            <input type='hidden' name='check_out' value='" . $check_out . "'>
            <input type='hidden' name='total_reserva' value='" . $total_reserva . "'>
            <input type='submit' value='Confirmar reserva'>
          </form>";

    // Cerrar la conexión
    $stmt_habitacion->close();
    $conexion->close();
} else {
    // Si no se seleccionó una habitación
    echo "Debe seleccionar una habitación.";
}
?>
