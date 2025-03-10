<?php
session_start();
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
     $apellido = $_POST['apellido'];
    $email = $_POST['email'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];
    $contrasena = $_POST['contrasena'];

        // Verificar si el email ya está registrado
    $sql_verificar_email = "SELECT * FROM Usuarios WHERE Email = '$email'";
    $result_verificar_email = mysqli_query($conexion, $sql_verificar_email);

    if (mysqli_num_rows($result_verificar_email) == 0) {
        // Cifrar la contraseña usando password_hash 
    $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);
        // Insertar nuevo usuario con la contraseña cifrada
        $sql_registrar = "INSERT INTO Usuarios (Nombre, Apellido, Email, Telefono, Direccion, Contraseña) VALUES ('$nombre', '$apellido', '$email', '$telefono', '$direccion', '$hashed_password')";
    if (mysqli_query($conexion, $sql_registrar)) {
             echo "<div class='alert alert-success text-center'>Registro exitoso. Ahora puedes <a href='login.php'>iniciar sesión</a>.</div>";
         } else {
            echo "<div class='alert alert-danger text-center'>Error: " . $sql_registrar . "<br>" . mysqli_error($conexion) . "</div>";
        }
    } else {
        echo "<div class='alert alert-warning text-center'>Este email ya está registrado. <a href='login.php'>Inicia sesión aquí</a>.</div>";
     }
}

mysqli_close($conexion);
?>

<!DOCTYPE html>
<html lang="es">
<head>
     <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Registro de Usuario</title>
    <!-- Enlace a Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card shadow p-4" style="width: 400px;">
             <h2 class="text-center mb-4">Registro de Usuario</h2>

         <!-- Formulario de registro -->
             <form method="post" action="registrar.php">
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre:</label>
                     <input type="text" id="nombre" name="nombre" class="form-control" required>
                 </div>

                <div class="mb-3">
                 <label for="apellido" class="form-label">Apellido:</label>
                     <input type="text" id="apellido" name="apellido" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                     <input type="email" id="email" name="email" class="form-control" required>
                </div>

                 <div class="mb-3">
                    <label for="telefono" class="form-label">Teléfono:</label>
                     <input type="text" id="telefono" name="telefono" class="form-control" required>
                 </div>

                <div class="mb-3">
                    <label for="direccion" class="form-label">Dirección:</label>
                     <input type="text" id="direccion" name="direccion" class="form-control" required>
                 </div>

                <div class="mb-3">
                    <label for="contrasena" class="form-label">Contraseña:</label>
                     <input type="password" id="contrasena" name="contrasena" class="form-control" required>
                </div>

                 <div class="text-center">
                     <button type="submit" class="btn btn-primary w-100">Registrarse</button>
                 </div>
             </form>

         <div class="text-center mt-3">
                 <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
         </div>
        </div>
    </div>

     <!-- Enlace a los scripts de Bootstrap -->
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

