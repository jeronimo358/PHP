<?php
session_start();
include("conexion.php");

// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION['Email']) || $_SESSION['Admin'] != 'Si') {
    header("Location: login.php");
    exit();
}

$tabla = $_GET['tabla'] ?? null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && $tabla) {
    // Obtener los datos del formulario
    $campos = array_keys($_POST);
    $valores = array_values($_POST);

    // Preparar la consulta de inserción
    $columnas = implode(", ", $campos);
    $valores = "'" . implode("', '", $valores) . "'";

    $sql_insertar = "INSERT INTO $tabla ($columnas) VALUES ($valores)";

    if (mysqli_query($conexion, $sql_insertar)) {
        echo "<div class='alert alert-success text-center'>Nuevo registro insertado exitosamente en la tabla $tabla.</div>";
    } else {
        echo "<div class='alert alert-danger text-center'>Error al insertar registro: " . mysqli_error($conexion) . "</div>";
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir Datos a la Tabla</title>
    <!-- Agregar los enlaces de Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center">Añadir Datos a la Tabla: <?php echo htmlspecialchars($tabla); ?></h2>

        <form method="POST" action="insertar.php?tabla=<?php echo htmlspecialchars($tabla); ?>" class="mt-4">
            <?php
            // Obtener las columnas de la tabla
            $sql = "DESCRIBE $tabla";
            $result = mysqli_query($conexion, $sql);
            while ($row = mysqli_fetch_assoc($result)) {
                if ($row['Field'] != "ID_" . strtolower($tabla)) {  // Evitar campos autoincrementales
                    echo "<div class='mb-3'>";
                    echo "<label class='form-label'>" . ucfirst($row['Field']) . ":</label><br>";
                    echo "<input type='text' class='form-control' name='" . $row['Field'] . "' required><br><br>";
                    echo "</div>";
                }
            }
            ?>
            <div class="text-center">
                <button type="submit" class="btn btn-primary">Añadir</button>
            </div>
        </form>

        <div class="text-center mt-4">
            <a href="modificardatos.php" class="btn btn-secondary">Volver a Modificar Datos</a>
        </div>
    </div>

    <!-- Agregar los scripts de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
