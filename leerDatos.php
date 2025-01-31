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
echo "
<h2> Listado de estudiantes </h2>

<table border='1'>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Edad</th>
            <th>Curso</th>
            <th> Promociona </th>
        </tr>";

// Recorrer cada fila de resultados y mostrarla
while ($row = mysqli_fetch_assoc($resultado)) {
    $nombre= $row['nombre'];
    if ($nombre != "Ana"){
        echo "<tr>
                <td>" . $row['id'] . "</td>
                <td>" . $nombre. "</td>
                <td>" . $row['edad'] . "</td>
                <td>" . $row['curso'] . "</td>
                <td>" . $row['promociona'] . "</td>
            </tr>";
    }
}

// Cerrar la tabla HTML
echo "</table>";


// Mostrar los menores de 20 años en formato tabla
// OPCION 1. FILTRAR EN LA CONSULTA.
$query = "SELECT id, nombre, edad, curso, promociona FROM alumnos
WHERE edad >= 20";
$resultadoFiltrado = mysqli_query($conexion, $query);
echo "
<h2> Listado de estudiantes fistrado por edad > 20</h2>

<table border='1'>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Edad</th>
            <th>Curso</th>
            <th> Promociona </th>
        </tr>";

// Recorrer cada fila de resultados y mostrarla
while ($row = mysqli_fetch_assoc($resultadoFiltrado)) {
    echo "<tr>
            <td>" . $row['id'] . "</td>
            <td>" . $row['nombre'] . "</td>
            <td>" . $row['edad']. "</td>
            <td>" . $row['curso'] . "</td>
            <td>" . $row['promociona'] . "</td>
        </tr>";
    
}

// Cerrar la tabla HTML
echo "</table>";

// OPCION 2. FILTRAR EN LOS RESULTADOS DE LA CONSULTA
$query = "SELECT id, nombre, edad, curso, promociona FROM alumnos";
$resultadoFiltrado2 = mysqli_query($conexion, $query);
echo "
<h2> Listado de estudiantes filtrado por edad >20. Opcion 2 </h2>

<table border='1'>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Edad</th>
            <th>Curso</th>
            <th> Promociona </th>
        </tr>";

// Recorrer cada fila de resultados y mostrarla
while ($row = mysqli_fetch_assoc($resultadoFiltrado2)) {
    $edad = $row['edad'];
    if($edad >= 20){
        echo "<tr>
                <td>" . $row['id'] . "</td>
                <td>" . $row['nombre'] . "</td>
                <td>" . $row['edad']. "</td>
                <td>" . $row['curso'] . "</td>
                <td>" . $row['promociona'] . "</td>
            </tr>";
    }
    
}

// Cerrar la tabla HTML
echo "</table>";

// Cerrar la conexión a la base de datos (opcional si no lo necesitas aquí)
// mysqli_close($conexion);
?>