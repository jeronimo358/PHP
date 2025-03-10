<?php
session_start();
include("conexion.php");

// Verificar si el usuario está logueado
if (!isset($_SESSION['Email'])) {
    header("Location: login.php");
    exit();
}

// Obtener ID del usuario logueado
$email = $_SESSION['Email'];
$sql = "SELECT ID_usuarios FROM Usuarios WHERE Email = '$email'";
$result = mysqli_query($conexion, $sql);
$row = mysqli_fetch_assoc($result);
$id_usuario = $row['ID_usuarios'];

// Verificar si el usuario tiene reservas
$sql_reservas = "SELECT * FROM Reservas WHERE ID_usuarios = '$id_usuario' AND Estado_reserva IN ('Confirmada', 'Pendiente')";
$result_reservas = mysqli_query($conexion, $sql_reservas);
$tiene_reservas = mysqli_num_rows($result_reservas) > 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_habitacion = $_POST['habitacion'];
    $servicios = isset($_POST['servicios']) ? $_POST['servicios'] : [];
    $fecha_check_in = $_POST['fecha_check_in'];
    $fecha_check_out = $_POST['fecha_check_out'];

    // Calcular el precio total de la reserva
    $sql_precio_habitacion = "SELECT Precio_noche FROM Habitaciones WHERE ID_habitaciones = '$id_habitacion'";
    $result_precio_habitacion = mysqli_query($conexion, $sql_precio_habitacion);
    $row_precio_habitacion = mysqli_fetch_assoc($result_precio_habitacion);
    $precio_habitacion = $row_precio_habitacion['Precio_noche'];

    $precio_servicios = 0;
    if (!empty($servicios)) {
        $sql_precio_servicios = "SELECT SUM(Precio) AS Precio_total_servicios FROM Servicios WHERE ID_servicios IN (" . implode(",", $servicios) . ")";
        $result_precio_servicios = mysqli_query($conexion, $sql_precio_servicios);
        $row_precio_servicios = mysqli_fetch_assoc($result_precio_servicios);
        $precio_servicios = $row_precio_servicios['Precio_total_servicios'];
    }

    $fecha_check_in_dt = new DateTime($fecha_check_in);
    $fecha_check_out_dt = new DateTime($fecha_check_out);
    $intervalo = $fecha_check_in_dt->diff($fecha_check_out_dt);
    $noches = $intervalo->d;

    $total_reserva = ($precio_habitacion * $noches) + $precio_servicios;

    // Insertar nueva reserva
    $sql_nueva_reserva = "INSERT INTO Reservas (ID_usuarios, ID_habitaciones, Fecha_check_in, Fecha_check_out, Estado_reserva, Total_reserva) 
                          VALUES ('$id_usuario', '$id_habitacion', '$fecha_check_in', '$fecha_check_out', 'Pendiente', '$total_reserva')";
    mysqli_query($conexion, $sql_nueva_reserva);
    $id_reserva = mysqli_insert_id($conexion);

    // Insertar servicios seleccionados
    foreach ($servicios as $id_servicio) {
        $sql_insertar_servicio = "INSERT INTO Servicios_Reservas (ID_reservas, ID_servicios, Cantidad) VALUES ('$id_reserva', '$id_servicio', 1)";
        mysqli_query($conexion, $sql_insertar_servicio);
    }

    // Actualizar el estado de la habitación a 'Ocupada'
    $sql_actualizar_habitacion = "UPDATE Habitaciones SET Estado = 'Ocupada' WHERE ID_habitaciones = '$id_habitacion'";
    mysqli_query($conexion, $sql_actualizar_habitacion);

    // Cambiar el estado de la reserva a 'Confirmada'
    $sql_actualizar_reserva = "UPDATE Reservas SET Estado_reserva = 'Confirmada' WHERE ID_reservas = '$id_reserva'";
    mysqli_query($conexion, $sql_actualizar_reserva);

    // Redireccionar después de hacer la reserva para evitar reenvíos de formulario
    header("Location: cliente.php");
    exit;
}

// Eliminar reserva
if (isset($_GET['eliminar_reserva'])) {
    $id_reserva = $_GET['eliminar_reserva'];
    
    // Obtener el ID de la habitación
    $sql_habitacion = "SELECT ID_habitaciones FROM Reservas WHERE ID_reservas = '$id_reserva'";
    $result_habitacion = mysqli_query($conexion, $sql_habitacion);
    $row_habitacion = mysqli_fetch_assoc($result_habitacion);
    $id_habitacion = $row_habitacion['ID_habitaciones'];

    // Eliminar la reserva
    $sqlEliminar = "DELETE FROM Reservas WHERE ID_reservas = '$id_reserva'";
    mysqli_query($conexion, $sqlEliminar);

    // Actualizar el estado de la habitación a 'Disponible' después de eliminar la reserva
    $sql_actualizar_habitacion = "UPDATE Habitaciones SET Estado = 'Disponible' WHERE ID_habitaciones = '$id_habitacion'";
    mysqli_query($conexion, $sql_actualizar_habitacion);

    // Redireccionar después de eliminar la reserva
    header("Location: cliente.php");
    exit;
}

// Verificar las reservas confirmadas que han finalizado
$current_date = date('Y-m-d');
$sql_check_reservas = "SELECT * FROM Reservas WHERE Estado_reserva = 'Confirmada' AND Fecha_check_out < '$current_date'";

$result_check_reservas = mysqli_query($conexion, $sql_check_reservas);
while ($row_reserva = mysqli_fetch_assoc($result_check_reservas)) {
    // Cambiar el estado de la habitación a 'Disponible'
    $id_habitacion = $row_reserva['ID_habitaciones'];
    $sql_actualizar_habitacion = "UPDATE Habitaciones SET Estado = 'Disponible' WHERE ID_habitaciones = '$id_habitacion'";
    mysqli_query($conexion, $sql_actualizar_habitacion);

    // Opcional: Cambiar el estado de la reserva a 'Finalizada'
    $sql_actualizar_reserva = "UPDATE Reservas SET Estado_reserva = 'Finalizada' WHERE ID_reservas = '{$row_reserva['ID_reservas']}'";
    mysqli_query($conexion, $sql_actualizar_reserva);
}

// Restaurar habitaciones "Ocupadas" sin reservas en la tabla "Reservas"
$sql_habitaciones_ocupadas = "SELECT * FROM Habitaciones WHERE Estado = 'Ocupada'";
$result_habitaciones_ocupadas = mysqli_query($conexion, $sql_habitaciones_ocupadas);

while ($row_habitacion_ocupada = mysqli_fetch_assoc($result_habitaciones_ocupadas)) {
    $id_habitacion = $row_habitacion_ocupada['ID_habitaciones'];

    // Verificar si la habitación tiene reservas activas
    $sql_verificar_reserva = "SELECT * FROM Reservas WHERE ID_habitaciones = '$id_habitacion' AND Estado_reserva IN ('Confirmada', 'Pendiente')";
    $result_verificar_reserva = mysqli_query($conexion, $sql_verificar_reserva);

    if (mysqli_num_rows($result_verificar_reserva) == 0) {
        // Si no hay reservas asociadas, actualizar el estado de la habitación a 'Disponible'
        $sql_actualizar_habitacion = "UPDATE Habitaciones SET Estado = 'Disponible' WHERE ID_habitaciones = '$id_habitacion'";
        mysqli_query($conexion, $sql_actualizar_habitacion);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cliente - Reservas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white text-center">
                <h2>Bienvenido, <?php echo $_SESSION['Nombre'] . " " . $_SESSION['Apellido']; ?></h2>
            </div>
            <div class="card-body">
                <?php if ($tiene_reservas) { ?>
                    <h3>Tus Reservas</h3>
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Habitación</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Estado</th>
                                <th>Total</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row_reserva = mysqli_fetch_assoc($result_reservas)) { ?>
                                <tr>
                                    <td><?php echo $row_reserva['ID_habitaciones']; ?></td>
                                    <td><?php echo $row_reserva['Fecha_check_in']; ?></td>
                                    <td><?php echo $row_reserva['Fecha_check_out']; ?></td>
                                    <td><span class="badge bg-success"><?php echo $row_reserva['Estado_reserva']; ?></span></td>
                                    <td>$<?php echo number_format($row_reserva['Total_reserva'], 2); ?></td>
                                    <td><a href="cliente.php?eliminar_reserva=<?php echo $row_reserva['ID_reservas']; ?>" class="btn btn-danger btn-sm">Eliminar</a></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } else { ?>
                    <p class="alert alert-info">No tienes reservas pendientes o confirmadas.</p>
                <?php } ?>

                <h3>Hacer una nueva reserva</h3>
                <form method="post" action="cliente.php">
                    <div class="mb-3">
                        <label for="habitacion" class="form-label">Habitación:</label>
                        <select id="habitacion" name="habitacion" class="form-select" required>
                            <?php
                            $sql_habitaciones = "SELECT * FROM Habitaciones WHERE Estado = 'Disponible'";
                            $result_habitaciones = mysqli_query($conexion, $sql_habitaciones);
                            while ($row_habitacion = mysqli_fetch_assoc($result_habitaciones)) {
                                echo "<option value='" . $row_habitacion['ID_habitaciones'] . "'>" . $row_habitacion['Tipo'] . " - $" . $row_habitacion['Precio_noche'] . " por noche</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="servicios" class="form-label">Servicios:</label>
                        <?php
                        $sql_servicios = "SELECT * FROM Servicios";
                        $result_servicios = mysqli_query($conexion, $sql_servicios);
                        while ($row_servicio = mysqli_fetch_assoc($result_servicios)) {
                            echo "<div class='form-check'>";
                            echo "<input class='form-check-input' type='checkbox' id='servicio_" . $row_servicio['ID_servicios'] . "' name='servicios[]' value='" . $row_servicio['ID_servicios'] . "'>";
                            echo "<label class='form-check-label' for='servicio_" . $row_servicio['ID_servicios'] . "'>" . $row_servicio['Nombre'] . " - $" . $row_servicio['Precio'] . "</label>";
                            echo "</div>";
                        }
                        ?>
                    </div>

                    <div class="mb-3">
                        <label for="fecha_check_in" class="form-label">Fecha de Check-in:</label>
                        <input type="date" id="fecha_check_in" name="fecha_check_in" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="fecha_check_out" class="form-label">Fecha de Check-out:</label>
                        <input type="date" id="fecha_check_out" name="fecha_check_out" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <button type="submit" class="btn btn-primary">Hacer Reserva</button>
                </form>

                <br>
                <a href="login.php">
                    <button class="btn btn-secondary">Volver al Login</button>
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Validación de fecha check-out no puede ser anterior a check-in -->
    <script>
        document.getElementById('fecha_check_out').addEventListener('change', function() {
            var checkInDate = new Date(document.getElementById('fecha_check_in').value);
            var checkOutDate = new Date(this.value);
            
            if (checkOutDate < checkInDate) {
                alert("La fecha de check-out no puede ser anterior a la de check-in.");
                this.value = ''; // Reset the check-out date
            }
        });
    </script>
</body>
</html>
