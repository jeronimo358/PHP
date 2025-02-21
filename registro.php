<?php
session_start();

// Conectar a la base de datos
$servidor = "localhost:3307";
$usuario = "root";
$contrasena = "";
$nombre_base_datos = "hoteldb";

$conexion = new mysqli($servidor, $usuario, $contrasena, $nombre_base_datos);

// Verificar la conexión
if ($conexion->connect_error) {
    die("Connection failed: " . $conexion->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $email = $_POST['email'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];
    $contraseña = $_POST['contraseña'];

    // Insertar nuevo usuario con 'admin' en 'No' por defecto
    $sql = "INSERT INTO Usuarios (Nombre, Apellido, Email, Teléfono, Dirección, Admin, Contraseña) 
            VALUES ('$nombre', '$apellido', '$email', '$telefono', '$direccion', 'No', '$contraseña')";

    if ($conexion->query($sql) === TRUE) {
        echo "Nuevo usuario registrado exitosamente. Puedes iniciar sesión.";
        header("Location: login.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conexion->error;
    }
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
</head>
<body>
    <h2>Registro de nuevo usuario</h2>
    <form method="POST" action="">
        <label for="nombre">Nombre:</label><br>
        <input type="text" id="nombre" name="nombre" required><br><br>
        
        <label for="apellido">Apellido:</label><br>
        <input type="text" id="apellido" name="apellido" required><br><br>
        
        <label for="email">Correo electrónico:</label><br>
        <input type="email" id="email" name="email" required><br><br>
        
        <label for="telefono">Teléfono:</label><br>
        <input type="text" id="telefono" name="telefono" required><br><br>
        
        <label for="direccion">Dirección:</label><br>
        <input type="text" id="direccion" name="direccion" required><br><br>
        
        <label for="contraseña">Contraseña:</label><br>
        <input type="password" id="contraseña" name="contraseña" required><br><br>
        
        <input type="submit" value="Registrar">
    </form>
</body>
</html>

