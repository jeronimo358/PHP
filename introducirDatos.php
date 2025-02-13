<?php
// Incluir el archivo de conexión (si es necesario)
include('conexion.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Introducir Alumno</title>
    
    <!-- Link al CSS de Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>
<body>

<div class="container mt-5">
<h2 class="mb-4">Agregar Alumno</h2>
<form action="introducir.php" method="POST">
    <div class="form-group">
        <label for="nombre">Nombre:</label>
        <input type="text" class="form-control" id="nombre" name="nombre" required>
    </div>
    
    <div class="form-group">
        <label for="edad">Edad:</label>
        <input type="number" class="form-control" id="edad" name="edad" required>
    </div>
    
    <div class="form-group">
        <label for="curso">Curso:</label>
        <select class="form-control" id="curso" name="curso" required>
            <option value="ASIR2">ASIR2</option>
            <option value="ASIR1">ASIR1</option>
            <option value="DAW1">DAW1</option>
            <option value="DAM1">DAM1</option>
            <option value="DAW2">DAW2</option>
            <option value="DAM2">DAM2</option>
        </select>
    </div>
    
    <div class="form-group">
        <label>Promociona:</label><br>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" id="promociona1" name="promociona" value="1" required>
            <label class="form-check-label" for="promociona1">Sí</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" id="promociona0" name="promociona" value="0" required>
            <label class="form-check-label" for="promociona0">No</label>
        </div>
    </div>
    
    <button type="submit" class="btn btn-primary">Insertar Alumno</button>
</form>
</div>

<!-- Scripts de Bootstrap -->
     <!-- Agregar el script de Bootstrap 5 desde el CDN al final del body -->
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>