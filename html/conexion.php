<?php
// Datos de conexión a la base de datos
$servername = "localhost"; // Cambia localhost si tu servidor de base de datos está en otro lugar
$username = "root"; // Utiliza el usuario del sistema operativo que normalmente usas con el comando sudo mysql
$password = "usuari"; // Deja vacío ya que no necesitas contraseña
$database = "recuperacion"; // Nombre de la base de datos

// Crear conexión
$conn = new mysqli($servername, $username, $password, $database);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}


?>
