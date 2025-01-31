<?php
// Incluir el archivo de conexión (si es necesario)
include('conexion.php');

// Mediante el formulario de  introducirDatos.php por método POST, recibo:
    // name="nombre"
    // name="edad"
    // name="curso"
    // name="promociona"

// Compruebo que estas variables existen y ademas no están vacias.

if ($_SERVER['REQUEST_METHOD'] == 'POST') { // Compruebo que entrado en introducir.php por llamada POST
    
    // Comprobación del nombre
    if (isset($_POST['nombre']) && !empty($_POST['nombre'])) {
        $nombre = $_POST['nombre'];
    // Comprobación de la edad
    if (isset($_POST['edad']) && !empty($_POST['edad'])) {
    $edad = $_POST['edad'];
    // Comprobación del curso
    if (isset($_POST['curso']) && !empty($_POST['curso'])) {
        $curso = $_POST['curso'];
    // Comprobación del promociona
    if (isset($_POST['promociona']) && !empty($_POST['promociona'])) {
        $promociona = $_POST['promociona'];

        // todas las variables se han recibido correctamente, por lo que voy a lanzar
        // una consulta de insert into
        $sql = "INSERT INTO alumnos (id, nombre, edad, curso, promociona) VALUES (NULL, '$nombre', '$edad', '$curso', '$promociona') ;";
        $resultado = mysqli_query($conexion, $sql);



    }}}}

    

}
    

?>