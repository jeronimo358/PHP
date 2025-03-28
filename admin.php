<?php
session_start();
include("conexion.php");

// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION['Email']) || $_SESSION['Admin'] != 'Si') {
    header("Location: login.php");
    exit();
}

$tablas = ["Usuarios", "Habitaciones", "Reservas", "Servicios", "Servicios_Reservas", "Trabajadores"];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador - Gestión del Hotel</title>
    <!-- Agregar los enlaces de Bootstrap -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center">Bienvenido, Administrador <?php echo $_SESSION['Nombre'] . " " . $_SESSION['Apellido']; ?></h2>
        <h3 class="text-center my-4">Visualización de Tablas</h3>

        <?php
        foreach ($tablas as $tabla) {
            $sql = "SELECT * FROM $tabla";
            $result = mysqli_query($conexion, $sql);

            if (mysqli_num_rows($result) > 0) {
                echo "<h3>Tabla: $tabla</h3>";
                echo "<table class='table table-bordered table-striped'><thead><tr>";

                // Obtener nombres de columnas de manera procedimental
                $columnas = array();
                if ($row = mysqli_fetch_assoc($result)) { // obtener fila de resultados de consulta en forma de array asociado
                    $columnas = array_keys($row); // deuelve el array con las keys
                    foreach ($columnas as $columna) {
                        echo "<th>" . $columna . "</th>";
                    }
                    echo "</tr></thead><tbody>";

                    // Reiniciar puntero para mostrar todas las filas
                    mysqli_data_seek($result, 0); // mueve el puntero interno de una consulta a una fila especifica

                    // Obtener datos de filas
                    while ($row = mysqli_fetch_assoc($result)) { // obtener fila de resultados de consulta en forma de array asociado
                        echo "<tr>";
                        foreach ($columnas as $columna) {
                            echo "<td>" . $row[$columna] . "</td>";
                        }
                        echo "</tr>";
                    }
                }
                echo "</tbody></table><br>";
            } else {
                echo "<div class='alert alert-warning'>No se encontraron datos en la tabla $tabla.</div><br>";
            }
        }
        ?>

        <div class="d-flex justify-content-center">
            <form method="post" action="modificardatos.php">
                <button type="submit" class="btn btn-warning mb-3 mr-3">Modificar Datos</button>
            </form>
            <form method="post" action="reservaadmin.php">
                <button type="submit" class="btn btn-primary mb-3">Hacer Reserva</button>
            </form>
        </div>

        <!-- Botón para volver al login -->
        <div class="text-center mt-3">
            <a href="login.php">
                <button type="button" class="btn btn-secondary">Volver al Login</button>
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
