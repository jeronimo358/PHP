<?php
session_start();

// Conectar a la base de datos
$servidor = "localhost:3307";
$usuario = "root";       // Usuario (por defecto root en XAMPP)
$contrasena = "";  
$nombre_base_datos = "hoteldb";

$conexion = new mysqli($servidor, $usuario, $contrasena, $nombre_base_datos);

// Verificar la conexión
if ($conexion->connect_error) {
    die("Connection failed: " . $conexion->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $contraseña = $_POST['contraseña'];

    // Consultar si el usuario existe en la tabla Usuarios
    $sql = "SELECT * FROM Usuarios WHERE Email = ?";  // Usar prepared statement para evitar inyección SQL
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('s', $email);  // Vincular el parámetro
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();

        // Verificar si la contraseña es correcta
        if ($contraseña == $usuario['Contraseña']) { // Verificación de la contraseña hasheada
            // Guardar los datos en la sesión
            $_SESSION['ID_usuario'] = $usuario['ID_usuario'];
            $_SESSION['Nombre'] = $usuario['Nombre'];
            $_SESSION['Admin'] = $usuario['Admin'];

            // Redirigir al cliente o admin según corresponda
            if ($usuario['Admin'] == 'Sí') {
                header("Location: admin.php"); // Redirige a la página de administrador
                exit();
            } else {
                header("Location: cliente.php"); // Redirige a la página del cliente
                exit();
            }
        } else {
            // Contraseña incorrecta
            echo "<p style='color:red;'>Contraseña incorrecta. Por favor intente nuevamente.</p>";
        }
    } else {
        // Usuario no encontrado, redirigir a registro.php
        echo "<p style='color:red;'>Usuario no encontrado. Si no tienes cuenta, <a href='registro.php'>regístrate aquí</a>.</p>";
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
    <h2>Iniciar sesión</h2>
    <form method="POST" action="">
        <label for="email">Correo electrónico:</label><br>
        <input type="email" id="email" name="email" required><br><br>
        <label for="contraseña">Contraseña:</label><br>
        <input type="password" id="contraseña" name="contraseña" required><br><br>
        <input type="submit" value="Iniciar sesión">
    </form>

    <p>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
</body>
</html>
