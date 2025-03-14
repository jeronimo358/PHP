<?php
session_start();
include("conexion.php");

// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION['Email']) || $_SESSION['Admin'] != 'Si') {
    header("Location: login.php");
    exit();
}

// Función para mostrar la tabla con opciones de eliminación y adición
function mostrarTablaConAcciones($conexion, $tabla) {
    $sql = "SELECT * FROM $tabla";
    $result = mysqli_query($conexion, $sql);

    if (mysqli_num_rows($result) > 0) { // si la tabla tiene datos es decir mayor que 0 se muestra la tabla en html
        echo "<h3>Tabla: $tabla</h3>";
        echo "<table class='table table-bordered table-striped'><thead><tr>";

        // Obtener los nombres de las columnas de manera procedimental
        $columnas = array();
        if ($row = mysqli_fetch_assoc($result)) {
            $columnas = array_keys($row);
            mysqli_data_seek($result, 0); // Reiniciar puntero para no perder la primera fila
        }

        mostrarEncabezados($columnas); // para imprimir los nombres de las columna 

        echo "<th>Acciones</th></tr></thead><tbody>";

        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            mostrarCeldas($columnas, $row); // para mostrar las deldas de las filas
            // boton para eliminar 
            echo "<td>
                    <form method='post' action='modificardatos.php' style='display:inline;'>
                        <input type='hidden' name='tabla' value='$tabla'>
                        <input type='hidden' name='accion' value='eliminar'>
                        <input type='hidden' name='id' value='" . $row[$columnas[0]] . "'>
                        <button type='submit' class='btn btn-danger btn-sm'>Eliminar</button>
                    </form>
                  </td>";
            echo "</tr>";
        }
        echo "</tbody></table><br>";
    } else {
        echo "<div class='alert alert-warning'>No se encontraron datos en la tabla $tabla.</div><br>"; // Mensaje de la tabla que este vacia
    }
}

// Función para mostrar los encabezados de la tabla
function mostrarEncabezados($columnas) {
    foreach ($columnas as $columna) {
        echo "<th>" . $columna . "</th>";
    }
}

// Función para mostrar las celdas de la tabla
function mostrarCeldas($columnas, $row) {
    foreach ($columnas as $columna) {
        echo "<td>" . $row[$columna] . "</td>";
    }
}

// Función para actualizar las habitaciones no reservadas a "Disponible"
function actualizarHabitacionesDisponibles($conexion) {
    $sqlHabitacionesReservadas = "SELECT ID_habitaciones FROM Reservas";
    $resultHabitacionesReservadas = mysqli_query($conexion, $sqlHabitacionesReservadas);

    $habitacionesReservadas = array();
    while ($row = mysqli_fetch_assoc($resultHabitacionesReservadas)) {
        $habitacionesReservadas[] = $row['ID_habitaciones'];
    }

    if (!empty($habitacionesReservadas)) {
        $habitacionesReservadasList = implode(",", $habitacionesReservadas);
        $sqlActualizarHabitaciones = "UPDATE Habitaciones SET Estado = 'Disponible' WHERE ID_habitaciones NOT IN ($habitacionesReservadasList)";
        mysqli_query($conexion, $sqlActualizarHabitaciones);
    } else {
        // Si no hay habitaciones reservadas, actualizar todas las habitaciones a "Disponible"
        $sqlActualizarHabitaciones = "UPDATE Habitaciones SET Estado = 'Disponible'";
        mysqli_query($conexion, $sqlActualizarHabitaciones);
    }
}

$tablas = ["Usuarios", "Habitaciones", "Reservas", "Servicios", "Servicios_Reservas", "Trabajadores"];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'eliminar') { // Si se recibe una solicitud POSTcon accion=eliminar, se procede con la eliminación.
    $tabla = $_POST['tabla'];
    $id = $_POST['id'];
    // Se asume que la clave primaria es del formato "ID_" seguido del nombre de la tabla en minúsculas
    $columna_primaria = "ID_" . strtolower($tabla);

    // Si se elimina una reserva, actualizar el estado de la habitación a "Disponible"
    if ($tabla == "Reservas") {
        // Obtener el ID de la habitación relacionada con la reserva
        $sqlHabitacion = "SELECT ID_habitaciones FROM Reservas WHERE $columna_primaria = '$id'";
        $resultHabitacion = mysqli_query($conexion, $sqlHabitacion);
        $rowHabitacion = mysqli_fetch_assoc($resultHabitacion);
        $id_habitacion = $rowHabitacion['ID_habitaciones'];

        // Actualizar el estado de la habitación a "Disponible"
        $sqlActualizarHabitacion = "UPDATE Habitaciones SET Estado = 'Disponible' WHERE ID_habitaciones = '$id_habitacion'";
        mysqli_query($conexion, $sqlActualizarHabitacion);
    }

    // Eliminar el registro
    $sqlEliminar = "DELETE FROM $tabla WHERE $columna_primaria = '$id'";
    if (mysqli_query($conexion, $sqlEliminar)) {
        echo "<div class='alert alert-success'>Registro eliminado correctamente.</div><br>"; // mensaje de que se a eliminado bien
    } else {
        echo "<div class='alert alert-danger'>Error al eliminar el registro: " . mysqli_error($conexion) . "</div><br>";
    }

    // Actualizar las habitaciones no reservadas a "Disponible"
    actualizarHabitacionesDisponibles($conexion);
}

// Mostrarlo en html 
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Administrador - Modificar Datos</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container mt-4">
    <h2 class="text-center">Modificar Datos</h2>
    <?php
    foreach ($tablas as $tabla) {
        mostrarTablaConAcciones($conexion, $tabla);
        // boton para añadir registros
        echo "<div class='text-center'>
                <form method='get' action='insertar.php' class='d-inline-block'>
                    <input type='hidden' name='tabla' value='$tabla'>
                    <button type='submit' class='btn btn-success mb-3'>Añadir registro a $tabla</button>
                </form>
              </div><br>";
    }
    ?>
    <div class="text-center mt-3">
      <a href="admin.php" class="btn btn-secondary">Volver a Admin</a>
    </div>
  </div>
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
<?php mysqli_close($conexion); ?>
