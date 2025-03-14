<?php
session_start();
include("conexion.php");

// Verificar que el usuario está logueado y es admin
if (!isset($_SESSION['Email']) || $_SESSION['Admin'] != 'Si') {
    header("Location: login.php");
    exit();
}

// Consultar usuarios, habitaciones disponibles y servicios
$result_usuarios = consultarDatos($conexion, "Usuarios");
$result_habitaciones = consultarHabitacionesDisponibles($conexion);
$result_servicios = consultarDatos($conexion, "Servicios");

// Obtener la fecha actual para restringir fechas pasadas
$current_date = date('Y-m-d');

// Función para consultar datos de una tabla
function consultarDatos($conexion, $tabla) {
    $sql = "SELECT * FROM $tabla";
    return mysqli_query($conexion, $sql);
}

// Función para consultar habitaciones disponibles
function consultarHabitacionesDisponibles($conexion) {
    $sql = "SELECT * FROM Habitaciones WHERE Estado = 'Disponible'";
    return mysqli_query($conexion, $sql);
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Administrador - Hacer Reserva</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <h2 class="text-center">Hacer una nueva reserva</h2>
    <form method="post" action="reservaadmin.php"> <!-- Los datos se enviarán a reservaadmin.phpcon POST. -->
      <!-- Seleccionar Usuario -->
      <div class="form-group">
        <h3>Seleccionar Usuario</h3>
        <select id="id_usuario" name="id_usuario" class="form-control" required>
          <?php
          while ($row_usuario = mysqli_fetch_assoc($result_usuarios)) { // obtiene una fila de resultado de consulta de forma array asociado
              echo "<option value='" . $row_usuario['ID_usuarios'] . "'>" . $row_usuario['Nombre'] . " " . $row_usuario['Apellido'] . " - " . $row_usuario['Email'] . "</option>";
          }
          ?>
        </select><br><br>
      </div>
      <!-- Seleccionar Habitación -->
      <div class="form-group">
        <h3>Seleccionar Habitación</h3>
        <select id="id_habitacion" name="id_habitacion" class="form-control" required>
          <?php
          while ($row_habitacion = mysqli_fetch_assoc($result_habitaciones)) { // obtiene una fila de resultado de consulta de forma array asociado
              echo "<option value='" . $row_habitacion['ID_habitaciones'] . "'>" . $row_habitacion['Tipo'] . " - $" . $row_habitacion['Precio_noche'] . " por noche</option>";
          }
          ?>
        </select><br><br>
      </div>
      <!-- Seleccionar Servicios -->
      <div class="form-group">
        <h3>Seleccionar Servicios</h3>
        <?php
        while ($row_servicio = mysqli_fetch_assoc($result_servicios)) { // obtiene una fila de resultado de consulta de forma array asociado
            echo "<div class='form-check'>";
            echo "<input type='checkbox' class='form-check-input' id='servicio_" . $row_servicio['ID_servicios'] . "' name='servicios[]' value='" . $row_servicio['ID_servicios'] . "'>";
            echo "<label class='form-check-label' for='servicio_" . $row_servicio['ID_servicios'] . "'>" . $row_servicio['Nombre'] . " - $" . $row_servicio['Precio'] . "</label><br>";
            echo "</div>";
        }
        ?><br>
      </div>
      <!-- Fechas de la Reserva -->
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
        $id_usuario = $_POST['id_usuario'];
        $id_habitacion = $_POST['id_habitacion'];
        $servicios = isset($_POST['servicios']) ? $_POST['servicios'] : [];
        $fecha_check_in = $_POST['fecha_check_in'];
        $fecha_check_out = $_POST['fecha_check_out'];

        // Obtener precio de la habitación
        $precio_habitacion = obtenerPrecioHabitacion($conexion, $id_habitacion); // como se hace esta abajo del todo
        if ($precio_habitacion === false) {
            echo "No se pudo obtener el precio de la habitación.";
            exit;
        }

        // Calcular total de los servicios seleccionados (si hay)
        $precio_servicios = obtenerPrecioServicios($conexion, $servicios); // como se hace esta abajo del todo

        // Calcular noches sin usar DateTime // fecha a texto
        $noches = (strtotime($fecha_check_out) - strtotime($fecha_check_in)) / 86400; // 86400 segundos en un día

        // Calcular total de la reserva
        $total_reserva = ($precio_habitacion * $noches) + $precio_servicios;

        // Insertar la nueva reserva con estado "Confirmada"
        $sql_nueva_reserva = "INSERT INTO reservas (id_usuarios, ID_habitaciones, Fecha_check_in, Fecha_check_out, Estado_reserva, Total_reserva) 
                             VALUES ('$id_usuario', '$id_habitacion', '$fecha_check_in', '$fecha_check_out', 'Confirmada', '$total_reserva')";
        if (mysqli_query($conexion, $sql_nueva_reserva)) {
            $id_reserva = mysqli_insert_id($conexion); // obtiene id autoincremental

            // Actualizar habitación a "Ocupada"
            $sql_actualizar_habitacion = "UPDATE Habitaciones SET Estado = 'Ocupada' WHERE ID_habitaciones = '$id_habitacion'";
            mysqli_query($conexion, $sql_actualizar_habitacion);

            // Insertar cada servicio seleccionado
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

    // Función para obtener el precio de la habitación
    function obtenerPrecioHabitacion($conexion, $id_habitacion) {
        $sql_precio_habitacion = "SELECT Precio_noche FROM Habitaciones WHERE ID_habitaciones = '$id_habitacion'";
        $result_precio_habitacion = mysqli_query($conexion, $sql_precio_habitacion);
        $row_precio_habitacion = mysqli_fetch_assoc($result_precio_habitacion); // obtiene una fila de resultado de consulta de forma array asociado
        return $row_precio_habitacion ? $row_precio_habitacion['Precio_noche'] : false;
    }

    // Función para obtener el precio de los servicios seleccionados
    function obtenerPrecioServicios($conexion, $servicios) {
        if (empty($servicios)) {
            return 0;
        }
        $sql_precio_servicios = "SELECT SUM(Precio) AS Precio_total_servicios FROM Servicios WHERE ID_servicios IN (" . implode(",", $servicios) . ")";
        $result_precio_servicios = mysqli_query($conexion, $sql_precio_servicios);
        $row_precio_servicios = mysqli_fetch_assoc($result_precio_servicios);  // obtiene una fila de resultado de consulta de forma array asociado
        return $row_precio_servicios['Precio_total_servicios'];
    }
    ?>

    <div class="text-center mt-3">
      <a href="admin.php" class="btn btn-primary">Volver al Panel de Administrador</a>
    </div>
  </div>
</body>
</html>
