<?php
session_start();
include("conexion.php");

// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION['Email']) || $_SESSION['Admin'] != 'Si') {
    header("Location: login.php");
    exit();
}

function mostrarTablaConAcciones($conexion, $tabla) {
    // Consultar los datos de la tabla
    $sql = "SELECT * FROM $tabla";
    $result = mysqli_query($conexion, $sql);

    // Verificar si hay resultados
    if (mysqli_num_rows($result) > 0) {
        echo "<h3>Tabla: $tabla</h3>";
        echo "<table class='table table-bordered table-striped'><thead><tr>";

        // Obtener nombres de columnas
        $columnas = array();
        while ($fieldinfo = mysqli_fetch_field($result)) {
            $columnas[] = $fieldinfo->name;
            echo "<th>" . $fieldinfo->name . "</th>";
        }
        echo "<th>Acciones</th></tr></thead><tbody>";

        // Mostrar filas de datos
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            foreach ($columnas as $columna) {
                echo "<td>" . $row[$columna] . "</td>";
            }
            echo "<td>
                    <form method='post' action='modificardatos.php' style='display:inline;'>
                        <input type='hidden' name='tabla' value='$tabla'>
                        <input type='hidden' name='accion' value='eliminar'>
                        <input type='hidden' name='id' value='" . $row[$columnas[0]] . "'>
                        <button type='submit' class='btn btn-danger btn-sm'>Eliminar</button>
                    </form>
                </td></tr>";
        }
        echo "</tbody></table><br>";
    } else {
        echo "<div class='alert alert-warning'>No se encontraron datos en la tabla $tabla.</div><br>";
    }
}

// Tablas a mostrar
$tablas = ["Usuarios", "Habitaciones", "Reservas", "Servicios", "Servicios_Reservas", "Trabajadores"];

// Verificar si hay una acción de eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
    $tabla = $_POST['tabla'];
    $id = $_POST['id'];

    // Verifica la clave primaria según la tabla, aquí se usa ID_nombre_tabla
    $columna_primaria = "ID_" . strtolower($tabla); // Ajusta esto si la columna primaria tiene otro nombre

    // Eliminar el registro de la tabla correspondiente
    $sqlEliminar = "DELETE FROM $tabla WHERE $columna_primaria = '$id'";

    if (mysqli_query($conexion, $sqlEliminar)) {
        echo "<div class='alert alert-success'>Registro eliminado correctamente.</div><br>";
    } else {
        echo "<div class='alert alert-danger'>Error al eliminar el registro: " . mysqli_error($conexion) . "</div><br>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador - Modificar Datos</title>
    <!-- Agregar los enlaces de Bootstrap -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <h2 class="text-center">Modificar Datos</h2>

        <?php
        // Mostrar las tablas y sus acciones
        foreach ($tablas as $tabla) {
            mostrarTablaConAcciones($conexion, $tabla);
            // Mostrar botón para añadir datos a la tabla
            echo "<div class='text-center'>
                    <form method='get' action='insertar.php' class='d-inline-block'>
                        <input type='hidden' name='tabla' value='$tabla'>
                        <button type='submit' class='btn btn-success mb-3'>Añadir registro a $tabla</button>
                    </form>
                </div><br>";
        }
        ?>

        <div class="text-center mt-3">
            <a href="admin.php">
                <button type="button" class="btn btn-secondary">Volver a Admin</button>
            </a>
        </div>
    </div>

    <!-- Agregar los scripts de Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
mysqli_close($conexion);
?>
