<?php
session_start();
include("conexion.php");

// Si se solicita cerrar sesión mediante GET, se destruye la sesión y se redirige a login.php
if (isset($_GET['accion']) && $_GET['accion'] == "logout") {
    session_destroy(); // destruye toda informacion en esa sesion
    header("Location: login.php");
    exit();
}

// Verificar que el usuario haya iniciado sesión; si no, redirigir a login.php
if (!isset($_SESSION['Email'])) {
    header("Location: login.php");
    exit();
}

// Si el usuario es administrador, redirigirlo al panel de administración
if ($_SESSION['Admin'] == 'Si') {
    header("Location: admin.php");
    exit();
}

$current_date = date('Y-m-d');  // Fecha actual para restricciones en los inputs de fecha
$mensaje = "";  // Variable para almacenar mensajes de éxito o error

// Procesar formularios enviados
if ($_SERVER["REQUEST_METHOD"] == "POST") { // verifica que se mande a traves del metodo post

    // 1. Cancelación (eliminación) de reserva:
    if (isset($_POST['accion']) && $_POST['accion'] == "cancelar" && isset($_POST['id_reserva'])) { // cancelar una reserva
        $id_reserva = $_POST['id_reserva'];

        // Obtener el ID de la habitación asociada a la reserva
        $sql_select = "SELECT ID_habitaciones FROM reservas WHERE ID_reservas = '$id_reserva'";
        $result_select = mysqli_query($conexion, $sql_select);
        if ($result_select && mysqli_num_rows($result_select) > 0) {
            $row_select = mysqli_fetch_assoc($result_select); // obtiene una fila de resultados de consulta en forma de array asociado
            $id_habitacion = $row_select['ID_habitaciones'];

            // Eliminar la reserva
            $sql_delete = "DELETE FROM reservas WHERE ID_reservas = '$id_reserva'";
            if (mysqli_query($conexion, $sql_delete)) {
                // Actualizar la habitación a "Disponible"
                $sql_update_room = "UPDATE Habitaciones SET Estado = 'Disponible' WHERE ID_habitaciones = '$id_habitacion'";
                mysqli_query($conexion, $sql_update_room);
                header("Location: cliente.php?mensaje=cancelado");
                exit();
            } else {
                $mensaje = "<div class='alert alert-danger'>Error al cancelar la reserva: " . mysqli_error($conexion) . "</div>";
            }
        } else {
            $mensaje = "<div class='alert alert-danger'>No se encontró la reserva.</div>";
        }
    }
    
    // 2. Realizar una nueva reserva:
    if (isset($_POST['accion']) && $_POST['accion'] == "reservar") {
        // Obtener el usuario actual a partir del email almacenado en sesión
        $user_email = $_SESSION['Email'];
        $sql_user = "SELECT ID_usuarios FROM usuarios WHERE Email = ?";
        $stmt_user = mysqli_prepare($conexion, $sql_user);
        mysqli_stmt_bind_param($stmt_user, "s", $user_email); // para enlazar valores de variables a una consulta preparada en MySQL
        mysqli_stmt_execute($stmt_user);  // ejecuta consulta preparada
        $result_user = mysqli_stmt_get_result($stmt_user); // obtiene el resultado
        if (mysqli_num_rows($result_user) == 0) { // evita inyeccion sql
            $mensaje = "<div class='alert alert-danger'>Usuario no encontrado.</div>";
        } else {
            $user_data = mysqli_fetch_assoc($result_user); // obtiene una fila de resultados de consulta en forma de array asociado
            $id_usuario = $user_data['ID_usuarios'];
            
            // Recoger datos del formulario
            $id_habitacion = $_POST['id_habitacion'];
            $fecha_check_in = $_POST['fecha_check_in'];
            $fecha_check_out = $_POST['fecha_check_out'];
            $servicios = isset($_POST['servicios']) ? $_POST['servicios'] : array();
            
            // Validar fechas: la fecha de check-out no debe ser anterior a la de check-in
            if ($fecha_check_out < $fecha_check_in) {
                $mensaje = "<div class='alert alert-danger'>La fecha de check-out no puede ser anterior a la de check-in.</div>";
            } else {
                // Calcular el número de noches
                $fecha_check_in_ts = strtotime($fecha_check_in); // fecha a texto
                $fecha_check_out_ts = strtotime($fecha_check_out);
                $interval_seconds = $fecha_check_out_ts - $fecha_check_in_ts;
                $noches = floor($interval_seconds / (60 * 60 * 24));
                if ($noches <= 0) {
                    $mensaje = "<div class='alert alert-danger'>Debe seleccionar al menos una noche de reserva.</div>";
                } else {
                    // Consultar el precio de la habitación seleccionada
                    $sql_price = "SELECT Precio_noche FROM Habitaciones WHERE ID_habitaciones = '$id_habitacion'";
                    $result_price = mysqli_query($conexion, $sql_price);
                    $row_price = mysqli_fetch_assoc($result_price); // obtiene una fila de resultados de consulta en forma de array asociado
                    if (!$row_price) {
                        $mensaje = "<div class='alert alert-danger'>No se pudo obtener el precio de la habitación.</div>";
                    } else {
                        $precio_habitacion = $row_price['Precio_noche'];
                        // Calcular el precio total de los servicios (si se seleccionaron)
                        $precio_servicios = 0;
                        if (!empty($servicios)) {
                            $sql_serv = "SELECT SUM(Precio) AS total FROM Servicios WHERE ID_servicios IN (" . implode(",", $servicios) . ")";
                            $result_serv = mysqli_query($conexion, $sql_serv);
                            $row_serv = mysqli_fetch_assoc($result_serv); // obtiene una fila de resultados de consulta en forma de array asociado
                            $precio_servicios = $row_serv['total'];
                        }
                        // Calcular el total de la reserva
                        $total_reserva = ($precio_habitacion * $noches) + $precio_servicios;
                        
                        // Insertar la nueva reserva con estado "Confirmada"
                        $sql_insert = "INSERT INTO reservas (id_usuarios, ID_habitaciones, Fecha_check_in, Fecha_check_out, Estado_reserva, Total_reserva)
                                       VALUES ('$id_usuario', '$id_habitacion', '$fecha_check_in', '$fecha_check_out', 'Confirmada', '$total_reserva')";
                        if (mysqli_query($conexion, $sql_insert)) {
                            $id_reserva = mysqli_insert_id($conexion); // obtiene el id autoincremental
                            
                            // Actualizar la habitación a "Ocupada"
                            $sql_update = "UPDATE Habitaciones SET Estado = 'Ocupada' WHERE ID_habitaciones = '$id_habitacion'";
                            mysqli_query($conexion, $sql_update);
                            
                            // Insertar los servicios seleccionados en la tabla Servicios_Reservas
                            foreach ($servicios as $id_servicio) {
                                $sql_ins_serv = "INSERT INTO Servicios_Reservas (ID_reservas, ID_servicios, Cantidad)
                                                 VALUES ('$id_reserva', '$id_servicio', 1)";
                                mysqli_query($conexion, $sql_ins_serv);
                            }
                            
                            $mensaje = "<div class='alert alert-success'>Reserva realizada con éxito. ID de la reserva: $id_reserva</div>";
                        } else {
                            $mensaje = "<div class='alert alert-danger'>Error al realizar la reserva: " . mysqli_error($conexion) . "</div>";
                        }
                    }
                }
            }
        }
    }
} // Fin de procesamiento de formularios

// Si venimos de una redirección tras cancelar, mostrar mensaje
if (isset($_GET['mensaje']) && $_GET['mensaje'] == "cancelado") {
    $mensaje = "<div class='alert alert-success'>Reserva cancelada correctamente.</div>";
}

// Obtener los datos del usuario para mostrar sus reservas si las tiene
$user_email = $_SESSION['Email'];
$sql_usuario = "SELECT ID_usuarios, Nombre, Apellido FROM usuarios WHERE Email = ?";
$stmt = mysqli_prepare($conexion, $sql_usuario);
mysqli_stmt_bind_param($stmt, "s", $user_email); // enlaza valores de variables a consulta preparada.
mysqli_stmt_execute($stmt);
$result_usuario = mysqli_stmt_get_result($stmt); // se obtiene resultado
if (mysqli_num_rows($result_usuario) == 0) {
    echo "Usuario no encontrado.";
    exit();
}
$usuario = mysqli_fetch_assoc($result_usuario);
$id_usuario = $usuario['ID_usuarios'];
$nombre = $usuario['Nombre'];
$apellido = $usuario['Apellido'];

// Consultar únicamente las reservas confirmadas del usuario
$sql_reservas = "SELECT r.ID_reservas, r.Fecha_check_in, r.Fecha_check_out, r.Estado_reserva, r.Total_reserva, h.Tipo AS HabitacionTipo 
                 FROM reservas r 
                 JOIN habitaciones h ON r.ID_habitaciones = h.ID_habitaciones 
                 WHERE r.id_usuarios = ? AND r.Estado_reserva = 'Confirmada'";
$stmt_reservas = mysqli_prepare($conexion, $sql_reservas);
mysqli_stmt_bind_param($stmt_reservas, "i", $id_usuario); // enlaza valores de variables a consulta preparada.
mysqli_stmt_execute($stmt_reservas);
$result_reservas = mysqli_stmt_get_result($stmt_reservas);

// Formulario de reservas html
?>

<<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cliente - Mis Reservas</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <?php echo $mensaje; ?>

    <h2 class="text-center">Bienvenido, <?php echo htmlspecialchars($nombre . " " . $apellido); ?></h2>
    
    <!-- Mostrar reservas actuales -->
    <h3 class="text-center mb-4">Mis Reservas</h3>
    <?php if (mysqli_num_rows($result_reservas) > 0) { ?>
      <table class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>ID Reserva</th>
            <th>Habitación</th>
            <th>Check-in</th>
            <th>Check-out</th>
            <th>Confirmación</th>
            <th>Total Reserva</th>
            <th>Opciones</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($reserva = mysqli_fetch_assoc($result_reservas)) { ?>
            <tr>
              <td><?php echo $reserva['ID_reservas']; ?></td>
              <td><?php echo htmlspecialchars($reserva['HabitacionTipo']); ?></td>
              <td><?php echo $reserva['Fecha_check_in']; ?></td>
              <td><?php echo $reserva['Fecha_check_out']; ?></td>
              <td><?php echo $reserva['Estado_reserva']; ?></td>
              <td>$<?php echo number_format($reserva['Total_reserva'], 2); ?></td> <!-- Formatea numero decimales sumando separadores -->
              <td>
                <form method="post" action="cliente.php" onsubmit="return confirm('¿Seguro que deseas cancelar esta reserva?');">
                  <input type="hidden" name="accion" value="cancelar">
                  <input type="hidden" name="id_reserva" value="<?php echo $reserva['ID_reservas']; ?>">
                  <button type="submit" class="btn btn-danger btn-sm">Cancelar</button>
                </form>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    <?php } else { ?>
      <div class="alert alert-info text-center">No tienes reservas registradas.</div>
    <?php } ?>

    <!-- Formulario para hacer una nueva reserva -->
    <h3 class="text-center mt-5">Hacer Nueva Reserva</h3>
    <form method="post" action="cliente.php">
      <!-- Seleccionar Habitación (solo las Disponibles) -->
      <div class="form-group">
        <label for="id_habitacion">Seleccionar Habitación</label>
        <select name="id_habitacion" id="id_habitacion" class="form-control" required>
          <?php
          $sql_habitaciones = "SELECT * FROM Habitaciones WHERE Estado = 'Disponible'";
          $result_habitaciones = mysqli_query($conexion, $sql_habitaciones);
          while ($row = mysqli_fetch_assoc($result_habitaciones)) { // obtener fila de resultados de consulta en forma de array asociado
              echo "<option value='" . $row['ID_habitaciones'] . "'>" . $row['Tipo'] . " - $" . $row['Precio_noche'] . " por noche</option>";
          }
          ?>
        </select>
      </div>
      <br>
      <!-- Seleccionar Servicios -->
      <div class="form-group">
        <label>Seleccionar Servicios</label>
        <?php
        $sql_servicios = "SELECT * FROM Servicios";
        $result_servicios = mysqli_query($conexion, $sql_servicios);
        while ($row = mysqli_fetch_assoc($result_servicios)) { // obtener fila de resultados de consulta en forma de array asociado
            echo "<div class='form-check'>";
            echo "<input type='checkbox' name='servicios[]' value='" . $row['ID_servicios'] . "' class='form-check-input' id='servicio_" . $row['ID_servicios'] . "'>";
            echo "<label class='form-check-label' for='servicio_" . $row['ID_servicios'] . "'>" . $row['Nombre'] . " - $" . $row['Precio'] . "</label>";
            echo "</div>";
        }
        ?>
      </div>
      <br>
      <!-- Fechas de la Reserva -->
      <div class="form-group">
        <label for="fecha_check_in">Fecha de Check-in</label>
        <input type="date" name="fecha_check_in" id="fecha_check_in" class="form-control" required min="<?php echo $current_date; ?>">
        <br>
        <label for="fecha_check_out">Fecha de Check-out</label>
        <input type="date" name="fecha_check_out" id="fecha_check_out" class="form-control" required min="<?php echo $current_date; ?>">
      </div>
      <br>
      <div class="text-center">
        <input type="hidden" name="accion" value="reservar">
        <button type="submit" class="btn btn-success">Hacer Reserva</button>
      </div>
    </form>

    <!-- Botón para Cerrar Sesión -->
    <div class="text-center mt-4">
      <a href="cliente.php?accion=logout" class="btn btn-secondary">Cerrar Sesión</a>
    </div>
  </div>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
mysqli_close($conexion);
?>
