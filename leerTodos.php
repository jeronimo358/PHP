<?php
// Incluir el archivo de conexión
include('conexion.php'); // Asegúrate de que el archivo 'conexion.php' esté en el mismo directorio o ajusta la ruta

// Consulta para obtener los datos de la tabla "alumnos"
$query = "SELECT id, nombre, edad, curso, promociona FROM alumnos";
$resultado = mysqli_query($conexion, $query);

// Verificar si la consulta fue exitosa
if (!$resultado) {
    die("Error en la consulta: " . mysqli_error($conexion));
}

// Mostrar los resultados en formato de tabla
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Estudiantes</title>
    
    <!-- Link al CSS de Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2 class="text-center mb-4">Listado de Estudiantes</h2>

        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Edad</th>
                    <th>Curso</th>
                    <th>Promociona</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Recorrer cada fila de resultados y mostrarla
                while ($row = mysqli_fetch_assoc($resultado)) {
                    echo "<tr>
                            <td>" . $row['id'] . "</td>
                            <td>" . $row['nombre'] . "</td>
                            <td>" . $row['edad'] . "</td>
                            <td>" . $row['curso'] . "</td>
                            <td>" . $row['promociona'] . "</td>
                        </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Scripts de Bootstrap -->
     <!-- Agregar el script de Bootstrap 5 desde el CDN al final del body -->
     <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>