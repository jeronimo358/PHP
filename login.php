<?php
session_start();

// Conectar a la base de datos
$servidor = "localhost:3307";
$usuario = "root";       // Usuario (por defecto root en XAMPP)
$contrasena = "";  
$nombre_base_datos = "hoteldb";

$conexion = new mysqli($servidor, $usuario, $contrasena, $nombre_base_datos);

// Verificar la conexion
if ($conexion->connect_error) {
    die("Connection failed: " . $conexion->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $contrasena = $_POST['contrasena'];

    // Consultar si el usuario existe en la tabla Usuarios
    $sql = "SELECT * FROM Usuarios WHERE Email = ?";  // Usar prepared statement para evitar inyeccion SQL
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('s', $email);  // Vincular el parametro
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();

        // Verificar si la contrasena es correcta
        if ($contrasena == $usuario['Contrasena']) { // Verificacion de la contrasena hasheada
            // Guardar los datos en la sesion
            $_SESSION['ID_usuario'] = $usuario['ID_usuario'];
            $_SESSION['Nombre'] = $usuario['Nombre'];
            $_SESSION['Admin'] = $usuario['Admin'];

            // Redirigir al cliente o admin segun corresponda
            if ($usuario['Admin'] == 'Si') {
                header("Location: admin.php"); // Redirige a la pagina de administrador
                exit();
            } else {
                header("Location: cliente.php"); // Redirige a la pagina del cliente
                exit();
            }
        } else {
            // Contrasena incorrecta
            echo "<p style='color:red;'>Contrasena incorrecta. Por favor intente nuevamente.</p>";
        }
    } else {
        // Usuario no encontrado, redirigir a registro.php
        echo "<p style='color:red;'>Usuario no encontrado. Si no tienes cuenta, <a href='registro.php'>registrate aqui</a>.</p>";
    }

    $stmt->close();
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h2>Iniciar sesion</h2>
    <form method="POST" action="">
        <label for="email">Correo electronico:</label><br>
        <input type="email" id="email" name="email" required><br><br>
        <label for="contrasena">Contrasena:</label><br>
        <input type="password" id="contrasena" name="contrasena" required><br><br>
        <input type="submit" value="Iniciar sesion">
    </form>

    <p>¿No tienes cuenta? <a href="registro.php">Registrate aqui</a></p>
</body>
</html>
