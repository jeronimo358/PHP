<?php 
session_start();
include("conexion.php");

// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION['Email']) || $_SESSION['Admin'] != 'Si') {
    header("Location: login.php");
    exit();
}

// Obtener los datos necesarios para mostrar en el formulario
$sql_usuarios = "SELECT * FROM Usuarios";
$result_usuarios = mysqli_query($conexion, $sql_usuarios);

$sql_habitaciones = "SELECT * FROM Habitaciones WHERE Estado = 'Disponible'"; // Filtrar solo habitaciones disponibles
$result_habitaciones = mysqli_query($conexion, $sql_habitaciones);

$sql_servicios = "SELECT * FROM Servicios";
$result_servicios = mysqli_query($conexion, $sql_servicios);

// Obtener la fecha actual en formato YYYY-MM-DD
$current_date = date('Y-m-d'); 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador - Hacer Reserva</title>
    <!-- Enlace de Bootstrap -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center">Hacer una nueva reserva</h2>
        <form method="post" action="reservaadmin.php">
            <!-- Selección de usuario -->
            <div class="form-group">
                <h3>Seleccionar Usuario</h3>
                <select id="id_usuario" name="id_usuario" class="form-control" required>
                    <?php
                    while ($row_usuario = mysqli_fetch_assoc($result_usuarios)) {
                        echo "<option value='" . $row_usuario['ID_usuarios'] . "'>" . $row_usuario['Nombre'] . " " . $row_usuario['Apellido'] . " - " . $row_usuario['Email'] . "</option>";
                    }
                    ?>
                </select><br><br>
            </div>

            <!-- Selección de habitación -->
            <div class="form-group">
                <h3>Seleccionar Habitación</h3>
                <select id="id_habitacion" name="id_habitacion" class="form-control" required>
                    <?php
                    while ($row_habitacion = mysqli_fetch_assoc($result_habitaciones)) {
                        echo "<option value='" . $row_habitacion['ID_habitaciones'] . "'>" . $row_habitacion['Tipo'] . " - $" . $row_habitacion['Precio_noche'] . " por noche</option>";
                    }
                    ?>
                </select><br><br>
            </div>

            <!-- Selección de servicios -->
            <div class="form-group">
                <h3>Seleccionar Servicios</h3>
                <?php
                while ($row_servicio = mysqli_fetch_assoc($result_servicios)) {
                    echo "<div class='form-check'>";
                    echo "<input type='checkbox' class='form-check-input' id='servicio_" . $row_servicio['ID_servicios'] . "' name='servicios[]' value='" . $row_servicio['ID_servicios'] . "'>";
                    echo "<label class='form-check-label' for='servicio_" . $row_servicio['ID_servicios'] . "'>" . $row_servicio['Nombre'] . " - $" . $row_servicio['Precio'] . "</label><br>";
                    echo "</div>";
                }
                ?><br>
            </div>

            <!-- Fechas de la reserva -->
            <div class="form-group">
                <h3>Fechas de la Reserva</h3>
                <label for="fecha_check_in">Fecha de Check-in:</label><br>
                <input type="date" id="fecha_check_in" name="fecha_check_in" class="form-control" required min="<?php echo $current_date; ?>"><br><br>

                <label for="fecha_check_out">Fecha de Check-out:</label><br>
                <input type="date" id="fecha_check_out" name="fecha_check_out" class="form-control" required min="<?php echo $current_date; ?>"><br><br>
            </div>

            <button type="submit" name="accion" value="reservar" class="btn btn-success">Hacer Reserva</button>
        </form>

        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == "reservar") {
            // Usar usuario existente
            $id_usuario = $_POST['id_usuario'];

            $id_habitacion = $_POST['id_habitacion'];
            $servicios = isset($_POST['servicios']) ? $_POST['servicios'] : [];
            $fecha_check_in = $_POST['fecha_check_in'];
            $fecha_check_out = $_POST['fecha_check_out'];

            // Calcular el precio total de la reserva
            $sql_precio_habitacion = "SELECT Precio_noche FROM Habitaciones WHERE ID_habitaciones = '$id_habitacion'";
            $result_precio_habitacion = mysqli_query($conexion, $sql_precio_habitacion);
            $row_precio_habitacion = mysqli_fetch_assoc($result_precio_habitacion);
            
            if ($row_precio_habitacion) {
                $precio_habitacion = $row_precio_habitacion['Precio_noche'];
            } else {
                echo "No se pudo obtener el precio de la habitación.";
                exit;
            }

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
            $sql_nueva_reserva = "INSERT INTO reservas (id_usuarios, ID_habitaciones, Fecha_check_in, Fecha_check_out, Estado_reserva, Total_reserva) 
                                  VALUES ('$id_usuario', '$id_habitacion', '$fecha_check_in', '$fecha_check_out', 'Pendiente', '$total_reserva')";
            if (mysqli_query($conexion, $sql_nueva_reserva)) {
                $id_reserva = mysqli_insert_id($conexion);
                
                // Cambiar estado de la habitación a 'Ocupada'
                $sql_actualizar_habitacion = "UPDATE Habitaciones SET Estado = 'Ocupada' WHERE ID_habitaciones = '$id_habitacion'";
                mysqli_query($conexion, $sql_actualizar_habitacion);

                // Insertar servicios seleccionados
                foreach ($servicios as $id_servicio) {
                    $sql_insertar_servicio = "INSERT INTO Servicios_Reservas (ID_reservas, ID_servicios, Cantidad) 
                                              VALUES ('$id_reserva', '$id_servicio', 1)";
                    mysqli_query($conexion, $sql_insertar_servicio);
                }

                echo "<div class='alert alert-success'>Reserva realizada con éxito. ID de la reserva: $id_reserva</div>";
            } else {
                echo "<div class='alert alert-danger'>Error al hacer la reserva: " . mysqli_error($conexion) . "</div>";
            }
        }
        ?>

        <!-- Botón para volver a la página admin.php -->
        <div class="text-center mt-3">
            <a href="admin.php" class="btn btn-primary">Volver al Panel de Administrador</a>
        </div>
    </div>
</body>
</html>
