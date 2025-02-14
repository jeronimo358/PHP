<?php
session_start();

// Conectar a la base de datos
$servidor = "localhost:3307";
$usuario = "root";       // Usuario (por defecto root en XAMPP)
$contrasena = "";  
$nombre_base_datos = "hoteldb";

$conn = new mysqli($servidor, $usuario, $contrasena, $nombre_base_datos);

// Verificar la conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $contraseña = $_POST['contraseña'];

    // Consultar si el usuario existe en la tabla Usuarios
    $sql = "SELECT * FROM Usuarios WHERE Email = '$email' AND Contraseña = '$contraseña'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Usuario encontrado
        $user = $result->fetch_assoc();

        // Verificar si es admin o cliente
        if ($user['Admin'] == 'Sí') {
            // Redirigir a la página de admin
            header("Location: admin.php");
            exit();
        } else {
            // Redirigir a la página de cliente
            header("Location: cliente.php");
            exit();
        }
    } else {
        // Usuario no encontrado, redirigir a registro
        header("Location: registro.php");
        exit();
    }
}

$conn->close();
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
</body>
</html>
