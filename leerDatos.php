<?php
// Incluir el archivo de conexión
include('conexion.php'); // Asegúrate de que el archivo 'conexion.php' esté en el mismo directorio o ajusta la ruta

// Crear conexión
$conn = new mysqli($servidor, $usuario, $contrasena, $nombre_base_datos);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Función para mostrar los resultados
function mostrarDatos($conn, $query, $tabla) {
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        echo "<h2>Datos de la tabla: $tabla</h2>";
        echo "<table border='1'><tr>";
        
        // Obtener los nombres de las columnas
        $fieldInfo = $result->fetch_fields();
        foreach ($fieldInfo as $val) {
            echo "<th>" . $val->name . "</th>";
        }
        echo "</tr>";

        // Mostrar los datos
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $column) {
                echo "<td>" . $column . "</td>";
            }
            echo "</tr>";
        }
        echo "</table><br>";
    } else {
        echo "0 resultados para la tabla: $tabla<br>";
    }
}

// Consultas para obtener los datos de las tablas
$queries = [
    "Usuarios" => "SELECT * FROM Usuarios",
    "Habitaciones" => "SELECT * FROM Habitaciones",
    "Reservas" => "SELECT * FROM Reservas",
    "Servicios" => "SELECT * FROM Servicios",
    "Servicios_Reservas" => "SELECT * FROM Servicios_Reservas",
    "Trabajadores" => "SELECT * FROM Trabajadores"
];

// Ejecutar las consultas y mostrar los datos
foreach ($queries as $tabla => $query) {
    mostrarDatos($conn, $query, $tabla);
}

// Cerrar la conexión
$conn->close();
?>