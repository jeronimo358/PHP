<?php
session_start();

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['ID_usuario']) || $_SESSION['Admin'] != 'Sí') {
    header('Location: login.php');
    exit();
}

$usuario_id = $_SESSION['ID_usuario'];

echo "<h2>Bienvenido, " . $_SESSION['Nombre'] . "!</h2>";
echo "<h3>Panel de administración</h3>";

$servidor = "localhost:3307";
$usuario = "root";
$contrasena = "";
$nombre_base_datos = "hoteldb";
$conexion = new mysqli($servidor, $usuario, $contrasena, $nombre_base_datos);

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

$usuario_modificar = isset($_GET['ID_usuario']) ? $_GET['ID_usuario'] : null;
$modificar_usuario = false;

// Si se pasa un ID de usuario en la URL, se carga el usuario para modificar
if ($usuario_modificar) {
    $modificar_usuario = true;
    // Obtener los datos del usuario
    $query_usuario = "SELECT * FROM Usuarios WHERE ID_usuario = ?";
    $stmt = $conexion->prepare($query_usuario);
    $stmt->bind_param('i', $usuario_modificar);
    $stmt->execute();
    $resultado_usuario = $stmt->get_result();
    $usuario_actual = $resultado_usuario->fetch_assoc();
}

// Procesar el formulario cuando se envíe
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $contraseña = password_hash($_POST['contraseña'], PASSWORD_DEFAULT); // Contraseña cifrada
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];
    $admin = $_POST['admin'] ?? 'No'; // Por defecto, no es administrador
    $habitacion_id = $_POST['ID_habitacion'];
    $servicios = isset($_POST['servicios']) ? $_POST['servicios'] : [];

    // Si es un nuevo usuario
    if (!$modificar_usuario) {
        // Insertar nuevo cliente
        $query_cliente = "INSERT INTO Usuarios (Nombre, Email, Contraseña, Direccion, Telefono, Admin) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_cliente = $conexion->prepare($query_cliente);
        $stmt_cliente->bind_param('ssssss', $nombre, $email, $contraseña, $direccion, $telefono, $admin);
        $stmt_cliente->execute();

        // Obtener el ID del nuevo cliente
        $cliente_id = $stmt_cliente->insert_id;
    } else {
        // Actualizar usuario existente
        $query_cliente = "UPDATE Usuarios SET Nombre = ?, Email = ?, Contraseña = ?, Direccion = ?, Telefono = ?, Admin = ? WHERE ID_usuario = ?";
        $stmt_cliente = $conexion->prepare($query_cliente);
        $stmt_cliente->bind_param('ssssssi', $nombre, $email, $contraseña, $direccion, $telefono, $admin, $usuario_modificar);
        $stmt_cliente->execute();
        $cliente_id = $usuario_modificar;
    }

    // Realizar la reserva
    $fecha_reserva = date("Y-m-d"); // Fecha de la reserva (hoy)
    $query_reserva = "INSERT INTO Reservas (ID_usuario, ID_habitacion, Fecha_reserva) VALUES (?, ?, ?)";
    $stmt_reserva = $conexion->prepare($query_reserva);
    $stmt_reserva->bind_param('iis', $cliente_id, $habitacion_id, $fecha_reserva);
    $stmt_reserva->execute();

    // Obtener el precio de la habitación seleccionada
    $query_precio_habitacion = "SELECT Precio_noche FROM Habitaciones WHERE ID_habitación = ?";
    $stmt_precio = $conexion->prepare($query_precio_habitacion);
    $stmt_precio->bind_param('i', $habitacion_id);
    $stmt_precio->execute();
    $resultado_precio = $stmt_precio->get_result();
    $habitacion = $resultado_precio->fetch_assoc();
    $precio_habitacion = $habitacion['Precio_noche'];

    // Asociar los servicios seleccionados
    $total_servicios = 0;
    foreach ($servicios as $servicio_id) {
        $query_servicio = "SELECT Precio FROM Servicios WHERE ID_servicio = ?";
        $stmt_servicio = $conexion->prepare($query_servicio);
        $stmt_servicio->bind_param('i', $servicio_id);
        $stmt_servicio->execute();
        $resultado_servicio = $stmt_servicio->get_result();
        $servicio = $resultado_servicio->fetch_assoc();
        $total_servicios += $servicio['Precio'];

        // Asociar servicio con la reserva
        $query_asociar_servicio = "INSERT INTO Reserva_Servicios (ID_reserva, ID_servicio) VALUES (?, ?)";
        $stmt_asociar_servicio = $conexion->prepare($query_asociar_servicio);
        $stmt_asociar_servicio->bind_param('ii', $stmt_reserva->insert_id, $servicio_id);
        $stmt_asociar_servicio->execute();
    }

    // Calcular el total de la reserva (habitacion + servicios)
    $total_reserva = $precio_habitacion + $total_servicios;

    // Actualizar el estado de la habitación (para que ya no esté disponible)
    $query_update_habitacion = "UPDATE Habitaciones SET Estado = 'Reservada' WHERE ID_habitación = ?";
    $stmt_update_habitacion = $conexion->prepare($query_update_habitacion);
    $stmt_update_habitacion->bind_param('i', $habitacion_id);
    $stmt_update_habitacion->execute();

    // Mostrar el total
    echo "Cliente " . ($modificar_usuario ? "actualizado" : "agregado") . " y reserva realizada con éxito.<br>";
    echo "Total de la reserva: $" . $total_reserva . "<br>";
    echo "<a href='admin.php'>Volver al panel de administración</a>";

    $conexion->close();
    exit();
}

// Mostrar el formulario de agregar o modificar cliente
?>

<h3><?php echo $modificar_usuario ? "Modificar cliente" : "Agregar nuevo cliente"; ?></h3>

<form action="admin.php" method="POST">
    Nombre: <input type="text" name="nombre" value="<?php echo $modificar_usuario ? $usuario_actual['Nombre'] : ''; ?>" required><br><br>
    Correo electrónico: <input type="email" name="email" value="<?php echo $modificar_usuario ? $usuario_actual['Email'] : ''; ?>" required><br><br>
    Contraseña: <input type="password" name="contraseña" required><br><br>
    Dirección: <input type="text" name="direccion" value="<?php echo $modificar_usuario ? $usuario_actual['Direccion'] : ''; ?>" required><br><br>
    Teléfono: <input type="text" name="telefono" value="<?php echo $modificar_usuario ? $usuario_actual['Telefono'] : ''; ?>" required><br><br>
    Administrador: 
    <select name="admin">
        <option value="No" <?php echo ($modificar_usuario && $usuario_actual['Admin'] == 'No') ? 'selected' : ''; ?>>No</option>
        <option value="Sí" <?php echo ($modificar_usuario && $usuario_actual['Admin'] == 'Sí') ? 'selected' : ''; ?>>Sí</option>
    </select><br><br>

    <label for="habitacion">Seleccionar habitación:</label><br>
    <select name="ID_habitacion" required>
        <option value="" disabled selected>Seleccione una habitación</option>
        <?php
        $query_habitaciones = "SELECT * FROM Habitaciones WHERE Estado = 'Disponible'";
        $result_habitaciones = $conexion->query($query_habitaciones);
        while ($habitacion = $result_habitaciones->fetch_assoc()) {
            echo "<option value='" . $habitacion['ID_habitación'] . "' " . ($modificar_usuario && $habitacion['ID_habitación'] == $usuario_actual['ID_habitacion'] ? 'selected' : '') . ">" . $habitacion['Tipo'] . " - $" . $habitacion['Precio_noche'] . " por noche</option>";
        }
        ?>
    </select><br><br>

    <h3>Selecciona los servicios adicionales:</h3>
    <?php
    $query_servicios = "SELECT * FROM Servicios";
    $result_servicios = $conexion->query($query_servicios);
    while ($servicio = $result_servicios->fetch_assoc()) {
        echo "<label for='servicio_" . $servicio['ID_servicio'] . "'>" . $servicio['Nombre'] . " - $" . $servicio['Precio'] . "</label><br>";
        echo "<input type='checkbox' name='servicios[]' value='" . $servicio['ID_servicio'] . "' id='servicio_" . $servicio['ID_servicio'] . "' " . ($modificar_usuario && in_array($servicio['ID_servicio'], explode(",", $usuario_actual['Servicios'])) ? 'checked' : '') . "> <br>";
    }
    ?>

    <br><input type="submit" value="Guardar cambios">
</form>

<h3>Visualización de todas las tablas</h3>

<!-- Mostrar Usuarios -->
<h4>Usuarios</h4>
<table border="1">
    <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Email</th>
        <th>Dirección</th>
        <th>Teléfono</th>
        <th>Administrador</th>
    </tr>
    <?php
    $query_usuarios = "SELECT * FROM Usuarios";
    $result_usuarios = $conexion->query($query_usuarios);
    while ($usuario = mysqli_fetch_assoc($result_usuarios)) {
        echo "<tr>
                <td>" . $usuario['ID_usuario'] . "</td>
                <td>" . $usuario['Nombre'] . "</td>
                <td>" . $usuario['Email'] . "</td>
                <td>" . $usuario['Direccion'] . "</td>
                <td>" . $usuario['Telefono'] . "</td>
                <td>" . $usuario['Admin'] . "</td>
            </tr>";
    }
    ?>
</table>

<!-- Mostrar Habitaciones -->
<h4>Habitaciones</h4>
<table border="1">
    <tr>
        <th>ID</th>
        <th>Tipo</th>
        <th>Precio por noche</th>
        <th>Estado</th>
    </tr>
    <?php
    $query_habitaciones = "SELECT * FROM Habitaciones";
    $result_habitaciones = $conexion->query($query_habitaciones);
    while ($habitacion = $result_habitaciones->fetch_assoc()) {
        echo "<tr>
                <td>" . $habitacion['ID_habitación'] . "</td>
                <td>" . $habitacion['Tipo'] . "</td>
                <td>$" . $habitacion['Precio_noche'] . "</td>
                <td>" . $habitacion['Estado'] . "</td>
            </tr>";
    }
    ?>
</table>

<!-- Mostrar Servicios -->
<h4>Servicios</h4>
<table border="1">
    <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Precio</th>
    </tr>
    <?php
    $query_servicios = "SELECT * FROM Servicios";
    $result_servicios = $conexion->query($query_servicios);
    while ($servicio = $result_servicios->fetch_assoc()) {
        echo "<tr>
                <td>" . $servicio['ID_servicio'] . "</td>
                <td>" . $servicio['Nombre'] . "</td>
                <td>$" . $servicio['Precio'] . "</td>
            </tr>";
    }
    ?>
</table>

<!-- Mostrar 
