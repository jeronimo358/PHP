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

// Conectar a la base de datos
$servidor = "localhost:3307";
$usuario = "root";
$contrasena = "";
$nombre_base_datos = "hoteldb";
$conexion = new mysqli($servidor, $usuario, $contrasena, $nombre_base_datos);

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

// Validar que la fecha de salida sea posterior a la de entrada
$fecha_check_in = new DateTime($check_in);
$fecha_check_out = new DateTime($check_out);

if ($fecha_check_in >= $fecha_check_out) {
    echo "<p style='color:red;'>La fecha de salida debe ser posterior a la fecha de entrada.</p>";
    exit();
}

// Verificar disponibilidad de la habitación durante las fechas seleccionadas
$query_disponibilidad = "
    SELECT * FROM Reservas 
    WHERE ID_habitacion = ? AND 
    ((Fecha_check_in <= ? AND Fecha_check_out > ?) OR 
     (Fecha_check_in < ? AND Fecha_check_out >= ?))
";
$stmt_disponibilidad = $conexion->prepare($query_disponibilidad);
$stmt_disponibilidad->bind_param('issss', $habitacion_id, $check_in, $check_in, $check_out, $check_out);
$stmt_disponibilidad->execute();
$resultado_disponibilidad = $stmt_disponibilidad->get_result();

// Si la habitación ya está reservada
if ($resultado_disponibilidad->num_rows > 0) {
    echo "<p style='color:red;'>Lo siento, la habitación seleccionada ya está ocupada en las fechas seleccionadas.</p>";
    exit();
}

// Obtener el precio de la habitación seleccionada
$query_habitacion = "SELECT Precio_noche, Tipo FROM Habitaciones WHERE ID_habitacion = ?";
$stmt_habitacion = $conexion->prepare($query_habitacion);
$stmt_habitacion->bind_param('i', $habitacion_id);
$stmt_habitacion->execute();
$resultado_habitacion = $stmt_habitacion->get_result();
$habitacion = $resultado_habitacion->fetch_assoc();

// Verificar si la habitación existe
if ($habitacion) {
    $precio_habitacion = $habitacion['Precio_noche'];
    $tipo_habitacion = isset($habitacion['Tipo']) ? $habitacion['Tipo'] : 'Información no disponible';
} else {
    echo "<p>La habitación seleccionada no existe.</p>";
    exit();
}

// Calcular el número de noches de la estancia
$intervalo = $fecha_check_in->diff($fecha_check_out);
$numero_noches = $intervalo->days;

// Calcular el costo de la habitación para el número de noches
$total_habitacion = $precio_habitacion * $numero_noches;

// Calcular el costo de los servicios seleccionados
$total_servicios = 0;
foreach ($servicios_seleccionados as $servicio_id) {
    $query_servicio = "SELECT Nombre, Precio FROM Servicios WHERE ID_servicio = ?";
    $stmt_servicio = $conexion->prepare($query_servicio);
    $stmt_servicio->bind_param('i', $servicio_id);
    $stmt_servicio->execute();
    $resultado_servicio = $stmt_servicio->get_result();
    $servicio = $resultado_servicio->fetch_assoc();
    $total_servicios += $servicio['Precio'];
}

// Total de la reserva
$total_reserva = $total_habitacion + $total_servicios;

// Insertar la reserva en la base de datos si la habitación está disponible
$query_insertar_reserva = "
    INSERT INTO Reservas (ID_usuario, ID_habitacion, Fecha_check_in, Fecha_check_out, Total_reserva)
    VALUES (?, ?, ?, ?, ?)
";
$stmt_insertar_reserva = $conexion->prepare($query_insertar_reserva);
$stmt_insertar_reserva->bind_param('iissd', $usuario_id, $habitacion_id, $check_in, $check_out, $total_reserva);

if ($stmt_insertar_reserva->execute()) {
    // Reserva insertada con éxito
    echo "<h2>Reserva realizada con éxito</h2>";
    echo "<p><strong>Habitación seleccionada:</strong> " . $tipo_habitacion . "</p>";
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
} else {
    echo "<p style='color:red;'>Error al realizar la reserva. Por favor, inténtalo nuevamente.</p>";
}

// Cerrar la conexión
$stmt_habitacion->close();
$stmt_disponibilidad->close();
$stmt_insertar_reserva->close();
$conexion->close();
?>
