<?php
session_start();
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
     $email = $_POST['email'];
     $password = $_POST['password'];
     
     // Usar consultas preparadas para evitar SQL injection
    $sql = "SELECT * FROM Usuarios WHERE Email = ?";
     $stmt = mysqli_prepare($conexion, $sql);
     
     // Vincular parámetros y ejecutar la consulta
     mysqli_stmt_bind_param($stmt, 's', $email);
     mysqli_stmt_execute($stmt);
     $result = mysqli_stmt_get_result($stmt);

     if (mysqli_num_rows($result) == 1) {
         $row = mysqli_fetch_assoc($result);
         
    // Verificar la contraseña usando password_verify
    if (password_verify($password, $row['Contrasena'])) {
             $_SESSION['Nombre'] = $row['Nombre'];
             $_SESSION['Apellido'] = $row['Apellido'];
             $_SESSION['Email'] = $row['Email'];
             $_SESSION['Admin'] = $row['Admin']; 

     if ($row['Admin'] == 'Si') {
                 header("Location: admin.php");
             } else {
                 header("Location: cliente.php");
             }
             exit();
         } else {
         $error = "Email o contrasena incorrectos";
         }
     } else {
        $error = "Email o contrasena incorrectos";
     }
}

mysqli_close($conexion);
?>

<!DOCTYPE html>
<html lang="es">
<head>
     <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center vh-100">
         <div class="card shadow p-4" style="width: 400px;">
             <h2 class="text-center">Iniciar Sesión</h2>
             <?php if (isset($error)) { ?>
                 <div class="alert alert-danger text-center"><?php echo $error; ?></div>
             <?php } ?>
             <form method="post" action="login.php">
             <div class="mb-3">
                 <label for="email" class="form-label">Email:</label>
                 <input type="email" id="email" name="email" class="form-control" required>
                </div>
                 <div class="mb-3">
                <label for="password" class="form-label">Contraseña:</label>
                 <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
            </form>
            <p class="text-center mt-3">¿No tienes una cuenta? <a href="registrar.php">Regístrate aquí</a></p>
         </div>
     </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
