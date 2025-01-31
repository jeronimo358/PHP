<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opciones - Alumnos</title>
    <!-- Link al CSS de Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
</head>
<body>
    <div class="container mt-5">
        <h1>Opciones de Lectura de Datos</h1>
        
        <!-- Sección Leer Datos -->
        <div class="card my-4">
            <div class="card-header">
                <h3>Leer Datos</h3>
            </div>
            <div class="card-body">
                <!-- Botón Alumnos -->
                <div class="mb-3">
                    <a href="leerTodos.php" class="btn btn-primary">Alumnos</a>
                </div>
                
                <!-- Formulario Ver Alumnos -->
                <form action="leerFiltro.php" method="POST">
                    <div class="mb-3">
                        <label for="nombreAlumno" class="form-label">Ver alumnos cuyo nombre sea:</label>
                        <input type="text" class="form-control" id="nombreAlumno" name="nombre" placeholder="Introduce el nombre del alumno" required>
                    </div>
                    <button type="submit" class="btn btn-success">Ver</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts de Bootstrap -->
     <!-- Agregar el script de Bootstrap 5 desde el CDN al final del body -->
     <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>