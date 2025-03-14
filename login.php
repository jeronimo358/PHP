<?php
session_start();
include("conexion.php");

// Inicializar variables para los campos del formulario
$email = '';
$password = '';

// Comprobar si hay cookis almacenadas
if (isset($_COOKIE['email']) && isset($_COOKIE['password'])) {
    $email = $_COOKIE['email'];
    $password = $_COOKIE['password'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") { //Procesa el formulario cuando se envia que lo que se envia son los datos de abajo
    $email = $_POST['email'];
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? $_POST['remember'] : '';

    // Consulta preparada para evitar inyección SQL
    $sql = "SELECT * FROM usuarios WHERE Email = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email); //sustituye ? por el email
    mysqli_stmt_execute($stmt); // se ejecuta
    $result = mysqli_stmt_get_result($stmt); // se obtiene el resultado

    if (mysqli_num_rows($result) == 1) { // verifica que exista el usuario
        $row = mysqli_fetch_assoc($result); // obtiene lo datos
        // Verificar la contraseña usando password_verify
        if (password_verify($password, $row['Contrasena'])) {
            $_SESSION['Nombre'] = $row['Nombre']; // accede al valor de la columna Nombreen una fila obtenida de una consulta a la base de datos.
            $_SESSION['Apellido'] = $row['Apellido'];
            $_SESSION['Email'] = $row['Email'];
            $_SESSION['Admin'] = $row['Admin'];

            // Guardar cookies si se selecciona "Recuérdame"
            if ($remember === 'on') {
                setcookie('email', $email, time() + (86400 * 30), "/"); // Cookie válida por 30 días
                setcookie('password', $password, time() + (86400 * 30), "/"); // Cookie válida por 30 días
            }

            if ($row['Admin'] == 'Si') {
                header("Location: admin.php");
            } else {
                header("Location: cliente.php");
            }
            exit();
        } else {
            $error = "Email o contraseña incorrectos";
        }
    } else {
        $error = "Email o contraseña incorrectos";
    }
}

mysqli_close($conexion);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
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
          <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Contraseña:</label>
          <input type="password" name="password" id="password" class="form-control" value="<?php echo htmlspecialchars($password); ?>" required>
        </div>
        <div class="mb-3 form-check">
          <input type="checkbox" name="remember" id="remember" class="form-check-input" <?php if(isset($_COOKIE['email'])) echo 'checked'; ?>>
          <label for="remember" class="form-check-label">Recuérdame</label>
        </div>
        <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
      </form>
      <p class="text-center mt-3">¿No tienes una cuenta? <a href="registrar.php">Regístrate aquí</a></p>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
