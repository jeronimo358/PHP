<?php 
session_start();
include("conexion.php");

// Verificar que el usuario esté logueado y sea admin
if (!isset($_SESSION['Email']) || $_SESSION['Admin'] != 'Si') {
    header("Location: login.php");
    exit();
}

$tabla = $_GET['tabla'] ?? null; // Obtiene el valor del parámetro tabladesde la URL ( insertar.php?tabla=nombre_tabla).
                                 // Si no existe, la variable $tablaserá null(operador ??).

if ($_SERVER["REQUEST_METHOD"] == "POST" && $tabla) { // Verifica que el formulario se haya enviado con POST
    // Recoger los datos enviados por el formulario
    $campos = array_keys($_POST); // devuelve el array con las key
    $valores = array_values($_POST); // devuelve un array con los valores de un array asociativo

    // Hash de la contraseña si la tabla es Usuarios y existe el campo Contraseña
    if ($tabla === 'Usuarios' && in_array('Contrasena', $campos)) { // nos permite verificar el contenido de un array
        $indiceContraseña = array_search('Contrasena', $campos); // busca un valor en un array y devuelve la clave de la primera coincidencia
        $valores[$indiceContraseña] = password_hash($valores[$indiceContraseña], PASSWORD_DEFAULT);
    }

    // Preparar la consulta de inserción
    $columnas = implode(", ", $campos); // convierte los nombres de las columnas en una cadena separada por comas
    $valores = "'" . implode("', '", $valores) . "'"; // Convierte los valores en una cadena con comillas simples

    $sql_insertar = "INSERT INTO $tabla ($columnas) VALUES ($valores)"; // inserta los datos que ha generado con lo de arriba

    // ejecuta la consulta y muestra el mensaje segun lo que haya pasado
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
  <title>Añadir Datos a la Tabla</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <h2 class="text-center">Añadir Datos a la Tabla: <?php echo htmlspecialchars($tabla); ?></h2>
    <form method="POST" action="insertar.php?tabla=<?php echo htmlspecialchars($tabla); ?>" class="mt-4">
      <?php
      // Obtener la estructura de la tabla
      $sql = "DESCRIBE $tabla"; 
      $result = mysqli_query($conexion, $sql);
      while ($row = mysqli_fetch_assoc($result)) { // obtiene una fila de resultado de consulta de forma array asociado
          // Evitar el campo autoincremental hace dinamico los campos
          if ($row['Key'] != 'PRI') {
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
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
